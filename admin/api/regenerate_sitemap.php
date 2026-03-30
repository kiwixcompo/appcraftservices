<?php
// Allow both direct calls (with session check) and internal includes
$isDirectCall = !defined('SITEMAP_INCLUDED');

if ($isDirectCall) {
    session_start();
    header('Content-Type: application/json');

    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
}

function generateSitemap() {
    $blogPostsFile = __DIR__ . '/../../data/blog_posts.json';
    $blogPosts = file_exists($blogPostsFile) ? json_decode(file_get_contents($blogPostsFile), true) : [];

    // Filter published posts only
    $publishedPosts = array_filter($blogPosts ?: [], function($post) {
        return isset($post['published']) && $post['published'] === true;
    });

    $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
    $xml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

    // Homepage
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

    // Main pages
    $mainPages = [
        ['url' => '/services',          'priority' => '0.9', 'freq' => 'monthly'],
        ['url' => '/pricing',           'priority' => '0.9', 'freq' => 'monthly'],
        ['url' => '/contact',           'priority' => '0.9', 'freq' => 'monthly'],
        ['url' => '/process',           'priority' => '0.8', 'freq' => 'monthly'],
        ['url' => '/blog',              'priority' => '0.8', 'freq' => 'weekly'],
        ['url' => '/schedule',          'priority' => '0.7', 'freq' => 'monthly'],
        ['url' => '/startup-packages',  'priority' => '0.7', 'freq' => 'monthly'],
        ['url' => '/payment',           'priority' => '0.6', 'freq' => 'monthly'],
        ['url' => '/terms',             'priority' => '0.3', 'freq' => 'yearly'],
    ];

    foreach ($mainPages as $page) {
        $xml .= "    <url>\n";
        $xml .= "        <loc>https://appcraftservices.com{$page['url']}</loc>\n";
        $xml .= "        <lastmod>" . date('Y-m-d') . "</lastmod>\n";
        $xml .= "        <changefreq>{$page['freq']}</changefreq>\n";
        $xml .= "        <priority>{$page['priority']}</priority>\n";
        $xml .= "    </url>\n\n";
    }

    // Blog posts — use real lastmod from updated_at
    if (!empty($publishedPosts)) {
        foreach ($publishedPosts as $post) {
            $lastmod = !empty($post['updated_at'])
                ? date('Y-m-d', strtotime($post['updated_at']))
                : date('Y-m-d');
            $slug = htmlspecialchars($post['slug']);
            $xml .= "    <url>\n";
            $xml .= "        <loc>https://appcraftservices.com/blog/{$slug}</loc>\n";
            $xml .= "        <lastmod>{$lastmod}</lastmod>\n";
            $xml .= "        <changefreq>monthly</changefreq>\n";
            $xml .= "        <priority>0.7</priority>\n";
            $xml .= "    </url>\n";
        }
        $xml .= "\n";
    }

    $xml .= "</urlset>";

    $sitemapPath = __DIR__ . '/../../sitemap.xml';
    $written = file_put_contents($sitemapPath, $xml);

    if ($written === false) {
        return ['success' => false, 'message' => 'Failed to write sitemap file'];
    }

    // Ping Google to notify of sitemap update
    $pingUrl = 'https://www.google.com/ping?sitemap=' . urlencode('https://appcraftservices.com/sitemap.xml');
    @file_get_contents($pingUrl); // fire-and-forget, ignore errors

    return [
        'success' => true,
        'message' => 'Sitemap regenerated and Google notified',
        'blog_posts_count' => count($publishedPosts)
    ];
}

$result = generateSitemap();

if ($isDirectCall) {
    echo json_encode($result);
}
?>
