<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // Check if image was uploaded
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No image uploaded']);
        exit;
    }
    
    $file = $_FILES['image'];
    $filename = $file['name'];
    $tmpPath = $file['tmp_name'];
    
    // Validate file type (should already be WebP from client-side conversion)
    $allowedTypes = ['image/webp', 'image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type']);
        exit;
    }
    
    // Create blog images directory if it doesn't exist
    $uploadDir = '../../assets/blog/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    if ($extension !== 'webp') {
        $extension = 'webp';
    }
    $newFilename = 'blog-' . time() . '-' . uniqid() . '.' . $extension;
    $uploadPath = $uploadDir . $newFilename;
    
    // Move uploaded file
    if (move_uploaded_file($tmpPath, $uploadPath)) {
        $relativePath = 'assets/blog/' . $newFilename;
        
        echo json_encode([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'path' => $relativePath,
            'filename' => $newFilename
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save image']);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
