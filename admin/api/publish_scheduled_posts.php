<?php
/**
 * Cron Job Script: Publish Scheduled Blog Posts
 * 
 * This script should be run every hour via cron job:
 * 0 * * * * php /path/to/admin/api/publish_scheduled_posts.php
 * 
 * Or via web cron (more reliable):
 * 0 * * * * curl https://appcraftservices.com/admin/api/publish_scheduled_posts.php?key=YOUR_SECRET_KEY
 */

// Security: Check for secret key if accessed via web
$secretKey = 'appcraftservices2026'; // Change this to a secure random string
if (php_sapi_name() !== 'cli') {
    if (!isset($_GET['key']) || $_GET['key'] !== $secretKey) {
        http_response_code(403);
        die('Unauthorized');
    }
}

header('Content-Type: application/json');

try {
    $blogFile = '../../data/blog_posts.json';
    
    if (!file_exists($blogFile)) {
        echo json_encode(['success' => false, 'message' => 'Blog posts file not found']);
        exit;
    }
    
    $blogPosts = json_decode(file_get_contents($blogFile), true) ?: [];
    $now = new Date();
    $publishedCount = 0;
    $updated = false;
    
    foreach ($blogPosts as $index => &$post) {
        // Check if post is scheduled and time has come
        if (isset($post['scheduled_date']) && 
            !empty($post['scheduled_date']) && 
            !$post['published']) {
            
            $scheduledTime = strtotime($post['scheduled_date']);
            $currentTime = time();
            
            // If scheduled time has passed, publish the post
            if ($scheduledTime <= $currentTime) {
                $post['published'] = true;
                $post['published_at'] = date('Y-m-d H:i:s');
                $post['scheduled_date'] = null; // Clear scheduled date
                $publishedCount++;
                $updated = true;
            }
        }
    }
    
    // Save updated posts if any were published
    if ($updated) {
        file_put_contents($blogFile, json_encode($blogPosts, JSON_PRETTY_PRINT));
        
        // Regenerate sitemap
        include 'regenerate_sitemap.php';
        
        echo json_encode([
            'success' => true,
            'message' => "Published $publishedCount scheduled post(s)",
            'published_count' => $publishedCount
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'No posts to publish at this time',
            'published_count' => 0
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
