<?php
/**
 * Database Initialization Script
 * Run this script to set up the MySQL database schema
 */

require_once 'database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting database initialization...\n";

try {
    // First, try to connect without specifying database to create it
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $username = $_ENV['DB_USER'] ?? 'root';
    $password = $_ENV['DB_PASS'] ?? '';
    
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to MySQL server successfully.\n";
    
    // Read and execute schema
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    
    // Split schema into individual statements
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                echo "Executed: " . substr($statement, 0, 50) . "...\n";
            } catch (PDOException $e) {
                echo "Warning: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "Database schema initialized successfully!\n";
    
    // Test the Database class
    $db = new Database();
    if ($db->testConnection()) {
        echo "Database connection test passed!\n";
    } else {
        echo "Database connection test failed!\n";
    }
    
    // Display table information
    $conn = $db->getConnection();
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\nCreated tables:\n";
    foreach ($tables as $table) {
        $count = $conn->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "- $table ($count records)\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nDatabase initialization complete!\n";
?>