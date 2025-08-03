<?php
// File: api/chat.php
// Handles chat requests and proxies them to Hugging Face Zephyr-7B-Beta

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

// Use environment variable for Hugging Face API key
$hf_api_key = $_ENV['HF_API_KEY'] ?? null;
if (!$hf_api_key || $hf_api_key === 'YOUR_HF_API_KEY') {
    // TODO: Insert your Hugging Face API key in your environment or .env file
    http_response_code(500);
    echo json_encode(['error' => 'Hugging Face API key not set. Set HF_API_KEY in your environment.']);
    exit();
}

$endpoint = 'https://api-inference.huggingface.co/models/HuggingFaceH4/zephyr-7b-beta';
$prompt = "<|system|>\nYou are MindMate, a supportive AI therapist. Always respond with empathy, encouragement, and practical guidance. Stay on topic and help users with their mental wellness.\n<|user|>\n" . $user_message . "\n<|assistant|>";

$payload = json_encode([
    'inputs' => $prompt
]);

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $hf_api_key,
    'Content-Type: application/json'
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
    echo json_encode(['error' => 'Hugging Face API error', 'response' => $response]);
    exit();
}

// Hugging Face returns an array with 'generated_text' or similar
$hf_data = json_decode($response, true);
$reply = '';
if (isset($hf_data[0]['generated_text'])) {
    $raw = $hf_data[0]['generated_text'];
} else if (isset($hf_data['generated_text'])) {
    $raw = $hf_data['generated_text'];
} else {
    $raw = $response;
}

// Extract only the assistant's response (after <|assistant|>)
$assistant_pos = strpos($raw, '<|assistant|>');
if ($assistant_pos !== false) {
    $reply = trim(substr($raw, $assistant_pos + strlen('<|assistant|>')));
} else {
    $reply = trim($raw);
}

echo json_encode(['reply' => $reply]);
