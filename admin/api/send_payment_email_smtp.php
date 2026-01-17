<?php
/**
 * Enhanced Payment Email Sender with SMTP Support
 * This version uses SMTP for better deliverability
 * 
 * To use this file:
 * 1. Install PHPMailer: composer require phpmailer/phpmailer
 * 2. Configure SMTP settings in data/settings.json
 * 3. Rename this file to send_payment_email.php (backup the old one first)
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

// Check if PHPMailer is installed
if (!file_exists('../../vendor/autoload.php')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'PHPMailer not installed. Run: composer require phpmailer/phpmailer'
    ]);
    exit;
}

require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
    
    // Load SMTP settings
    $settingsFile = '../../data/settings.json';
    $smtpSettings = [];
    if (file_exists($settingsFile)) {
        $settings = json_decode(file_get_contents($settingsFile), true);
        if (isset($settings['email'])) {
            $smtpSettings = $settings['email'];
        }
    }
    
    // Create PHPMailer instance
    $mail = new PHPMailer(true);
    
    // Server settings
    if (!empty($smtpSettings['smtp_host'])) {
        $mail->isSMTP();
        $mail->Host = $smtpSettings['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtpSettings['smtp_username'] ?? '';
        $mail->Password = $smtpSettings['smtp_password'] ?? '';
        $mail->SMTPSecure = ($smtpSettings['smtp_encryption'] ?? true) ? PHPMailer::ENCRYPTION_STARTTLS : '';
        $mail->Port = $smtpSettings['smtp_port'] ?? 587;
    } else {
        // Fallback to PHP mail()
        $mail->isMail();
    }
    
    // Recipients
    $mail->setFrom('hello@appcraftservices.com', 'App Craft Services');
    $mail->addAddress($clientEmail);
    $mail->addReplyTo('hello@appcraftservices.com', 'App Craft Services');
    
    // Content
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = "Your payment details for " . $description;
    
    // HTML body
    $htmlBody = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Payment Request</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; }
        .header h1 { color: white; margin: 0; font-size: 28px; font-weight: 300; }
        .content { padding: 40px 30px; }
        .payment-box { background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 12px; padding: 25px; margin: 25px 0; }
        .payment-button { display: block; width: 100%; max-width: 300px; margin: 30px auto; padding: 18px 30px; background: #28a745; color: white; text-decoration: none; border-radius: 8px; text-align: center; font-weight: bold; font-size: 18px; }
        .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .details-table td { padding: 12px; border-bottom: 1px solid #dee2e6; }
        .details-table td:first-child { font-weight: bold; color: #495057; width: 40%; }
        .footer { background: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #dee2e6; }
        .security-badge { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 6px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>App Craft Services</h1>
            <p style='color: #f8f9fa; margin: 10px 0 0 0; font-size: 16px;'>Payment Request</p>
        </div>
        
        <div class='content'>
            <h2 style='color: #343a40; margin-top: 0;'>Hello!</h2>
            <p style='font-size: 16px; line-height: 1.6; color: #495057;'>
                Thank you for choosing App Craft Services. Your payment details for <strong>{$description}</strong> are ready below.
            </p>
            
            <div class='payment-box'>
                <h3 style='color: #495057; margin-top: 0; text-align: center;'>Payment Information</h3>
                <table class='details-table'>
                    <tr>
                        <td>Service</td>
                        <td>{$description}</td>
                    </tr>
                    <tr>
                        <td>Payment Stage</td>
                        <td style='color: #007bff; font-weight: bold;'>{$stageDisplay}</td>
                    </tr>
                    <tr>
                        <td>Amount Due</td>
                        <td style='color: #28a745; font-weight: bold; font-size: 20px;'>{$amount}</td>
                    </tr>";

    if ($totalAmount && $totalAmount !== $amount) {
        $htmlBody .= "
                    <tr>
                        <td>Total Project</td>
                        <td>{$totalAmount}</td>
                    </tr>";
    }

    $htmlBody .= "
                </table>
            </div>
            
            <div style='text-align: center; margin: 40px 0;'>
                <a href='{$paymentLink}' class='payment-button' style='color: white; text-decoration: none;'>
                    Complete Payment
                </a>
                <p style='margin: 15px 0 0 0; font-size: 14px; color: #6c757d;'>
                    Click above to proceed with your payment
                </p>
            </div>
            
            <div class='security-badge'>
                <strong>Payment Options Available:</strong>
                <ul style='margin: 10px 0; padding-left: 20px;'>
                    <li>PayPal</li>
                    <li>Bank Transfer</li>
                </ul>
            </div>
            
            <p style='font-size: 14px; color: #6c757d; line-height: 1.5;'>
                <strong>Need assistance?</strong> Reply to this email or contact us at 
                <a href='mailto:hello@appcraftservices.com' style='color: #007bff;'>hello@appcraftservices.com</a>
            </p>
        </div>
        
        <div class='footer'>
            <p style='margin: 0; color: #6c757d; font-size: 14px;'>
                <strong>App Craft Services</strong><br>
                Professional Web Development Solutions<br>
                <a href='https://appcraftservices.com' style='color: #007bff;'>appcraftservices.com</a>
            </p>
            <p style='margin: 15px 0 0 0; color: #adb5bd; font-size: 12px;'>
                You received this email because you requested a payment link from App Craft Services.
            </p>
        </div>
    </div>
</body>
</html>";

    $mail->Body = $htmlBody;
    
    // Plain text version
    $textBody = "Hello!

Thank you for choosing App Craft Services.

PAYMENT DETAILS:
Service: {$description}
Payment Stage: {$stageDisplay}
Payment Amount: {$amount}";

    if ($totalAmount && $totalAmount !== $amount) {
        $textBody .= "
Total Project Value: {$totalAmount}";
    }

    $textBody .= "

SECURE PAYMENT LINK:
{$paymentLink}

PAYMENT OPTIONS AVAILABLE:
- PayPal
- Bank Transfer

This link is secure and encrypted for your protection.

Need assistance? Contact us at hello@appcraftservices.com

Best regards,
App Craft Services Team
https://appcraftservices.com";

    $mail->AltBody = $textBody;
    
    // Additional headers for better deliverability
    $mail->addCustomHeader('X-Mailer', 'App Craft Services Payment System v3.0');
    $mail->addCustomHeader('X-Priority', '3');
    $mail->addCustomHeader('List-Unsubscribe', '<mailto:hello@appcraftservices.com?subject=Unsubscribe>');
    
    // Send email
    $mail->send();
    
    // Log the email sending
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'client_email' => $clientEmail,
        'amount' => $amount,
        'stage' => $stageDisplay,
        'description' => $description,
        'payment_link' => $paymentLink,
        'delivery_method' => !empty($smtpSettings['smtp_host']) ? 'SMTP' : 'PHP mail()',
        'status' => 'sent'
    ];
    
    $logFile = '../../logs/payment_emails.log';
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment email sent successfully to ' . $clientEmail
    ]);
    
} catch (Exception $e) {
    // Log error
    $errorLog = [
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => $e->getMessage(),
        'client_email' => $clientEmail ?? 'unknown'
    ];
    
    $logFile = '../../logs/payment_email_errors.log';
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    file_put_contents($logFile, json_encode($errorLog) . "\n", FILE_APPEND | LOCK_EX);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send email: ' . $e->getMessage()
    ]);
}
?>
