<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database configuration
require_once __DIR__ . '/config/database.php';

// Test database connection
echo "Testing database connection...\n";
$conn = get_db_connection();

if (!$conn) {
    die("Failed to connect to database. Check your database configuration.\n");
}
echo "✓ Database connection successful!\n\n";

// Test mood logs table
echo "Checking mood_logs table...\n";
$result = $conn->query("SHOW TABLES LIKE 'mood_logs'");
if ($result->num_rows === 0) {
    die("Error: 'mood_logs' table does not exist in the database.\n");
}
echo "✓ 'mood_logs' table exists.\n";

// Check if there are any mood entries
$result = $conn->query("SELECT COUNT(*) as count FROM mood_logs");
$row = $result->fetch_assoc();
echo "✓ Found " . $row['count'] . " mood entries in the database.\n\n";

// Test the mood API endpoint
echo "Testing mood API endpoint...\n";
$baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/mindmate/api/mood.php';
echo "API Endpoint: $baseUrl\n";

// Test with limit parameter
$testUrl = $baseUrl . '?limit=5';
echo "\nTesting GET request to: $testUrl\n";

$ch = curl_init($testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    die("cURL Error: " . curl_error($ch) . "\n");
}

curl_close($ch);

echo "HTTP Status Code: $httpCode\n";
$responseData = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Response (raw): " . htmlspecialchars($response) . "\n";
} else {
    echo "Response (JSON): ";
    print_r($responseData);
}

// Test with period parameter
$testUrl = $baseUrl . '?period=week';
echo "\n\nTesting GET request to: $testUrl\n";

$ch = curl_init($testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: $httpCode\n";
$responseData = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Response (raw): " . htmlspecialchars($response) . "\n";
} else {
    echo "Response (JSON): ";
    print_r($responseData);
}

// List all mood entries (limited to 5)
echo "\n\nLast 5 mood entries from database:\n";
$result = $conn->query("SELECT * FROM mood_logs ORDER BY logged_at DESC LIMIT 5");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "- ID: " . $row['id'] . ", Mood: " . $row['mood'] . ", Notes: " . substr($row['notes'] ?? '', 0, 50) . "..., Date: " . $row['logged_at'] . "\n";
    }
} else {
    echo "No mood entries found in the database.\n";}

$conn->close();
?>
