<?php
/**
 * Get Reviews API for Admin Dashboard
 * Retrieves reviews with filtering by moderation status
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

require_once '../../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Get filter parameters
    $status = $_GET['status'] ?? 'all';
    $limit = intval($_GET['limit'] ?? 50);
    $offset = intval($_GET['offset'] ?? 0);
    $orderBy = $_GET['order_by'] ?? 'submission_date';
    $orderDir = $_GET['order_dir'] ?? 'DESC';
    
    // Validate parameters
    $allowedStatuses = ['all', 'pending', 'approved', 'rejected'];
    if (!in_array($status, $allowedStatuses)) {
        $status = 'all';
    }
    
    $allowedOrderBy = ['submission_date', 'rating', 'reviewer_name', 'company', 'moderation_status'];
    if (!in_array($orderBy, $allowedOrderBy)) {
        $orderBy = 'submission_date';
    }
    
    $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';
    
    // Build query
    $whereClause = '';
    $params = [];
    
    if ($status !== 'all') {
        $whereClause = 'WHERE moderation_status = :status';
        $params[':status'] = $status;
    }
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM reviews $whereClause";
    $countStmt = $conn->prepare($countSql);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalCount = $countStmt->fetch()['total'];
    
    // Get reviews
    $sql = "SELECT 
                id, reviewer_name, company, project_type, rating, content,
                project_completion_date, submission_date, moderation_status,
                moderator_id, moderation_date, moderation_notes,
                contact_permission, verified, reviewer_email, project_id,
                display_order, created_at, updated_at
            FROM reviews 
            $whereClause 
            ORDER BY $orderBy $orderDir 
            LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $reviews = $stmt->fetchAll();
    
    // Format dates and add additional info
    foreach ($reviews as &$review) {
        $review['submission_date_formatted'] = date('M j, Y g:i A', strtotime($review['submission_date']));
        $review['project_completion_date_formatted'] = date('M j, Y', strtotime($review['project_completion_date']));
        $review['moderation_date_formatted'] = $review['moderation_date'] ? 
            date('M j, Y g:i A', strtotime($review['moderation_date'])) : null;
        $review['rating_stars'] = str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']);
        $review['content_preview'] = strlen($review['content']) > 100 ? 
            substr($review['content'], 0, 100) . '...' : $review['content'];
    }
    
    echo json_encode([
        'success' => true,
        'reviews' => $reviews,
        'pagination' => [
            'total' => $totalCount,
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $totalCount
        ],
        'filters' => [
            'status' => $status,
            'order_by' => $orderBy,
            'order_dir' => $orderDir
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
    error_log("Get reviews error: " . $e->getMessage());
}
?>