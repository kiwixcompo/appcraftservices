<?php
// 1. Start output buffering to catch any unwanted warnings/errors
ob_start();

// 2. Set error handling to silent for the client, but log internally
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$adminEmail = 'williamsaonen@gmail.com';
$fromEmail = 'noreply@appcraftservices.com'; // Ensure this domain is valid or use admin email
$fromName = 'App Craft Services';

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
    
    if (empty($name) || empty($email) || empty($message)) {
        throw new Exception('Name, email, and message are required');
    }
    
    $messageId = uniqid('msg_', true);
    
    // Data structure
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
    
    // 3. Save to file (Correct Path Resolution)
    $dataDir = __DIR__ . '/../data';
    if (!file_exists($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    $messagesFile = $dataDir . '/messages.json';
    $messages = [];
    
    if (file_exists($messagesFile)) {
        $jsonContent = file_get_contents($messagesFile);
        $messages = json_decode($jsonContent, true) ?: [];
    }
    
    // Add new message to start of array
    array_unshift($messages, $messageData);
    
    // Save back to file
    file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT));
    
    // 4. Attempt Email (Safe Mode)
    // We try to send the email, but catch failures so the user still sees "Success"
    $emailStatus = 'not_attempted';
    try {
        // Always attempt to send email (remove localhost check)
        $subject = "New Contact Message from $name";
        $body = "You have received a new contact message from your website.\n\n";
        $body .= "Name: $name\n";
        $body .= "Email: $email\n";
        $body .= "Company: $company\n";
        $body .= "Project Type: $projectType\n";
        $body .= "Timeline: $timeline\n";
        $body .= "Budget: $budget\n";
        $body .= "Message:\n$message\n\n";
        $body .= "Submitted: " . date('Y-m-d H:i:s') . "\n";
        $body .= "IP Address: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
        $body .= "Message ID: $messageId\n";
        
        $headers = "From: $fromName <$fromEmail>\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        if(mail($adminEmail, $subject, $body, $headers)) {
            $emailStatus = 'sent';
        } else {
            $emailStatus = 'failed';
        }
    } catch (Throwable $e) {
        $emailStatus = 'error: ' . $e->getMessage();
    }
    
    // 5. Clean output buffer before sending JSON
    // This removes any "Warning: mail()..." text that might have been generated
    $output = ob_get_clean();
    
    // Log any unexpected output for debugging
    if (!empty($output)) {
        error_log("Unexpected output in contact.php: " . $output);
    }
    
    // Ensure we're sending clean JSON
    header('Content-Type: application/json; charset=utf-8');
    
    echo json_encode([
        'success' => true,
        'message' => 'Your message has been sent successfully!',
        'message_id' => $messageId,
        'email_status' => $emailStatus
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