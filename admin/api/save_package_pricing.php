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
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    $required_fields = ['package_id', 'price', 'range'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Load existing settings
    $settingsFile = '../../data/settings.json';
    $settings = [];
    
    if (file_exists($settingsFile)) {
        $settings = json_decode(file_get_contents($settingsFile), true);
        if (!$settings) {
            $settings = [];
        }
    }
    
    // Initialize package_pricing array if it doesn't exist
    if (!isset($settings['package_pricing'])) {
        $settings['package_pricing'] = [];
    }
    
    // Update package pricing
    $settings['package_pricing'][$input['package_id']] = [
        'price' => $input['price'],
        'range' => $input['range'],
        'description' => $input['description'] ?? '',
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Update timestamp
    $settings['updated_at'] = date('Y-m-d H:i:s');
    
    // Save settings
    if (file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT))) {
        echo json_encode([
            'success' => true,
            'message' => 'Package pricing updated successfully'
        ]);
    } else {
        throw new Exception('Failed to save settings file');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>