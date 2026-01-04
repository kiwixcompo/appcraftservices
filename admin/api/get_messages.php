<?php
session_start();

// Disable error display to prevent JSON corruption
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    // Correct path relative to admin/api/
    $messagesFile = __DIR__ . '/../../data/messages.json';
    
    if (!file_exists($messagesFile)) {
        echo json_encode([]);
        exit;
    }
    
    $messages = json_decode(file_get_contents($messagesFile), true);
    
    if (!is_array($messages)) {
        echo json_encode([]);
        exit;
    }
    
    // Sort Newest First
    usort($messages, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    echo json_encode($messages);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>