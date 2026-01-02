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
    $paymentsFile = '../../data/payments.json';
    
    if (!file_exists($paymentsFile)) {
        // Create sample payment data
        $samplePayments = [
            'statistics' => [
                'total_revenue' => 12450,
                'monthly_revenue' => 3200,
                'total_transactions' => 28,
                'pending_payments' => 3
            ],
            'transactions' => [
                [
                    'id' => 1,
                    'client_name' => 'John Doe',
                    'client_email' => 'john@example.com',
                    'amount' => 1500,
                    'currency' => 'USD',
                    'status' => 'completed',
                    'payment_method' => 'stripe',
                    'service' => 'Essential App',
                    'created_at' => '2024-12-20 10:30:00',
                    'updated_at' => '2024-12-20 10:35:00'
                ],
                [
                    'id' => 2,
                    'client_name' => 'Jane Smith',
                    'client_email' => 'jane@example.com',
                    'amount' => 2000,
                    'currency' => 'USD',
                    'status' => 'pending',
                    'payment_method' => 'paypal',
                    'service' => 'Custom Enterprise',
                    'created_at' => '2024-12-19 14:20:00',
                    'updated_at' => '2024-12-19 14:20:00'
                ],
                [
                    'id' => 3,
                    'client_name' => 'Bob Johnson',
                    'client_email' => 'bob@example.com',
                    'amount' => 800,
                    'currency' => 'USD',
                    'status' => 'completed',
                    'payment_method' => 'stripe',
                    'service' => 'Maintenance & Support',
                    'created_at' => '2024-12-18 09:15:00',
                    'updated_at' => '2024-12-18 09:20:00'
                ]
            ]
        ];
        
        file_put_contents($paymentsFile, json_encode($samplePayments, JSON_PRETTY_PRINT));
        echo json_encode($samplePayments);
    } else {
        $payments = json_decode(file_get_contents($paymentsFile), true);
        echo json_encode($payments ?: []);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>