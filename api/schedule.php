<?php
// Schedule Request Handler for App Craft Services
// Handles consultation booking requests with enhanced email formatting

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
    'from_email' => 'hello@appcraftservices.com',
    'from_name' => 'App Craft Services',
    'reply_to_admin' => 'geniusonen@gmail.com',
    'auto_reply' => true
];

function sendScheduleEmail($to, $subject, $body, $fromEmail, $fromName, $replyTo = null) {
    global $config;
    
    $headers = [];
    $headers[] = "From: {$config['from_name']} <{$config['from_email']}>";
    $headers[] = "Reply-To: " . ($replyTo ?: $config['reply_to_admin']);
    $headers[] = "X-Mailer: App Craft Services Schedule System";
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: text/plain; charset=UTF-8";
    $headers[] = "X-Priority: 2"; // High priority for schedule requests
    
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
    $phone = trim($input['phone'] ?? '');
    $preferredDate = trim($input['preferred_date'] ?? '');
    $preferredTime = trim($input['preferred_time'] ?? '');
    $projectDescription = trim($input['project_description'] ?? '');
    
    if (empty($name) || empty($email) || empty($preferredDate) || empty($preferredTime)) {
        throw new Exception('Name, email, preferred date, and time are required');
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please provide a valid email address');
    }
    
    // Validate date (must be in the future)
    $requestedDate = DateTime::createFromFormat('Y-m-d', $preferredDate);
    $today = new DateTime();
    if (!$requestedDate || $requestedDate <= $today) {
        throw new Exception('Please select a future date for your consultation');
    }
    
    $scheduleId = uniqid('schedule_', true);
    
    // Data structure
    $scheduleData = [
        'id' => $scheduleId,
        'type' => 'schedule_request',
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'preferred_date' => $preferredDate,
        'preferred_time' => $preferredTime,
        'project_description' => $projectDescription,
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s'),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    // Save to schedule requests file
    $dataDir = __DIR__ . '/../data';
    if (!file_exists($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    $scheduleFile = $dataDir . '/schedule_requests.json';
    $schedules = [];
    
    if (file_exists($scheduleFile)) {
        $jsonContent = file_get_contents($scheduleFile);
        $schedules = json_decode($jsonContent, true) ?: [];
    }
    
    // Add new schedule request to start of array
    array_unshift($schedules, $scheduleData);
    
    // Keep only last 500 requests
    if (count($schedules) > 500) {
        $schedules = array_slice($schedules, 0, 500);
    }
    
    // Save back to file
    file_put_contents($scheduleFile, json_encode($schedules, JSON_PRETTY_PRINT));
    
    // Also save to regular messages for admin dashboard
    $messagesFile = $dataDir . '/messages.json';
    $messages = [];
    
    if (file_exists($messagesFile)) {
        $jsonContent = file_get_contents($messagesFile);
        $messages = json_decode($jsonContent, true) ?: [];
    }
    
    // Create message format for admin dashboard
    $messageData = [
        'id' => $scheduleId,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'company' => '',
        'project_type' => 'Consultation Request',
        'timeline' => 'ASAP',
        'budget' => 'Free Consultation',
        'message' => "CONSULTATION REQUEST\n\nPreferred Date: $preferredDate\nPreferred Time: $preferredTime\n\nProject Description:\n$projectDescription",
        'created_at' => date('Y-m-d H:i:s'),
        'read' => false,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    array_unshift($messages, $messageData);
    if (count($messages) > 1000) {
        $messages = array_slice($messages, 0, 1000);
    }
    file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT));
    
    // Send notification email to admin
    $emailStatus = 'not_attempted';
    try {
        $formattedDate = date('l, F j, Y', strtotime($preferredDate));
        $formattedTime = date('g:i A', strtotime($preferredTime . ':00'));
        
        $subject = "🗓️ NEW CONSULTATION REQUEST from $name";
        
        $body = "🎉 URGENT: New consultation request received!\n\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "📅 REQUESTED CONSULTATION TIME\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "📆 Date: $formattedDate\n";
        $body .= "🕐 Time: $formattedTime\n";
        $body .= "⚠️  ACTION REQUIRED: Please confirm or suggest alternative times\n\n";
        
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "👤 CLIENT INFORMATION\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "👤 Name: $name\n";
        $body .= "📧 Email: $email\n";
        if ($phone) $body .= "📱 Phone: $phone\n";
        $body .= "\n";
        
        if ($projectDescription) {
            $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $body .= "💼 PROJECT DESCRIPTION\n";
            $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $body .= "$projectDescription\n\n";
        }
        
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "🚀 NEXT STEPS\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "1. Reply to this email to confirm the time or suggest alternatives\n";
        $body .= "2. Send a calendar invite to: $email\n";
        $body .= "3. Include meeting link (Zoom/Google Meet) in the invite\n";
        $body .= "4. Send confirmation email with meeting details\n\n";
        
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "📊 REQUEST DETAILS\n";
        $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $body .= "🕒 Submitted: " . date('Y-m-d H:i:s T') . "\n";
        $body .= "🌐 IP Address: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
        $body .= "🆔 Request ID: $scheduleId\n";
        $body .= "🔗 Admin Panel: https://appcraftservices.com/admin/\n\n";
        
        $body .= "⏰ REMINDER: Client expects confirmation within 24 hours!\n\n";
        $body .= "Reply directly to this email to respond to the client.\n";
        $body .= "Their email ($email) is set as the reply-to address.\n\n";
        $body .= "Best regards,\n";
        $body .= "App Craft Services Schedule System";
        
        if (sendScheduleEmail($config['admin_email'], $subject, $body, $config['from_email'], $config['from_name'], $email)) {
            $emailStatus = 'sent_to_admin';
        } else {
            $emailStatus = 'failed_to_admin';
        }
        
        // Send auto-reply to client
        if ($config['auto_reply']) {
            $clientSubject = "Consultation Request Received - We'll Confirm Within 24 Hours!";
            
            $clientBody = "Hi $name,\n\n";
            $clientBody .= "Thank you for requesting a consultation with App Craft Services! 🎉\n\n";
            
            $clientBody .= "📅 CONSULTATION REQUEST SUMMARY\n";
            $clientBody .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $clientBody .= "📆 Requested Date: $formattedDate\n";
            $clientBody .= "🕐 Requested Time: $formattedTime\n";
            if ($projectDescription) {
                $clientBody .= "💼 Project: " . substr($projectDescription, 0, 100) . (strlen($projectDescription) > 100 ? "..." : "") . "\n";
            }
            $clientBody .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            
            $clientBody .= "⏰ WHAT HAPPENS NEXT?\n";
            $clientBody .= "• We'll review your request and confirm availability within 24 hours\n";
            $clientBody .= "• You'll receive a calendar invite with meeting details\n";
            $clientBody .= "• If your preferred time isn't available, we'll suggest alternatives\n";
            $clientBody .= "• The consultation will be conducted via video call (Zoom/Google Meet)\n\n";
            
            $clientBody .= "📋 CONSULTATION AGENDA (30 minutes):\n";
            $clientBody .= "• Project discovery and requirements discussion (10 min)\n";
            $clientBody .= "• Technical approach and recommendations (10 min)\n";
            $clientBody .= "• Timeline, budget, and next steps (10 min)\n\n";
            
            $clientBody .= "💡 PREPARE FOR OUR CALL:\n";
            $clientBody .= "• Think about your target users and main goals\n";
            $clientBody .= "• Consider your preferred timeline and budget range\n";
            $clientBody .= "• Prepare any questions about our process or services\n";
            $clientBody .= "• Have examples of apps/websites you like (optional)\n\n";
            
            $clientBody .= "📞 NEED TO CHANGE YOUR REQUEST?\n";
            $clientBody .= "Simply reply to this email with your updated preferences.\n\n";
            
            $clientBody .= "🚀 EXCITED TO DISCUSS YOUR PROJECT!\n";
            $clientBody .= "We're looking forward to learning about your startup idea and helping you bring it to life.\n\n";
            
            $clientBody .= "Best regards,\n";
            $clientBody .= "The App Craft Services Team\n";
            $clientBody .= "🌐 https://appcraftservices.com\n";
            $clientBody .= "📧 hello@appcraftservices.com\n\n";
            
            $clientBody .= "P.S. Check out our recent projects while you wait: https://appcraftservices.com/#portfolio";
            
            if (sendScheduleEmail($email, $clientSubject, $clientBody, $config['from_email'], $config['from_name'], $config['admin_email'])) {
                $emailStatus .= '_and_client';
            } else {
                $emailStatus .= '_client_failed';
            }
        }
        
    } catch (Throwable $e) {
        $emailStatus = 'error: ' . $e->getMessage();
        error_log("Email sending error in schedule.php: " . $e->getMessage());
    }
    
    // Clean output buffer before sending JSON
    $output = ob_get_clean();
    
    // Log any unexpected output for debugging
    if (!empty($output)) {
        error_log("Unexpected output in schedule.php: " . $output);
    }
    
    // Ensure we're sending clean JSON
    header('Content-Type: application/json; charset=utf-8');
    
    echo json_encode([
        'success' => true,
        'message' => 'Your consultation request has been sent! We\'ll confirm your time within 24 hours.',
        'schedule_id' => $scheduleId,
        'email_status' => $emailStatus,
        'requested_time' => "$formattedDate at $formattedTime",
        'auto_reply_sent' => $config['auto_reply']
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Clean buffer on error too
    $output = ob_get_clean();
    
    // Log any unexpected output for debugging
    if (!empty($output)) {
        error_log("Unexpected output in schedule.php error: " . $output);
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