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
    
    // Validate required fields
    $required = ['invoice_number', 'client_name', 'project_name', 'total_amount'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    $invoicesFile = '../../data/invoices.json';
    $invoices = [];
    
    // Load existing invoices
    if (file_exists($invoicesFile)) {
        $invoices = json_decode(file_get_contents($invoicesFile), true) ?: [];
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
    if (file_put_contents($invoicesFile, json_encode($invoices, JSON_PRETTY_PRINT))) {
        echo json_encode([
            'success' => true, 
            'message' => 'Invoice saved successfully', 
            'invoice_id' => $invoice['id'],
            'invoice_number' => $invoice['invoice_number']
        ]);
    } else {
        throw new Exception('Failed to save invoice');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>