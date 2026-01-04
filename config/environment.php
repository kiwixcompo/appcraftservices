<?php
/**
 * Environment Configuration
 * Automatically detects whether running locally or on production
 */

// Detect if running locally or on production
$isLocal = (
    $_SERVER['HTTP_HOST'] === 'localhost' ||
    $_SERVER['HTTP_HOST'] === 'localhost:80' ||
    $_SERVER['HTTP_HOST'] === 'localhost:8080' ||
    $_SERVER['HTTP_HOST'] === 'localhost:3000' ||
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') === 0 ||
    strpos($_SERVER['HTTP_HOST'], '192.168.') === 0
);

// Set base URL based on environment
if ($isLocal) {
    // Local development - use /appcraftservices/ path
    define('BASE_URL', '/appcraftservices/');
    define('API_URL', '/appcraftservices/api/');
    define('ADMIN_URL', '/appcraftservices/admin/');
    define('ASSETS_URL', '/appcraftservices/assets/');
} else {
    // Production - use root path
    define('BASE_URL', '/');
    define('API_URL', '/api/');
    define('ADMIN_URL', '/admin/');
    define('ASSETS_URL', '/assets/');
}

// Environment type
define('ENVIRONMENT', $isLocal ? 'local' : 'production');
define('IS_LOCAL', $isLocal);
define('IS_PRODUCTION', !$isLocal);

// Debug mode
define('DEBUG_MODE', $isLocal);
?>
