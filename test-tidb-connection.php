<?php
// Test TiDB Connection Script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TiDB Connection Test ===\n";

// Load environment variables from .env file
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            putenv(trim($name) . '=' . trim($value));
        }
    }
    echo "✓ Environment variables loaded from .env\n";
} else {
    echo "⚠ No .env file found, using system environment variables\n";
}

// Include database configuration
require_once __DIR__ . '/config/database.php';

echo "\n=== Configuration Check ===\n";
echo "Host: " . (defined('DB_HOST') ? DB_HOST : 'NOT SET') . "\n";
echo "User: " . (defined('DB_USER') ? DB_USER : 'NOT SET') . "\n";
echo "Database: " . (defined('DB_NAME') ? DB_NAME : 'NOT SET') . "\n";
echo "SSL: " . (defined('DB_SSL') && DB_SSL ? 'enabled' : 'disabled') . "\n";
echo "SSL CA: " . (defined('DB_SSL_CA') ? DB_SSL_CA : 'NOT SET') . "\n";

// Check if SSL CA file exists
if (defined('DB_SSL_CA') && DB_SSL_CA) {
    if (file_exists(DB_SSL_CA)) {
        echo "✓ SSL CA certificate found at: " . DB_SSL_CA . "\n";
    } else {
        echo "⚠ SSL CA certificate NOT found at: " . DB_SSL_CA . "\n";
        echo "  This may cause SSL connection issues\n";
    }
}

echo "\n=== PDO Connection Test ===\n";
try {
    $pdo = get_db_connection();
    echo "✓ PDO connection successful!\n";
    
    // Test basic query
    $stmt = $pdo->query("SELECT VERSION() as version, CONNECTION_ID() as connection_id");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✓ Database version: " . $result['version'] . "\n";
    echo "✓ Connection ID: " . $result['connection_id'] . "\n";
    
    // Test SSL status
    $stmt = $pdo->query("SHOW STATUS LIKE 'Ssl_cipher'");
    $ssl_result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ssl_result && !empty($ssl_result['Value'])) {
        echo "✓ SSL connection active - Cipher: " . $ssl_result['Value'] . "\n";
    } else {
        echo "⚠ SSL status unclear or not active\n";
    }
    
    // Test table access
    echo "\n=== Database Schema Test ===\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "⚠ No tables found in database '" . DB_NAME . "'\n";
        echo "  You may need to import your schema.sql file\n";
    } else {
        echo "✓ Found " . count($tables) . " tables:\n";
        foreach ($tables as $table) {
            echo "  - " . $table . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ PDO connection failed: " . $e->getMessage() . "\n";
    
    // Additional debugging info
    echo "\nDebugging information:\n";
    echo "- Check if TiDB cluster is running\n";
    echo "- Verify credentials are correct\n";
    echo "- Ensure SSL certificate path is correct\n";
    echo "- Check network connectivity to TiDB\n";
}

echo "\n=== MySQLi Connection Test (Legacy) ===\n";
try {
    $mysqli = get_mysqli_connection();
    echo "✓ MySQLi connection successful!\n";
    
    $result = $mysqli->query("SELECT VERSION() as version");
    $row = $result->fetch_assoc();
    echo "✓ MySQLi version: " . $row['version'] . "\n";
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "✗ MySQLi connection failed: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>
