<?php
// Server-side render blog posts for SEO / Googlebot
$postsFile = __DIR__ . '/../data/blog_posts.json';
$allPosts   = file_exists($postsFile) ? json_decode(file_get_contents($postsFile), true) : [];

$publishedPosts = array_filter($allPosts ?: [], function($p) {
    return isset($p['published']) && $p['published'] === true;
});

usort($publishedPosts, function($a, $b) {
    return strtotime($b['published_at'] ?? 0) - strtotime($a['published_at'] ?? 0);
});

function safeHtml($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }
function fmtDate($d) {
    $ts = strtotime($d ?? '');
    return $ts ? date('F j, Y', $ts) : '';
}
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

    <title>Blog - App Craft Services | Startup Development Insights</title>
    <meta name="description" content="Insights, tips, and guides for startup founders and entrepreneurs. Learn about web development, MVP creation, and building successful digital products.">
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    <link rel="canonical" href="https://appcraftservices.com/blog">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://appcraftservices.com/blog">
    <meta property="og:title" content="Blog - App Craft Services">
    <meta property="og:description" content="Startup development insights, tips, and guides.">

    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <link rel="preload" href="../assets/styles.css" as="style">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: { extend: { colors: { 'navy': '#1e3a8a', 'electric-blue': '#3b82f6', 'light-gray': '#f8fafc' } } }
        }
    </script>
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/mobile-responsive.css">
</head>
<body class="bg-light-gray">

    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <a href="/" class="text-2xl font-bold text-navy">App Craft Services</a>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/" class="text-gray-700 hover:text-electric-blue transition">Home</a>
                    <a href="/process" class="text-gray-700 hover:text-electric-blue transition">Process</a>
                    <a href="/services" class="text-gray-700 hover:text-electric-blue transition">Services</a>
                    <a href="/pricing" class="text-gray-700 hover:text-electric-blue transition">Pricing</a>
                    <a href="/blog" class="text-navy font-medium">Blog</a>
                    <a href="/contact" class="bg-electric-blue text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">Get Started</a>
                </div>
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-gray-700 hover:text-electric-blue">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div id="mobile-menu" class="md:hidden hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 bg-white shadow-lg">
                <a href="/" class="block px-3 py-2 text-gray-700 hover:text-electric-blue">Home</a>
                <a href="/process" class="block px-3 py-2 text-gray-700 hover:text-electric-blue">Process</a>
                <a href="/services" class="block px-3 py-2 text-gray-700 hover:text-electric-blue">Services</a>
                <a href="/pricing" class="block px-3 py-2 text-gray-700 hover:text-electric-blue">Pricing</a>
                <a href="/blog" class="block px-3 py-2 text-navy font-medium">Blog</a>
                <a href="/contact" class="block px-3 py-2 bg-electric-blue text-white rounded-md font-semibold">Get Started</a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="bg-gradient-to-br from-navy to-electric-blue text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">Startup Insights & Development Tips</h1>
            <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto text-blue-100">
                Expert advice on building successful web applications, MVPs, and scaling your startup
            </p>
        </div>
    </section>

    <!-- Blog Posts — server-side rendered for SEO -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if (empty($publishedPosts)): ?>
                <div class="text-center py-12">
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">No Blog Posts Yet</h3>
                    <p class="text-gray-600">Check back soon for startup insights and development tips!</p>
                </div>
            <?php else: ?>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($publishedPosts as $post): ?>
                    <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-all duration-300">
                        <?php if (!empty($post['featured_image'])): ?>
                        <div class="aspect-video overflow-hidden">
                            <img src="/<?php echo safeHtml($post['featured_image']); ?>"
                                 alt="<?php echo safeHtml($post['title']); ?>"
                                 class="w-full h-full object-cover"
                                 loading="lazy"
                                 onerror="this.parentElement.innerHTML='<div class=\'aspect-video bg-gradient-to-br from-blue-50 to-indigo-50 flex items-center justify-center\'><i class=\'fas fa-blog text-4xl text-electric-blue\'></i></div>'">
                        </div>
                        <?php else: ?>
                        <div class="aspect-video bg-gradient-to-br from-blue-50 to-indigo-50 flex items-center justify-center">
                            <i class="fas fa-blog text-4xl text-electric-blue"></i>
                        </div>
                        <?php endif; ?>

                        <div class="p-6">
                            <div class="flex items-center text-sm text-gray-500 mb-3">
                                <span class="bg-electric-blue text-white px-2 py-1 rounded text-xs font-medium mr-3">
                                    <?php echo safeHtml($post['category']); ?>
                                </span>
                                <time datetime="<?php echo safeHtml($post['published_at']); ?>">
                                    <?php echo fmtDate($post['published_at']); ?>
                                </time>
                            </div>
                            <h2 class="text-xl font-bold text-navy mb-3 hover:text-electric-blue transition">
                                <a href="/blog/<?php echo safeHtml($post['slug']); ?>">
                                    <?php echo safeHtml($post['title']); ?>
                                </a>
                            </h2>
                            <p class="text-gray-600 mb-4 line-clamp-3"><?php echo safeHtml($post['excerpt']); ?></p>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">By <?php echo safeHtml($post['author']); ?></span>
                                <a href="/blog/<?php echo safeHtml($post['slug']); ?>"
                                   class="text-electric-blue hover:text-blue-700 font-medium text-sm">
                                    Read More →
                                </a>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="bg-navy text-white py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold mb-4">Stay Updated</h2>
            <p class="text-xl text-blue-100 mb-8">Get the latest startup insights delivered to your inbox</p>
            <form class="flex flex-col sm:flex-row gap-4 max-w-md mx-auto">
                <input type="email" placeholder="Enter your email"
                       class="flex-1 px-4 py-3 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-electric-blue">
                <button type="submit" class="bg-electric-blue text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    Subscribe
                </button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div class="col-span-2">
                    <h3 class="text-2xl font-bold mb-4">App Craft Services</h3>
                    <p class="text-gray-300 mb-4">Professional web development services for growing businesses.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="/" class="text-gray-300 hover:text-white">Home</a></li>
                        <li><a href="/process" class="text-gray-300 hover:text-white">Process</a></li>
                        <li><a href="/services" class="text-gray-300 hover:text-white">Services</a></li>
                        <li><a href="/pricing" class="text-gray-300 hover:text-white">Pricing</a></li>
                        <li><a href="/contact" class="text-gray-300 hover:text-white">Contact</a></li>
                        <li><a href="/blog" class="text-gray-300 hover:text-white">Blog</a></li>
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

    <script src="../assets/cache-buster.js"></script>
    <script src="../assets/script.js"></script>
    <script>
        // Mobile menu
        document.getElementById('mobile-menu-button')?.addEventListener('click', () => {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
        document.getElementById('current-year') && (document.getElementById('current-year').textContent = new Date().getFullYear());
    </script>
</body>
</html>
