<?php
/**
 * Database Utility Functions
 * Common database operations for reviews, leads, and case studies
 */

require_once 'database.php';

class DatabaseUtils {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Get database connection
     * @return PDO|null
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Execute a prepared statement safely
     * @param string $sql
     * @param array $params
     * @return PDOStatement|false
     */
    public function executeQuery($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Insert a new record and return the ID
     * @param string $table
     * @param array $data
     * @return int|false
     */
    public function insert($table, $data) {
        $columns = array_keys($data);
        $placeholders = array_map(function($col) { return ":$col"; }, $columns);
        
        $sql = "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->executeQuery($sql, $data);
        if ($stmt) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    /**
     * Update a record
     * @param string $table
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function update($table, $data, $where) {
        $setClause = array_map(function($col) { return "$col = :$col"; }, array_keys($data));
        $whereClause = array_map(function($col) { return "$col = :where_$col"; }, array_keys($where));
        
        $sql = "UPDATE $table SET " . implode(', ', $setClause) . " WHERE " . implode(' AND ', $whereClause);
        
        // Merge data and where parameters
        $params = $data;
        foreach ($where as $key => $value) {
            $params["where_$key"] = $value;
        }
        
        $stmt = $this->executeQuery($sql, $params);
        return $stmt !== false;
    }
    
    /**
     * Delete a record
     * @param string $table
     * @param array $where
     * @return bool
     */
    public function delete($table, $where) {
        $whereClause = array_map(function($col) { return "$col = :$col"; }, array_keys($where));
        $sql = "DELETE FROM $table WHERE " . implode(' AND ', $whereClause);
        
        $stmt = $this->executeQuery($sql, $where);
        return $stmt !== false;
    }
    
    /**
     * Find records with optional conditions
     * @param string $table
     * @param array $where
     * @param string $orderBy
     * @param int $limit
     * @return array|false
     */
    public function find($table, $where = [], $orderBy = '', $limit = 0) {
        $sql = "SELECT * FROM $table";
        
        if (!empty($where)) {
            $whereClause = array_map(function($col) { return "$col = :$col"; }, array_keys($where));
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        if ($limit > 0) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = $this->executeQuery($sql, $where);
        if ($stmt) {
            return $stmt->fetchAll();
        }
        return false;
    }
    
    /**
     * Find a single record
     * @param string $table
     * @param array $where
     * @return array|false
     */
    public function findOne($table, $where) {
        $result = $this->find($table, $where, '', 1);
        return $result ? $result[0] : false;
    }
    
    /**
     * Check if database tables exist
     * @return bool
     */
    public function tablesExist() {
        $requiredTables = ['reviews', 'startup_leads', 'case_studies'];
        
        try {
            $stmt = $this->conn->query("SHOW TABLES");
            $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($requiredTables as $table) {
                if (!in_array($table, $existingTables)) {
                    return false;
                }
            }
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get table statistics
     * @return array
     */
    public function getTableStats() {
        $tables = ['reviews', 'startup_leads', 'case_studies', 'lead_interactions', 'review_moderation_log'];
        $stats = [];
        
        foreach ($tables as $table) {
            try {
                $stmt = $this->conn->query("SELECT COUNT(*) FROM $table");
                $stats[$table] = $stmt->fetchColumn();
            } catch (PDOException $e) {
                $stats[$table] = 'Error: ' . $e->getMessage();
            }
        }
        
        return $stats;
    }
}
?>