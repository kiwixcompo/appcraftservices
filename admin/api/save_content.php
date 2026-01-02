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
    
    // Structure the content data
    $content = [
        'site_info' => [
            'title' => $input['site_title'] ?? '',
            'tagline' => $input['site_tagline'] ?? '',
            'description' => $input['site_description'] ?? '',
            'email' => $input['site_email'] ?? '',
            'phone' => $input['site_phone'] ?? '',
        ],
        'hero' => [
            'headline' => $input['hero_headline'] ?? '',
            'subheadline' => $input['hero_subheadline'] ?? '',
            'cta_text' => $input['hero_cta'] ?? '',
        ],
        'value_props' => [],
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Process value propositions
    if (isset($input['value_prop_title']) && isset($input['value_prop_description'])) {
        $titles = $input['value_prop_title'];
        $descriptions = $input['value_prop_description'];
        
        for ($i = 0; $i < count($titles); $i++) {
            if (!empty($titles[$i]) && !empty($descriptions[$i])) {
                $content['value_props'][] = [
                    'title' => $titles[$i],
                    'description' => $descriptions[$i]
                ];
            }
        }
    }
    
    // Ensure data directory exists
    $dataDir = '../../data';
    if (!file_exists($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    // Save to JSON file
    $contentFile = $dataDir . '/website_content.json';
    $result = file_put_contents($contentFile, json_encode($content, JSON_PRETTY_PRINT));
    
    if ($result === false) {
        throw new Exception('Failed to save content file');
    }
    
    // Log the change
    $logFile = $dataDir . '/admin_log.txt';
    $logEntry = date('Y-m-d H:i:s') . " - Content updated by " . $_SESSION['admin_username'] . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    echo json_encode(['success' => true, 'message' => 'Content saved successfully']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>