<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $postId = $input['id'] ?? '';
    
    if (empty($postId)) {
        echo json_encode(['success' => false, 'message' => 'Post ID is required']);
        exit;
    }
    
    $blogFile = '../../data/blog_posts.json';
    
    if (!file_exists($blogFile)) {
        echo json_encode(['success' => false, 'message' => 'Blog posts file not found']);
        exit;
    }
    
    $blogPosts = json_decode(file_get_contents($blogFile), true) ?: [];
    
    // Find and remove the blog post
    $postFound = false;
    $imagePath = '';
    
    foreach ($blogPosts as $index => $post) {
        if ($post['id'] === $postId) {
            $imagePath = $post['featured_image'] ?? '';
            unset($blogPosts[$index]);
            $postFound = true;
            break;
        }
    }
    
    if (!$postFound) {
        echo json_encode(['success' => false, 'message' => 'Blog post not found']);
        exit;
    }
    
    // Remove image file if it exists
    if (!empty($imagePath) && file_exists('../../' . $imagePath)) {
        unlink('../../' . $imagePath);
    }
    
    // Re-index array
    $blogPosts = array_values($blogPosts);
    
    // Save updated blog posts
    if (file_put_contents($blogFile, json_encode($blogPosts, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true, 'message' => 'Blog post deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save changes']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>