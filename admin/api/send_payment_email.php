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
    
    if (!$input || !isset($input['email']) || !isset($input['paymentLink'])) {
        throw new Exception('Invalid input data');
    }
    
    $clientEmail = $input['email'];
    $paymentLink = $input['paymentLink'];
    $amount = $input['amount'] ?? '';
    $description = $input['description'] ?? '';
    $stage = $input['stage'] ?? '';
    $totalAmount = $input['totalAmount'] ?? '';
    
    // Map stage to readable text
    $stageText = [
        'initial' => 'Initial Payment (50%)',
        'final' => 'Final Payment (50%)',
        'full' => 'Full Payment (100%)'
    ];
    
    $stageDisplay = isset($stageText[$stage]) ? $stageText[$stage] : $stage;
    
    // Create email subject and body
    $subject = "Payment Request - App Craft Services ({$amount})";
    
    $body = "Dear Valued Client,

Thank you for choosing App Craft Services for your project!

We've prepared a secure payment link for your {$description} service.

PAYMENT DETAILS:
• Service: {$description}
• Payment Stage: {$stageDisplay}
• Payment Amount: {$amount}";

    if ($totalAmount && $totalAmount !== $amount) {
        $body .= "
• Total Project Value: {$totalAmount}";
    }

    $body .= "

SECURE PAYMENT LINK:
{$paymentLink}

You can complete your payment using any of these secure methods:
✓ Credit/Debit Card (Stripe) - Instant processing
✓ PayPal - Pay with your PayPal account
✓ Direct Bank Transfer - Transfer directly to our account

IMPORTANT NOTES:
• This link is secure and encrypted for your protection
• You'll receive a confirmation email once payment is processed
• For bank transfers, please include your email address in the reference field

If you have any questions about your payment or project, please don't hesitate to contact us at hello@appcraftservices.com or reply to this email.

We're excited to work with you and deliver exceptional results!

Best regards,
The App Craft Services Team

---
App Craft Services
Email: hello@appcraftservices.com
Website: https://appcraftservices.com

This is an automated message. Please do not reply to this email address.";

    // Email headers
    $headers = "From: App Craft Services <hello@appcraftservices.com>\r\n";
    $headers .= "Reply-To: hello@appcraftservices.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    // Send email
    if (mail($clientEmail, $subject, $body, $headers)) {
        // Log the email sending for admin records
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'client_email' => $clientEmail,
            'amount' => $amount,
            'stage' => $stageDisplay,
            'description' => $description,
            'payment_link' => $paymentLink
        ];
        
        // Save to log file (optional)
        $logFile = '../../logs/payment_emails.log';
        if (!file_exists(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Payment email sent successfully to ' . $clientEmail
        ]);
    } else {
        throw new Exception('Failed to send email');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>