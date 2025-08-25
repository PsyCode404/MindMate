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
 * Generate psychiatrist response using intent-driven architecture
 */
function generateAIPoweredResponse($userMessage, $witData) {
    // Step 1: Extract intent, entities, and traits from Wit.ai
    $intent = extractIntent($witData);
    $entities = extractEntities($witData);
    $traits = extractTraits($witData);
    
    // Step 2: Use intent as primary driver for response logic
    if ($intent && $intent['confidence'] >= 0.7) {
        return handleIntent($intent, $entities, $traits, $userMessage);
    }
    
    // Step 3: Fallback for low confidence or no intent
    return handleFallback($userMessage, $witData);
}

/**
 * Extract intent from Wit.ai response
 */
function extractIntent($witData) {
    if (isset($witData['intents']) && !empty($witData['intents'])) {
        return [
            'name' => $witData['intents'][0]['name'] ?? null,
            'confidence' => $witData['intents'][0]['confidence'] ?? 0
        ];
    }
    return null;
}

/**
 * Extract entities from Wit.ai response
 */
function extractEntities($witData) {
    return isset($witData['entities']) ? $witData['entities'] : [];
}

/**
 * Extract traits from Wit.ai response
 */
function extractTraits($witData) {
    return isset($witData['traits']) ? $witData['traits'] : [];
}

/**
 * Handle specific intents with psychiatrist responses
 */
function handleIntent($intent, $entities, $traits, $userMessage) {
    $intentName = $intent['name'];
    
    // Map intents to specific psychiatrist response flows
    switch ($intentName) {
        case 'greet':
        case 'greeting':
            return handleGreeting($entities, $traits);
            
        case 'anxiety':
        case 'anxiety_concern':
            return handleAnxiety($entities, $traits, $userMessage);
            
        case 'depression':
        case 'depression_concern':
            return handleDepression($entities, $traits, $userMessage);
            
        case 'relationship_issue':
        case 'relationship_problem':
            return handleRelationship($entities, $traits, $userMessage);
            
        case 'stress':
        case 'stress_concern':
            return handleStress($entities, $traits, $userMessage);
            
        case 'sleep_issue':
        case 'insomnia':
            return handleSleep($entities, $traits, $userMessage);
            
        case 'emotional_distress':
        case 'mental_health_concern':
            return handleEmotionalDistress($entities, $traits, $userMessage);
            
        case 'gratitude':
        case 'thanks':
            return handleGratitude($entities, $traits);
            
        case 'goodbye':
        case 'end_session':
            return handleGoodbye($entities, $traits);
            
        default:
            // Unknown intent - use general therapeutic approach
            return handleUnknownIntent($intentName, $entities, $traits, $userMessage);
    }
}

/**
 * Intent handler functions for psychiatrist responses
 */

function handleGreeting($entities, $traits) {
    $sentiment = getSentiment($traits);
    
    if ($sentiment === 'negative') {
        return "Hello, I can sense you might be going through something difficult. I'm Dr. MindMate, and I'm here to provide you with a safe space to talk. What's been on your mind?";
    }
    
    return "Hello! I'm Dr. MindMate, your AI psychiatrist. I'm glad you're here and I'm ready to listen. What would you like to talk about today?";
}

function handleAnxiety($entities, $traits, $userMessage) {
    $triggers = extractEntityValues($entities, 'trigger') ?: extractEntityValues($entities, 'situation');
    $severity = extractEntityValues($entities, 'severity');
    
    $response = "I understand you're experiencing anxiety, and I want you to know that's very common and treatable. ";
    
    if ($triggers) {
        $response .= "You mentioned " . implode(', ', $triggers) . " as triggers. ";
    }
    
    if ($severity) {
        $response .= "It sounds like this has been quite " . $severity[0] . " for you. ";
    }
    
    $response .= "Can you tell me more about when these anxious feelings tend to occur? Understanding the patterns can help us develop coping strategies together.";
    
    return $response;
}

function handleDepression($entities, $traits, $userMessage) {
    $duration = extractEntityValues($entities, 'duration') ?: extractEntityValues($entities, 'time');
    $symptoms = extractEntityValues($entities, 'symptom');
    
    $response = "Thank you for sharing this with me. Depression is a serious condition, but it's also very treatable, and you've taken an important step by reaching out. ";
    
    if ($duration) {
        $response .= "You mentioned this has been going on for " . implode(', ', $duration) . ". ";
    }
    
    if ($symptoms) {
        $response .= "The symptoms you're experiencing - " . implode(', ', $symptoms) . " - are common with depression. ";
    }
    
    $response .= "How has this been affecting your daily activities and relationships? I'm here to help you work through this.";
    
    return $response;
}

