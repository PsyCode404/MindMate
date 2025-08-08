<?php
// Database Configuration

// Function to get environment variable with fallback
function getEnvVar($key, $default = null) {
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

// Check if running in Railway environment
$isRailway = getenv('RAILWAY') === 'true' || getenv('RAILWAY_ENVIRONMENT') || getenv('DB_HOST');

if ($isRailway) {
    // Railway provides a DATABASE_URL in the format: 
    // mysql://user:password@hostname:port/railway
    if (getenv('DATABASE_URL')) {
        $db = parse_url(getenv('DATABASE_URL'));
        
        if ($db === false) {
            error_log("Failed to parse DATABASE_URL");
        } else {
            define('DB_HOST', $db['host'] . (isset($db['port']) ? ':' . $db['port'] : ':3306'));
            define('DB_USER', $db['user']);
            define('DB_PASS', $db['pass']);
            define('DB_NAME', ltrim($db['path'], '/'));
            
            // Log the parsed database host (without credentials) for debugging
            error_log("Using Railway database: " . $db['host'] . "...");
        }
    } else {
        // Fallback to individual env vars
        define('DB_HOST', getEnvVar('DB_HOST', 'localhost') . ':' . getEnvVar('DB_PORT', '3306'));
        define('DB_USER', getEnvVar('DB_USER', 'root'));
        define('DB_PASS', getEnvVar('DB_PASS', ''));
        define('DB_NAME', getEnvVar('DB_NAME', 'mindmate_v'));
    }
} else {
    // Local development defaults - using Railway database
    define('DB_HOST', getEnvVar('MYSQLHOST', 'localhost') . ':' . getEnvVar('MYSQLPORT', '3306'));
    define('DB_USER', getEnvVar('MYSQLUSER', 'root'));
    define('DB_PASS', getEnvVar('MYSQLPASSWORD', ''));
    define('DB_NAME', getEnvVar('MYSQLDATABASE', 'railway'));
    
    error_log("Using database configuration:");
    error_log("- Host: " . DB_HOST);
    error_log("- Database: " . DB_NAME);
    error_log("- User: " . DB_USER);
}

// Create database connection
function get_db_connection() {
    try {
        // Log connection attempt (without sensitive data)
        error_log("Attempting to connect to database on " . DB_HOST);
        
        // For Railway, we need to ensure we're using the correct port and SSL
        $isRailway = getenv('RAILWAY') === 'true' || getenv('RAILWAY_ENVIRONMENT') || getenv('DB_HOST');
        
        if ($isRailway) {
            // Force MySQLi to use TCP connection
            $host = DB_HOST;
            if (strpos($host, ':') === false) {
                $host .= ':3306'; // Default MySQL port if not specified
            }
            
            // Create connection with error reporting
            $conn = new mysqli();
            $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
            
            // Connect with error suppression to handle errors ourselves
            @$conn->real_connect($host, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }
            
            // Set SSL if available
            if (function_exists('mysqli_ssl_set')) {
                $conn->ssl_set(null, null, null, null, null);
            }
        } else {
            // Local connection
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        }
        
        if ($conn->connect_error) {
            $error = "Connection failed: " . $conn->connect_error . 
                    " [Error No: " . $conn->connect_errno . "]";
            error_log($error);
            throw new Exception($error);
        }
        
        // Set charset to utf8mb4
        if (!$conn->set_charset("utf8mb4")) {
            error_log("Error loading character set utf8mb4: " . $conn->error);
        } else {
            error_log("Database connection successful. Current character set: " . $conn->character_set_name());
        }
        
        return $conn;
    } catch (Exception $e) {
        $error = "Database connection error: " . $e->getMessage();
        error_log($error);
        error_log("Stack trace: " . $e->getTraceAsString());
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
