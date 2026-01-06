<?php
// Disable error display to prevent JSON corruption
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $postsFile = __DIR__ . '/../data/blog_posts.json';
    
    if (!file_exists($postsFile)) {
        echo json_encode(['success' => true, 'posts' => [], 'has_more' => false]);
        exit;
    }
    
    $posts = json_decode(file_get_contents($postsFile), true);
    
    if (!is_array($posts)) {
        echo json_encode(['success' => true, 'posts' => [], 'has_more' => false]);
        exit;
    }
    
    // Filter only published posts
    $publishedPosts = array_filter($posts, function($post) {
        return isset($post['published']) && $post['published'] === true;
    });
    
    // Sort by published date (newest first)
    usort($publishedPosts, function($a, $b) {
        return strtotime($b['published_at']) - strtotime($a['published_at']);
    });
    
    // Pagination
    $page = intval($_GET['page'] ?? 1);
    $limit = intval($_GET['limit'] ?? 10);
    $offset = ($page - 1) * $limit;
    
    $totalPosts = count($publishedPosts);
    $paginatedPosts = array_slice($publishedPosts, $offset, $limit);
    $hasMore = ($offset + $limit) < $totalPosts;
    
    echo json_encode([
        'success' => true,
        'posts' => $paginatedPosts,
        'has_more' => $hasMore,
        'total' => $totalPosts,
        'page' => $page,
        'limit' => $limit
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    error_log("Get blog posts error: " . $e->getMessage());
}
?>