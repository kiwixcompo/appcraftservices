<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Email configuration
$adminEmail = 'williamsaonen@gmail.com';
$fromEmail = 'noreply@appcraftservices.com';
$fromName = 'App Craft Services';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    // Get form data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        // Fallback to $_POST for regular form submission
        $input = $_POST;
    }
    
    // Validate required fields
    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $company = trim($input['company'] ?? '');
    $projectType = trim($input['project-type'] ?? '');
    $timeline = trim($input['timeline'] ?? '');
    $budget = trim($input['budget'] ?? '');
    $message = trim($input['project-details'] ?? $input['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($message)) {
        throw new Exception('Name, email, and message are required');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }
    
    // Generate unique message ID
    $messageId = uniqid('msg_', true);
    
    // Prepare message data
    $messageData = [
        'id' => $messageId,
        'name' => $name,
        'email' => $email,
        'company' => $company,
        'project_type' => $projectType,
        'timeline' => $timeline,
        'budget' => $budget,
        'message' => $message,
        'created_at' => date('Y-m-d H:i:s'),
        'read' => false,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    // Save message to file
    $dataDir = '../data';
    if (!file_exists($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    $messagesFile = $dataDir . '/messages.json';
    $messages = [];
    
    if (file_exists($messagesFile)) {
        $messages = json_decode(file_get_contents($messagesFile), true) ?: [];
    }
    
    $messages[] = $messageData;
    file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT));
    
    // Send email to admin
    $adminSubject = "New Contact Form Submission - App Craft Services";
    $adminMessage = "
    <html>
    <head>
        <title>New Contact Form Submission</title>
    </head>
    <body>
        <h2>New Contact Form Submission</h2>
        <p><strong>Message ID:</strong> {$messageId}</p>
        <p><strong>Date:</strong> " . date('F j, Y, g:i a') . "</p>
        <hr>
        <p><strong>Name:</strong> {$name}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Company:</strong> " . ($company ?: 'Not specified') . "</p>
        <p><strong>Project Type:</strong> " . ($projectType ?: 'Not specified') . "</p>
        <p><strong>Timeline:</strong> " . ($timeline ?: 'Not specified') . "</p>
        <p><strong>Budget:</strong> " . ($budget ?: 'Not specified') . "</p>
        <hr>
        <p><strong>Message:</strong></p>
        <p>" . nl2br(htmlspecialchars($message)) . "</p>
        <hr>
        <p><strong>IP Address:</strong> " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "</p>
        <p><em>This message was sent from the App Craft Services contact form.</em></p>
    </body>
    </html>
    ";
    
    $adminHeaders = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'Reply-To: ' . $name . ' <' . $email . '>',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    $adminEmailSent = mail($adminEmail, $adminSubject, $adminMessage, implode("\r\n", $adminHeaders));
    
    // Send confirmation email to client
    $clientSubject = "Thank you for contacting App Craft Services";
    $clientMessage = "
    <html>
    <head>
        <title>Thank you for your inquiry</title>
    </head>
    <body>
        <h2>Thank you for contacting App Craft Services!</h2>
        <p>Dear {$name},</p>
        <p>We have received your inquiry and appreciate you taking the time to contact us. Here's what happens next:</p>
        
        <h3>Next Steps:</h3>
        <ol>
            <li><strong>Review (24 hours):</strong> Our team will review your project details and requirements.</li>
            <li><strong>Initial Response:</strong> We'll send you an initial response within 24-48 hours with any clarifying questions.</li>
            <li><strong>Consultation Call:</strong> If your project is a good fit, we'll schedule a consultation call to discuss your needs in detail.</li>
            <li><strong>Proposal:</strong> After our consultation, we'll provide a detailed proposal with timeline and pricing.</li>
        </ol>
        
        <h3>Your Inquiry Details:</h3>
        <p><strong>Reference ID:</strong> {$messageId}</p>
        <p><strong>Project Type:</strong> " . ($projectType ?: 'Not specified') . "</p>
        <p><strong>Timeline:</strong> " . ($timeline ?: 'Not specified') . "</p>
        <p><strong>Budget Range:</strong> " . ($budget ?: 'Not specified') . "</p>
        
        <h3>In the Meantime:</h3>
        <ul>
            <li>Feel free to browse our <a href='https://appcraftservices.com/services'>services page</a> to learn more about what we offer.</li>
            <li>Check out our <a href='https://appcraftservices.com/process'>development process</a> to understand how we work.</li>
            <li>If you have any urgent questions, you can reach us at {$adminEmail}</li>
        </ul>
        
        <p>We're excited about the possibility of working with you and helping bring your project to life!</p>
        
        <p>Best regards,<br>
        <strong>The App Craft Services Team</strong><br>
        Email: {$adminEmail}<br>
        WhatsApp: +2348061581916<br>
        Website: <a href='https://appcraftservices.com'>appcraftservices.com</a></p>
        
        <hr>
        <p><em>This is an automated confirmation email. Please do not reply to this email address. If you need to contact us, please use {$adminEmail}</em></p>
    </body>
    </html>
    ";
    
    $clientHeaders = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'Reply-To: ' . $adminEmail,
        'X-Mailer: PHP/' . phpversion()
    ];
    
    $clientEmailSent = mail($email, $clientSubject, $clientMessage, implode("\r\n", $clientHeaders));
    
    // Log the submission
    $logFile = $dataDir . '/contact_log.txt';
    $logEntry = date('Y-m-d H:i:s') . " - New submission from {$name} ({$email}) - ID: {$messageId} - Admin email: " . ($adminEmailSent ? 'sent' : 'failed') . " - Client email: " . ($clientEmailSent ? 'sent' : 'failed') . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your message! We\'ll get back to you within 24 hours.',
        'message_id' => $messageId,
        'admin_email_sent' => $adminEmailSent,
        'client_email_sent' => $clientEmailSent
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
    // Log the error
    $errorLog = '../data/error_log.txt';
    $errorEntry = date('Y-m-d H:i:s') . " - Contact form error: " . $e->getMessage() . "\n";
    file_put_contents($errorLog, $errorEntry, FILE_APPEND | LOCK_EX);
}
?>