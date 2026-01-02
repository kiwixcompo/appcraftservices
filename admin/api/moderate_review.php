<?php
/**
 * Review Moderation API for Admin Dashboard
 * Handles review approval/rejection with status tracking
 * Requirements: 2.6
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    // Validate required fields
    $reviewId = intval($input['review_id'] ?? 0);
    $action = trim($input['action'] ?? '');
    $moderatorNotes = trim($input['moderator_notes'] ?? '');
    $moderatorName = $_SESSION['admin_name'] ?? 'Admin';
    
    if ($reviewId <= 0) {
        throw new Exception('Valid review ID is required');
    }
    
    if (!in_array($action, ['approve', 'reject'])) {
        throw new Exception('Action must be either "approve" or "reject"');
    }
    
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Start transaction
    $conn->beginTransaction();
    
    try {
        // Get current review status
        $sql = "SELECT id, moderation_status, reviewer_name, reviewer_email, company, rating, content 
                FROM reviews WHERE id = :review_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':review_id', $reviewId);
        $stmt->execute();
        
        $review = $stmt->fetch();
        
        if (!$review) {
            throw new Exception('Review not found');
        }
        
        $previousStatus = $review['moderation_status'];
        $newStatus = $action === 'approve' ? 'approved' : 'rejected';
        
        // Update review status
        $updateSql = "UPDATE reviews SET 
                        moderation_status = :new_status,
                        moderator_id = :moderator_name,
                        moderation_date = NOW(),
                        moderation_notes = :moderator_notes,
                        updated_at = NOW()
                      WHERE id = :review_id";
        
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bindParam(':new_status', $newStatus);
        $updateStmt->bindParam(':moderator_name', $moderatorName);
        $updateStmt->bindParam(':moderator_notes', $moderatorNotes);
        $updateStmt->bindParam(':review_id', $reviewId);
        
        if (!$updateStmt->execute()) {
            throw new Exception('Failed to update review status');
        }
        
        // Log the moderation action
        $logSql = "INSERT INTO review_moderation_log 
                   (review_id, action, moderator_name, notes, previous_status, new_status) 
                   VALUES (:review_id, :action, :moderator_name, :notes, :previous_status, :new_status)";
        
        $logStmt = $conn->prepare($logSql);
        $logStmt->bindParam(':review_id', $reviewId);
        $logStmt->bindParam(':action', $action);
        $logStmt->bindParam(':moderator_name', $moderatorName);
        $logStmt->bindParam(':notes', $moderatorNotes);
        $logStmt->bindParam(':previous_status', $previousStatus);
        $logStmt->bindParam(':new_status', $newStatus);
        $logStmt->execute();
        
        // If approved, set display order (latest approved reviews get higher order)
        if ($action === 'approve') {
            $orderSql = "UPDATE reviews SET display_order = (
                            SELECT COALESCE(MAX(display_order), 0) + 1 
                            FROM (SELECT display_order FROM reviews WHERE moderation_status = 'approved') as approved_reviews
                         ) WHERE id = :review_id";
            $orderStmt = $conn->prepare($orderSql);
            $orderStmt->bindParam(':review_id', $reviewId);
            $orderStmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        // Send notification email to reviewer
        $emailSent = false;
        if ($action === 'approve') {
            $emailSent = sendApprovalNotification($review['reviewer_email'], $review['reviewer_name'], $reviewId);
        } else {
            $emailSent = sendRejectionNotification($review['reviewer_email'], $review['reviewer_name'], $moderatorNotes);
        }
        
        echo json_encode([
            'success' => true,
            'message' => ucfirst($action) . 'd review successfully',
            'review_id' => $reviewId,
            'new_status' => $newStatus,
            'previous_status' => $previousStatus,
            'moderator' => $moderatorName,
            'email_sent' => $emailSent
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    error_log("Review moderation error: " . $e->getMessage());
}

/**
 * Send approval notification to reviewer
 * @param string $reviewerEmail Reviewer email
 * @param string $reviewerName Reviewer name
 * @param int $reviewId Review ID
 * @return bool
 */
function sendApprovalNotification($reviewerEmail, $reviewerName, $reviewId) {
    $fromEmail = 'noreply@appcraftservices.com';
    $fromName = 'App Craft Services';
    
    $subject = "Your review is now live - App Craft Services";
    
    $message = "
    <html>
    <head>
        <title>Your review is now live!</title>
    </head>
    <body>
        <h2>Your review is now live!</h2>
        <p>Dear {$reviewerName},</p>
        
        <p>Great news! Your review has been approved and is now live on our website. Thank you for taking the time to share your experience with App Craft Services.</p>
        
        <p><strong>What this means:</strong></p>
        <ul>
            <li>Your review is now visible to potential clients on our website</li>
            <li>It helps other startups understand the quality of our work</li>
            <li>Your feedback contributes to our continuous improvement</li>
        </ul>
        
        <p><strong>View your review:</strong> <a href='https://appcraftservices.com/#reviews'>Visit our website</a></p>
        
        <p>We truly appreciate your feedback and are thrilled that you had a positive experience working with us. If you know other startups who might benefit from our services, we'd be grateful for any referrals!</p>
        
        <p>Thank you again for being a valued client.</p>
        
        <p>Best regards,<br>
        <strong>The App Craft Services Team</strong><br>
        Email: williamsaonen@gmail.com<br>
        Website: <a href='https://appcraftservices.com'>appcraftservices.com</a></p>
        
        <hr>
        <p><em>Reference ID: {$reviewId}</em></p>
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

/**
 * Send rejection notification to reviewer
 * @param string $reviewerEmail Reviewer email
 * @param string $reviewerName Reviewer name
 * @param string $reason Rejection reason
 * @return bool
 */
function sendRejectionNotification($reviewerEmail, $reviewerName, $reason) {
    $fromEmail = 'noreply@appcraftservices.com';
    $fromName = 'App Craft Services';
    
    $subject = "Update on your review submission - App Craft Services";
    
    $reasonText = !empty($reason) ? $reason : 'The review did not meet our publication guidelines.';
    
    $message = "
    <html>
    <head>
        <title>Update on your review submission</title>
    </head>
    <body>
        <h2>Update on your review submission</h2>
        <p>Dear {$reviewerName},</p>
        
        <p>Thank you for taking the time to submit a review about your experience with App Craft Services. We appreciate all feedback from our clients.</p>
        
        <p>After review, we were unable to publish your submission for the following reason:</p>
        <blockquote style='background: #f5f5f5; padding: 15px; border-left: 4px solid #ccc; margin: 15px 0;'>
            {$reasonText}
        </blockquote>
        
        <p><strong>What you can do:</strong></p>
        <ul>
            <li>If you'd like to submit a revised review, please feel free to do so</li>
            <li>If you have questions about our review guidelines, please contact us</li>
            <li>We welcome any feedback about your experience, even if not for publication</li>
        </ul>
        
        <p>We value your business and your feedback. If you'd like to discuss your experience further or have any concerns, please don't hesitate to reach out to us directly.</p>
        
        <p>Best regards,<br>
        <strong>The App Craft Services Team</strong><br>
        Email: williamsaonen@gmail.com<br>
        Website: <a href='https://appcraftservices.com'>appcraftservices.com</a></p>
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