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
    
    if (!$input || !isset($input['client_email'])) {
        throw new Exception('Invalid input data - client email is required');
    }
    
    $clientEmail = $input['client_email'];
    $clientName = $input['client_name'] ?? 'Valued Client';
    $invoiceNumber = $input['invoice_number'] ?? '';
    $amountDue = $input['amount_due'] ?? '0';
    $dueDate = $input['due_date'] ?? '';
    $projectName = $input['project_name'] ?? '';
    $projectType = $input['project_type'] ?? '';
    $totalAmount = $input['total_amount'] ?? '0';
    $amountPaid = $input['amount_paid'] ?? '0';
    $currency = $input['currency'] ?? 'USD';
    $notes = $input['notes'] ?? '';
    
    // Ensure amounts have $ sign if currency is USD
    if ($currency === 'USD') {
        if ($amountDue && !str_starts_with($amountDue, '$')) {
            $amountDue = '$' . $amountDue;
        }
        if ($totalAmount && !str_starts_with($totalAmount, '$')) {
            $totalAmount = '$' . $totalAmount;
        }
        if ($amountPaid && !str_starts_with($amountPaid, '$')) {
            $amountPaid = '$' . $amountPaid;
        }
    }
    
    // Create email subject
    $subject = "Invoice {$invoiceNumber} from App Craft Services";
    
    // Create HTML email body
    $htmlBody = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Invoice</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; }
        .header h1 { color: white; margin: 0; font-size: 28px; font-weight: 300; }
        .content { padding: 40px 30px; }
        .invoice-box { background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 12px; padding: 25px; margin: 25px 0; }
        .invoice-button { display: block; width: 100%; max-width: 300px; margin: 30px auto; padding: 18px 30px; background: #28a745; color: white; text-decoration: none; border-radius: 8px; text-align: center; font-weight: bold; font-size: 18px; }
        .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .details-table td { padding: 12px; border-bottom: 1px solid #dee2e6; }
        .details-table .label { font-weight: bold; color: #495057; width: 40%; }
        .amount-due { font-size: 24px; font-weight: bold; color: #dc3545; text-align: center; margin: 20px 0; }
        .footer { background: #f8f9fa; padding: 30px; text-align: center; color: #6c757d; font-size: 14px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>App Craft Services</h1>
            <p style='color: #e9ecef; margin: 10px 0 0 0; font-size: 16px;'>Professional Web Development</p>
        </div>
        
        <div class='content'>
            <h2 style='color: #495057; margin-bottom: 10px;'>Hello {$clientName},</h2>
            <p style='color: #6c757d; line-height: 1.6; margin-bottom: 30px;'>
                Thank you for choosing App Craft Services for your web development needs. 
                Please find your invoice details below.
            </p>
            
            <div class='invoice-box'>
                <h3 style='color: #495057; margin-top: 0; text-align: center;'>Invoice {$invoiceNumber}</h3>
                
                <table class='details-table'>
                    <tr>
                        <td class='label'>Project:</td>
                        <td>{$projectName}</td>
                    </tr>
                    <tr>
                        <td class='label'>Type:</td>
                        <td>{$projectType}</td>
                    </tr>
                    <tr>
                        <td class='label'>Total Amount:</td>
                        <td><strong>{$totalAmount}</strong></td>
                    </tr>
                    <tr>
                        <td class='label'>Amount Paid:</td>
                        <td style='color: #28a745;'><strong>{$amountPaid}</strong></td>
                    </tr>
                    <tr>
                        <td class='label'>Due Date:</td>
                        <td>{$dueDate}</td>
                    </tr>
                </table>
                
                <div class='amount-due'>
                    Amount Due: {$amountDue}
                </div>
                
                " . ($notes ? "<div style='margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px;'>
                    <strong>Notes:</strong><br>
                    " . nl2br(htmlspecialchars($notes)) . "
                </div>" : "") . "
            </div>
            
            <p style='color: #6c757d; line-height: 1.6; text-align: center;'>
                If you have any questions about this invoice, please don't hesitate to contact us.
            </p>
        </div>
        
        <div class='footer'>
            <p><strong>App Craft Services</strong></p>
            <p>Email: hello@appcraftservices.com | Phone: +2348061581916</p>
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html>";

    // Create plain text version
    $textBody = "Invoice {$invoiceNumber} from App Craft Services\n\n";
    $textBody .= "Hello {$clientName},\n\n";
    $textBody .= "Thank you for choosing App Craft Services. Please find your invoice details below:\n\n";
    $textBody .= "Project: {$projectName}\n";
    $textBody .= "Type: {$projectType}\n";
    $textBody .= "Total Amount: {$totalAmount}\n";
    $textBody .= "Amount Paid: {$amountPaid}\n";
    $textBody .= "Amount Due: {$amountDue}\n";
    $textBody .= "Due Date: {$dueDate}\n\n";
    if ($notes) {
        $textBody .= "Notes: {$notes}\n\n";
    }
    $textBody .= "If you have any questions, please contact us at hello@appcraftservices.com\n\n";
    $textBody .= "Thank you for your business!\n";
    $textBody .= "App Craft Services";

    // Email headers for better deliverability
    $headers = [
        'From: App Craft Services <hello@appcraftservices.com>',
        'Reply-To: hello@appcraftservices.com',
        'X-Mailer: PHP/' . phpversion(),
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="boundary123"',
        'X-Priority: 3',
        'X-MSMail-Priority: Normal',
        'Importance: Normal'
    ];

    // Create multipart email body
    $emailBody = "--boundary123\r\n";
    $emailBody .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $emailBody .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $emailBody .= $textBody . "\r\n\r\n";
    $emailBody .= "--boundary123\r\n";
    $emailBody .= "Content-Type: text/html; charset=UTF-8\r\n";
    $emailBody .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $emailBody .= $htmlBody . "\r\n\r\n";
    $emailBody .= "--boundary123--";

    // Send email
    $success = mail($clientEmail, $subject, $emailBody, implode("\r\n", $headers));
    
    if ($success) {
        echo json_encode([
            'success' => true, 
            'message' => 'Invoice email sent successfully to ' . $clientEmail
        ]);
    } else {
        throw new Exception('Failed to send email');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
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
            <p style='color: #f8f9fa; margin: 10px 0 0 0; font-size: 16px;'>Invoice</p>
        </div>
        
        <div class='content'>
            <h2 style='color: #343a40; margin-top: 0;'>Hello {$clientName}!</h2>
            <p style='font-size: 16px; line-height: 1.6; color: #495057;'>
                Thank you for choosing App Craft Services. Please find your invoice details below.
            </p>
            
            <div class='invoice-box'>
                <h3 style='color: #495057; margin-top: 0; text-align: center;'>Invoice Details</h3>
                <table class='details-table'>
                    <tr>
                        <td>Invoice Number</td>
                        <td>{$invoiceNumber}</td>
                    </tr>
                    <tr>
                        <td>Amount Due</td>
                        <td style='color: #dc3545; font-weight: bold; font-size: 20px;'>{$amountDue}</td>
                    </tr>
                    <tr>
                        <td>Due Date</td>
                        <td>" . date('F j, Y', strtotime($dueDate)) . "</td>
                    </tr>
                </table>
            </div>
            
            <div style='text-align: center; margin: 40px 0;'>
                <a href='https://appcraftservices.com/admin/api/view_invoice.php?id={$invoiceId}&token=" . md5($invoiceId . 'invoice_view') . "' class='invoice-button' style='color: white; text-decoration: none;'>
                    View Full Invoice
                </a>
                <p style='margin: 15px 0 0 0; font-size: 14px; color: #6c757d;'>
                    Click above to view the complete invoice details
                </p>
            </div>
            
            <div class='security-badge'>
                <strong>Payment Options:</strong>
                <ul style='margin: 10px 0; padding-left: 20px;'>
                    <li>PayPal</li>
                    <li>Bank Transfer</li>
                    <li>Contact us for other payment methods</li>
                </ul>
            </div>
            
            <p style='font-size: 14px; color: #6c757d; line-height: 1.5;'>
                <strong>Questions?</strong> Reply to this email or contact us at 
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
                This invoice was sent from App Craft Services. Please keep this email for your records.
            </p>
        </div>
    </div>
</body>
</html>";

    // Plain text version
    $textBody = "Hello {$clientName}!

Thank you for choosing App Craft Services.

INVOICE DETAILS:
Invoice Number: {$invoiceNumber}
Amount Due: {$amountDue}
Due Date: " . date('F j, Y', strtotime($dueDate)) . "

VIEW INVOICE:
https://appcraftservices.com/admin/api/view_invoice.php?id={$invoiceId}&token=" . md5($invoiceId . 'invoice_view') . "

PAYMENT OPTIONS:
- PayPal
- Bank Transfer
- Contact us for other payment methods

Questions? Contact us at hello@appcraftservices.com

Best regards,
App Craft Services Team
https://appcraftservices.com";

    // Enhanced email headers
    $headers = array();
    $headers[] = "From: App Craft Services <hello@appcraftservices.com>";
    $headers[] = "Reply-To: App Craft Services <hello@appcraftservices.com>";
    $headers[] = "Return-Path: hello@appcraftservices.com";
    $headers[] = "Organization: App Craft Services";
    $headers[] = "X-Sender: hello@appcraftservices.com";
    $headers[] = "X-Mailer: App Craft Services Invoice System v1.0";
    $headers[] = "X-Priority: 3";
    $headers[] = "X-MSMail-Priority: Normal";
    $headers[] = "Importance: Normal";
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: multipart/alternative; boundary=\"boundary789\"";
    $headers[] = "Message-ID: <" . time() . "." . md5($clientEmail . $invoiceId) . "@appcraftservices.com>";
    $headers[] = "Date: " . date('r');
    
    // Anti-spam headers
    $headers[] = "X-Spam-Status: No";
    $headers[] = "X-Authenticated-Sender: hello@appcraftservices.com";
    $headers[] = "List-Unsubscribe: <mailto:hello@appcraftservices.com?subject=Unsubscribe>";
    
    // Create multipart email body
    $emailBody = "--boundary789\r\n";
    $emailBody .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $emailBody .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $emailBody .= $textBody . "\r\n\r\n";
    $emailBody .= "--boundary789\r\n";
    $emailBody .= "Content-Type: text/html; charset=UTF-8\r\n";
    $emailBody .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $emailBody .= $htmlBody . "\r\n\r\n";
    $emailBody .= "--boundary789--";
    
    // Send email
    $headerString = implode("\r\n", $headers);
    
    if (mail($clientEmail, $subject, $emailBody, $headerString)) {
        // Log the email sending
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'invoice_id' => $invoiceId,
            'client_email' => $clientEmail,
            'invoice_number' => $invoiceNumber,
            'amount_due' => $amountDue,
            'type' => 'invoice_email'
        ];
        
        $logFile = '../../logs/invoice_emails.log';
        if (!file_exists(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Invoice emailed successfully to ' . $clientEmail
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