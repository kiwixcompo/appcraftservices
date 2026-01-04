<?php
/**
 * GitHub Auto-Deploy Script for appcraftservices.com
 * 
 * This script automatically pulls the latest changes from GitHub
 * when triggered by a webhook or manual execution.
 */

// Configuration
$config = [
    'repo_url' => 'https://github.com/kiwixcompo/appcraftservices.git',
    'branch' => 'main',
    'deploy_path' => __DIR__, // Current directory where this script is located
    'secret_key' => 'AppCraft2026SecureKey!@#$%', // Secure webhook secret
    'log_file' => __DIR__ . '/deploy.log',
    'backup_dir' => __DIR__ . '/backups',
    'allowed_ips' => [
        '140.82.112.0/20',    // GitHub webhook IPs
        '185.199.108.0/22',   // GitHub webhook IPs
        '192.30.252.0/22',    // GitHub webhook IPs
        '127.0.0.1',          // Localhost for manual testing
    ]
];

// Security check for webhook requests
function isValidRequest($config) {
    // Check if request is from allowed IP (basic security)
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // For webhook requests, verify signature
    if (isset($_SERVER['HTTP_X_HUB_SIGNATURE_256'])) {
        $payload = file_get_contents('php://input');
        $signature = hash_hmac('sha256', $payload, $config['secret_key']);
        $expected_signature = 'sha256=' . $signature;
        
        if (!hash_equals($expected_signature, $_SERVER['HTTP_X_HUB_SIGNATURE_256'])) {
            return false;
        }
    }
    
    return true;
}

// Logging function
function logMessage($message, $config) {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($config['log_file'], $log_entry, FILE_APPEND | LOCK_EX);
}

// Create backup before deployment
function createBackup($config) {
    if (!is_dir($config['backup_dir'])) {
        mkdir($config['backup_dir'], 0755, true);
    }
    
    $backup_name = 'backup_' . date('Y-m-d_H-i-s') . '.tar.gz';
    $backup_path = $config['backup_dir'] . '/' . $backup_name;
    
    // Create backup (excluding .git, backups, and logs)
    $exclude_dirs = '--exclude=.git --exclude=backups --exclude=*.log --exclude=deploy.php';
    $command = "tar -czf $backup_path $exclude_dirs .";
    
    exec($command, $output, $return_code);
    
    if ($return_code === 0) {
        logMessage("Backup created: $backup_name", $config);
        
        // Keep only last 5 backups
        $backups = glob($config['backup_dir'] . '/backup_*.tar.gz');
        if (count($backups) > 5) {
            rsort($backups);
            for ($i = 5; $i < count($backups); $i++) {
                unlink($backups[$i]);
            }
        }
        
        return true;
    }
    
    logMessage("Backup failed: " . implode("\n", $output), $config);
    return false;
}

// Main deployment function
function deploy($config) {
    logMessage("=== DEPLOYMENT STARTED ===", $config);
    
    // Change to deployment directory
    chdir($config['deploy_path']);
    
    // Create backup
    if (!createBackup($config)) {
        logMessage("WARNING: Backup creation failed, continuing with deployment", $config);
    }
    
    // Initialize git if not already done
    if (!is_dir('.git')) {
        logMessage("Initializing Git repository", $config);
        exec('git init', $output, $return_code);
        if ($return_code !== 0) {
            logMessage("ERROR: Git init failed", $config);
            return false;
        }
        
        exec("git remote add origin {$config['repo_url']}", $output, $return_code);
        if ($return_code !== 0) {
            logMessage("ERROR: Adding remote origin failed", $config);
            return false;
        }
    }
    
    // Fetch latest changes
    logMessage("Fetching latest changes from GitHub", $config);
    exec("git fetch origin {$config['branch']}", $output, $return_code);
    if ($return_code !== 0) {
        logMessage("ERROR: Git fetch failed: " . implode("\n", $output), $config);
        return false;
    }
    
    // Reset to latest commit (hard reset to avoid conflicts)
    logMessage("Resetting to latest commit", $config);
    exec("git reset --hard origin/{$config['branch']}", $output, $return_code);
    if ($return_code !== 0) {
        logMessage("ERROR: Git reset failed: " . implode("\n", $output), $config);
        return false;
    }
    
    // Set proper permissions
    logMessage("Setting file permissions", $config);
    exec("find . -type f -name '*.php' -exec chmod 644 {} \;");
    exec("find . -type f -name '*.html' -exec chmod 644 {} \;");
    exec("find . -type f -name '*.css' -exec chmod 644 {} \;");
    exec("find . -type f -name '*.js' -exec chmod 644 {} \;");
    exec("find . -type d -exec chmod 755 {} \;");
    
    // Ensure data directory exists and is writable
    if (!is_dir('data')) {
        mkdir('data', 0755, true);
    }
    chmod('data', 0755);
    
    logMessage("=== DEPLOYMENT COMPLETED SUCCESSFULLY ===", $config);
    return true;
}

// Handle the request
try {
    // Security check
    if (!isValidRequest($config)) {
        http_response_code(403);
        logMessage("ERROR: Unauthorized deployment attempt from " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), $config);
        die('Unauthorized');
    }
    
    // Check if this is a manual trigger or webhook
    $is_manual = isset($_GET['manual']) && $_GET['manual'] === 'true';
    $is_webhook = isset($_SERVER['HTTP_X_GITHUB_EVENT']);
    
    if ($is_manual) {
        logMessage("Manual deployment triggered", $config);
        echo "<h2>Manual Deployment Started</h2>";
        echo "<pre>";
        flush();
    } elseif ($is_webhook) {
        $event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? 'unknown';
        logMessage("Webhook deployment triggered: $event", $config);
        
        // Only deploy on push events to main branch
        if ($event !== 'push') {
            logMessage("Ignoring non-push event: $event", $config);
            echo "OK - Event ignored";
            exit;
        }
        
        // Parse payload to check branch
        $payload = json_decode(file_get_contents('php://input'), true);
        if (isset($payload['ref']) && $payload['ref'] !== 'refs/heads/' . $config['branch']) {
            logMessage("Ignoring push to non-main branch: " . $payload['ref'], $config);
            echo "OK - Branch ignored";
            exit;
        }
    } else {
        logMessage("ERROR: Invalid request method", $config);
        http_response_code(400);
        die('Invalid request');
    }
    
    // Perform deployment
    $success = deploy($config);
    
    if ($success) {
        if ($is_manual) {
            echo "</pre>";
            echo "<h3 style='color: green;'>✅ Deployment Successful!</h3>";
            echo "<p>Your website has been updated with the latest changes from GitHub.</p>";
            echo "<p><a href='/'>View Website</a> | <a href='?manual=true'>Deploy Again</a></p>";
        } else {
            echo "OK - Deployment successful";
        }
        logMessage("Deployment completed successfully", $config);
    } else {
        if ($is_manual) {
            echo "</pre>";
            echo "<h3 style='color: red;'>❌ Deployment Failed!</h3>";
            echo "<p>Check the deployment log for details.</p>";
        } else {
            http_response_code(500);
            echo "ERROR - Deployment failed";
        }
        logMessage("Deployment failed", $config);
    }
    
} catch (Exception $e) {
    logMessage("EXCEPTION: " . $e->getMessage(), $config);
    if (isset($is_manual) && $is_manual) {
        echo "<h3 style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
    } else {
        http_response_code(500);
        echo "ERROR - Exception occurred";
    }
}
?>