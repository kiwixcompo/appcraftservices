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
    
    if (!$input || !isset($input['client_email']) || !isset($input['reply_message'])) {
        throw new Exception('Invalid input data');
    }
    
    $clientEmail = $input['client_email'];
    $clientName = $input['client_name'] ?? 'Valued Client';
    $replyMessage = $input['reply_message'];
    $originalSubject = $input['original_subject'] ?? 'Your inquiry';
    $messageId = $input['message_id'] ?? '';
    
    // Create email subject
    $subject = "Re: " . $originalSubject;
    
    // Create HTML email body
    $htmlBody = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Reply from App Craft Services</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; }
        .header h1 { color: white; margin: 0; font-size: 28px; font-weight: 300; }
        .content { padding: 40px 30px; }
        .message-box { background: #f8f9fa; border-left: 4px solid #007bff; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0; }
        .footer { background: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>App Craft Services</h1>
            <p style='color: #f8f9fa; margin: 10px 0 0 0; font-size: 16px;'>Reply to Your Inquiry</p>
        </div>
        
        <div class='content'>
            <h2 style='color: #343a40; margin-top: 0;'>Hello {$clientName}!</h2>
            <p style='font-size: 16px; line-height: 1.6; color: #495057;'>
                Thank you for contacting App Craft Services. Here's our response to your inquiry:
            </p>
            
            <div class='message-box'>
                <div style='font-size: 16px; line-height: 1.6; color: #495057; white-space: pre-line;'>{$replyMessage}</div>
            </div>
            
            <p style='font-size: 14px; color: #6c757d; line-height: 1.5; margin-top: 30px;'>
                <strong>Need further assistance?</strong> Feel free to reply to this email or contact us directly:
            </p>
            
            <div style='background: #e3f2fd; border: 1px solid #bbdefb; border-radius: 8px; padding: 20px; margin: 20px 0;'>
                <div style='font-size: 14px; color: #1565c0;'>
                    <div style='margin-bottom: 8px;'><strong>📧 Email:</strong> hello@appcraftservices.com</div>
                    <div style='margin-bottom: 8px;'><strong>🌐 Website:</strong> https://appcraftservices.com</div>
                    <div><strong>📅 Schedule a Call:</strong> <a href='https://appcraftservices.com/schedule' style='color: #1565c0;'>Book a consultation</a></div>
                </div>
            </div>
        </div>
        
        <div class='footer'>
            <p style='margin: 0; color: #6c757d; font-size: 14px;'>
                <strong>App Craft Services</strong><br>
                Professional Web Development Solutions<br>
                <a href='https://appcraftservices.com' style='color: #007bff;'>appcraftservices.com</a>
            </p>
            <p style='margin: 15px 0 0 0; color: #adb5bd; font-size: 12px;'>
                This email was sent in response to your inquiry. We appreciate your interest in our services.
            </p>
        </div>
    </div>
</body>
</html>";

    // Plain text version
    $textBody = "Hello {$clientName}!

Thank you for contacting App Craft Services. Here's our response to your inquiry:

{$replyMessage}

Need further assistance? Feel free to reply to this email or contact us directly:

Email: hello@appcraftservices.com
Website: https://appcraftservices.com
Schedule a Call: https://appcraftservices.com/schedule

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
    $headers[] = "X-Mailer: App Craft Services Message System v1.0";
    $headers[] = "X-Priority: 3";
    $headers[] = "X-MSMail-Priority: Normal";
    $headers[] = "Importance: Normal";
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: multipart/alternative; boundary=\"boundary456\"";
    $headers[] = "Message-ID: <" . time() . "." . md5($clientEmail . $messageId) . "@appcraftservices.com>";
    $headers[] = "Date: " . date('r');
    
    // Anti-spam headers
    $headers[] = "X-Spam-Status: No";
    $headers[] = "X-Authenticated-Sender: hello@appcraftservices.com";
    $headers[] = "List-Unsubscribe: <mailto:hello@appcraftservices.com?subject=Unsubscribe>";
    
    // Create multipart email body
    $emailBody = "--boundary456\r\n";
    $emailBody .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $emailBody .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $emailBody .= $textBody . "\r\n\r\n";
    $emailBody .= "--boundary456\r\n";
    $emailBody .= "Content-Type: text/html; charset=UTF-8\r\n";
    $emailBody .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $emailBody .= $htmlBody . "\r\n\r\n";
    $emailBody .= "--boundary456--";
    
    // Send email
    $headerString = implode("\r\n", $headers);
    
    if (mail($clientEmail, $subject, $emailBody, $headerString)) {
        // Mark original message as read if message_id provided
        if ($messageId) {
            $messagesFile = '../../data/messages.json';
            if (file_exists($messagesFile)) {
                $messages = json_decode(file_get_contents($messagesFile), true);
                if (is_array($messages)) {
                    for ($i = 0; $i < count($messages); $i++) {
                        if (isset($messages[$i]['id']) && $messages[$i]['id'] == $messageId) {
                            $messages[$i]['status'] = 'replied';
                            $messages[$i]['replied_at'] = date('Y-m-d H:i:s');
                            break;
                        }
                    }
                    file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT));
                }
            }
        }
        
        // Log the email sending
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message_id' => $messageId,
            'client_email' => $clientEmail,
            'reply_sent' => true,
            'type' => 'message_reply'
        ];
        
        $logFile = '../../logs/message_replies.log';
        if (!file_exists(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Reply sent successfully to ' . $clientEmail
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