<?php
// Simple test to check if we can save invoice data
session_start();

// Set admin session for testing
$_SESSION['admin_logged_in'] = true;

header('Content-Type: application/json');

try {
    // Test data
    $testInvoice = [
        'id' => 'test_' . time(),
        'invoice_number' => 'TEST-001',
        'client_name' => 'Test Client',
        'project_name' => 'Test Project',
        'total_amount' => 100.00,
        'amount_due' => 100.00,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $invoicesFile = '../data/invoices.json';
    
    // Check if file exists and is readable
    if (file_exists($invoicesFile)) {
        echo json_encode(['status' => 'File exists', 'readable' => is_readable($invoicesFile), 'writable' => is_writable($invoicesFile)]);
    } else {
        echo json_encode(['status' => 'File does not exist', 'directory_writable' => is_writable('../data/')]);
    }
    
    // Try to read existing data
    $invoices = [];
    if (file_exists($invoicesFile)) {
        $content = file_get_contents($invoicesFile);
        $invoices = json_decode($content, true) ?: [];
        echo json_encode(['existing_invoices_count' => count($invoices), 'file_content_length' => strlen($content)]);
    }
    
    // Try to write test data
    $invoices[] = $testInvoice;
    $result = file_put_contents($invoicesFile, json_encode($invoices, JSON_PRETTY_PRINT));
    
    if ($result !== false) {
        echo json_encode(['success' => true, 'bytes_written' => $result]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to write file']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>