<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid input data');
    }
    
    $settingsFile = '../../data/settings.json';
    $settings = [];
    
    // Load existing settings
    if (file_exists($settingsFile)) {
        $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
    }
    
    // Update settings with new data
    $settings = array_merge($settings, $input);
    $settings['updated_at'] = date('Y-m-d H:i:s');
    
    // Save settings
    if (file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true, 'message' => 'Settings saved successfully']);
    } else {
        throw new Exception('Failed to save settings');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>