<?php
/**
 * Review Submission API Endpoint
 * Handles review submission with validation and verification
 * Requirements: 2.7, 2.5
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

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
    $reviewerName = trim($input['reviewer_name'] ?? '');
    $company = trim($input['company'] ?? '');
    $projectType = trim($input['project_type'] ?? '');
    $rating = intval($input['rating'] ?? 0);
    $content = trim($input['content'] ?? '');
    $projectCompletionDate = trim($input['project_completion_date'] ?? '');
    $reviewerEmail = trim($input['reviewer_email'] ?? '');
    $projectId = trim($input['project_id'] ?? '');
    $contactPermission = isset($input['contact_permission']) ? (bool)$input['contact_permission'] : false;
    
    // Validation
    if (empty($reviewerName) || empty($company) || empty($projectType) || empty($content) || empty($reviewerEmail)) {
        throw new Exception('Reviewer name, company, project type, content, and email are required');
    }
    
    if ($rating < 1 || $rating > 5) {
        throw new Exception('Rating must be between 1 and 5');
    }
    
    if (!filter_var($reviewerEmail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }
    
    if (empty($projectCompletionDate) || !strtotime($projectCompletionDate)) {
        throw new Exception('Valid project completion date is required');
    }
    
    // Validate project completion date is not in the future
    if (strtotime($projectCompletionDate) > time()) {
        throw new Exception('Project completion date cannot be in the future');
    }
    
    // Content length validation
    if (strlen($content) < 10) {
        throw new Exception('Review content must be at least 10 characters long');
    }
    
    if (strlen($content) > 2000) {
        throw new Exception('Review content cannot exceed 2000 characters');
    }
    
    // Initialize database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Verify reviewer against project database (if project_id provided)
    $verified = false;
    if (!empty($projectId)) {
        $verified = verifyReviewer($conn, $reviewerEmail, $projectId);
    }
    
    // Insert review into database
    $sql = "INSERT INTO reviews (
        reviewer_name, company, project_type, rating, content, 
        project_completion_date, reviewer_email, project_id, 
        contact_permission, verified, moderation_status
    ) VALUES (
        :reviewer_name, :company, :project_type, :rating, :content,
        :project_completion_date, :reviewer_email, :project_id,
        :contact_permission, :verified, 'pending'
    )";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':reviewer_name', $reviewerName);
    $stmt->bindParam(':company', $company);
    $stmt->bindParam(':project_type', $projectType);
    $stmt->bindParam(':rating', $rating);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':project_completion_date', $projectCompletionDate);
    $stmt->bindParam(':reviewer_email', $reviewerEmail);
    $stmt->bindParam(':project_id', $projectId);
    $stmt->bindParam(':contact_permission', $contactPermission, PDO::PARAM_BOOL);
    $stmt->bindParam(':verified', $verified, PDO::PARAM_BOOL);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to save review');
    }
    
    $reviewId = $conn->lastInsertId();
    
    // Log the review submission
    logReviewAction($conn, $reviewId, 'submitted', null, 'Review submitted by ' . $reviewerName);
    
    // Send notification email to admin
    $adminNotificationSent = sendAdminNotification($reviewId, $reviewerName, $company, $rating, $content);
    
    // Send confirmation email to reviewer
    $reviewerNotificationSent = sendReviewerConfirmation($reviewerEmail, $reviewerName, $reviewId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your review! It will be published after moderation.',
        'review_id' => $reviewId,
        'verified' => $verified,
        'admin_notification_sent' => $adminNotificationSent,
        'reviewer_notification_sent' => $reviewerNotificationSent
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
    // Log the error
    error_log("Review submission error: " . $e->getMessage());
}

/**
 * Verify reviewer against project database
 * @param PDO $conn Database connection
 * @param string $email Reviewer email
 * @param string $projectId Project ID
 * @return bool
 */
function verifyReviewer($conn, $email, $projectId) {
    // For now, we'll implement a simple verification
    // In a real system, this would check against a projects database
    // or CRM system to verify the reviewer worked on the project
    
    try {
        // Check if this email has submitted reviews for this project before
        $sql = "SELECT COUNT(*) as count FROM reviews WHERE reviewer_email = :email AND project_id = :project_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':project_id', $projectId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        
        // If no previous reviews, consider it potentially valid
        // In production, this would integrate with project management system
        return $result['count'] == 0;
        
    } catch (Exception $e) {
        error_log("Reviewer verification error: " . $e->getMessage());
        return false;
    }
}

