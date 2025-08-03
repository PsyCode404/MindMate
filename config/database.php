<?php
// Database Configuration
// If running in production (Render/Railway/etc.), read credentials from env vars
if (getenv('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') . (getenv('DB_PORT') ? ':' . getenv('DB_PORT') : ''));
    define('DB_USER', getenv('DB_USER'));
    define('DB_PASS', getenv('DB_PASS'));
    define('DB_NAME', getenv('DB_NAME'));
} else {
    // Local XAMPP defaults
    define('DB_HOST', 'localhost:3307');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'mindmate_v');
}

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
