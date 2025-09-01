<?php
// File: api/chat.php
// Handles chat requests and sends them to GroqCloud API

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

    // Use environment variable for Groq API Key
    $groq_api_key = $_ENV['GROQ_API_KEY'] ?? null;
    
    if (!$groq_api_key) {
        throw new Exception('Groq API key not configured. Please set GROQ_API_KEY environment variable.');
    }
    
    // Call Groq API
    $reply = callGroqAPI($user_message, $groq_api_key);

    echo json_encode(['reply' => $reply]);

} catch (Exception $e) {
    error_log('Chat API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
}

/**
 * Call Groq API for AI-generated responses
 */
function callGroqAPI($userMessage, $apiKey) {
    $endpoint = 'https://api.groq.com/openai/v1/chat/completions';
    
    // Create system prompt for MindMate psychiatrist
    $systemPrompt = "You are Dr. MindMate, a compassionate AI psychiatrist and mental health assistant. Your role is to:

1. Provide empathetic, professional mental health support
2. Listen actively and validate the user's feelings
3. Ask thoughtful follow-up questions to understand their situation better
4. Offer evidence-based coping strategies and therapeutic insights
5. Maintain appropriate boundaries while being warm and supportive
6. Encourage professional help when needed for serious issues

Guidelines:
- Always be empathetic and non-judgmental
- Keep responses conversational but professional
- Focus on the user's emotional wellbeing
- Provide practical, actionable advice when appropriate
- Never diagnose or prescribe medication
- If someone expresses suicidal thoughts, encourage them to seek immediate professional help

Respond in a caring, therapeutic manner that makes the user feel heard and supported.";

    $payload = [
        'model' => 'llama-3.1-8b-instant', // Updated model (llama3-8b-8192 deprecated)
        'messages' => [
            [
                'role' => 'system',
                'content' => $systemPrompt
            ],
            [
                'role' => 'user',
                'content' => $userMessage
            ]
        ],
        'max_tokens' => 500,
        'temperature' => 0.7,
        'top_p' => 0.9
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log('Groq API cURL Error: ' . $error);
        throw new Exception('Connection error: Unable to reach AI service');
    }

    if ($httpCode !== 200) {
        error_log("Groq API Error ($httpCode): " . $response);
        
        // Parse error response for better error handling
        $errorData = json_decode($response, true);
        if (isset($errorData['error']['message'])) {
            throw new Exception('AI service error: ' . $errorData['error']['message']);
        } else {
            throw new Exception('AI service temporarily unavailable (HTTP ' . $httpCode . ')');
        }
    }

    $data = json_decode($response, true);
    
    if (!isset($data['choices'][0]['message']['content'])) {
        error_log('Groq API Invalid Response: ' . $response);
        throw new Exception('Invalid response from AI service');
    }

    return trim($data['choices'][0]['message']['content']);
}
