<?php
// Database Configuration
define('DB_HOST', 'localhost:3307');
define('DB_USER', 'root');     // Change in production
define('DB_PASS', '');  // No password for local development
define('DB_NAME', 'mindmate_v');

// Create database connection
function get_db_connection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset to utf8mb4
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return false;
    }
}

// Example usage:
/*
$conn = get_db_connection();
if ($conn) {
    // Your database operations here
    $conn->close();
}
*/
?>
