<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // Load blog posts
    $blogPostsFile = '../../data/blog_posts.json';
    $blogPosts = file_exists($blogPostsFile) ? json_decode(file_get_contents($blogPostsFile), true) : [];
    
    // Filter published posts only
    $publishedPosts = array_filter($blogPosts, function($post) {
        return isset($post['published']) && $post['published'] === true;
    });
    
    // Generate sitemap XML
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
    $xml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
    
    // Homepage
    $xml .= "    <!-- Homepage - Highest Priority -->\n";
    $xml .= "    <url>\n";
    $xml .= "        <loc>https://appcraftservices.com/</loc>\n";
    $xml .= "        <lastmod>" . date('Y-m-d') . "</lastmod>\n";
    $xml .= "        <changefreq>weekly</changefreq>\n";
    $xml .= "        <priority>1.0</priority>\n";
    $xml .= "        <image:image>\n";
    $xml .= "            <image:loc>https://appcraftservices.com/assets/portrait-photo.webp</image:loc>\n";
    $xml .= "            <image:title>Williams Alfred Onen - Founder &amp; Lead Developer</image:title>\n";
    $xml .= "        </image:image>\n";
    $xml .= "    </url>\n\n";
    
    // Main service pages
    $xml .= "    <!-- Main Service Pages - High Priority -->\n";
    $mainPages = [
        ['url' => '/services', 'priority' => '0.9'],
        ['url' => '/pricing', 'priority' => '0.9'],
        ['url' => '/contact', 'priority' => '0.9'],
        ['url' => '/process', 'priority' => '0.8'],
        ['url' => '/blog', 'priority' => '0.8']
    ];
    
    foreach ($mainPages as $page) {
        $xml .= "    <url>\n";
        $xml .= "        <loc>https://appcraftservices.com{$page['url']}</loc>\n";
        $xml .= "        <lastmod>" . date('Y-m-d') . "</lastmod>\n";
        $xml .= "        <changefreq>monthly</changefreq>\n";
        $xml .= "        <priority>{$page['priority']}</priority>\n";
        $xml .= "    </url>\n\n";
    }
    
    // Blog posts
    if (!empty($publishedPosts)) {
        $xml .= "    <!-- Blog Posts -->\n";
        foreach ($publishedPosts as $post) {
            $lastmod = isset($post['updated_at']) ? date('Y-m-d', strtotime($post['updated_at'])) : date('Y-m-d');
            $xml .= "    <url>\n";
            $xml .= "        <loc>https://appcraftservices.com/blog/{$post['slug']}</loc>\n";
            $xml .= "        <lastmod>{$lastmod}</lastmod>\n";
            $xml .= "        <changefreq>monthly</changefreq>\n";
            $xml .= "        <priority>0.7</priority>\n";
            $xml .= "    </url>\n";
        }
        $xml .= "\n";
    }
    
    // Conversion pages
    $xml .= "    <!-- Conversion Pages -->\n";
    $conversionPages = [
        ['url' => '/schedule', 'priority' => '0.7'],
        ['url' => '/startup-packages', 'priority' => '0.7'],
        ['url' => '/payment', 'priority' => '0.6']
    ];
    
    foreach ($conversionPages as $page) {
        $xml .= "    <url>\n";
        $xml .= "        <loc>https://appcraftservices.com{$page['url']}</loc>\n";
        $xml .= "        <lastmod>" . date('Y-m-d') . "</lastmod>\n";
        $xml .= "        <changefreq>monthly</changefreq>\n";
        $xml .= "        <priority>{$page['priority']}</priority>\n";
        $xml .= "    </url>\n\n";
    }
    
    // Legal pages
    $xml .= "    <!-- Legal & Support Pages -->\n";
    $xml .= "    <url>\n";
    $xml .= "        <loc>https://appcraftservices.com/terms</loc>\n";
    $xml .= "        <lastmod>" . date('Y-m-d') . "</lastmod>\n";
    $xml .= "        <changefreq>yearly</changefreq>\n";
    $xml .= "        <priority>0.3</priority>\n";
    $xml .= "    </url>\n";
    
    $xml .= "</urlset>";
    
    // Write sitemap file
    $sitemapPath = '../../sitemap.xml';
    if (file_put_contents($sitemapPath, $xml)) {
        echo json_encode([
            'success' => true,
            'message' => 'Sitemap regenerated successfully',
            'blog_posts_count' => count($publishedPosts)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to write sitemap file'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
