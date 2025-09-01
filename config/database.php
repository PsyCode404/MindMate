<?php
// Database Configuration

// Load environment variables from .env file
require_once __DIR__ . '/../load_env.php';

// Function to get environment variable with fallback
function getEnvVar($key, $default = null) {
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

// Database connection configuration
$dbConfig = [];

// Force use of individual MYSQL* variables (ignore DATABASE_URL to avoid conflicts)
if (true) {
    $dbConfig['host'] = getEnvVar('MYSQLHOST', getEnvVar('DB_HOST', 'localhost'));
    $dbConfig['port'] = getEnvVar('MYSQLPORT', getEnvVar('DB_PORT', '3306'));
    $dbConfig['user'] = getEnvVar('MYSQLUSER', getEnvVar('DB_USER', 'root'));
    $dbConfig['pass'] = getEnvVar('MYSQLPASSWORD', getEnvVar('DB_PASS', ''));
    $dbConfig['name'] = getEnvVar('MYSQLDATABASE', getEnvVar('DB_NAME', 'mindmate'));
    
    error_log("Using environment variables for database connection to: " . $dbConfig['host'] . "...");
}

// SSL Configuration
$dbConfig['ssl'] = getEnvVar('MYSQL_SSL', 'false') === 'true';
$dbConfig['ssl_ca'] = getEnvVar('MYSQL_SSL_CA', '/var/www/html/isrgrootx1.pem');

// Define constants for backward compatibility
define('DB_HOST', $dbConfig['host'] . ':' . $dbConfig['port']);
define('DB_USER', $dbConfig['user']);
define('DB_PASS', $dbConfig['pass']);
define('DB_NAME', $dbConfig['name']);
define('DB_SSL', $dbConfig['ssl']);
define('DB_SSL_CA', $dbConfig['ssl_ca']);

error_log("Database configuration loaded:");
error_log("- Host: " . $dbConfig['host'] . ":" . $dbConfig['port']);
error_log("- Database: " . $dbConfig['name']);
error_log("- User: " . $dbConfig['user']);
error_log("- SSL: " . ($dbConfig['ssl'] ? 'enabled' : 'disabled'));

// Create database connection using MySQLi with SSL support
function get_db_connection() {
    try {
        error_log("Attempting to connect to database on " . DB_HOST);
        
        $host = explode(':', DB_HOST)[0];
        $port = explode(':', DB_HOST)[1] ?? 3306;
        
        // Create MySQLi connection
        $conn = new mysqli();
        
        // Set SSL options if enabled
        if (DB_SSL) {
            error_log("SSL connection enabled for TiDB");
            $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
            
            // Add CA certificate if specified and file exists
            if (DB_SSL_CA && file_exists(DB_SSL_CA)) {
                $conn->ssl_set(null, null, DB_SSL_CA, null, null);
                error_log("Using SSL CA certificate: " . DB_SSL_CA);
            } else {
                error_log("SSL CA certificate not found or not specified, using system default");
            }
        }
        
        // Connect
        $conn->real_connect($host, DB_USER, DB_PASS, DB_NAME, $port);
        
        if ($conn->connect_error) {
            throw new Exception("MySQLi connection failed: " . $conn->connect_error);
        }
        
        // Set charset
        if (!$conn->set_charset("utf8mb4")) {
            error_log("Error setting charset utf8mb4: " . $conn->error);
        } else {
            error_log("Database connection successful using MySQLi");
        }
        
        return $conn;
        
    } catch (Exception $e) {
        $error = "Database connection error: " . $e->getMessage();
        error_log($error);
        error_log("Connection details - Host: " . $host . ":" . $port . ", Database: " . DB_NAME);
        throw new Exception($error);
    }
}

// Legacy MySQLi connection function for backward compatibility
function get_mysqli_connection() {
    try {
        error_log("Creating MySQLi connection for backward compatibility");
        
        $host = explode(':', DB_HOST)[0];
        $port = explode(':', DB_HOST)[1] ?? 3306;
        
        // Create MySQLi connection
        $conn = new mysqli();
        
        // Set SSL options if enabled
        if (DB_SSL) {
            $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
            if (DB_SSL_CA && file_exists(DB_SSL_CA)) {
                $conn->ssl_set(null, null, DB_SSL_CA, null, null);
            }
        }
        
        // Connect
        $conn->real_connect($host, DB_USER, DB_PASS, DB_NAME, $port);
        
        if ($conn->connect_error) {
            throw new Exception("MySQLi connection failed: " . $conn->connect_error);
        }
        
        // Set charset
        if (!$conn->set_charset("utf8mb4")) {
            error_log("Error setting charset utf8mb4: " . $conn->error);
        }
        
        error_log("MySQLi connection successful");
        return $conn;
        
    } catch (Exception $e) {
        $error = "MySQLi connection error: " . $e->getMessage();
        error_log($error);
        throw new Exception($error);
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
