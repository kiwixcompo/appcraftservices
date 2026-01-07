<?php
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['email']) || !isset($input['amount'])) {
        throw new Exception('Invalid input data');
    }
    
    $email = $input['email'];
    $amount = $input['amount'];
    $description = $input['description'] ?? 'Service Payment';
    
    // Ensure amount has $ sign
    if ($amount && !str_starts_with($amount, '$')) {
        $amount = '$' . $amount;
    }
    
    // Email to admin about bank transfer
    $to = 'hello@appcraftservices.com';
    $subject = 'Bank Transfer Notification - App Craft Services';
    $message = "
    A client has indicated they completed a bank transfer:
    
    Client Email: {$email}
    Amount: {$amount}
    Service: {$description}
    Date: " . date('Y-m-d H:i:s') . "
    
    Please check your bank account for the incoming transfer and confirm receipt.
    
    Bank Details Used:
    Account Name: Williams Alfred Onen
    Account Number: 214720533676
    ACH Routing: 101019644
    
    Please follow up with the client once the transfer is confirmed.
    ";
    
    $headers = "From: App Craft Services <hello@appcraftservices.com>\r\n";
    $headers .= "Reply-To: {$email}\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    if (mail($to, $subject, $message, $headers)) {
        echo json_encode(['success' => true, 'message' => 'Notification sent']);
    } else {
        throw new Exception('Failed to send notification email');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>