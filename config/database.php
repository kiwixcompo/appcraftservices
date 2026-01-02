<?php
/**
 * Database Configuration for App Craft Services
 * Handles MySQL connection for reviews, leads, and case studies
 */

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    
    public function __construct() {
        // Load database configuration from environment or default values
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->db_name = $_ENV['DB_NAME'] ?? 'appcraft_services';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASS'] ?? '';
    }
    
    /**
     * Get database connection
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            return null;
        }
        
        return $this->conn;
    }
    
    /**
     * Test database connection
     * @return bool
     */
    public function testConnection() {
        $conn = $this->getConnection();
        return $conn !== null;
    }
    
    /**
     * Initialize database schema
     * @return bool
     */
    public function initializeSchema() {
        $conn = $this->getConnection();
        if (!$conn) {
            return false;
        }
        
        try {
            // Read and execute schema file
            $schema = file_get_contents(__DIR__ . '/schema.sql');
            $conn->exec($schema);
            return true;
        } catch(PDOException $exception) {
            error_log("Schema initialization error: " . $exception->getMessage());
            return false;
        }
    }
}
?>