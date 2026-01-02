<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$page = $input['page'] ?? '';
$content = $input['content'] ?? '';
$changes = $input['changes'] ?? [];

// Define page file paths
$pageFiles = [
    'home' => '../../index.html',
    'services' => '../../services/index.html',
    'pricing' => '../../pricing/index.html',
    'contact' => '../../contact/index.html',
    'process' => '../../process/index.html',
    'startup-packages' => '../../startup-packages/index.html'
];

if (!isset($pageFiles[$page])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid page specified']);
    exit;
}

$filePath = $pageFiles[$page];

try {
    // Create backup before saving
    $backupDir = '../../data/backups';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    $backupFile = $backupDir . '/' . $page . '_' . date('Y-m-d_H-i-s') . '.html';
    if (file_exists($filePath)) {
        copy($filePath, $backupFile);
    }
    
    // Clean up the content (remove editor-specific elements)
    $cleanContent = $content;
    
    // Remove editor-specific query parameters from URLs
    $cleanContent = preg_replace('/\?editor=1/', '', $cleanContent);
    
    // Remove edit overlays and editor classes more thoroughly
    $cleanContent = preg_replace('/<div class="edit-overlay"[^>]*>.*?<\/div>/s', '', $cleanContent);
    $cleanContent = preg_replace('/<div class="element-type-badge"[^>]*>.*?<\/div>/s', '', $cleanContent);
    
    // Clean up editor classes
    $cleanContent = preg_replace('/\s*editable-element\s*/', ' ', $cleanContent);
    $cleanContent = preg_replace('/\s*editing\s*/', ' ', $cleanContent);
    $cleanContent = preg_replace('/\s*class=""\s*/', '', $cleanContent);
    $cleanContent = preg_replace('/\s*class="\s*"\s*/', '', $cleanContent);
    
    // Remove editor attributes
    $cleanContent = preg_replace('/\s*data-editable="[^"]*"/', '', $cleanContent);
    $cleanContent = preg_replace('/\s*data-original-content="[^"]*"/', '', $cleanContent);
    $cleanContent = preg_replace('/\s*data-original-classes="[^"]*"/', '', $cleanContent);
    $cleanContent = preg_replace('/\s*data-original-styles="[^"]*"/', '', $cleanContent);
    
    // Clean up extra spaces in class attributes
    $cleanContent = preg_replace('/class="([^"]*)"/', function($matches) {
        $classes = trim(preg_replace('/\s+/', ' ', $matches[1]));
        return $classes ? 'class="' . $classes . '"' : '';
    }, $cleanContent);
    
    // Remove empty class attributes
    $cleanContent = preg_replace('/\s*class=""\s*/', '', $cleanContent);
    
    // Save the file
    $result = file_put_contents($filePath, $cleanContent);
    
    if ($result === false) {
        throw new Exception('Failed to write file');
    }
    
    // Log the changes
    $logFile = '../../data/editor_changes.log';
    $logEntry = date('Y-m-d H:i:s') . " - Page: $page - Changes: " . count($changes) . " elements\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    // Save changes metadata
    $changesFile = '../../data/realtime_changes.json';
    $allChanges = [];
    if (file_exists($changesFile)) {
        $allChanges = json_decode(file_get_contents($changesFile), true) ?: [];
    }
    
    $allChanges[$page] = [
        'timestamp' => date('Y-m-d H:i:s'),
        'changes' => $changes,
        'backup_file' => $backupFile
    ];
    
    file_put_contents($changesFile, json_encode($allChanges, JSON_PRETTY_PRINT));
    
    echo json_encode([
        'success' => true, 
        'message' => 'Changes saved successfully',
        'backup_created' => $backupFile,
        'changes_count' => count($changes)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error saving changes: ' . $e->getMessage()
    ]);
}
?>