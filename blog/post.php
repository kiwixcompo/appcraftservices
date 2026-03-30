<?php
// Get the slug from the URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
    header('Location: /blog');
    exit;
}

// Load blog posts
$blogPostsFile = '../data/blog_posts.json';
$blogPosts = file_exists($blogPostsFile) ? json_decode(file_get_contents($blogPostsFile), true) : [];

// Find the post
$post = null;
foreach ($blogPosts as $p) {
    if ($p['slug'] === $slug && $p['published']) {
        $post = $p;
        break;
    }
}

// If post not found, redirect to blog
if (!$post) {
    header('Location: /blog');
    exit;
}

// Simple Markdown to HTML converter
function markdownToHtml($text) {
    // Headers
    $text = preg_replace('/^### (.+)$/m', '<h3 class="text-2xl font-bold text-navy mt-8 mb-4">$1</h3>', $text);
    $text = preg_replace('/^## (.+)$/m', '<h2 class="text-3xl font-bold text-navy mt-10 mb-6">$1</h2>', $text);
    $text = preg_replace('/^# (.+)$/m', '<h1 class="text-4xl font-bold text-navy mt-12 mb-8">$1</h1>', $text);
    
    // Bold and italic
    $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong class="font-semibold">$1</strong>', $text);
    $text = preg_replace('/\*(.+?)\*/s', '<em class="italic">$1</em>', $text);
    
    // Images — must come before links so ![alt](url) is matched first
    $text = preg_replace(
        '/!\[([^\]]*)\]\(([^\)]+)\)/',
        '<img src="$2" alt="$1" class="w-full rounded-lg shadow-md my-6" loading="lazy" onerror="this.style.display=\'none\'">',
        $text
    );
    
    // Links
    $text = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2" class="text-electric-blue hover:underline">$1</a>', $text);
    
    // Code blocks
    $text = preg_replace('/```([^`]+)```/s', '<pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto my-4"><code>$1</code></pre>', $text);
    $text = preg_replace('/`([^`]+)`/', '<code class="bg-gray-100 px-2 py-1 rounded text-sm">$1</code>', $text);
    
    // Lists
    $text = preg_replace('/^\- (.+)$/m', '<li class="ml-6 mb-2">$1</li>', $text);
    $text = preg_replace('/^(\d+)\. (.+)$/m', '<li class="ml-6 mb-2">$2</li>', $text);
    
    // Wrap lists
    $text = preg_replace('/(<li[^>]*>.*<\/li>)/s', '<ul class="list-disc my-4">$1</ul>', $text);
    
    // Paragraphs — skip lines that are already HTML block elements
    $text = preg_replace('/^(?!<h[1-6]|<ul|<li|<pre|<img|<p)(.+)$/m', '<p class="mb-4 text-gray-700 leading-relaxed">$1</p>', $text);
    
    return $text;
}

