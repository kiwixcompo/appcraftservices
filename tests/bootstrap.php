<?php
/**
 * PHPUnit Bootstrap File
 * Sets up testing environment for App Craft Services
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables for testing
$_ENV['DB_HOST'] = $_ENV['DB_HOST'] ?? 'localhost';
$_ENV['DB_NAME'] = $_ENV['DB_NAME'] ?? 'appcraft_services'; // Use same DB as main app
$_ENV['DB_USER'] = $_ENV['DB_USER'] ?? 'root';
$_ENV['DB_PASS'] = $_ENV['DB_PASS'] ?? '';

// Include database configuration
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/database_utils.php';

// Set error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', 1);