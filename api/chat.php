<?php
// File: api/chat.php
// Handles chat requests and proxies them to Wit.ai

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get the incoming JSON body
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No message provided']);
    exit();
}

$user_message = $data['message'];

// Use environment variable for Wit.ai Server Access Token
$wit_server_token = $_ENV['WIT_SERVER_TOKEN'] ?? 'IQXJAJ62K72LTF3HQO6OWAL5DYGMG3YL';

$endpoint = 'https://api.wit.ai/message';
$params = http_build_query([
    'q' => $user_message
]);

$ch = curl_init($endpoint . '?' . $params);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $wit_server_token
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL error: ' . $error]);
    exit();
}

if ($http_code !== 200) {
    http_response_code($http_code);
    echo json_encode(['error' => 'Wit.ai API error', 'response' => $response]);
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
