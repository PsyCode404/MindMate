<?php
header('Content-Type: application/json');

require_once __DIR__ . '/config/database.php';

// Test database connection
$conn = get_db_connection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

try {
    // Test a simple query
    $result = $conn->query("SELECT 1 as test");
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'database' => DB_NAME,
            'host' => DB_HOST,
            'user' => DB_USER,
            'connection' => 'Successfully connected to the database',
            'test_query' => $result->fetch_assoc()
        ]);
    } else {
        throw new Exception("Test query failed: " . $conn->error);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database test failed',
        'error' => $e->getMessage(),
        'database' => DB_NAME,
        'host' => DB_HOST,
        'user' => DB_USER
    ]);
}

$conn->close();
?>
