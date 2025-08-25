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
    $reply = '';

    // For a psychiatrist functionality, we'll need to process the response appropriately
    // This is a placeholder implementation - you would need to implement your own logic
    // to generate appropriate psychiatric responses based on the intents/entities detected

    if (isset($wit_data['traits']['wit$message_body'][0]['value'])) {
        $reply = $wit_data['traits']['wit$message_body'][0]['value'];
    } else {
        // Fallback response if no message body trait is found
        $reply = "I understand you're sharing your thoughts with me. As a psychiatrist, I'm here to listen and help. Could you tell me more about what's on your mind?";
    }

    echo json_encode(['reply' => $reply]);

} catch (Exception $e) {
    error_log('Chat API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
}
