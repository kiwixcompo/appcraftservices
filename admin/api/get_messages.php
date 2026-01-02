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
    $messagesFile = '../../data/messages.json';
    
    if (!file_exists($messagesFile)) {
        echo json_encode([]);
        exit;
    }
    
    $messages = json_decode(file_get_contents($messagesFile), true);
    
    if (!$messages) {
        echo json_encode([]);
        exit;
    }
    
    // Sort messages by date (newest first)
    usort($messages, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    echo json_encode($messages);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>