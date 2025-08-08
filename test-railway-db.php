<?php
header('Content-Type: text/plain');

echo "Testing Railway Database Connection\n";
echo "================================\n\n";

// Show all environment variables (for debugging)
echo "Environment Variables:\n";
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'DB_') === 0 || strpos($key, 'RAILWAY_') === 0) {
        echo "$key = " . (in_array($key, ['DB_PASS', 'DATABASE_URL']) ? '***HIDDEN***' : $value) . "\n";
    }
}

echo "\n";

// Include database configuration
require_once __DIR__ . '/config/database.php';

// Test database connection
echo "\nTesting Database Connection...\n";
$conn = get_db_connection();

if (!$conn) {
    die("❌ Failed to connect to the database. Check the error logs for more details.\n");
}

echo "✅ Successfully connected to the database!\n";

// Test a simple query
echo "\nTesting Query...\n";
$query = "SHOW TABLES";
$result = $conn->query($query);

if ($result) {
    echo "✅ Query executed successfully. Found tables:\n";
    while ($row = $result->fetch_array()) {
        echo "- " . $row[0] . "\n";
    }
} else {
    echo "❌ Query failed: " . $conn->error . "\n";
}

// Close connection
$conn->close();
?>

<!-- Add this to the end of the file to ensure it's not truncated -->
<!-- End of test-railway-db.php -->
