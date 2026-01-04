<?php
session_start();

// Disable error display to prevent JSON corruption
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['message_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Message ID is required']);
        exit;
    }
    
    $messageId = $input['message_id'];
    $messagesFile = __DIR__ . '/../../data/messages.json';
    
    if (!file_exists($messagesFile)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Messages file not found']);
        exit;
    }
    
    $messages = json_decode(file_get_contents($messagesFile), true);
    
    if (!is_array($messages)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Invalid messages data']);
        exit;
    }
    
    // Find and update the message
    $messageFound = false;
    for ($i = 0; $i < count($messages); $i++) {
        if (isset($messages[$i]['id']) && $messages[$i]['id'] == $messageId) {
            $messages[$i]['status'] = 'read';
            $messages[$i]['read_at'] = date('Y-m-d H:i:s');
            $messageFound = true;
            break;
        }
    }
    
    if (!$messageFound) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Message not found']);
        exit;
    }
    
    // Save updated messages
    if (file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true, 'message' => 'Message marked as read']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save changes']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>