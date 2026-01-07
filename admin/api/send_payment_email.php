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
    
    // Ensure amounts have $ sign
    if ($amount && !str_starts_with($amount, '$')) {
        $amount = '$' . $amount;
    }
    if ($totalAmount && !str_starts_with($totalAmount, '$')) {
        $totalAmount = '$' . $totalAmount;
    }
    
    // Map stage to readable text
    $stageText = [
        'initial' => 'Initial Payment (50%)',
        'final' => 'Final Payment (50%)',
        'full' => 'Full Payment (100%)'
    ];
    
    $stageDisplay = isset($stageText[$stage]) ? $stageText[$stage] : $stage;
    
    // Create email subject and body
    $subject = "Payment Request - App Craft Services ({$amount})";
    
    // Create HTML email body to avoid spam and use proper hyperlinks
    $htmlBody = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Payment Request - App Craft Services</title>
</head>
<body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
    <div style='background: #f8f9fa; padding: 30px; border-radius: 10px; border: 1px solid #e9ecef;'>
        <div style='text-align: center; margin-bottom: 30px;'>
            <h1 style='color: #2563eb; margin: 0; font-size: 28px;'>App Craft Services</h1>
            <p style='color: #6b7280; margin: 5px 0 0 0; font-size: 16px;'>Payment Request</p>
        </div>
        
        <div style='background: white; padding: 25px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
            <h2 style='color: #1f2937; margin-top: 0; font-size: 22px;'>Dear Valued Client,</h2>
            <p style='margin-bottom: 20px; font-size: 16px;'>Thank you for choosing App Craft Services for your project! We've prepared a secure payment link for your <strong>{$description}</strong> service.</p>
            
            <div style='background: #f3f4f6; padding: 20px; border-radius: 6px; margin: 20px 0;'>
                <h3 style='color: #374151; margin-top: 0; font-size: 18px; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px;'>Payment Details</h3>
                <table style='width: 100%; border-collapse: collapse;'>
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold; color: #6b7280;'>Service:</td>
                        <td style='padding: 8px 0; text-align: right; color: #1f2937;'>{$description}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold; color: #6b7280;'>Payment Stage:</td>
                        <td style='padding: 8px 0; text-align: right; color: #2563eb; font-weight: bold;'>{$stageDisplay}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold; color: #6b7280;'>Payment Amount:</td>
                        <td style='padding: 8px 0; text-align: right; color: #059669; font-weight: bold; font-size: 20px;'>{$amount}</td>
                    </tr>";

    if ($totalAmount && $totalAmount !== $amount) {
        $htmlBody .= "
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold; color: #6b7280;'>Total Project Value:</td>
                        <td style='padding: 8px 0; text-align: right; color: #1f2937;'>{$totalAmount}</td>
                    </tr>";
    }

    $htmlBody .= "
                </table>
            </div>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='{$paymentLink}' style='display: inline-block; background: #2563eb; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 18px; box-shadow: 0 4px 6px rgba(37, 99, 235, 0.3);'>
                    🔒 Complete Secure Payment
                </a>
                <p style='margin: 15px 0 0 0; font-size: 14px; color: #6b7280;'>Click the button above to access your secure payment page</p>
            </div>
            
            <div style='background: #ecfdf5; border: 1px solid #d1fae5; padding: 20px; border-radius: 6px; margin: 20px 0;'>
                <h4 style='color: #065f46; margin-top: 0; font-size: 16px;'>✓ Available Payment Methods</h4>
                <ul style='margin: 10px 0; padding-left: 20px; color: #047857;'>
                    <li style='margin: 5px 0;'><strong>Credit/Debit Card (Stripe)</strong> - Instant processing</li>
                    <li style='margin: 5px 0;'><strong>PayPal</strong> - Pay with your PayPal account</li>
                    <li style='margin: 5px 0;'><strong>Direct Bank Transfer</strong> - Transfer directly to our account</li>
                </ul>
            </div>
            
            <div style='background: #fef3c7; border: 1px solid #fcd34d; padding: 15px; border-radius: 6px; margin: 20px 0;'>
                <p style='margin: 0; color: #92400e; font-size: 14px;'>
                    <strong>🔐 Security Notice:</strong> This link is secure and encrypted for your protection. 
                    You'll receive a confirmation email once payment is processed.
                </p>
            </div>
        </div>
        
        <div style='text-align: center; padding: 20px; border-top: 1px solid #e5e7eb;'>
            <p style='margin: 0 0 10px 0; color: #6b7280; font-size: 14px;'>
                Questions about your payment or project? Contact us:
            </p>
            <p style='margin: 0; color: #2563eb; font-weight: bold;'>
                📧 <a href='mailto:hello@appcraftservices.com' style='color: #2563eb; text-decoration: none;'>hello@appcraftservices.com</a>
            </p>
            <p style='margin: 15px 0 0 0; color: #6b7280; font-size: 12px;'>
                App Craft Services | <a href='https://appcraftservices.com' style='color: #6b7280;'>appcraftservices.com</a>
            </p>
        </div>
    </div>
</body>
</html>";

    // Plain text version for better deliverability
    $textBody = "Dear Valued Client,

Thank you for choosing App Craft Services for your project!

We've prepared a secure payment link for your {$description} service.

PAYMENT DETAILS:
• Service: {$description}
• Payment Stage: {$stageDisplay}
• Payment Amount: {$amount}";

    if ($totalAmount && $totalAmount !== $amount) {
        $textBody .= "
• Total Project Value: {$totalAmount}";
    }

    $textBody .= "

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

If you have any questions about your payment or project, please contact us at hello@appcraftservices.com

We're excited to work with you and deliver exceptional results!

Best regards,
The App Craft Services Team

---
App Craft Services
Email: hello@appcraftservices.com
Website: https://appcraftservices.com";

    // Enhanced email headers to avoid spam
    $headers = "From: App Craft Services <hello@appcraftservices.com>\r\n";
    $headers .= "Reply-To: hello@appcraftservices.com\r\n";
    $headers .= "Return-Path: hello@appcraftservices.com\r\n";
    $headers .= "Organization: App Craft Services\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"boundary123\"\r\n";
    $headers .= "X-Mailer: App Craft Services Payment System\r\n";
    $headers .= "X-Priority: 3\r\n";
    $headers .= "X-MSMail-Priority: Normal\r\n";
    $headers .= "Importance: Normal\r\n";
    
    // Create multipart email body
    $emailBody = "--boundary123\r\n";
    $emailBody .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $emailBody .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $emailBody .= $textBody . "\r\n\r\n";
    $emailBody .= "--boundary123\r\n";
    $emailBody .= "Content-Type: text/html; charset=UTF-8\r\n";
    $emailBody .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $emailBody .= $htmlBody . "\r\n\r\n";
    $emailBody .= "--boundary123--";
    
    // Send email
    if (mail($clientEmail, $subject, $emailBody, $headers)) {
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