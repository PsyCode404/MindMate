<?php
// Database Configuration Setup Script

// Check if the script is being run in CLI mode
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.\n");
}

echo "=== Database Configuration Setup ===\n";

// Get database configuration from user input
function getInput($prompt, $default = '') {
    echo $prompt . ($default ? " [$default]: " : ": ");
    $input = trim(fgets(STDIN));
    return $input ?: $default;
}

// Get database details
$dbHost = getInput("Enter database host", "localhost");
$dbPort = getInput("Enter database port", "3306");
$dbName = getInput("Enter database name", "railway");
$dbUser = getInput("Enter database username", "root");
$dbPass = getInput("Enter database password", "");

// Create the database configuration
$config = "<?php
// Database Configuration

// Function to get environment variable with fallback
function getEnvVar(\$key, \$default = null) {
    \$value = getenv(\$key);
    return \$value !== false ? \$value : \$default;
}

// Check if running in Railway environment
\$isRailway = getenv('RAILWAY') === 'true' || getenv('RAILWAY_ENVIRONMENT') || getenv('DB_HOST');

if (\$isRailway) {
    // Railway provides a DATABASE_URL in the format: 
    // mysql://user:password@hostname:port/railway
    if (getenv('DATABASE_URL')) {
        \$db = parse_url(getenv('DATABASE_URL'));
        
        if (\$db === false) {
            error_log(\"Failed to parse DATABASE_URL\");
        } else {
            define('DB_HOST', \$db['host'] . (isset(\$db['port']) ? ':' . \$db['port'] : ':3306'));
            define('DB_USER', \$db['user']);
            define('DB_PASS', \$db['pass']);
            define('DB_NAME', ltrim(\$db['path'], '/'));
            
            // Log the parsed database host (without credentials) for debugging
            error_log(\"Using Railway database: \" . \$db['host'] . \"...\");
        }
    } else {
        // Fallback to individual env vars
        define('DB_HOST', getEnvVar('DB_HOST', 'localhost') . ':' . getEnvVar('DB_PORT', '3306'));
        define('DB_USER', getEnvVar('DB_USER', 'root'));
        define('DB_PASS', getEnvVar('DB_PASS', ''));
        define('DB_NAME', getEnvVar('DB_NAME', 'railway'));
    }
} else {
    // Local development - use the provided credentials
    define('DB_HOST', '{$dbHost}:{$dbPort}');
    define('DB_USER', '{$dbUser}');
    define('DB_PASS', '{$dbPass}');
    define('DB_NAME', '{$dbName}');
    
    error_log(\"Using local database configuration: {$dbHost}:{$dbPort}/{$dbName}\");
}

// Create database connection
function get_db_connection() {
    try {
        // Log connection attempt (without sensitive data)
        error_log(\"Attempting to connect to database on \" . DB_HOST);
        
        // Create connection
        \$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if (\$conn->connect_error) {
            throw new Exception(\$conn->connect_error);
        }
        
        // Set charset to utf8mb4
        \$conn->set_charset(\"utf8mb4\");
        
        error_log(\"Database connection successful\");
        return \$conn;
    } catch (Exception \$e) {
        error_log(\"Database connection error: \" . \$e->getMessage());
        return null;
    }
}
";

// Write the configuration to a file
$configFile = __DIR__ . '/config/database.php';
if (file_put_contents($configFile, $config) !== false) {
    echo "\n✅ Database configuration saved successfully to config/database.php\n";
    
    // Test the database connection
    echo "\nTesting database connection...\n";
    require_once __DIR__ . '/config/database.php';
    $conn = get_db_connection();
    
    if ($conn) {
        echo "✅ Successfully connected to the database!\n";
        
        // Check if mood_logs table exists
        $result = $conn->query("SHOW TABLES LIKE 'mood_logs'");
        if ($result->num_rows > 0) {
            echo "✅ 'mood_logs' table exists.\n";
            
            // Count mood entries
            $countResult = $conn->query("SELECT COUNT(*) as count FROM mood_logs");
            $row = $countResult->fetch_assoc();
            echo "ℹ️  Found " . $row['count'] . " mood entries in the database.\n";
        } else {
            echo "⚠️  Warning: 'mood_logs' table does not exist in the database.\n";
        }
        
        $conn->close();
    } else {
        echo "❌ Failed to connect to the database. Please check your credentials.\n";
    }
} else {
    echo "❌ Error: Could not write to config/database.php. Please check file permissions.\n";
}

echo "\nSetup complete!\n";
?>
