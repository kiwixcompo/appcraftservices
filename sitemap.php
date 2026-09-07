<?php
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php
$staticPages = [
    '' => ['changefreq' => 'weekly', 'priority' => '1.0'],
    'services' => ['changefreq' => 'monthly', 'priority' => '0.8'],
    'process' => ['changefreq' => 'monthly', 'priority' => '0.8'],
    'pricing' => ['changefreq' => 'monthly', 'priority' => '0.8'],
    'blog' => ['changefreq' => 'daily', 'priority' => '0.9'],
    'contact' => ['changefreq' => 'monthly', 'priority' => '0.8']
];

$baseUrl = 'https://appcraftservices.com/';

foreach ($staticPages as $path => $meta) {
    echo "    <url>\n";
    echo "        <loc>" . htmlspecialchars($baseUrl . $path) . "</loc>\n";
    echo "        <changefreq>" . $meta['changefreq'] . "</changefreq>\n";
    echo "        <priority>" . $meta['priority'] . "</priority>\n";
    echo "    </url>\n";
}

$blogPostsFile = __DIR__ . '/data/blog_posts.json';
if (file_exists($blogPostsFile)) {
    $blogPosts = json_decode(file_get_contents($blogPostsFile), true);
    if (is_array($blogPosts)) {
        foreach ($blogPosts as $post) {
            echo "    <url>\n";
            echo "        <loc>" . htmlspecialchars($baseUrl . 'blog/' . $post['slug']) . "</loc>\n";
            
            if (!empty($post['published_at'])) {
                $date = date('Y-m-d', strtotime($post['published_at']));
                echo "        <lastmod>" . $date . "</lastmod>\n";
            }
            
            echo "        <changefreq>monthly</changefreq>\n";
            echo "        <priority>0.7</priority>\n";
            echo "    </url>\n";
        }
    }
}
?>
</urlset>