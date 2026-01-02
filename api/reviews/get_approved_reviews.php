<?php
/**
 * Get Approved Reviews API for Public Display
 * Returns only approved reviews for homepage display
 * Requirements: 2.1, 2.2, 2.3, 2.4
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Get query parameters
    $limit = intval($_GET['limit'] ?? 10);
    $offset = intval($_GET['offset'] ?? 0);
    $orderBy = $_GET['order_by'] ?? 'display_order';
    $orderDir = $_GET['order_dir'] ?? 'DESC';
    
    // Validate parameters
    $allowedOrderBy = ['display_order', 'submission_date', 'rating', 'reviewer_name'];
    if (!in_array($orderBy, $allowedOrderBy)) {
        $orderBy = 'display_order';
    }
    
    $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';
    $limit = min($limit, 50); // Maximum 50 reviews per request
    
    // Get approved reviews only
    $sql = "SELECT 
                id, reviewer_name, company, project_type, rating, content,
                project_completion_date, submission_date, contact_permission,
                verified, display_order
            FROM reviews 
            WHERE moderation_status = 'approved'
            ORDER BY $orderBy $orderDir 
            LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
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
            'project_completion_date' => $review['project_completion_date'],
            'project_completion_date_formatted' => date('M Y', strtotime($review['project_completion_date'])),
            'submission_date' => $review['submission_date'],
            'submission_date_formatted' => date('M j, Y', strtotime($review['submission_date'])),
            'contact_permission' => (bool)$review['contact_permission'],
            'verified' => (bool)$review['verified'],
            'display_order' => intval($review['display_order'])
        ];
    }
    
    // Get total count of approved reviews
    $countSql = "SELECT COUNT(*) as total FROM reviews WHERE moderation_status = 'approved'";
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute();
    $totalCount = $countStmt->fetch()['total'];
    
    // Calculate average rating
    $avgSql = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE moderation_status = 'approved'";
    $avgStmt = $conn->prepare($avgSql);
    $avgStmt->execute();
    $avgData = $avgStmt->fetch();
    
    echo json_encode([
        'success' => true,
        'reviews' => $formattedReviews,
        'pagination' => [
            'total' => intval($totalCount),
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $totalCount
        ],
        'statistics' => [
            'total_reviews' => intval($avgData['review_count']),
            'average_rating' => round($avgData['avg_rating'], 1),
            'average_rating_stars' => str_repeat('★', round($avgData['avg_rating'])) . str_repeat('☆', 5 - round($avgData['avg_rating']))
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'reviews' => [],
        'pagination' => [
            'total' => 0,
            'limit' => 0,
            'offset' => 0,
            'has_more' => false
        ],
        'statistics' => [
            'total_reviews' => 0,
            'average_rating' => 0,
            'average_rating_stars' => '☆☆☆☆☆'
        ]
    ]);
    error_log("Get approved reviews error: " . $e->getMessage());
}
?>