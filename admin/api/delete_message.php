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
    $messageId = $input['id'] ?? null;
    
    if (!$messageId) {
        throw new Exception('Message ID required');
    }
    
    $messagesFile = '../../data/messages.json';
    
    if (!file_exists($messagesFile)) {
        throw new Exception('Messages file not found');
    }
    
    $messages = json_decode(file_get_contents($messagesFile), true);
    
    // Filter out the message to delete
    $originalCount = count($messages);
    $messages = array_filter($messages, function($message) use ($messageId) {
        return $message['id'] != $messageId;
    });
    
    if (count($messages) === $originalCount) {
        throw new Exception('Message not found');
    }
    
    // Re-index array
    $messages = array_values($messages);
    
    // Save updated messages
    file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT));
    
    echo json_encode(['success' => true, 'message' => 'Message deleted']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>