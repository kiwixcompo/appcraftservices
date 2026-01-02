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
    $invoicesFile = '../../data/invoices.json';
    
    if (!file_exists($invoicesFile)) {
        echo json_encode([]);
        exit;
    }
    
    $invoices = json_decode(file_get_contents($invoicesFile), true);
    
    if (!$invoices) {
        echo json_encode([]);
        exit;
    }
    
    // Sort invoices by date (newest first)
    usort($invoices, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    // Update overdue status
    foreach ($invoices as &$invoice) {
        if ($invoice['status'] !== 'paid' && strtotime($invoice['due_date']) < time()) {
            $invoice['status'] = 'overdue';
        }
    }
    
    echo json_encode($invoices);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>