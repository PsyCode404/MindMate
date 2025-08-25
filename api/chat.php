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
    
    // Generate psychiatrist response based on user message and Wit.ai analysis
    $reply = generatePsychiatristResponse($user_message, $wit_data);

    echo json_encode(['reply' => $reply]);

} catch (Exception $e) {
    error_log('Chat API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
}

/**
 * Generate psychiatrist response based on user message and Wit.ai analysis
 */
function generatePsychiatristResponse($userMessage, $witData) {
    // Analyze the user's message for emotional content and mental health indicators
    $userMessage = strtolower($userMessage);
    
    // Check for specific mental health keywords and respond appropriately
    if (strpos($userMessage, 'anxious') !== false || strpos($userMessage, 'anxiety') !== false) {
        return "I hear that you're experiencing anxiety. That's a very common feeling, and you're not alone in this. Can you tell me what specific situations or thoughts tend to trigger your anxiety? Understanding these patterns can help us work together to develop coping strategies.";
    }
    
    if (strpos($userMessage, 'depressed') !== false || strpos($userMessage, 'depression') !== false || strpos($userMessage, 'sad') !== false) {
        return "Thank you for sharing that with me. Depression can feel overwhelming, but reaching out is an important first step. How long have you been feeling this way? Are there particular times of day or situations when these feelings are stronger?";
    }
    
    if (strpos($userMessage, 'stress') !== false || strpos($userMessage, 'stressed') !== false) {
        return "Stress is something many people struggle with, especially in today's world. I'd like to help you explore what's causing this stress and find healthy ways to manage it. What are the main sources of stress in your life right now?";
    }
    
    if (strpos($userMessage, 'sleep') !== false || strpos($userMessage, 'insomnia') !== false || strpos($userMessage, 'tired') !== false) {
        return "Sleep issues can significantly impact our mental health and daily functioning. Poor sleep often creates a cycle where we feel more stressed or anxious, which then makes it harder to sleep. Can you describe your current sleep patterns and what might be keeping you awake?";
    }
    
    if (strpos($userMessage, 'relationship') !== false || strpos($userMessage, 'partner') !== false || strpos($userMessage, 'family') !== false) {
        return "Relationships are fundamental to our wellbeing, and it's natural to have challenges in this area. I'm here to help you navigate these feelings. Can you tell me more about what's happening in your relationships that's concerning you?";
    }
    
    if (strpos($userMessage, 'work') !== false || strpos($userMessage, 'job') !== false || strpos($userMessage, 'career') !== false) {
        return "Work-related stress and concerns are very common. Our professional lives can significantly impact our mental health. What specific aspects of your work situation are troubling you? Is it the workload, relationships with colleagues, or perhaps uncertainty about your career path?";
    }
    
    if (strpos($userMessage, 'angry') !== false || strpos($userMessage, 'anger') !== false || strpos($userMessage, 'frustrated') !== false) {
        return "Anger and frustration are valid emotions that often signal that something important to us isn't being met or respected. It's good that you're acknowledging these feelings. Can you help me understand what situations or thoughts tend to trigger these feelings for you?";
    }
    
    if (strpos($userMessage, 'lonely') !== false || strpos($userMessage, 'alone') !== false || strpos($userMessage, 'isolated') !== false) {
        return "Loneliness can be one of the most painful experiences we face as humans. It takes courage to reach out and talk about these feelings. You're taking an important step by sharing this with me. Can you tell me more about when you feel most lonely?";
    }
    
    // Check for positive expressions
    if (strpos($userMessage, 'better') !== false || strpos($userMessage, 'good') !== false || strpos($userMessage, 'happy') !== false) {
        return "I'm glad to hear you're feeling better. It's important to acknowledge and celebrate these positive moments. What do you think has contributed to this improvement in how you're feeling? Understanding what helps can be valuable for maintaining your wellbeing.";
    }
    
    // Check for greetings
    if (strpos($userMessage, 'hello') !== false || strpos($userMessage, 'hi') !== false || strpos($userMessage, 'hey') !== false) {
        return "Hello! I'm glad you're here. I'm Dr. MindMate, and I'm here to provide you with a safe, supportive space to talk about whatever is on your mind. What would you like to discuss today?";
    }
    
    // Check for thanks
    if (strpos($userMessage, 'thank') !== false || strpos($userMessage, 'thanks') !== false) {
        return "You're very welcome. It's my privilege to be here with you on this journey. Remember, seeking support and working on your mental health takes courage. How are you feeling about our conversation so far?";
    }
    
    // Default empathetic response for general messages
    $responses = [
        "I appreciate you sharing that with me. Your feelings and experiences are valid and important. Can you tell me more about what's been on your mind lately?",
        "Thank you for opening up. It sounds like you have a lot going on. What feels most pressing or important for you to talk about right now?",
        "I'm here to listen and support you. What you're experiencing matters, and I want to understand better. Can you help me understand what's been most challenging for you recently?",
        "It takes strength to reach out and talk about personal matters. I'm honored that you're sharing with me. What would be most helpful for us to focus on today?",
        "I can hear that you're going through something significant. Your willingness to talk about it is an important step. What aspects of your situation feel most overwhelming or confusing right now?"
    ];
    
    return $responses[array_rand($responses)];
}
