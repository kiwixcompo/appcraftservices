<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
        throw new Exception('Invalid input data - no JSON received');
    }
    
    // Log received data for debugging
    error_log('Invoice save attempt: ' . json_encode($input));
    
    // Validate required fields
    $required = ['invoice_number', 'client_name', 'project_name', 'total_amount'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    $invoicesFile = '../../data/invoices.json';
    $invoices = [];
    
    // Check if directory exists
    $dataDir = '../../data/';
    if (!is_dir($dataDir)) {
        throw new Exception('Data directory does not exist');
    }
    
    if (!is_writable($dataDir)) {
        throw new Exception('Data directory is not writable');
    }
    
    // Load existing invoices
    if (file_exists($invoicesFile)) {
        $content = file_get_contents($invoicesFile);
        if ($content === false) {
            throw new Exception('Cannot read invoices file');
        }
        $invoices = json_decode($content, true);
        if ($invoices === null) {
            throw new Exception('Invalid JSON in invoices file');
        }
    }
    
    // Check if invoice number already exists
    foreach ($invoices as $existingInvoice) {
        if ($existingInvoice['invoice_number'] === $input['invoice_number']) {
            throw new Exception('Invoice number already exists');
        }
    }
    
    // Add new invoice
    $invoice = [
        'id' => uniqid('inv_', true),
        'invoice_number' => $input['invoice_number'],
        'invoice_date' => $input['invoice_date'],
        'due_date' => $input['due_date'],
        'status' => $input['status'] ?? 'draft',
        'client_name' => $input['client_name'],
        'client_email' => $input['client_email'] ?? '',
        'client_address' => $input['client_address'] ?? '',
        'project_name' => $input['project_name'],
        'project_type' => $input['project_type'] ?? 'Custom',
        'project_description' => $input['project_description'] ?? '',
        'total_amount' => floatval($input['total_amount']),
        'amount_paid' => floatval($input['amount_paid'] ?? 0),
        'amount_due' => floatval($input['amount_due']),
        'tax_rate' => floatval($input['tax_rate'] ?? 0),
        'currency' => $input['currency'] ?? 'USD',
        'notes' => $input['notes'] ?? '',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $invoices[] = $invoice;
    
    // Save invoices
    $jsonData = json_encode($invoices, JSON_PRETTY_PRINT);
    if ($jsonData === false) {
        throw new Exception('Failed to encode JSON data');
    }
    
    $result = file_put_contents($invoicesFile, $jsonData);
    if ($result === false) {
        throw new Exception('Failed to write to invoices file');
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Invoice saved successfully', 
        'invoice_id' => $invoice['id'],
        'invoice_number' => $invoice['invoice_number']
    ]);
    
} catch (Exception $e) {
    error_log('Invoice save error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>