$contentHtml = markdownToHtml($post['content']);
$publishedDate = date('F j, Y', strtotime($post['published_at']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17861189621"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'AW-17861189621');
    </script>
    
    <title><?php echo htmlspecialchars($post['title']); ?> | App Craft Services Blog</title>
    <meta name="description" content="<?php echo htmlspecialchars($post['excerpt']); ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="https://appcraftservices.com/blog/<?php echo htmlspecialchars($post['slug']); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($post['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($post['excerpt']); ?>">
    <?php if (!empty($post['featured_image'])): ?>
    <meta property="og:image" content="https://appcraftservices.com/<?php echo htmlspecialchars($post['featured_image']); ?>">
    <?php endif; ?>
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://appcraftservices.com/blog/<?php echo htmlspecialchars($post['slug']); ?>">
    <meta property="twitter:title" content="<?php echo htmlspecialchars($post['title']); ?>">
    <meta property="twitter:description" content="<?php echo htmlspecialchars($post['excerpt']); ?>">
    <?php if (!empty($post['featured_image'])): ?>
    <meta property="twitter:image" content="https://appcraftservices.com/<?php echo htmlspecialchars($post['featured_image']); ?>">
    <?php endif; ?>
    
    <meta name="robots" content="index, follow">
    <meta name="author" content="<?php echo htmlspecialchars($post['author']); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(implode(', ', $post['tags'])); ?>">
    
    <link rel="canonical" content="https://appcraftservices.com/blog/<?php echo htmlspecialchars($post['slug']); ?>">
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'navy': '#1e3a8a',
                        'electric-blue': '#3b82f6',
                        'light-gray': '#f8fafc'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/mobile-responsive.css">
</head>
<body class="bg-white">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex-shrink-0">
                    <a href="/" class="flex items-center">
                        <img src="../assets/logo.png" alt="App Craft Services" class="h-10 w-auto">
                    </a>
                </div>
                
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="/" class="text-gray-700 hover:text-electric-blue px-3 py-2 rounded-md text-sm font-medium">Home</a>
                        <a href="/process" class="text-gray-700 hover:text-electric-blue px-3 py-2 rounded-md text-sm font-medium">Process</a>
                        <a href="/services" class="text-gray-700 hover:text-electric-blue px-3 py-2 rounded-md text-sm font-medium">Services</a>
                        <a href="/pricing" class="text-gray-700 hover:text-electric-blue px-3 py-2 rounded-md text-sm font-medium">Pricing</a>
                        <a href="/blog" class="text-navy hover:text-electric-blue px-3 py-2 rounded-md text-sm font-medium">Blog</a>
                        <a href="/contact" class="text-gray-700 hover:text-electric-blue px-3 py-2 rounded-md text-sm font-medium">Contact</a>
                    </div>
                </div>

                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-gray-700 hover:text-electric-blue">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="md:hidden hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-white shadow-lg">
                <a href="/" class="text-gray-700 hover:text-electric-blue block px-3 py-2 rounded-md text-base font-medium">Home</a>
                <a href="/process" class="text-gray-700 hover:text-electric-blue block px-3 py-2 rounded-md text-base font-medium">Process</a>
                <a href="/services" class="text-gray-700 hover:text-electric-blue block px-3 py-2 rounded-md text-base font-medium">Services</a>
                <a href="/pricing" class="text-gray-700 hover:text-electric-blue block px-3 py-2 rounded-md text-base font-medium">Pricing</a>
                <a href="/blog" class="text-navy hover:text-electric-blue block px-3 py-2 rounded-md text-base font-medium">Blog</a>
                <a href="/contact" class="text-gray-700 hover:text-electric-blue block px-3 py-2 rounded-md text-base font-medium">Contact</a>
            </div>
        </div>
    </nav>

    <!-- Blog Post Content -->
    <article class="pt-24 pb-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="mb-8 text-sm">
                <a href="/" class="text-electric-blue hover:underline">Home</a>
                <span class="mx-2 text-gray-400">/</span>
                <a href="/blog" class="text-electric-blue hover:underline">Blog</a>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-600"><?php echo htmlspecialchars($post['title']); ?></span>
            </nav>
            
            <!-- Post Header -->
            <header class="mb-12">
                <div class="mb-4">
                    <span class="inline-block px-3 py-1 bg-electric-blue text-white text-sm font-medium rounded-full">
                        <?php echo htmlspecialchars($post['category']); ?>
                    </span>
                </div>
                
                <h1 class="text-4xl md:text-5xl font-bold text-navy mb-6 leading-tight">
                    <?php echo htmlspecialchars($post['title']); ?>
                </h1>
                
                <div class="flex items-center text-gray-600 text-sm space-x-4">
                    <div class="flex items-center">
                        <i class="fas fa-user mr-2"></i>
                        <span><?php echo htmlspecialchars($post['author']); ?></span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-calendar mr-2"></i>
                        <span><?php echo $publishedDate; ?></span>
                    </div>
                </div>
                
                <?php if (!empty($post['tags'])): ?>
                <div class="mt-6 flex flex-wrap gap-2">
                    <?php foreach ($post['tags'] as $tag): ?>
                        <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">
                            #<?php echo htmlspecialchars($tag); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </header>
            
            <!-- Featured Image -->
            <?php if (!empty($post['featured_image'])): ?>
            <div class="mb-12">
                <img src="../<?php echo htmlspecialchars($post['featured_image']); ?>" 
                     alt="<?php echo htmlspecialchars($post['title']); ?>"
                     class="w-full rounded-lg shadow-lg"
                     onerror="this.style.display='none'">
            </div>
            <?php endif; ?>
            
            <!-- Post Content -->
            <div class="prose prose-lg max-w-none">
                <?php echo $contentHtml; ?>
            </div>
            
            <!-- Share Buttons -->
            <div class="mt-12 pt-8 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-navy mb-4">Share this article</h3>
                <div class="flex space-x-4">
                    <a href="https://twitter.com/intent/tweet?url=https://appcraftservices.com/blog/<?php echo urlencode($post['slug']); ?>&text=<?php echo urlencode($post['title']); ?>" 
                       target="_blank" rel="noopener"
                       class="px-4 py-2 bg-blue-400 text-white rounded-lg hover:bg-blue-500 transition">
                        <i class="fab fa-twitter mr-2"></i>Twitter
                    </a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=https://appcraftservices.com/blog/<?php echo urlencode($post['slug']); ?>" 
                       target="_blank" rel="noopener"
                       class="px-4 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 transition">
                        <i class="fab fa-linkedin mr-2"></i>LinkedIn
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=https://appcraftservices.com/blog/<?php echo urlencode($post['slug']); ?>" 
                       target="_blank" rel="noopener"
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fab fa-facebook mr-2"></i>Facebook
                    </a>
                </div>
            </div>
            
            <!-- Back to Blog -->
            <div class="mt-12 text-center">
                <a href="/blog" class="inline-block px-6 py-3 bg-electric-blue text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Blog
                </a>
            </div>
        </div>
    </article>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-2xl font-bold mb-4">App Craft Services</h3>
                    <p class="text-gray-300 mb-4">Professional web development services for growing businesses.</p>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="/" class="text-gray-300 hover:text-white">Home</a></li>
                        <li><a href="/services" class="text-gray-300 hover:text-white">Services</a></li>
                        <li><a href="/pricing" class="text-gray-300 hover:text-white">Pricing</a></li>
                        <li><a href="/blog" class="text-gray-300 hover:text-white">Blog</a></li>
                        <li><a href="/contact" class="text-gray-300 hover:text-white">Contact</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Services</h4>
                    <ul class="space-y-2">
                        <li><a href="/services" class="text-gray-300 hover:text-white">Custom Web Apps</a></li>
                        <li><a href="/services" class="text-gray-300 hover:text-white">MVP Development</a></li>
                        <li><a href="/services" class="text-gray-300 hover:text-white">Maintenance & Support</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                <p class="text-gray-300">Copyright © <?php echo date('Y'); ?> App Craft Services. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../assets/script.js"></script>
</body>
</html>
