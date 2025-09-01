<?php
// One-time setup script to create TiDB schema
require_once __DIR__ . '/config/database.php';

try {
    $pdo = get_db_connection();
    
    echo "Creating TiDB schema...\n";
    
    // Read and execute schema
    $schema = file_get_contents(__DIR__ . '/tidb_schema.sql');
    $statements = explode(';', $schema);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        echo "Executing: " . substr($statement, 0, 50) . "...\n";
        $pdo->exec($statement);
    }
    
    echo "✓ Schema created successfully!\n";
    
    // Verify tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "✓ Tables created: " . implode(', ', $tables) . "\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
