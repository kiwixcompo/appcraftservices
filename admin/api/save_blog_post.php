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
    
    // Load existing blog posts
    $blogPosts = [];
    if (file_exists($blogFile)) {
        $blogPosts = json_decode(file_get_contents($blogFile), true) ?: [];
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }
    
    // Extract data
    $id = $input['id'] ?? '';
    $title = $input['title'] ?? '';
    $slug = $input['slug'] ?? '';
    $excerpt = $input['excerpt'] ?? '';
    $content = $input['content'] ?? '';
    $category = $input['category'] ?? '';
    $author = $input['author'] ?? 'Williams Alfred Onen';
    $published = $input['published'] ?? false;
    $featured = $input['featured'] ?? false;
    $tags = $input['tags'] ?? [];
    $featuredImage = $input['featured_image'] ?? '';
    $scheduledDate = $input['scheduled_date'] ?? null;
    
    // Validate required fields
    if (empty($title) || empty($slug) || empty($excerpt) || empty($content) || empty($category)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
        exit;
    }
    
    // Check if updating existing post
    $existingIndex = -1;
    foreach ($blogPosts as $index => $post) {
        if ($post['id'] === $id || $post['slug'] === $slug) {
            $existingIndex = $index;
            break;
        }
    }
    
    // Create/update blog post
    $postData = [
        'id' => $id ?: uniqid('post_'),
        'title' => $title,
        'slug' => $slug,
        'excerpt' => $excerpt,
        'content' => $content,
        'category' => $category,
        'author' => $author,
        'published' => $published,
        'featured' => $featured,
        'tags' => $tags,
        'featured_image' => $featuredImage,
        'scheduled_date' => $scheduledDate,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    if ($existingIndex >= 0) {
        // Update existing post
        $postData['created_at'] = $blogPosts[$existingIndex]['created_at'] ?? date('Y-m-d H:i:s');
        $postData['published_at'] = $blogPosts[$existingIndex]['published_at'] ?? ($published ? date('Y-m-d H:i:s') : null);
        $blogPosts[$existingIndex] = $postData;
    } else {
        // New post
        $postData['created_at'] = date('Y-m-d H:i:s');
        $postData['published_at'] = $published ? date('Y-m-d H:i:s') : null;
        array_unshift($blogPosts, $postData); // Add to beginning
    }
    
    // Save to file
    if (file_put_contents($blogFile, json_encode($blogPosts, JSON_PRETTY_PRINT))) {
        // Auto-regenerate sitemap
        include 'regenerate_sitemap.php';
        
        echo json_encode([
            'success' => true, 
            'message' => 'Blog post saved successfully',
            'post_id' => $postData['id']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save blog post']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>