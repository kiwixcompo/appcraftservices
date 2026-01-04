<?php
// Professional Email Configuration for appcraftservices.com
// This setup allows using Gmail while appearing to send from your domain

// 1. Start output buffering to catch any unwanted warnings/errors
ob_start();

// 2. Set error handling to silent for the client, but log internally
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Email Configuration
$config = [
    'admin_email' => 'geniusonen@gmail.com', // Updated to your preferred email
    'backup_admin_email' => 'williamsaonen@gmail.com', // Backup email
    'from_email' => 'hello@appcraftservices.com', // This will appear as sender
    'from_name' => 'App Craft Services',
    'reply_to_admin' => 'geniusonen@gmail.com', // Hidden from users
    'use_smtp' => true, // Enable SMTP for better delivery
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'geniusonen@gmail.com',
    'smtp_password' => '', // You'll need to set this up with App Password
    'auto_reply' => true // Send confirmation to user
];

// Include PHPMailer for better email delivery
function sendEmailWithSMTP($to, $subject, $body, $fromEmail, $fromName, $replyTo = null) {
    global $config;
    
    // Simple mail() function with proper headers for now
    // You can upgrade to PHPMailer later for better reliability
    $headers = [];
    $headers[] = "From: {$config['from_name']} <{$config['from_email']}>";
    $headers[] = "Reply-To: " . ($replyTo ?: $config['reply_to_admin']);
    $headers[] = "X-Mailer: App Craft Services Contact System";
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: text/plain; charset=UTF-8";
    $headers[] = "X-Priority: 3";
    
    return mail($to, $subject, $body, implode("\r\n", $headers));
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    // Get raw input
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);
    
    // Fallback to $_POST if JSON failed
    if (!$input) {
        $input = $_POST;
    }
    
    // Validate required fields
    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $message = trim($input['project-details'] ?? $input['message'] ?? '');
    $company = trim($input['company'] ?? '');
    $projectType = trim($input['project-type'] ?? '');
    $timeline = trim($input['timeline'] ?? '');
    $budget = trim($input['budget'] ?? '');
    $phone = trim($input['phone'] ?? '');
    
    // Captcha validation
    $captchaAnswer = trim($input['captcha_answer'] ?? '');
    $captchaCorrect = trim($input['captcha_correct'] ?? '');
    
    if (empty($name) || empty($email) || empty($message)) {
        throw new Exception('Name, email, and message are required');
    }
    
    // Validate captcha
    if (empty($captchaAnswer) || empty($captchaCorrect)) {
        throw new Exception('Please complete the security verification');
    }
    
    if ($captchaAnswer !== $captchaCorrect) {
        throw new Exception('Security verification failed. Please solve the math problem correctly.');
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please provide a valid email address');
    }
    
    $messageId = uniqid('msg_', true);
    
    // Data structure
    $messageData = [
        'id' => $messageId,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'company' => $company,
        'project_type' => $projectType,
        'funding_stage' => trim($input['funding-stage'] ?? ''),
        'investor_deadline' => trim($input['investor-deadline'] ?? ''),
        'timeline' => $timeline,
        'budget' => $budget,
        'message' => $message,
        'created_at' => date('Y-m-d H:i:s'),
        'read' => false,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    // Save to file
    $dataDir = __DIR__ . '/../data';
    if (!file_exists($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    // Calculate lead score
    $leadScore = 0;
    $qualificationLevel = 'Needs Qualification';
    try {
        // Include lead scoring class
        require_once __DIR__ . '/lead-scoring.php';
        $scorer = new LeadScorer();
        $scoreResult = $scorer->calculateScore($messageData);
        $leadScore = $scoreResult['total_score'];
        $qualificationLevel = $scoreResult['qualification_level'];
        $messageData['lead_score'] = $scoreResult;
    } catch (Exception $e) {
        error_log("Lead scoring error: " . $e->getMessage());
    }
    
    $messagesFile = $dataDir . '/messages.json';
    $messages = [];
    
    if (file_exists($messagesFile)) {
        $jsonContent = file_get_contents($messagesFile);
        $messages = json_decode($jsonContent, true) ?: [];
    }
    
    // Add new message to start of array
    array_unshift($messages, $messageData);
    
    // Keep only last 1000 messages to prevent file from getting too large
    if (count($messages) > 1000) {
        $messages = array_slice($messages, 0, 1000);
    }
    
    // Save back to file
    file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT));
    
    // Send notification email to admin
    $emailStatus = 'not_attempted';
    try {
        $subject = "🚀 New Project Inquiry from $name";
        
        $body = "You have received a new project inquiry from your website!\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "📋 CLIENT DETAILS\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "👤 Name: $name\n";
        $body .= "📧 Email: $email\n";
        if ($phone) $body .= "📱 Phone: $phone\n";
        if ($company) $body .= "🏢 Company: $company\n";
        $body .= "\n";
        
        if ($projectType || $timeline || $budget) {
            $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $body .= "💼 PROJECT DETAILS\n";
            $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            if ($projectType) $body .= "🎯 Project Type: $projectType\n";
            if ($timeline) $body .= "⏰ Timeline: $timeline\n";
            if ($budget) $body .= "💰 Budget: $budget\n";
            $body .= "\n";
        }
        
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "💬 MESSAGE\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "$message\n\n";
        
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "📊 SUBMISSION INFO\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "🕒 Submitted: " . date('Y-m-d H:i:s T') . "\n";
        $body .= "🌐 IP Address: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
        $body .= "🆔 Message ID: $messageId\n";
        $body .= "🔗 Admin Panel: https://appcraftservices.com/admin/\n\n";
        
        $body .= "Reply directly to this email to respond to the client.\n";
        $body .= "Their email ($email) is set as the reply-to address.\n\n";
        $body .= "Best regards,\n";
        $body .= "App Craft Services Contact System";
        
        if (sendEmailWithSMTP($config['admin_email'], $subject, $body, $config['from_email'], $config['from_name'], $email)) {
            $emailStatus = 'sent_to_admin';
            
            // Also send to backup admin email if different
            if ($config['backup_admin_email'] && $config['backup_admin_email'] !== $config['admin_email']) {
                if (sendEmailWithSMTP($config['backup_admin_email'], $subject, $body, $config['from_email'], $config['from_name'], $email)) {
                    $emailStatus = 'sent_to_both_admins';
                } else {
                    $emailStatus = 'sent_to_primary_admin_only';
                }
            }
        } else {
            $emailStatus = 'failed_to_admin';
        }
        
        // Send auto-reply to client if enabled
        if ($config['auto_reply']) {
            $clientSubject = "Thank you for contacting App Craft Services, $name!";
            
            $clientBody = "Hi $name,\n\n";
            $clientBody .= "Thank you for reaching out to App Craft Services! We've received your project inquiry and are excited to learn more about your vision.\n\n";
            
            $clientBody .= "📋 Here's what we received:\n";
            $clientBody .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            if ($projectType) $clientBody .= "Project Type: $projectType\n";
            if ($timeline) $clientBody .= "Timeline: $timeline\n";
            if ($budget) $clientBody .= "Budget: $budget\n";
            $clientBody .= "Message: " . substr($message, 0, 200) . (strlen($message) > 200 ? "..." : "") . "\n";
            $clientBody .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            
            $clientBody .= "🚀 What happens next?\n";
            $clientBody .= "• We'll review your project details within 24 hours\n";
            $clientBody .= "• Our team will prepare a customized proposal\n";
            $clientBody .= "• We'll schedule a consultation call to discuss your vision\n";
            $clientBody .= "• You'll receive a detailed project roadmap and timeline\n\n";
            
            $clientBody .= "💡 In the meantime:\n";
            $clientBody .= "• Check out our recent projects: https://appcraftservices.com/#recent-projects\n";
            $clientBody .= "• Learn about our process: https://appcraftservices.com/process\n";
            $clientBody .= "• View our pricing options: https://appcraftservices.com/pricing\n\n";
            
            $clientBody .= "📞 Need immediate assistance?\n";
            $clientBody .= "Feel free to reply to this email with any additional questions or urgent requirements.\n\n";
            
            $clientBody .= "We're looking forward to helping you turn your startup idea into reality!\n\n";
            $clientBody .= "Best regards,\n";
            $clientBody .= "The App Craft Services Team\n";
            $clientBody .= "🌐 https://appcraftservices.com\n";
            $clientBody .= "📧 hello@appcraftservices.com";
            
            if (sendEmailWithSMTP($email, $clientSubject, $clientBody, $config['from_email'], $config['from_name'], $config['admin_email'])) {
                $emailStatus .= '_and_client';
            } else {
                $emailStatus .= '_client_failed';
            }
        }
        
    } catch (Throwable $e) {
        $emailStatus = 'error: ' . $e->getMessage();
        error_log("Email sending error in contact.php: " . $e->getMessage());
    }
    
    // Clean output buffer before sending JSON
    $output = ob_get_clean();
    
    // Log any unexpected output for debugging
    if (!empty($output)) {
        error_log("Unexpected output in contact.php: " . $output);
    }
    
    // Ensure we're sending clean JSON
    header('Content-Type: application/json; charset=utf-8');
    
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your message! We\'ll get back to you within 24 hours.',
        'message_id' => $messageId,
        'email_status' => $emailStatus,
        'auto_reply_sent' => $config['auto_reply']
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Clean buffer on error too
    $output = ob_get_clean();
    
    // Log any unexpected output for debugging
    if (!empty($output)) {
        error_log("Unexpected output in contact.php error: " . $output);
    }
    
    // Ensure we're sending clean JSON
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(400);
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>