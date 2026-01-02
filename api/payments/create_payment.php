<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'stripe_config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid input data');
    }
    
    $amount = $input['amount'] ?? 0;
    $currency = $input['currency'] ?? 'usd';
    $description = $input['description'] ?? 'App Craft Services Payment';
    $customerEmail = $input['customer_email'] ?? '';
    $customerName = $input['customer_name'] ?? '';
    $paymentMethod = $input['payment_method'] ?? 'stripe'; // 'stripe' or 'paypal'
    
    if ($amount <= 0) {
        throw new Exception('Invalid amount');
    }
    
    if ($paymentMethod === 'stripe') {
        // Stripe Payment Intent
        $config = include 'stripe_config.php';
        
        // In a real implementation, you would use the Stripe PHP SDK
        // For now, we'll create a mock response
        $paymentIntent = [
            'id' => 'pi_' . uniqid(),
            'client_secret' => 'pi_' . uniqid() . '_secret_' . uniqid(),
            'amount' => $amount * 100, // Stripe uses cents
            'currency' => $currency,
            'status' => 'requires_payment_method'
        ];
        
        // Log the payment attempt
        $logData = [
            'payment_id' => $paymentIntent['id'],
            'amount' => $amount,
            'currency' => $currency,
            'description' => $description,
            'customer_email' => $customerEmail,
            'customer_name' => $customerName,
            'payment_method' => 'stripe',
            'status' => 'initiated',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Save payment log
        $dataDir = '../../data';
        if (!file_exists($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
        
        $paymentsFile = $dataDir . '/payments.json';
        $payments = [];
        
        if (file_exists($paymentsFile)) {
            $payments = json_decode(file_get_contents($paymentsFile), true) ?: [];
        }
        
        $payments[] = $logData;
        file_put_contents($paymentsFile, json_encode($payments, JSON_PRETTY_PRINT));
        
        echo json_encode([
            'success' => true,
            'payment_method' => 'stripe',
            'client_secret' => $paymentIntent['client_secret'],
            'publishable_key' => $config['publishable_key']
        ]);
        
    } elseif ($paymentMethod === 'paypal') {
        // PayPal Payment
        $paypalOrderId = 'PAYPAL_' . uniqid();
        
        // Log the payment attempt
        $logData = [
            'payment_id' => $paypalOrderId,
            'amount' => $amount,
            'currency' => $currency,
            'description' => $description,
            'customer_email' => $customerEmail,
            'customer_name' => $customerName,
            'payment_method' => 'paypal',
            'status' => 'initiated',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Save payment log
        $dataDir = '../../data';
        if (!file_exists($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
        
        $paymentsFile = $dataDir . '/payments.json';
        $payments = [];
        
        if (file_exists($paymentsFile)) {
            $payments = json_decode(file_get_contents($paymentsFile), true) ?: [];
        }
        
        $payments[] = $logData;
        file_put_contents($paymentsFile, json_encode($payments, JSON_PRETTY_PRINT));
        
        echo json_encode([
            'success' => true,
            'payment_method' => 'paypal',
            'order_id' => $paypalOrderId,
            'paypal_client_id' => 'your_paypal_client_id_here' // Replace with actual PayPal client ID
        ]);
        
    } else {
        throw new Exception('Unsupported payment method');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>