<?php
/**
 * Server-Sent Events endpoint for real-time review updates
 * Provides live updates when new reviews are approved
 * Requirements: 2.2
 */

// Prevent direct access - only allow from EventSource
if (empty($_SERVER['HTTP_ACCEPT']) || strpos($_SERVER['HTTP_ACCEPT'], 'text/event-stream') === false) {
    http_response_code(400);
    echo json_encode(['error' => 'This endpoint is for Server-Sent Events only']);
    exit;
}

// Set headers for Server-Sent Events
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Cache-Control');

// Disable output buffering for SSE
if (ob_get_level()) {
    ob_end_clean();
}

require_once '../../config/database.php';

// Function to send SSE data
function sendSSE($data, $event = 'message') {
    echo "event: $event\n";
    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}

// Function to get the latest review timestamp
function getLatestReviewTimestamp($conn) {
    try {
        $sql = "SELECT MAX(updated_at) as latest_timestamp FROM reviews WHERE moderation_status = 'approved'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['latest_timestamp'] ?? '1970-01-01 00:00:00';
    } catch (Exception $e) {
        error_log("Error getting latest timestamp: " . $e->getMessage());
        return '1970-01-01 00:00:00';
    }
}

// Function to get new reviews since timestamp
function getNewReviews($conn, $since) {
    try {
        $sql = "SELECT 
                    id, reviewer_name, company, project_type, rating, content,
                    project_completion_date, submission_date, contact_permission,
                    verified, display_order, updated_at
                FROM reviews 
                WHERE moderation_status = 'approved' 
                AND updated_at > :since
                ORDER BY updated_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':since', $since);
        $stmt->execute();
        
        $reviews = $stmt->fetchAll();
        
        // Format reviews for public display
        $formattedReviews = [];
        foreach ($reviews as $review) {
            $formattedReviews[] = [
                'id' => $review['id'],
                'reviewer_name' => $review['reviewer_name'],
                'company' => $review['company'],
                'project_type' => $review['project_type'],
                'rating' => intval($review['rating']),
                'rating_stars' => str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']),
                'content' => $review['content'],
                'project_completion_date_formatted' => date('M Y', strtotime($review['project_completion_date'])),
                'submission_date_formatted' => date('M j, Y', strtotime($review['submission_date'])),
                'contact_permission' => (bool)$review['contact_permission'],
                'verified' => (bool)$review['verified'],
                'display_order' => intval($review['display_order']),
                'updated_at' => $review['updated_at']
            ];
        }
        
        return $formattedReviews;
    } catch (Exception $e) {
        error_log("Error getting new reviews: " . $e->getMessage());
        return [];
    }
}

try {
    // Initialize database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        sendSSE(['error' => 'Database connection failed'], 'error');
        exit;
    }
    
    // Send initial connection message
    sendSSE(['message' => 'Connected to review updates', 'timestamp' => date('c')], 'connected');
    
    // Get initial timestamp
    $lastCheck = getLatestReviewTimestamp($conn);
    
    // Keep connection alive and check for updates (max 5 minutes)
    $maxExecutionTime = 300;
    $startTime = time();
    $heartbeatCounter = 0;
    
    while (time() - $startTime < $maxExecutionTime) {
        // Check for new reviews
        $newReviews = getNewReviews($conn, $lastCheck);
        
        if (!empty($newReviews)) {
            // Send new reviews
            sendSSE([
                'type' => 'new_reviews',
                'reviews' => $newReviews,
                'count' => count($newReviews),
                'timestamp' => date('c')
            ], 'review_update');
            
            // Update last check timestamp
            $lastCheck = max(array_column($newReviews, 'updated_at'));
        }
        
        // Send heartbeat every 30 seconds
        $heartbeatCounter++;
        if ($heartbeatCounter % 6 === 0) { // 6 * 5 seconds = 30 seconds
            sendSSE([
                'type' => 'heartbeat',
                'timestamp' => date('c'),
                'uptime' => time() - $startTime
            ], 'heartbeat');
        }
        
        // Check if client disconnected
        if (connection_aborted()) {
            break;
        }
        
        // Sleep for 5 seconds before next check
        sleep(5);
    }
    
} catch (Exception $e) {
    sendSSE(['error' => $e->getMessage()], 'error');
    error_log("SSE error: " . $e->getMessage());
}

// Send disconnect message
sendSSE(['message' => 'Connection closed', 'timestamp' => date('c')], 'disconnected');
?>