/**
 * Log review action for audit trail
 * @param PDO $conn Database connection
 * @param int $reviewId Review ID
 * @param string $action Action performed
 * @param string $moderator Moderator name
 * @param string $notes Additional notes
 */
function logReviewAction($conn, $reviewId, $action, $moderator = null, $notes = null) {
    try {
        $sql = "INSERT INTO review_moderation_log (review_id, action, moderator_name, notes) 
                VALUES (:review_id, :action, :moderator_name, :notes)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':review_id', $reviewId);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':moderator_name', $moderator);
        $stmt->bindParam(':notes', $notes);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Review logging error: " . $e->getMessage());
    }
}

/**
 * Send notification email to admin about new review
 * @param int $reviewId Review ID
 * @param string $reviewerName Reviewer name
 * @param string $company Company name
 * @param int $rating Rating given
 * @param string $content Review content
 * @return bool
 */
function sendAdminNotification($reviewId, $reviewerName, $company, $rating, $content) {
    $adminEmail = 'williamsaonen@gmail.com';
    $fromEmail = 'noreply@appcraftservices.com';
    $fromName = 'App Craft Services Review System';
    
    $subject = "New Review Submitted - Requires Moderation";
    $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
    
    $message = "
    <html>
    <head>
        <title>New Review Submitted</title>
    </head>
    <body>
        <h2>New Review Requires Moderation</h2>
        <p><strong>Review ID:</strong> {$reviewId}</p>
        <p><strong>Date:</strong> " . date('F j, Y, g:i a') . "</p>
        <hr>
        <p><strong>Reviewer:</strong> {$reviewerName}</p>
        <p><strong>Company:</strong> {$company}</p>
        <p><strong>Rating:</strong> {$stars} ({$rating}/5)</p>
        <hr>
        <p><strong>Review Content:</strong></p>
        <p>" . nl2br(htmlspecialchars($content)) . "</p>
        <hr>
        <p><strong>Action Required:</strong> Please log into the admin dashboard to moderate this review.</p>
        <p><a href='https://appcraftservices.com/admin/'>Admin Dashboard</a></p>
        <p><em>This review will not be displayed publicly until approved.</em></p>
    </body>
    </html>
    ";
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    return mail($adminEmail, $subject, $message, implode("\r\n", $headers));
}

/**
 * Send confirmation email to reviewer
 * @param string $reviewerEmail Reviewer email
 * @param string $reviewerName Reviewer name
 * @param int $reviewId Review ID
 * @return bool
 */
function sendReviewerConfirmation($reviewerEmail, $reviewerName, $reviewId) {
    $fromEmail = 'noreply@appcraftservices.com';
    $fromName = 'App Craft Services';
    
    $subject = "Thank you for your review - App Craft Services";
    
    $message = "
    <html>
    <head>
        <title>Thank you for your review</title>
    </head>
    <body>
        <h2>Thank you for your review!</h2>
        <p>Dear {$reviewerName},</p>
        <p>Thank you for taking the time to share your experience with App Craft Services. Your feedback is invaluable to us and helps other potential clients understand the quality of our work.</p>
        
        <h3>What happens next:</h3>
        <ol>
            <li><strong>Review Process:</strong> Our team will review your submission to ensure it meets our guidelines.</li>
            <li><strong>Publication:</strong> Once approved, your review will be published on our website within 24-48 hours.</li>
            <li><strong>Notification:</strong> We'll send you another email when your review goes live.</li>
        </ol>
        
        <p><strong>Reference ID:</strong> {$reviewId}</p>
        
        <h3>Stay Connected:</h3>
        <p>We'd love to stay in touch and keep you updated on our latest work and insights:</p>
        <ul>
            <li>Visit our website: <a href='https://appcraftservices.com'>appcraftservices.com</a></li>
            <li>Follow our latest projects and case studies</li>
            <li>Refer other startups who might benefit from our services</li>
        </ul>
        
        <p>If you have any questions about your review or our services, please don't hesitate to contact us.</p>
        
        <p>Best regards,<br>
        <strong>The App Craft Services Team</strong><br>
        Email: williamsaonen@gmail.com<br>
        Website: <a href='https://appcraftservices.com'>appcraftservices.com</a></p>
        
        <hr>
        <p><em>This is an automated confirmation email. If you need to contact us, please reply to this email or use williamsaonen@gmail.com</em></p>
    </body>
    </html>
    ";
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'Reply-To: williamsaonen@gmail.com',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    return mail($reviewerEmail, $subject, $message, implode("\r\n", $headers));
}
?>