function handleRelationship($entities, $traits, $userMessage) {
    $people = extractEntityValues($entities, 'person') ?: extractEntityValues($entities, 'relationship_type');
    $emotions = extractEntityValues($entities, 'emotion');
    
    $response = "Relationship challenges can be really difficult and emotionally draining. ";
    
    if ($people) {
        $response .= "It sounds like this involves your relationship with " . implode(', ', $people) . ". ";
    }
    
    if ($emotions) {
        $response .= "I can hear that you're feeling " . implode(', ', $emotions) . " about this situation. ";
    }
    
    $response .= "Relationships are fundamental to our wellbeing. What aspects of this situation feel most challenging or confusing for you right now?";
    
    return $response;
}

function handleStress($entities, $traits, $userMessage) {
    $sources = extractEntityValues($entities, 'stress_source') ?: extractEntityValues($entities, 'work') ?: extractEntityValues($entities, 'situation');
    
    $response = "Stress is something many people struggle with, especially in today's world. ";
    
    if ($sources) {
        $response .= "You mentioned stress from " . implode(', ', $sources) . ". ";
    }
    
    $response .= "I'd like to help you explore what's causing this stress and find healthy ways to manage it. What feels like the biggest source of stress in your life right now?";
    
    return $response;
}

function handleSleep($entities, $traits, $userMessage) {
    $sleepIssues = extractEntityValues($entities, 'sleep_issue') ?: extractEntityValues($entities, 'symptom');
    
    $response = "Sleep issues can significantly impact our mental health and daily functioning. ";
    
    if ($sleepIssues) {
        $response .= "You mentioned problems with " . implode(', ', $sleepIssues) . ". ";
    }
    
    $response .= "Poor sleep often creates a cycle where we feel more stressed or anxious, which then makes it harder to sleep. Can you describe your current sleep patterns and what might be keeping you awake?";
    
    return $response;
}

function handleEmotionalDistress($entities, $traits, $userMessage) {
    $emotions = extractEntityValues($entities, 'emotion');
    $severity = extractEntityValues($entities, 'severity');
    
    $response = "I can hear that you're going through something really difficult right now. ";
    
    if ($emotions) {
        $response .= "The emotions you're experiencing - " . implode(', ', $emotions) . " - are completely valid. ";
    }
    
    if ($severity) {
        $response .= "It sounds like this has been quite " . $severity[0] . " for you. ";
    }
    
    $response .= "Your feelings matter, and I want to understand better what you're going through. Can you tell me more about what's been most challenging for you?";
    
    return $response;
}

function handleGratitude($entities, $traits) {
    return "You're very welcome. It takes courage to reach out and work on your mental health. I'm honored to be part of your journey. How are you feeling about our conversation so far?";
}

function handleGoodbye($entities, $traits) {
    return "Thank you for sharing with me today. Remember, I'm here whenever you need support. Take care of yourself, and don't hesitate to reach out again.";
}

function handleUnknownIntent($intentName, $entities, $traits, $userMessage) {
    $sentiment = getSentiment($traits);
    
    $response = "I want to make sure I understand what you're sharing with me. ";
    
    if ($sentiment === 'negative') {
        $response .= "I can sense this is difficult for you. ";
    } elseif ($sentiment === 'positive') {
        $response .= "I'm glad you're sharing this with me. ";
    }
    
    $response .= "Could you tell me a bit more about what's most important for us to focus on today?";
    
    return $response;
}

/**
 * Handle fallback when no confident intent is detected
 */
function handleFallback($userMessage, $witData) {
    $sentiment = getSentiment(extractTraits($witData));
    $entities = extractEntities($witData);
    
    // Use sentiment and entities for contextual fallback
    if ($sentiment === 'negative') {
        return "I can sense you're going through something difficult. I'm here to listen and support you. Could you help me understand what's most pressing for you right now?";
    } elseif ($sentiment === 'positive') {
        return "I'm glad you're sharing something positive with me. It's important to acknowledge these good moments. What would you like to explore about this?";
    }
    
    // Check for any entities that might give us context
    if (!empty($entities)) {
        return "I want to make sure I understand what you're sharing. It sounds like there are some important things on your mind. Could you tell me more about what's most significant for you?";
    }
    
    // Generic therapeutic fallback
    return "I'm here to listen and support you. Sometimes it can be helpful to start wherever feels most comfortable. What's been on your mind lately?";
}

/**
 * Helper function to get sentiment from traits
 */
function getSentiment($traits) {
    if (isset($traits['wit$sentiment'])) {
        return $traits['wit$sentiment'][0]['value'] ?? null;
    }
    return null;
}

/**
 * Helper function to extract entity values by type
 */
function extractEntityValues($entities, $entityType) {
    if (isset($entities[$entityType])) {
        return array_map(function($entity) {
            return $entity['value'] ?? $entity['body'] ?? '';
        }, $entities[$entityType]);
    }
    return null;
}
