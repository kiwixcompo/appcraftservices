<?php
/**
 * Review Request API Endpoint
 * Sends email notifications to clients requesting reviews after project completion
 * Requirements: 2.7
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    // Get form data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    // Validate required fields
    $clientName = trim($input['client_name'] ?? '');
    $clientEmail = trim($input['client_email'] ?? '');
    $company = trim($input['company'] ?? '');
    $projectType = trim($input['project_type'] ?? '');
    $projectId = trim($input['project_id'] ?? '');
    $projectCompletionDate = trim($input['project_completion_date'] ?? '');
    
    if (empty($clientName) || empty($clientEmail) || empty($company) || empty($projectType)) {
        throw new Exception('Client name, email, company, and project type are required');
    }
    
    if (!filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }
    
    // Send review request email
    $emailSent = sendReviewRequestEmail($clientName, $clientEmail, $company, $projectType, $projectId, $projectCompletionDate);
    
    if ($emailSent) {
        // Log the review request
        logReviewRequest($clientEmail, $projectId, $projectType);
        
        echo json_encode([
            'success' => true,
            'message' => 'Review request sent successfully',
            'client_email' => $clientEmail,
            'project_id' => $projectId
        ]);
    } else {
        throw new Exception('Failed to send review request email');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
    error_log("Review request error: " . $e->getMessage());
}

/**
 * Send review request email to client
 * @param string $clientName Client name
 * @param string $clientEmail Client email
 * @param string $company Company name
 * @param string $projectType Project type
 * @param string $projectId Project ID
 * @param string $completionDate Project completion date
 * @return bool
 */
function sendReviewRequestEmail($clientName, $clientEmail, $company, $projectType, $projectId, $completionDate) {
    $fromEmail = 'noreply@appcraftservices.com';
    $fromName = 'App Craft Services';
    $replyToEmail = 'williamsaonen@gmail.com';
    
    $subject = "We'd love your feedback on your recent project - App Craft Services";
    
    // Create review submission URL with pre-filled data
    $reviewUrl = 'https://appcraftservices.com/submit-review?' . http_build_query([
        'client_name' => $clientName,
        'company' => $company,
        'project_type' => $projectType,
        'project_id' => $projectId,
        'project_completion_date' => $completionDate
    ]);
    
    $message = "
    <html>
    <head>
        <title>We'd love your feedback</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .button { display: inline-block; background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
            .project-details { background: white; padding: 15px; border-left: 4px solid #2563eb; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>We'd Love Your Feedback!</h1>
            </div>
            <div class='content'>
                <p>Dear {$clientName},</p>
                
                <p>I hope you're thriving with your new {$projectType}! It was a pleasure working with {$company} on this project.</p>
                
                <div class='project-details'>
                    <h3>Project Details:</h3>
                    <p><strong>Company:</strong> {$company}</p>
                    <p><strong>Project Type:</strong> {$projectType}</p>
                    " . (!empty($completionDate) ? "<p><strong>Completion Date:</strong> " . date('F j, Y', strtotime($completionDate)) . "</p>" : "") . "
                    " . (!empty($projectId) ? "<p><strong>Project ID:</strong> {$projectId}</p>" : "") . "
                </div>
                
                <p>Your feedback means the world to us and helps other entrepreneurs understand the value we provide. Would you mind taking 2-3 minutes to share your experience?</p>
                
                <h3>What we'd love to know:</h3>
                <ul>
                    <li>How was your overall experience working with our team?</li>
                    <li>Did we meet your expectations for timeline and quality?</li>
                    <li>How has the project impacted your business?</li>
                    <li>Would you recommend our services to other startups?</li>
                </ul>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$reviewUrl}' class='button'>Leave Your Review</a>
                </div>
                
                <p><strong>Why reviews matter:</strong></p>
                <ul>
                    <li>Help other startups find quality development partners</li>
                    <li>Allow us to showcase our work and client success stories</li>
                    <li>Provide valuable feedback for continuous improvement</li>
                </ul>
                
                <p>Your review will be moderated before publication to ensure quality and authenticity. If you have any concerns or prefer to provide feedback privately, please reply to this email.</p>
                
                <h3>Stay Connected:</h3>
                <p>We'd love to continue supporting your growth:</p>
                <ul>
                    <li>Need additional features or scaling support? Just reach out!</li>
                    <li>Follow our latest work: <a href='https://appcraftservices.com'>appcraftservices.com</a></li>
                    <li>Refer other startups who might benefit from our services</li>
                </ul>
                
                <p>Thank you again for choosing App Craft Services. We're excited to see your continued success!</p>
                
                <p>Best regards,<br>
                <strong>Williams Aonen</strong><br>
                Founder, App Craft Services<br>
                Email: {$replyToEmail}<br>
                WhatsApp: +2348061581916<br>
                Website: <a href='https://appcraftservices.com'>appcraftservices.com</a></p>
                
                <hr>
                <p style='font-size: 12px; color: #666;'>
                    <em>This email was sent because you recently completed a project with App Craft Services. 
                    If you believe you received this email in error, please contact us at {$replyToEmail}</em>
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'Reply-To: ' . $replyToEmail,
        'X-Mailer: PHP/' . phpversion()
    ];
    
    return mail($clientEmail, $subject, $message, implode("\r\n", $headers));
}

/**
 * Log review request for tracking
 * @param string $clientEmail Client email
 * @param string $projectId Project ID
 * @param string $projectType Project type
 */
function logReviewRequest($clientEmail, $projectId, $projectType) {
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'client_email' => $clientEmail,
        'project_id' => $projectId,
        'project_type' => $projectType,
        'action' => 'review_request_sent'
    ];
    
    $logFile = '../../data/review_requests_log.json';
    $logs = [];
    
    if (file_exists($logFile)) {
        $logs = json_decode(file_get_contents($logFile), true) ?: [];
    }
    
    $logs[] = $logData;
    
    // Keep only last 1000 entries
    if (count($logs) > 1000) {
        $logs = array_slice($logs, -1000);
    }
    
    file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT));
}
?>