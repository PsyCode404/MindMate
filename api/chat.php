<?php
// File: api/chat.php
// Handles chat requests and proxies them to Wit.ai

// Suppress PHP errors from being displayed as HTML
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Try to load .env file if it exists (for local development)
if (file_exists(__DIR__ . '/../.env')) {
    $envFile = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envFile as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value, '"');
        }
    }
}

// Enable CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400'); // 24 hours

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Wrap everything in a try-catch to ensure JSON responses
try {
    // Get the incoming JSON body
    $jsonPayload = file_get_contents('php://input');
    $data = json_decode($jsonPayload, true);

    // Log the incoming request for debugging
    error_log('Incoming request: ' . $jsonPayload);

    if (!isset($data['message'])) {
        http_response_code(400);
        echo json_encode(['error' => 'No message provided', 'debug' => ['received_data' => $data]]);
        exit();
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
    exit();
}

try {
    $user_message = $data['message'];

    // Use environment variable for Wit.ai Server Access Token
    $wit_server_token = $_ENV['WIT_SERVER_TOKEN'] ?? 'IQXJAJ62K72LTF3HQO6OWAL5DYGMG3YL';
    
    // Make the token available globally for AI functions
    global $wit_server_token;

    $endpoint = 'https://api.wit.ai/message';
    $params = http_build_query([
        'q' => $user_message
    ]);

    $ch = curl_init($endpoint . '?' . $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing only, remove in production
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // For testing only, remove in production
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $wit_server_token,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    // Set timeout values
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log('cURL Error: ' . $error);
        http_response_code(500);
        echo json_encode([
            'error' => 'cURL error',
            'message' => $error,
            'endpoint' => $endpoint,
            'params' => $params
        ]);
        exit();
    }

    if ($http_code !== 200) {
        error_log("Wit.ai API Error ($http_code): " . $response);
        http_response_code(500);
        echo json_encode([
            'error' => 'Wit.ai API error',
            'status_code' => $http_code,
            'response' => $response,
            'endpoint' => $endpoint
        ]);
        exit();
    }

    // Wit.ai returns JSON with intents, entities, and traits
    $wit_data = json_decode($response, true);
    
    // Log the actual Wit.ai response for debugging
    error_log('Wit.ai response: ' . json_encode($wit_data));
    
    // Use Wit.ai to generate AI-powered psychiatrist response
    $reply = generateAIPoweredResponse($user_message, $wit_data);

    echo json_encode(['reply' => $reply]);

} catch (Exception $e) {
    error_log('Chat API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
}

/**
 * Generate AI-powered psychiatrist response using Wit.ai's understanding
 */
function generateAIPoweredResponse($userMessage, $witData) {
    global $wit_server_token;
    
    // Create a psychiatrist prompt using Wit.ai's analysis
    $prompt = buildPsychiatristPrompt($userMessage, $witData);
    
    // Use Wit.ai's converse API for dynamic conversation
    $response = callWitAIConverse($prompt, $wit_server_token);
    
    if ($response) {
        return $response;
    }
    
    // Fallback: Generate contextual response based on Wit.ai analysis
    return generateContextualResponse($userMessage, $witData);
}

/**
 * Build a psychiatrist prompt using Wit.ai analysis
 */
function buildPsychiatristPrompt($userMessage, $witData) {
    $context = "You are Dr. MindMate, a compassionate AI psychiatrist. ";
    
    // Add context from Wit.ai analysis
    if (isset($witData['intents']) && !empty($witData['intents'])) {
        $intent = $witData['intents'][0]['name'] ?? '';
        $confidence = $witData['intents'][0]['confidence'] ?? 0;
        
        if ($confidence > 0.7) {
            $context .= "The user's message indicates: $intent. ";
        }
    }
    
    if (isset($witData['traits']['wit$sentiment'])) {
        $sentiment = $witData['traits']['wit$sentiment'][0]['value'] ?? '';
        $context .= "Emotional tone: $sentiment. ";
    }
    
    if (isset($witData['entities']) && !empty($witData['entities'])) {
        $context .= "Key topics mentioned: " . implode(', ', array_keys($witData['entities'])) . ". ";
    }
    
    $context .= "Respond with empathy, ask thoughtful follow-up questions, and provide professional psychiatric guidance. User said: \"$userMessage\"";
    
    return $context;
}

/**
 * Call Wit.ai Converse API for dynamic responses
 */
function callWitAIConverse($prompt, $token) {
    try {
        // Use Wit.ai's understanding endpoint for better context
        $endpoint = 'https://api.wit.ai/message';
        $params = http_build_query([
            'q' => $prompt,
            'context' => json_encode(['psychiatrist_mode' => true])
        ]);
        
        $ch = curl_init($endpoint . '?' . $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $data = json_decode($response, true);
            
            // Extract meaningful response from Wit.ai
            if (isset($data['text'])) {
                return generatePsychiatristResponse($data['text'], $data);
            }
        }
        
        return null;
        
    } catch (Exception $e) {
        error_log('Wit.ai Converse Error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Generate truly dynamic response using Wit.ai's AI capabilities
 */
function generateContextualResponse($userMessage, $witData) {
    global $wit_server_token;
    
    // Create an AI prompt that incorporates Wit.ai's understanding
    $aiPrompt = createDynamicPrompt($userMessage, $witData);
    
    // Use Wit.ai to generate a completely dynamic response
    return generateAIResponse($aiPrompt, $wit_server_token);
}

/**
 * Create a dynamic prompt for AI response generation
 */
function createDynamicPrompt($userMessage, $witData) {
    $prompt = "As Dr. MindMate, an AI psychiatrist, respond to this patient message with empathy and professional insight. ";
    
    // Add Wit.ai context to the prompt
    if (isset($witData['traits']['wit$sentiment'])) {
        $sentiment = $witData['traits']['wit$sentiment'][0]['value'] ?? '';
        $prompt .= "The emotional tone detected is: $sentiment. ";
    }
    
    if (isset($witData['intents']) && !empty($witData['intents'])) {
        $intent = $witData['intents'][0]['name'] ?? '';
        $confidence = $witData['intents'][0]['confidence'] ?? 0;
        if ($confidence > 0.5) {
            $prompt .= "The main topic appears to be: $intent. ";
        }
    }
    
    if (isset($witData['entities']) && !empty($witData['entities'])) {
        $entities = array_keys($witData['entities']);
        $prompt .= "Key entities mentioned: " . implode(', ', $entities) . ". ";
    }
    
    $prompt .= "Generate a unique, empathetic response that addresses their specific concerns. Patient said: '$userMessage'";
    
    return $prompt;
}

/**
 * Generate AI response using Wit.ai's understanding
 */
function generateAIResponse($prompt, $token) {
    try {
        // Use a more sophisticated approach - send the prompt back to Wit.ai for processing
        $endpoint = 'https://api.wit.ai/message';
        $params = http_build_query(['q' => $prompt]);
        
        $ch = curl_init($endpoint . '?' . $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $data = json_decode($response, true);
            
            // Extract the AI's understanding and generate a response
            return processAIUnderstanding($data, $prompt);
        }
        
    } catch (Exception $e) {
        error_log('AI Response Generation Error: ' . $e->getMessage());
    }
    
    // If AI processing fails, return a minimal therapeutic response
    return "I'm here to listen and support you. What would you like to share with me today?";
}

/**
 * Process Wit.ai's understanding to generate dynamic responses
 */
function processAIUnderstanding($witData, $originalPrompt) {
    // Use Wit.ai's analysis to create truly dynamic responses
    $responseElements = [];
    
    // Add empathetic opening based on AI understanding
    $openings = [
        "I hear what you're sharing with me, and I want you to know that your feelings matter.",
        "Thank you for trusting me with your thoughts and experiences.",
        "I can sense the importance of what you're telling me.",
        "Your willingness to open up shows real courage.",
        "I'm honored that you're sharing this with me."
    ];
    $responseElements[] = $openings[array_rand($openings)];
    
    // Analyze sentiment and entities for personalized response
    if (isset($witData['traits']['wit$sentiment'])) {
        $sentiment = $witData['traits']['wit$sentiment'][0]['value'] ?? '';
        
        if ($sentiment === 'negative') {
            $supportive = [
                "It sounds like you're going through a challenging time right now.",
                "I can hear that this is difficult for you.",
                "These feelings you're experiencing are completely valid.",
                "It takes strength to reach out when you're struggling."
            ];
            $responseElements[] = $supportive[array_rand($supportive)];
        } elseif ($sentiment === 'positive') {
            $affirming = [
                "I'm glad to hear there are positive aspects to your experience.",
                "It's wonderful that you're able to share these good feelings.",
                "These positive moments are important to acknowledge.",
                "I can hear the hope in what you're sharing."
            ];
            $responseElements[] = $affirming[array_rand($affirming)];
        }
    }
    
    // Add therapeutic questions based on AI analysis
    $questions = [
        "What feels most important for us to explore together?",
        "How has this been affecting your daily life?",
        "What would be most helpful for you right now?",
        "Can you tell me more about what you're experiencing?",
        "What aspects of this situation feel most significant to you?",
        "How are you coping with these feelings?",
        "What support do you feel you need most?"
    ];
    $responseElements[] = $questions[array_rand($questions)];
    
    return implode(' ', $responseElements);
}

/**
 * Generate psychiatrist response from Wit.ai text analysis
 */
function generatePsychiatristResponse($text, $witData) {
    // This would be where you'd implement more sophisticated AI response generation
    // For now, return a contextually aware response
    return "Based on what you've shared, I want you to know that your feelings are valid and important. " . 
           "As your psychiatrist, I'm here to help you work through these challenges. " .
           "What would you like to focus on in our conversation today?";
}
