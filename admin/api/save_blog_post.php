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
    
    // Get form data
    $title = $_POST['title'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $excerpt = $_POST['excerpt'] ?? '';
    $content = $_POST['content'] ?? '';
    $category = $_POST['category'] ?? '';
    $author = $_POST['author'] ?? 'App Craft Services';
    $status = $_POST['status'] ?? 'draft';
    $tags = json_decode($_POST['tags'] ?? '[]', true) ?: [];
    
    // Validate required fields
    if (empty($title) || empty($slug) || empty($excerpt) || empty($content) || empty($category)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
        exit;
    }
    
    // Check if slug already exists
    foreach ($blogPosts as $post) {
        if ($post['slug'] === $slug) {
            echo json_encode(['success' => false, 'message' => 'Slug already exists. Please choose a different slug.']);
            exit;
        }
    }
    
    // Handle image upload
    $imagePath = '';
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../assets/blog/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
        $fileName = $slug . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $uploadPath)) {
            $imagePath = 'assets/blog/' . $fileName;
        }
    }
    
    // Create new blog post
    $newPost = [
        'id' => uniqid(),
        'title' => $title,
        'slug' => $slug,
        'excerpt' => $excerpt,
        'content' => $content,
        'category' => $category,
        'author' => $author,
        'status' => $status,
        'tags' => $tags,
        'featured_image' => $imagePath,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Add to blog posts array
    array_unshift($blogPosts, $newPost); // Add to beginning for newest first
    
    // Save to file
    if (file_put_contents($blogFile, json_encode($blogPosts, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true, 'message' => 'Blog post saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save blog post']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>