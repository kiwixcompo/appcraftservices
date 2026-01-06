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
        echo json_encode(['success' => true, 'posts' => []]);
        exit;
    }
    
    $blogPosts = json_decode(file_get_contents($blogFile), true) ?: [];
    
    echo json_encode(['success' => true, 'posts' => $blogPosts]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>