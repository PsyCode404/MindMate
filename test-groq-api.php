<?php
// Test script for Groq API integration

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
        'model' => 'llama3-8b-8192', // Fast Groq model
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

// Test the API call
try {
    $testMessage = "Hello, I'm feeling anxious today";
    $apiKey = 'YOUR_GROQ_API_KEY_HERE'; // Replace with your actual API key for testing
    
    echo "Testing Groq API integration...\n";
    echo "User message: " . $testMessage . "\n\n";
    
    $response = callGroqAPI($testMessage, $apiKey);
    
    echo "AI Response:\n";
    echo $response . "\n";
    echo "\n✅ Test successful!\n";
    
} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
}
?>
