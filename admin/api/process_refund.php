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
    $required_fields = ['transactionId', 'paymentMethod', 'clientEmail', 'refundAmount', 'refundType', 'refundReason', 'refundExplanation', 'adminPassword'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Verify admin password (in production, use proper password verification)
    // For demo purposes, we'll use a simple check
    if ($input['adminPassword'] !== 'admin123') {
        throw new Exception('Invalid admin password');
    }
    
    // Generate refund reference ID
    $refundId = 'REF_' . date('Ymd') . '_' . strtoupper(substr(md5($input['transactionId'] . time()), 0, 8));
    
    // Create refund record
    $refundRecord = [
        'refund_id' => $refundId,
        'transaction_id' => $input['transactionId'],
        'payment_method' => $input['paymentMethod'],
        'client_email' => $input['clientEmail'],
        'original_amount' => $input['originalAmount'] ?? '',
        'refund_amount' => $input['refundAmount'],
        'refund_type' => $input['refundType'],
        'reason_category' => $input['refundReason'],
        'reason_explanation' => $input['refundExplanation'],
        'processed_by' => 'admin',
        'processed_at' => date('Y-m-d H:i:s'),
        'status' => 'processed',
        'notify_client' => $input['notifyClient'] ?? false,
        'update_records' => $input['updateRecords'] ?? false,
        'admin_notification' => $input['adminNotification'] ?? false
    ];
    
    // Save refund record to file
    $refundsFile = '../../logs/refunds.json';
    $refunds = [];
    
    if (file_exists($refundsFile)) {
        $refunds = json_decode(file_get_contents($refundsFile), true) ?: [];
    }
    
    $refunds[] = $refundRecord;
    
    // Ensure logs directory exists
    if (!file_exists(dirname($refundsFile))) {
        mkdir(dirname($refundsFile), 0755, true);
    }
    
    file_put_contents($refundsFile, json_encode($refunds, JSON_PRETTY_PRINT));
    
    // Send client notification email if requested
    if ($input['notifyClient']) {
        $clientSubject = 'Refund Processed - App Craft Services';
        $clientMessage = "Dear Valued Client,

Your refund has been processed successfully.

REFUND DETAILS:
• Refund ID: {$refundId}
• Transaction ID: {$input['transactionId']}
• Refund Amount: {$input['refundAmount']}
• Processing Date: " . date('F j, Y') . "

The refund will appear in your account within 3-5 business days depending on your payment method.

If you have any questions about this refund, please contact us at hello@appcraftservices.com.

Thank you for your understanding.

Best regards,
App Craft Services Team

---
This is an automated message. Please do not reply to this email.";

        $clientHeaders = "From: App Craft Services <hello@appcraftservices.com>\r\n";
        $clientHeaders .= "Reply-To: hello@appcraftservices.com\r\n";
        $clientHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        mail($input['clientEmail'], $clientSubject, $clientMessage, $clientHeaders);
    }
    
    // Send admin notification if requested
    if ($input['adminNotification']) {
        $adminSubject = 'Refund Processed - Admin Notification';
        $adminMessage = "A refund has been processed:

REFUND DETAILS:
• Refund ID: {$refundId}
• Transaction ID: {$input['transactionId']}
• Client Email: {$input['clientEmail']}
• Payment Method: {$input['paymentMethod']}
• Refund Amount: {$input['refundAmount']}
• Refund Type: {$input['refundType']}
• Reason: {$input['refundReason']}
• Explanation: {$input['refundExplanation']}
• Processed At: " . date('Y-m-d H:i:s') . "

Please follow up with the payment processor to complete the refund process.";

        $adminHeaders = "From: Admin System <noreply@appcraftservices.com>\r\n";
        $adminHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        mail('hello@appcraftservices.com', $adminSubject, $adminMessage, $adminHeaders);
    }
    
    // In a real implementation, you would integrate with payment processors here:
    // - Stripe: \Stripe\Refund::create()
    // - PayPal: PayPal API refund call
    // - Bank Transfer: Manual processing notification
    
    echo json_encode([
        'success' => true,
        'message' => 'Refund processed successfully',
        'refundId' => $refundId,
        'transactionId' => $input['transactionId'],
        'amount' => $input['refundAmount']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>