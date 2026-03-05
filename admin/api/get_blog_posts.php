<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $blogFile = '../../data/blog_posts.json';
    
    if (!file_exists($blogFile)) {
        echo json_encode([]);
        exit;
    }
    
    $blogPosts = json_decode(file_get_contents($blogFile), true) ?: [];
    
    // Return just the array for consistency
    echo json_encode($blogPosts);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>