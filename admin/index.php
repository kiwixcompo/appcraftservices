<?php
session_start();

// Security headers to prevent "Dangerous Site" warnings
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com; img-src \'self\' data: https:; font-src \'self\' https://cdnjs.cloudflare.com; connect-src \'self\';');

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Load website content from JSON file
$contentFile = '../data/website_content.json';
$content = file_exists($contentFile) ? json_decode(file_get_contents($contentFile), true) : [];

// Default content structure
$defaultContent = [
    'site_info' => [
        'title' => 'App Craft Services',
        'tagline' => 'Custom Web Applications for Growing Businesses',
        'description' => 'Professional web development services for small businesses and startups.',
        'email' => 'williamsaonen@gmail.com',
        'phone' => '+2348061581916',
        'address' => ''
    ],
    'hero' => [
        'headline' => 'Custom Web Applications for Growing Businesses',
        'subheadline' => 'We turn your business logic into powerful, scalable digital tools',
        'cta_text' => 'Schedule a Consultation'
    ],
    'value_props' => [
        [
            'title' => 'Efficiency First',
            'description' => 'Streamline your operations with custom tools designed specifically for your workflow'
        ],
        [
            'title' => 'Smart Automation',
            'description' => 'Reduce manual work and human error with intelligent automation solutions'
        ],
        [
            'title' => 'Scalable Growth',
            'description' => 'Build solutions that grow with your business, from startup to enterprise'
        ]
    ],
    'services' => [
        [
            'name' => 'Custom Web Apps',
            'price' => '$1,000 - $2,000',
            'description' => 'Dashboards, Internal Tools, Portals'
        ],
        [
            'name' => 'MVP Development',
            'price' => 'Custom Quote',
            'description' => 'For Startups needing to go to market fast'
        ],
        [
            'name' => 'Maintenance & Support',
            'price' => 'Monthly Plans',
            'description' => 'Ongoing security and updates'
        ]
    ],
    'colors' => [
        'primary' => '#1e3a8a',
        'accent' => '#3b82f6',
        'background' => '#f8fafc'
    ]
];

// Merge with default content
$content = array_merge($defaultContent, $content);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - App Craft Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .sidebar-item.active { background-color: #3b82f6; color: white; }
        
        /* Ensure sidebar scrolling works properly */
        .sidebar-nav {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }
        
        /* Custom scrollbar for sidebar */
        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }
        
        .sidebar-nav::-webkit-scrollbar-track {
            background: #374151;
        }
        
        .sidebar-nav::-webkit-scrollbar-thumb {
            background: #6b7280;
            border-radius: 2px;
        }
        
        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 text-white flex flex-col h-full">
            <div class="p-4 flex-shrink-0">
                <h1 class="text-xl font-bold">Admin Dashboard</h1>
                <p class="text-sm text-gray-300">App Craft Services</p>
            </div>
            
            <nav class="flex-1 overflow-y-auto py-4 sidebar-nav">
                <a href="#dashboard" class="sidebar-item active flex items-center px-4 py-3 hover:bg-gray-700" onclick="showTab('dashboard')">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Dashboard
                </a>
                <a href="#content" class="sidebar-item flex items-center px-4 py-3 hover:bg-gray-700" onclick="showTab('content')">
                    <i class="fas fa-edit mr-3"></i>
                    Content Management
                </a>
                <a href="#realtime-editing" class="sidebar-item flex items-center px-4 py-3 hover:bg-gray-700" onclick="openRealtimeEditor()">
                    <i class="fas fa-magic mr-3"></i>
                    Realtime Editing
                </a>
                <a href="#pages" class="sidebar-item flex items-center px-4 py-3 hover:bg-gray-700" onclick="showTab('pages')">
                    <i class="fas fa-file-alt mr-3"></i>
                    Page Editor
                </a>
                <a href="#design" class="sidebar-item flex items-center px-4 py-3 hover:bg-gray-700" onclick="showTab('design')">
                    <i class="fas fa-palette mr-3"></i>
                    Design & Styling
                </a>
                <a href="#reviews" class="sidebar-item flex items-center px-4 py-3 hover:bg-gray-700" onclick="showTab('reviews')">
                    <i class="fas fa-star mr-3"></i>
                    Reviews
                </a>
                <a href="#projects" class="sidebar-item flex items-center px-4 py-3 hover:bg-gray-700" onclick="showTab('projects')">
                    <i class="fas fa-project-diagram mr-3"></i>
                    Projects
                </a>
                <a href="#blog" class="sidebar-item flex items-center px-4 py-3 hover:bg-gray-700" onclick="showTab('blog')">
                    <i class="fas fa-blog mr-3"></i>
                    Blog Posts
                </a>
                <a href="#messages" class="sidebar-item flex items-center px-4 py-3 hover:bg-gray-700" onclick="showTab('messages')">
                    <i class="fas fa-envelope mr-3"></i>
                    Messages
                </a>
                <a href="#invoices" class="sidebar-item flex items-center px-4 py-3 hover:bg-gray-700" onclick="showTab('invoices')">
                    <i class="fas fa-file-invoice mr-3"></i>
                    Invoices
                </a>
                <a href="#payments" class="sidebar-item flex items-center px-4 py-3 hover:bg-gray-700" onclick="showTab('payments')">
                    <i class="fas fa-credit-card mr-3"></i>
                    Payments
                </a>
                <a href="#analytics" class="sidebar-item flex items-center px-4 py-3 hover:bg-gray-700" onclick="showTab('analytics')">
                    <i class="fas fa-chart-bar mr-3"></i>
                    Analytics
                </a>
                <a href="#settings" class="sidebar-item flex items-center px-4 py-3 hover:bg-gray-700" onclick="showTab('settings')">
                    <i class="fas fa-cog mr-3"></i>
                    Settings
                </a>
            </nav>
            
            <div class="flex-shrink-0 border-t border-gray-700">
                <a href="logout.php" class="flex items-center px-4 py-3 hover:bg-gray-700 text-gray-300">
                    <i class="fas fa-sign-out-alt mr-3"></i>
                    Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 p-4">
                <div class="flex justify-between items-center">
                    <h2 id="page-title" class="text-2xl font-semibold text-gray-800">Dashboard</h2>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">Welcome, Admin</span>
                        <a href="../" target="_blank" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            <i class="fas fa-external-link-alt mr-2"></i>View Website
                        </a>
                        <button onclick="previewSite()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            <i class="fas fa-eye mr-2"></i>Preview Site
                        </button>
                    </div>
                </div>
            </header>

            <!-- Dashboard Tab -->
            <div id="dashboard" class="tab-content active p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i class="fas fa-envelope text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">New Messages</p>
                                <p class="text-2xl font-semibold text-gray-900" id="message-count">0</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="fas fa-dollar-sign text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Revenue</p>
                                <p class="text-2xl font-semibold text-gray-900">$0</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Visitors</p>
                                <p class="text-2xl font-semibold text-gray-900">0</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <i class="fas fa-project-diagram text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Projects</p>
                                <p class="text-2xl font-semibold text-gray-900">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Recent Messages</h3>
                        <div id="recent-messages">
                            <p class="text-gray-500">No messages yet</p>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <button onclick="showTab('content')" class="w-full text-left p-3 bg-blue-50 hover:bg-blue-100 rounded">
                                <i class="fas fa-edit mr-2"></i>Edit Homepage Content
                            </button>
                            <button onclick="showTab('messages')" class="w-full text-left p-3 bg-green-50 hover:bg-green-100 rounded">
                                <i class="fas fa-envelope mr-2"></i>Check Messages
                            </button>
                            <button onclick="showTab('payments')" class="w-full text-left p-3 bg-yellow-50 hover:bg-yellow-100 rounded">
                                <i class="fas fa-credit-card mr-2"></i>Payment Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Management Tab -->
            <div id="content" class="tab-content p-6">
                <form id="content-form" class="space-y-8">
                    <!-- Site Information -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Site Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Site Title</label>
                                <input type="text" name="site_title" value="<?php echo htmlspecialchars($content['site_info']['title']); ?>" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tagline</label>
                                <input type="text" name="site_tagline" value="<?php echo htmlspecialchars($content['site_info']['tagline']); ?>" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                                <input type="email" name="site_email" value="<?php echo htmlspecialchars($content['site_info']['email']); ?>" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="text" name="site_phone" value="<?php echo htmlspecialchars($content['site_info']['phone']); ?>" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Site Description</label>
                            <textarea name="site_description" rows="3" class="w-full p-3 border border-gray-300 rounded-md"><?php echo htmlspecialchars($content['site_info']['description']); ?></textarea>
                        </div>
                    </div>

                    <!-- Hero Section -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Hero Section</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Main Headline</label>
                                <input type="text" name="hero_headline" value="<?php echo htmlspecialchars($content['hero']['headline']); ?>" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Subheadline</label>
                                <textarea name="hero_subheadline" rows="2" class="w-full p-3 border border-gray-300 rounded-md"><?php echo htmlspecialchars($content['hero']['subheadline']); ?></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Call-to-Action Text</label>
                                <input type="text" name="hero_cta" value="<?php echo htmlspecialchars($content['hero']['cta_text']); ?>" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                        </div>
                    </div>

                    <!-- Value Propositions -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Value Propositions</h3>
                        <div id="value-props">
                            <?php foreach ($content['value_props'] as $index => $prop): ?>
                            <div class="value-prop-item border border-gray-200 p-4 rounded mb-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                                        <input type="text" name="value_prop_title[]" value="<?php echo htmlspecialchars($prop['title']); ?>" class="w-full p-3 border border-gray-300 rounded-md">
                                    </div>
                                    <div>
                                        <button type="button" onclick="removeValueProp(this)" class="mt-6 bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                    <textarea name="value_prop_description[]" rows="2" class="w-full p-3 border border-gray-300 rounded-md"><?php echo htmlspecialchars($prop['description']); ?></textarea>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" onclick="addValueProp()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            <i class="fas fa-plus"></i> Add Value Proposition
                        </button>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="previewChanges()" class="bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700">
                            <i class="fas fa-eye mr-2"></i>Preview Changes
                        </button>
                        <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700">
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Page Editor Tab -->
            <div id="pages" class="tab-content p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Page Management</h3>
                        <div class="space-y-3">
                            <button onclick="editPage('home')" class="w-full text-left p-3 bg-blue-50 hover:bg-blue-100 rounded flex items-center">
                                <i class="fas fa-home mr-3 text-blue-600"></i>
                                <div>
                                    <div class="font-medium">Homepage</div>
                                    <div class="text-sm text-gray-500">Main landing page</div>
                                </div>
                            </button>
                            <button onclick="editPage('process')" class="w-full text-left p-3 bg-green-50 hover:bg-green-100 rounded flex items-center">
                                <i class="fas fa-cogs mr-3 text-green-600"></i>
                                <div>
                                    <div class="font-medium">Process Page</div>
                                    <div class="text-sm text-gray-500">Our development process</div>
                                </div>
                            </button>
                            <button onclick="editPage('services')" class="w-full text-left p-3 bg-purple-50 hover:bg-purple-100 rounded flex items-center">
                                <i class="fas fa-briefcase mr-3 text-purple-600"></i>
                                <div>
                                    <div class="font-medium">Services Page</div>
                                    <div class="text-sm text-gray-500">Service offerings</div>
                                </div>
                            </button>
                            <button onclick="editPage('pricing')" class="w-full text-left p-3 bg-yellow-50 hover:bg-yellow-100 rounded flex items-center">
                                <i class="fas fa-dollar-sign mr-3 text-yellow-600"></i>
                                <div>
                                    <div class="font-medium">Pricing Page</div>
                                    <div class="text-sm text-gray-500">Pricing tiers</div>
                                </div>
                            </button>
                            <button onclick="editPage('contact')" class="w-full text-left p-3 bg-red-50 hover:bg-red-100 rounded flex items-center">
                                <i class="fas fa-envelope mr-3 text-red-600"></i>
                                <div>
                                    <div class="font-medium">Contact Page</div>
                                    <div class="text-sm text-gray-500">Contact form & info</div>
                                </div>
                            </button>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">SEO Settings</h3>
                        <form id="seo-form" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Site Title</label>
                                <input type="text" id="seo-title" class="w-full p-3 border border-gray-300 rounded-md" value="App Craft Services - Custom Web Applications">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                                <textarea id="seo-description" rows="3" class="w-full p-3 border border-gray-300 rounded-md">Professional web development services for small businesses and startups. Custom web applications, MVP development, and ongoing support.</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Keywords</label>
                                <input type="text" id="seo-keywords" class="w-full p-3 border border-gray-300 rounded-md" value="web development, custom applications, MVP development, business automation">
                            </div>
                            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                                Update SEO Settings
                            </button>
                        </form>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Page Analytics</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                <span class="font-medium">Homepage</span>
                                <span class="text-blue-600 font-semibold">1,234 views</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                <span class="font-medium">Services</span>
                                <span class="text-blue-600 font-semibold">856 views</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                <span class="font-medium">Pricing</span>
                                <span class="text-blue-600 font-semibold">642 views</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                <span class="font-medium">Contact</span>
                                <span class="text-blue-600 font-semibold">423 views</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="page-editor" class="bg-white p-6 rounded-lg shadow" style="display: none;">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Editing: <span id="current-page-name">Page</span></h3>
                        <button onclick="closePageEditor()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="page-editor-content">
                        <!-- Dynamic content will be loaded here -->
                    </div>
                </div>
            </div>

            <!-- Design & Styling Tab -->
            <div id="design" class="tab-content p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Color Scheme</h3>
                        <form id="color-form" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Primary Color (Navy)</label>
                                <div class="flex items-center space-x-3">
                                    <input type="color" id="primary-color" value="#1e3a8a" class="w-16 h-10 border border-gray-300 rounded">
                                    <input type="text" id="primary-color-hex" value="#1e3a8a" class="flex-1 p-2 border border-gray-300 rounded text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Accent Color (Electric Blue)</label>
                                <div class="flex items-center space-x-3">
                                    <input type="color" id="accent-color" value="#3b82f6" class="w-16 h-10 border border-gray-300 rounded">
                                    <input type="text" id="accent-color-hex" value="#3b82f6" class="flex-1 p-2 border border-gray-300 rounded text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Background Color</label>
                                <div class="flex items-center space-x-3">
                                    <input type="color" id="bg-color" value="#f8fafc" class="w-16 h-10 border border-gray-300 rounded">
                                    <input type="text" id="bg-color-hex" value="#f8fafc" class="flex-1 p-2 border border-gray-300 rounded text-sm">
                                </div>
                            </div>
                            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                                Apply Color Changes
                            </button>
                        </form>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Typography</h3>
                        <form id="typography-form" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Primary Font</label>
                                <select id="primary-font" class="w-full p-3 border border-gray-300 rounded-md">
                                    <option value="Inter">Inter (Default)</option>
                                    <option value="Roboto">Roboto</option>
                                    <option value="Open Sans">Open Sans</option>
                                    <option value="Lato">Lato</option>
                                    <option value="Montserrat">Montserrat</option>
                                    <option value="Poppins">Poppins</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Heading Font</label>
                                <select id="heading-font" class="w-full p-3 border border-gray-300 rounded-md">
                                    <option value="Inter">Inter (Default)</option>
                                    <option value="Roboto">Roboto</option>
                                    <option value="Playfair Display">Playfair Display</option>
                                    <option value="Merriweather">Merriweather</option>
                                    <option value="Source Serif Pro">Source Serif Pro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Base Font Size</label>
                                <select id="base-font-size" class="w-full p-3 border border-gray-300 rounded-md">
                                    <option value="14px">14px (Small)</option>
                                    <option value="16px" selected>16px (Default)</option>
                                    <option value="18px">18px (Large)</option>
                                    <option value="20px">20px (Extra Large)</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">
                                Apply Typography
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Logo & Branding</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Current Logo</label>
                                <div class="border border-gray-300 rounded p-4 text-center">
                                    <img src="../assets/logo.png" alt="Current Logo" class="h-16 mx-auto">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Upload New Logo</label>
                                <input type="file" id="logo-upload" accept="image/*" class="w-full p-2 border border-gray-300 rounded">
                            </div>
                            <button onclick="uploadLogo()" class="w-full bg-purple-600 text-white py-2 px-4 rounded hover:bg-purple-700">
                                Update Logo
                            </button>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Layout Settings</h3>
                        <form id="layout-form" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Container Width</label>
                                <select id="container-width" class="w-full p-3 border border-gray-300 rounded-md">
                                    <option value="max-w-6xl">Standard (1152px)</option>
                                    <option value="max-w-7xl" selected>Wide (1280px)</option>
                                    <option value="max-w-full">Full Width</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Section Spacing</label>
                                <select id="section-spacing" class="w-full p-3 border border-gray-300 rounded-md">
                                    <option value="py-12">Compact</option>
                                    <option value="py-16" selected>Standard</option>
                                    <option value="py-20">Spacious</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Border Radius</label>
                                <select id="border-radius" class="w-full p-3 border border-gray-300 rounded-md">
                                    <option value="rounded-none">None</option>
                                    <option value="rounded-md">Small</option>
                                    <option value="rounded-lg" selected>Medium</option>
                                    <option value="rounded-xl">Large</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700">
                                Apply Layout
                            </button>
                        </form>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Theme Presets</h3>
                        <div class="space-y-3">
                            <button onclick="applyTheme('default')" class="w-full p-3 border border-gray-300 rounded hover:bg-gray-50 text-left">
                                <div class="font-medium">Default Theme</div>
                                <div class="text-sm text-gray-500">Navy & Electric Blue</div>
                            </button>
                            <button onclick="applyTheme('dark')" class="w-full p-3 border border-gray-300 rounded hover:bg-gray-50 text-left">
                                <div class="font-medium">Dark Theme</div>
                                <div class="text-sm text-gray-500">Dark & Orange</div>
                            </button>
                            <button onclick="applyTheme('green')" class="w-full p-3 border border-gray-300 rounded hover:bg-gray-50 text-left">
                                <div class="font-medium">Nature Theme</div>
                                <div class="text-sm text-gray-500">Green & Teal</div>
                            </button>
                            <button onclick="applyTheme('purple')" class="w-full p-3 border border-gray-300 rounded hover:bg-gray-50 text-left">
                                <div class="font-medium">Creative Theme</div>
                                <div class="text-sm text-gray-500">Purple & Pink</div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reviews Tab -->
            <div id="reviews" class="tab-content p-6">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-blue-600" id="total-reviews">0</div>
                        <div class="text-sm text-gray-600">Total Reviews</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-yellow-600" id="pending-reviews">0</div>
                        <div class="text-sm text-gray-600">Pending Reviews</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-green-600" id="approved-reviews">0</div>
                        <div class="text-sm text-gray-600">Approved Reviews</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-red-600" id="rejected-reviews">0</div>
                        <div class="text-sm text-gray-600">Rejected Reviews</div>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Review Management</h3>
                        <div class="flex space-x-2">
                            <button onclick="filterReviews('all')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" id="filter-all">All</button>
                            <button onclick="filterReviews('pending')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300" id="filter-pending">Pending</button>
                            <button onclick="filterReviews('approved')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300" id="filter-approved">Approved</button>
                            <button onclick="filterReviews('rejected')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300" id="filter-rejected">Rejected</button>
                            <button onclick="exportReviews()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                <i class="fas fa-download mr-2"></i>Export
                            </button>
                        </div>
                    </div>
                    <div id="reviews-list">
                        <p class="text-gray-600">Loading reviews...</p>
                    </div>
                </div>
            </div>

            <!-- Projects Tab -->
            <div id="projects" class="tab-content p-6">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-blue-600" id="total-projects">0</div>
                        <div class="text-sm text-gray-600">Total Projects</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-green-600" id="featured-projects">0</div>
                        <div class="text-sm text-gray-600">Featured Projects</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-purple-600" id="completed-projects">0</div>
                        <div class="text-sm text-gray-600">Completed Projects</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-orange-600" id="project-categories">0</div>
                        <div class="text-sm text-gray-600">Categories</div>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Project Management</h3>
                        <button onclick="showAddProjectModal()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            <i class="fas fa-plus mr-2"></i>Add New Project
                        </button>
                    </div>
                    <div id="projects-list">
                        <p class="text-gray-600">Loading projects...</p>
                    </div>
                </div>
            </div>

            <!-- Blog Tab -->
            <div id="blog" class="tab-content p-6">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-blue-600" id="total-posts">0</div>
                        <div class="text-sm text-gray-600">Total Posts</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-green-600" id="published-posts">0</div>
                        <div class="text-sm text-gray-600">Published Posts</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-yellow-600" id="draft-posts">0</div>
                        <div class="text-sm text-gray-600">Draft Posts</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-purple-600" id="blog-categories">0</div>
                        <div class="text-sm text-gray-600">Categories</div>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Blog Post Management</h3>
                        <button onclick="showAddBlogModal()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            <i class="fas fa-plus mr-2"></i>Add New Post
                        </button>
                    </div>
                    <div id="blog-posts-list">
                        <p class="text-gray-600">Loading blog posts...</p>
                    </div>
                </div>
            </div>

            <!-- Messages Tab -->
            <div id="messages" class="tab-content p-6">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-blue-600" id="total-messages">0</div>
                        <div class="text-sm text-gray-600">Total Messages</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-green-600" id="unread-messages">0</div>
                        <div class="text-sm text-gray-600">Unread Messages</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-yellow-600" id="today-messages">0</div>
                        <div class="text-sm text-gray-600">Today's Messages</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-purple-600" id="schedule-requests">0</div>
                        <div class="text-sm text-gray-600">Schedule Requests</div>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Contact Messages</h3>
                        <div class="flex flex-wrap gap-2">
                            <button onclick="filterMessages('all')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">All</button>
                            <button onclick="filterMessages('unread')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Unread</button>
                            <button onclick="filterMessages('today')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Today</button>
                            <button onclick="filterMessages('schedule')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">📅 Consultations</button>
                            <button onclick="exportMessages()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                <i class="fas fa-download mr-2"></i>Export
                            </button>
                        </div>
                    </div>
                    <div id="messages-list">
                        <p class="text-gray-600">Loading messages...</p>
                    </div>
                </div>
            </div>

            <!-- Invoices Tab -->
            <div id="invoices" class="tab-content p-6">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-blue-600" id="total-invoices">0</div>
                        <div class="text-sm text-gray-600">Total Invoices</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-green-600" id="paid-invoices">0</div>
                        <div class="text-sm text-gray-600">Paid Invoices</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-yellow-600" id="pending-invoices">0</div>
                        <div class="text-sm text-gray-600">Pending Invoices</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-red-600" id="overdue-invoices">0</div>
                        <div class="text-sm text-gray-600">Overdue Invoices</div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Create New Invoice -->
                    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Create New Invoice</h3>
                        <form id="invoice-form" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Number</label>
                                    <div class="w-full p-3 border border-gray-300 rounded-md bg-gray-50 text-gray-700 font-medium" id="invoice-number-display">
                                        INV-202412-0001
                                    </div>
                                    <input type="hidden" id="invoice-number" name="invoice-number" value="INV-202412-0001">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Date</label>
                                    <input type="date" id="invoice-date" class="w-full p-3 border border-gray-300 rounded-md" required>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                                    <input type="date" id="due-date" class="w-full p-3 border border-gray-300 rounded-md" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <select id="invoice-status" class="w-full p-3 border border-gray-300 rounded-md">
                                        <option value="draft">Draft</option>
                                        <option value="sent">Sent</option>
                                        <option value="paid">Paid</option>
                                        <option value="overdue">Overdue</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="border-t pt-4">
                                <h4 class="font-medium mb-3">Client Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Client Name</label>
                                        <input type="text" id="client-name" class="w-full p-3 border border-gray-300 rounded-md" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Client Email</label>
                                        <input type="email" id="client-email" class="w-full p-3 border border-gray-300 rounded-md" required>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Client Address</label>
                                    <textarea id="client-address" rows="3" class="w-full p-3 border border-gray-300 rounded-md"></textarea>
                                </div>
                            </div>
                            
                            <div class="border-t pt-4">
                                <h4 class="font-medium mb-3">Project Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Project Name</label>
                                        <input type="text" id="project-name" class="w-full p-3 border border-gray-300 rounded-md" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Project Type</label>
                                        <select id="project-type" class="w-full p-3 border border-gray-300 rounded-md">
                                            <option value="Essential App">Essential App</option>
                                            <option value="Custom Enterprise">Custom Enterprise</option>
                                            <option value="MVP Development">MVP Development</option>
                                            <option value="Maintenance & Support">Maintenance & Support</option>
                                            <option value="Custom">Custom</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Project Description</label>
                                    <textarea id="project-description" rows="3" class="w-full p-3 border border-gray-300 rounded-md"></textarea>
                                </div>
                            </div>
                            
                            <div class="border-t pt-4">
                                <h4 class="font-medium mb-3">Payment Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Total Amount Agreed ($)</label>
                                        <input type="number" id="total-amount" step="0.01" min="0" class="w-full p-3 border border-gray-300 rounded-md" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount Paid ($)</label>
                                        <input type="number" id="amount-paid" step="0.01" min="0" class="w-full p-3 border border-gray-300 rounded-md" value="0">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount Due ($)</label>
                                        <input type="number" id="amount-due" step="0.01" min="0" class="w-full p-3 border border-gray-300 rounded-md" readonly>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Tax Rate (%)</label>
                                        <input type="number" id="tax-rate-1" step="0.01" min="0" max="100" class="w-full p-3 border border-gray-300 rounded-md" value="0">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                                        <select id="currency" class="w-full p-3 border border-gray-300 rounded-md">
                                            <option value="USD">USD - US Dollar</option>
                                            <option value="EUR">EUR - Euro</option>
                                            <option value="GBP">GBP - British Pound</option>
                                            <option value="NGN">NGN - Nigerian Naira</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="border-t pt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                                <textarea id="invoice-notes" rows="3" class="w-full p-3 border border-gray-300 rounded-md" placeholder="Payment terms, additional information, etc."></textarea>
                            </div>
                            
                            <div class="flex space-x-4">
                                <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700">
                                    <i class="fas fa-save mr-2"></i>Save Invoice
                                </button>
                                <button type="button" onclick="previewInvoice()" class="bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700">
                                    <i class="fas fa-eye mr-2"></i>Preview
                                </button>
                                <button type="button" onclick="generateInvoicePDF()" class="bg-red-600 text-white px-6 py-3 rounded hover:bg-red-700">
                                    <i class="fas fa-file-pdf mr-2"></i>Export PDF
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Invoice Preview -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Invoice Preview</h3>
                        <div id="invoice-preview" class="text-sm">
                            <div class="text-center text-gray-500 py-8">
                                <i class="fas fa-file-invoice text-4xl mb-4"></i>
                                <p>Fill out the form to see preview</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Invoice List -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Invoice History</h3>
                        <div class="flex space-x-2">
                            <select id="invoice-filter" class="px-3 py-2 border border-gray-300 rounded">
                                <option value="all">All Invoices</option>
                                <option value="draft">Draft</option>
                                <option value="sent">Sent</option>
                                <option value="paid">Paid</option>
                                <option value="overdue">Overdue</option>
                            </select>
                            <button onclick="refreshInvoices()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                <i class="fas fa-refresh"></i>
                            </button>
                        </div>
                    </div>
                    <div id="invoices-list">
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-file-invoice text-4xl mb-4"></i>
                            <p>No invoices created yet</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payments Tab -->
            <div id="payments" class="tab-content p-6">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-green-600" id="total-revenue">$0</div>
                        <div class="text-sm text-gray-600">Total Revenue</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-blue-600" id="monthly-revenue">$0</div>
                        <div class="text-sm text-gray-600">This Month</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-purple-600" id="total-transactions">0</div>
                        <div class="text-sm text-gray-600">Transactions</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-yellow-600" id="pending-payments">0</div>
                        <div class="text-sm text-gray-600">Pending</div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Stripe Configuration -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Stripe Configuration</h3>
                        <form id="stripe-config-form" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Environment</label>
                                <select id="stripe-environment" class="w-full p-3 border border-gray-300 rounded-md">
                                    <option value="test">Test Mode</option>
                                    <option value="live">Live Mode</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Publishable Key</label>
                                <input type="text" id="stripe-publishable-key" placeholder="pk_test_..." class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Secret Key</label>
                                <input type="password" id="stripe-secret-key" placeholder="sk_test_..." class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Webhook Endpoint</label>
                                <input type="url" id="stripe-webhook" placeholder="https://yoursite.com/webhook" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>Save Stripe Config
                            </button>
                        </form>
                    </div>
                    
                    <!-- PayPal Configuration -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">PayPal Configuration</h3>
                        <form id="paypal-config-form" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Environment</label>
                                <select id="paypal-environment" class="w-full p-3 border border-gray-300 rounded-md">
                                    <option value="sandbox">Sandbox</option>
                                    <option value="live">Live</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Client ID</label>
                                <input type="text" id="paypal-client-id" placeholder="Your PayPal Client ID" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Client Secret</label>
                                <input type="password" id="paypal-client-secret" placeholder="Your PayPal Client Secret" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Webhook ID</label>
                                <input type="text" id="paypal-webhook-id" placeholder="Webhook ID" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <button type="submit" class="w-full bg-yellow-600 text-white py-2 px-4 rounded hover:bg-yellow-700">
                                <i class="fas fa-save mr-2"></i>Save PayPal Config
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Service Packages</h3>
                        <div id="service-packages" class="space-y-3">
                            <div class="border border-gray-200 rounded p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-medium">Essential App</h4>
                                    <button onclick="editPackage('essential')" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                                <p class="text-2xl font-bold text-green-600">$1,500</p>
                                <p class="text-sm text-gray-600">Basic web application</p>
                            </div>
                            <div class="border border-gray-200 rounded p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-medium">Custom Enterprise</h4>
                                    <button onclick="editPackage('enterprise')" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                                <p class="text-2xl font-bold text-green-600">Custom Quote</p>
                                <p class="text-sm text-gray-600">Complex platforms</p>
                            </div>
                        </div>
                        <button onclick="addPackage()" class="w-full mt-4 bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">
                            <i class="fas fa-plus mr-2"></i>Add Package
                        </button>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Payment Settings</h3>
                        <form id="payment-settings-form" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                                <select id="payment-currency" class="w-full p-3 border border-gray-300 rounded-md">
                                    <option value="USD">USD - US Dollar</option>
                                    <option value="EUR">EUR - Euro</option>
                                    <option value="GBP">GBP - British Pound</option>
                                    <option value="NGN">NGN - Nigerian Naira</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tax Rate (%)</label>
                                <input type="number" id="tax-rate-2" step="0.01" min="0" max="100" class="w-full p-3 border border-gray-300 rounded-md" value="0">
                            </div>
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" id="require-billing-address" class="mr-2">
                                    <span class="text-sm">Require billing address</span>
                                </label>
                            </div>
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" id="send-receipts" class="mr-2" checked>
                                    <span class="text-sm">Send email receipts</span>
                                </label>
                            </div>
                            <button type="submit" class="w-full bg-purple-600 text-white py-2 px-4 rounded hover:bg-purple-700">
                                Update Settings
                            </button>
                        </form>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <button onclick="generateInvoice()" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                                <i class="fas fa-file-invoice mr-2"></i>Generate Invoice
                            </button>
                            <button onclick="sendPaymentLink()" class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">
                                <i class="fas fa-link mr-2"></i>Send Payment Link
                            </button>
                            <button onclick="refundPayment()" class="w-full bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700">
                                <i class="fas fa-undo mr-2"></i>Process Refund
                            </button>
                            <button onclick="exportPayments()" class="w-full bg-gray-600 text-white py-2 px-4 rounded hover:bg-gray-700">
                                <i class="fas fa-download mr-2"></i>Export Data
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Recent Transactions</h3>
                        <div class="flex space-x-2">
                            <select id="transaction-filter" class="px-3 py-2 border border-gray-300 rounded">
                                <option value="all">All Transactions</option>
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                                <option value="failed">Failed</option>
                            </select>
                            <button onclick="refreshTransactions()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                <i class="fas fa-refresh"></i>
                            </button>
                        </div>
                    </div>
                    <div id="transactions-list">
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-credit-card text-4xl mb-4"></i>
                            <p>No transactions yet</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Tab -->
            <div id="analytics" class="tab-content p-6">
                <!-- Analytics Filters -->
                <div class="bg-white p-4 rounded-lg shadow mb-6">
                    <div class="flex flex-wrap gap-4 items-center">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Time Period</label>
                            <select id="analytics-period" class="px-3 py-2 border border-gray-300 rounded-md">
                                <option value="7">Last 7 days</option>
                                <option value="30" selected>Last 30 days</option>
                                <option value="90">Last 90 days</option>
                                <option value="365">Last year</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Page Filter</label>
                            <select id="analytics-page" class="px-3 py-2 border border-gray-300 rounded-md">
                                <option value="">All Pages</option>
                                <option value="/">Homepage</option>
                                <option value="/services">Services</option>
                                <option value="/pricing">Pricing</option>
                                <option value="/contact">Contact</option>
                                <option value="/process">Process</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Traffic Source</label>
                            <select id="analytics-source" class="px-3 py-2 border border-gray-300 rounded-md">
                                <option value="">All Sources</option>
                                <option value="direct">Direct</option>
                                <option value="google">Google</option>
                                <option value="facebook">Facebook</option>
                                <option value="twitter">Twitter</option>
                                <option value="linkedin">LinkedIn</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button onclick="refreshAnalytics()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                <i class="fas fa-refresh mr-2"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Analytics Overview Cards -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-blue-600" id="total-visitors">Loading...</div>
                        <div class="text-sm text-gray-600">Total Visitors</div>
                        <div class="text-xs mt-1" id="visitors-change">Loading...</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-green-600" id="page-views">Loading...</div>
                        <div class="text-sm text-gray-600">Page Views</div>
                        <div class="text-xs mt-1" id="views-change">Loading...</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-purple-600" id="bounce-rate">Loading...</div>
                        <div class="text-sm text-gray-600">Bounce Rate</div>
                        <div class="text-xs mt-1" id="bounce-change">Loading...</div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow text-center">
                        <div class="text-3xl font-bold text-yellow-600" id="avg-load-time">Loading...</div>
                        <div class="text-sm text-gray-600">Avg. Load Time</div>
                        <div class="text-xs mt-1" id="load-time-change">Loading...</div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Traffic Chart -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Traffic Overview</h3>
                        <div class="h-64" id="traffic-chart">
                            <canvas id="traffic-canvas" width="400" height="200"></canvas>
                        </div>
                    </div>
                    
                    <!-- Top Pages -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Top Pages</h3>
                        <div id="top-pages-list">
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <p>Loading page data...</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Traffic Sources -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Traffic Sources</h3>
                        <div id="traffic-sources-list">
                            <div class="text-center py-4 text-gray-500">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Device Types -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Device Types</h3>
                        <div id="device-types-list">
                            <div class="text-center py-4 text-gray-500">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Browsers -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Top Browsers</h3>
                        <div id="browsers-list">
                            <div class="text-center py-4 text-gray-500">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Visitors -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">Recent Visitors</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                </tr>
                            </thead>
                            <tbody id="recent-visitors-list" class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>Loading recent visitors...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Google Analytics Integration -->
                <div class="bg-white p-6 rounded-lg shadow mt-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Google Analytics Integration</h3>
                            <p class="text-sm text-gray-600 mt-1">Connect your Google Analytics account to view real-time traffic data</p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
                            </svg>
                        </div>
                    </div>

                    <div id="ga-status" class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            Google Analytics is not connected. Add your tracking ID to enable real-time analytics.
                        </p>
                    </div>

                    <form id="ga-settings-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Google Analytics Tracking ID</label>
                            <input type="text" id="ga-tracking-id" placeholder="G-XXXXXXXXXX or UA-XXXXXXXXX-X" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="">
                            <p class="text-xs text-gray-500 mt-1">Find your tracking ID in Google Analytics settings</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Measurement ID (GA4)</label>
                            <input type="text" id="ga-measurement-id" placeholder="G-XXXXXXXXXX" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="">
                            <p class="text-xs text-gray-500 mt-1">Optional: For Google Analytics 4 properties</p>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-medium text-blue-900 mb-2">How to find your Tracking ID:</h4>
                            <ol class="text-sm text-blue-800 space-y-1 list-decimal list-inside">
                                <li>Go to <a href="https://analytics.google.com" target="_blank" class="underline hover:text-blue-600">Google Analytics</a></li>
                                <li>Select your property</li>
                                <li>Go to Admin → Property Settings</li>
                                <li>Copy your Tracking ID (starts with G- or UA-)</li>
                            </ol>
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-save mr-2"></i>Save Analytics Settings
                            </button>
                            <button type="button" onclick="testGAConnection()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-200">
                                <i class="fas fa-check-circle mr-2"></i>Test Connection
                            </button>
                        </div>
                    </form>

                    <div id="ga-data-section" class="mt-8 pt-8 border-t border-gray-200" style="display: none;">
                        <h4 class="font-semibold text-gray-900 mb-4">Real-Time Analytics from Google</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-600 mb-1">Active Users (Real-time)</div>
                                <div class="text-3xl font-bold text-blue-600" id="ga-active-users">-</div>
                            </div>
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-600 mb-1">Sessions (Today)</div>
                                <div class="text-3xl font-bold text-green-600" id="ga-sessions">-</div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-4">
                            <i class="fas fa-sync-alt mr-1"></i>
                            Data updates automatically. Full analytics available in <a href="https://analytics.google.com" target="_blank" class="text-blue-600 hover:underline">Google Analytics</a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Settings Tab -->
            <div id="settings" class="tab-content p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">General Settings</h3>
                        <form id="general-settings-form" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Site Name</label>
                                <input type="text" id="site-name" value="App Craft Services" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Admin Email</label>
                                <input type="email" id="admin-email" value="williamsaonen@gmail.com" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                                <select id="timezone" class="w-full p-3 border border-gray-300 rounded-md">
                                    <option value="UTC">UTC</option>
                                    <option value="America/New_York">Eastern Time</option>
                                    <option value="America/Chicago">Central Time</option>
                                    <option value="America/Denver">Mountain Time</option>
                                    <option value="America/Los_Angeles">Pacific Time</option>
                                    <option value="Africa/Lagos" selected>West Africa Time</option>
                                    <option value="Europe/London">GMT</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                                <select id="language" class="w-full p-3 border border-gray-300 rounded-md">
                                    <option value="en" selected>English</option>
                                    <option value="es">Spanish</option>
                                    <option value="fr">French</option>
                                    <option value="de">German</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                                Save General Settings
                            </button>
                        </form>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Security Settings</h3>
                        <form id="security-settings-form" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                <input type="password" id="current-password" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                <input type="password" id="new-password" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                <input type="password" id="confirm-password" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" id="two-factor-auth" class="mr-2">
                                    <span class="text-sm">Enable Two-Factor Authentication</span>
                                </label>
                            </div>
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" id="login-notifications" class="mr-2" checked>
                                    <span class="text-sm">Email notifications for logins</span>
                                </label>
                            </div>
                            <button type="submit" class="w-full bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700">
                                Update Security Settings
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Email Settings</h3>
                        <form id="email-settings-form" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Host</label>
                                <input type="text" id="smtp-host" placeholder="smtp.gmail.com" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Port</label>
                                <input type="number" id="smtp-port" value="587" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Username</label>
                                <input type="text" id="smtp-username" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Password</label>
                                <input type="password" id="smtp-password" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" id="smtp-encryption" class="mr-2" checked>
                                    <span class="text-sm">Use TLS encryption</span>
                                </label>
                            </div>
                            <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">
                                Save Email Settings
                            </button>
                        </form>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Backup & Restore</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Automatic Backups</label>
                                <select id="backup-frequency" class="w-full p-3 border border-gray-300 rounded-md">
                                    <option value="daily">Daily</option>
                                    <option value="weekly" selected>Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="disabled">Disabled</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Backup Location</label>
                                <select id="backup-location" class="w-full p-3 border border-gray-300 rounded-md">
                                    <option value="local" selected>Local Server</option>
                                    <option value="cloud">Cloud Storage</option>
                                    <option value="email">Email</option>
                                </select>
                            </div>
                            <button onclick="createBackup()" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                                <i class="fas fa-download mr-2"></i>Create Backup Now
                            </button>
                            <button onclick="restoreBackup()" class="w-full bg-yellow-600 text-white py-2 px-4 rounded hover:bg-yellow-700">
                                <i class="fas fa-upload mr-2"></i>Restore from Backup
                            </button>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">System Information</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">PHP Version:</span>
                                <span class="text-sm font-medium"><?php echo PHP_VERSION; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Server:</span>
                                <span class="text-sm font-medium"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Memory Limit:</span>
                                <span class="text-sm font-medium"><?php echo ini_get('memory_limit'); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Upload Max:</span>
                                <span class="text-sm font-medium"><?php echo ini_get('upload_max_filesize'); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Disk Space:</span>
                                <span class="text-sm font-medium"><?php echo round(disk_free_space('.') / 1024 / 1024 / 1024, 2); ?> GB free</span>
                            </div>
                        </div>
                        <button onclick="runSystemCheck()" class="w-full mt-4 bg-purple-600 text-white py-2 px-4 rounded hover:bg-purple-700">
                            <i class="fas fa-check-circle mr-2"></i>Run System Check
                        </button>
                    </div>
                </div>
                
                <!-- Payment Configuration Sections -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Stripe Configuration -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Stripe Configuration</h3>
                        <form id="stripe-settings-form" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Environment</label>
                                <select id="stripe-environment" class="w-full p-3 border border-gray-300 rounded-md">
                                    <option value="sandbox">Sandbox</option>
                                    <option value="live">Live</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Publishable Key</label>
                                <input type="text" id="stripe-publishable-key" placeholder="pk_test_..." class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Secret Key</label>
                                <input type="password" id="stripe-secret-key" placeholder="sk_test_..." class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Webhook Endpoint</label>
                                <input type="url" id="stripe-webhook-endpoint" placeholder="https://yoursite.com/api/stripe/webhook" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                                Save Stripe Settings
                            </button>
                        </form>
                    </div>

                    <!-- PayPal Configuration -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">PayPal Configuration</h3>
                        <form id="paypal-settings-form" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Environment</label>
                                <select id="paypal-environment" class="w-full p-3 border border-gray-300 rounded-md">
                                    <option value="sandbox">Sandbox</option>
                                    <option value="live">Live</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Client ID</label>
                                <input type="text" id="paypal-client-id" placeholder="AYjcyDKflPBAhxHlw..." class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Client Secret</label>
                                <input type="password" id="paypal-client-secret" placeholder="EHxHlwAYjcyDKflPBA..." class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Webhook ID</label>
                                <input type="text" id="paypal-webhook-id" placeholder="8PT597110X687430LKGECATA" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <button type="submit" class="w-full bg-orange-600 text-white py-2 px-4 rounded hover:bg-orange-700">
                                Save PayPal Settings
                            </button>
                        </form>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Payment Settings -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Payment Settings</h3>
                        <form id="payment-general-settings-form" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                                <select id="payment-currency" class="w-full p-3 border border-gray-300 rounded-md">
                                    <option value="USD">USD - US Dollar</option>
                                    <option value="EUR">EUR - Euro</option>
                                    <option value="GBP">GBP - British Pound</option>
                                    <option value="CAD">CAD - Canadian Dollar</option>
                                    <option value="AUD">AUD - Australian Dollar</option>
                                    <option value="NGN">NGN - Nigerian Naira</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tax Rate (%)</label>
                                <input type="number" id="payment-tax-rate" value="0" min="0" max="100" step="0.01" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" id="require-billing-address" class="mr-2">
                                    <span class="text-sm">Require billing address</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" id="send-email-receipts" class="mr-2" checked>
                                    <span class="text-sm">Send email receipts</span>
                                </label>
                            </div>
                            <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">
                                Update Payment Settings
                            </button>
                        </form>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <button onclick="generateInvoice()" class="w-full bg-blue-600 text-white py-3 px-4 rounded hover:bg-blue-700 flex items-center justify-center">
                                <i class="fas fa-file-invoice mr-2"></i>
                                Generate Invoice
                            </button>
                            <button onclick="sendPaymentLink()" class="w-full bg-purple-600 text-white py-3 px-4 rounded hover:bg-purple-700 flex items-center justify-center">
                                <i class="fas fa-link mr-2"></i>
                                Send Payment Link
                            </button>
                            <button onclick="processRefund()" class="w-full bg-yellow-600 text-white py-3 px-4 rounded hover:bg-yellow-700 flex items-center justify-center">
                                <i class="fas fa-undo mr-2"></i>
                                Process Refund
                            </button>
                            <button onclick="exportPaymentData()" class="w-full bg-gray-600 text-white py-3 px-4 rounded hover:bg-gray-700 flex items-center justify-center">
                                <i class="fas fa-download mr-2"></i>
                                Export Data
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Service Packages -->
                <div class="bg-white p-6 rounded-lg shadow mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Service Packages</h3>
                        <button onclick="showAddPackageModal()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            <i class="fas fa-plus mr-2"></i>Add Package
                        </button>
                    </div>
                    <div id="service-packages-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Essential App Package -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="font-semibold text-lg">Essential App</h4>
                                    <p class="text-2xl font-bold text-green-600">$1,500</p>
                                </div>
                                <div class="flex space-x-1">
                                    <button onclick="editPackage('essential-app')" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deletePackage('essential-app')" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="text-gray-600 text-sm mb-3">Perfect for small businesses starting their digital journey</p>
                            <ul class="text-sm text-gray-700 space-y-1">
                                <li><i class="fas fa-check text-green-500 mr-2"></i>Custom web application</li>
                                <li><i class="fas fa-check text-green-500 mr-2"></i>Responsive design</li>
                                <li><i class="fas fa-check text-green-500 mr-2"></i>Basic admin panel</li>
                                <li><i class="fas fa-check text-green-500 mr-2"></i>30 days support</li>
                            </ul>
                        </div>

                        <!-- Custom Enterprise Package -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="font-semibold text-lg">Custom Enterprise</h4>
                                    <p class="text-2xl font-bold text-blue-600">Custom Quote</p>
                                </div>
                                <div class="flex space-x-1">
                                    <button onclick="editPackage('custom-enterprise')" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deletePackage('custom-enterprise')" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="text-gray-600 text-sm mb-3">Tailored solutions for enterprise-level requirements</p>
                            <ul class="text-sm text-gray-700 space-y-1">
                                <li><i class="fas fa-check text-green-500 mr-2"></i>Custom architecture</li>
                                <li><i class="fas fa-check text-green-500 mr-2"></i>Advanced integrations</li>
                                <li><i class="fas fa-check text-green-500 mr-2"></i>Scalable infrastructure</li>
                                <li><i class="fas fa-check text-green-500 mr-2"></i>Ongoing maintenance</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Maintenance Mode</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" id="maintenance-mode" class="mr-2">
                                    <span class="text-sm font-medium">Enable Maintenance Mode</span>
                                </label>
                                <p class="text-xs text-gray-500 mt-1">Visitors will see a maintenance page</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Maintenance Message</label>
                                <textarea id="maintenance-message" rows="3" class="w-full p-3 border border-gray-300 rounded-md" placeholder="We're currently performing scheduled maintenance. Please check back soon."></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Expected Return Time</label>
                                <input type="datetime-local" id="maintenance-return" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <button onclick="toggleMaintenanceMode()" class="w-full bg-orange-600 text-white py-2 px-4 rounded hover:bg-orange-700">
                                Toggle Maintenance Mode
                            </button>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Advanced Settings</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" id="debug-mode" class="mr-2">
                                    <span class="text-sm">Enable Debug Mode</span>
                                </label>
                            </div>
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" id="cache-enabled" class="mr-2" checked>
                                    <span class="text-sm">Enable Caching</span>
                                </label>
                            </div>
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" id="compression-enabled" class="mr-2" checked>
                                    <span class="text-sm">Enable GZIP Compression</span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cache Duration (hours)</label>
                                <input type="number" id="cache-duration" value="24" min="1" max="168" class="w-full p-3 border border-gray-300 rounded-md">
                            </div>
                            <button onclick="clearCache()" class="w-full bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700">
                                <i class="fas fa-trash mr-2"></i>Clear Cache
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Add Package Modal -->
                <div id="package-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
                    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-xl font-bold">Add Service Package</h3>
                                <button onclick="hideAddPackageModal()" class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                            
                            <form id="package-form" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Package Name</label>
                                        <input type="text" name="name" required class="w-full p-3 border border-gray-300 rounded-md">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                                        <input type="text" name="price" placeholder="$1,500 or Custom Quote" required class="w-full p-3 border border-gray-300 rounded-md">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                    <textarea name="description" rows="3" required class="w-full p-3 border border-gray-300 rounded-md"></textarea>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Features (one per line)</label>
                                    <textarea name="features" rows="5" placeholder="Custom web application&#10;Responsive design&#10;Basic admin panel&#10;30 days support" required class="w-full p-3 border border-gray-300 rounded-md"></textarea>
                                </div>
                                
                                <div class="flex justify-end space-x-4">
                                    <button type="button" onclick="hideAddPackageModal()" class="px-6 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        Add Package
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/config.js"></script>
    <script src="admin.js"></script>
    <script>
        // Invoice functionality
        let currentInvoiceNumber = 1;
        
        // Load existing invoices to get the next invoice number
        async function loadNextInvoiceNumber() {
            try {
                const response = await fetch('api/get_invoices.php');
                const invoices = await response.json();
                
                if (invoices.length > 0) {
                    // Find the highest invoice number
                    const numbers = invoices.map(inv => {
                        const match = inv.invoice_number.match(/INV-\d{6}-(\d+)/);
                        return match ? parseInt(match[1]) : 0;
                    });
                    currentInvoiceNumber = Math.max(...numbers) + 1;
                } else {
                    currentInvoiceNumber = 1;
                }
                
                generateNewInvoiceNumber();
            } catch (error) {
                console.error('Error loading invoices:', error);
                generateNewInvoiceNumber();
            }
        }
        
        function generateNewInvoiceNumber() {
            const date = new Date();
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const counter = String(currentInvoiceNumber).padStart(4, '0');
            
            const invoiceNumber = `INV-${year}${month}-${counter}`;
            
            // Update the display and hidden field
            const displayElement = document.getElementById('invoice-number-display');
            const hiddenElement = document.getElementById('invoice-number');
            
            if (displayElement) {
                displayElement.textContent = invoiceNumber;
            }
            
            if (hiddenElement) {
                hiddenElement.value = invoiceNumber;
            }
            
            console.log('Generated invoice number:', invoiceNumber);
        }
        
        function calculateAmountDue() {
            const totalAmount = parseFloat(document.getElementById('total-amount')?.value) || 0;
            const amountPaid = parseFloat(document.getElementById('amount-paid')?.value) || 0;
            const taxRate = parseFloat(document.getElementById('tax-rate')?.value) || 0;
            
            // Calculate tax amount
            const taxAmount = (totalAmount * taxRate) / 100;
            
            // Calculate total with tax
            const totalWithTax = totalAmount + taxAmount;
            
            // Calculate amount due
            const amountDue = totalWithTax - amountPaid;
            
            // Update the amount due field
            const amountDueField = document.getElementById('amount-due');
            if (amountDueField) {
                amountDueField.value = Math.max(0, amountDue).toFixed(2);
            }
            
            // Update preview
            updateInvoicePreview();
            
            console.log('Amount calculation:', {
                totalAmount,
                taxRate,
                taxAmount,
                totalWithTax,
                amountPaid,
                amountDue: Math.max(0, amountDue)
            });
        }
        
        function updateInvoicePreview() {
            const previewElement = document.getElementById('invoice-preview');
            if (!previewElement) return;
            
            const invoiceData = {
                invoice_number: document.getElementById('invoice-number')?.value || 'INV-202412-0001',
                invoice_date: document.getElementById('invoice-date')?.value || '',
                due_date: document.getElementById('due-date')?.value || '',
                client_name: document.getElementById('client-name')?.value || '',
                client_email: document.getElementById('client-email')?.value || '',
                project_name: document.getElementById('project-name')?.value || '',
                project_type: document.getElementById('project-type')?.value || 'Essential App',
                total_amount: parseFloat(document.getElementById('total-amount')?.value) || 0,
                amount_paid: parseFloat(document.getElementById('amount-paid')?.value) || 0,
                amount_due: parseFloat(document.getElementById('amount-due')?.value) || 0,
                tax_rate: parseFloat(document.getElementById('tax-rate')?.value) || 0,
                currency: document.getElementById('currency')?.value || 'USD'
            };
            
            if (!invoiceData.client_name && !invoiceData.project_name) {
                previewElement.innerHTML = `
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-file-invoice text-4xl mb-4"></i>
                        <p>Fill out the form to see preview</p>
                    </div>
                `;
                return;
            }
            
            const taxAmount = (invoiceData.total_amount * invoiceData.tax_rate) / 100;
            const totalWithTax = invoiceData.total_amount + taxAmount;
            
            previewElement.innerHTML = `
                <div class="border border-gray-200 rounded p-4">
                    <div class="text-center mb-4 pb-2 border-b">
                        <h4 class="font-bold text-blue-600">App Craft Services</h4>
                        <p class="text-xs text-gray-600">Professional Web Development</p>
                    </div>
                    
                    <div class="mb-3">
                        <div class="font-semibold text-sm">INVOICE</div>
                        <div class="text-xs">${invoiceData.invoice_number}</div>
                        <div class="text-xs">${invoiceData.invoice_date}</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="font-semibold text-sm">BILL TO</div>
                        <div class="text-xs">${invoiceData.client_name}</div>
                        <div class="text-xs">${invoiceData.client_email}</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="font-semibold text-sm">PROJECT</div>
                        <div class="text-xs">${invoiceData.project_name}</div>
                        <div class="text-xs">${invoiceData.project_type}</div>
                    </div>
                    
                    <div class="border-t pt-2 text-xs">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>${invoiceData.currency} ${invoiceData.total_amount.toFixed(2)}</span>
                        </div>
                        ${invoiceData.tax_rate > 0 ? `
                        <div class="flex justify-between">
                            <span>Tax (${invoiceData.tax_rate}%):</span>
                            <span>${invoiceData.currency} ${taxAmount.toFixed(2)}</span>
                        </div>
                        ` : ''}
                        <div class="flex justify-between font-semibold">
                            <span>Total:</span>
                            <span>${invoiceData.currency} ${totalWithTax.toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between text-green-600">
                            <span>Paid:</span>
                            <span>${invoiceData.currency} ${invoiceData.amount_paid.toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between font-bold text-red-600">
                            <span>Amount Due:</span>
                            <span>${invoiceData.currency} ${invoiceData.amount_due}</span>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function previewInvoice() {
            const invoiceData = {
                invoice_number: document.getElementById('invoice-number')?.value,
                client_name: document.getElementById('client-name')?.value,
                project_name: document.getElementById('project-name')?.value
            };
            
            if (!invoiceData.client_name || !invoiceData.project_name) {
                showNotification('Please fill in client name and project name first', 'warning');
                return;
            }
            
            updateInvoicePreview();
            showNotification('Invoice preview updated!', 'success');
        }
        
        async function saveInvoice(event) {
            event.preventDefault();
            
            // Collect all form data
            const invoiceData = {
                invoice_number: document.getElementById('invoice-number')?.value,
                invoice_date: document.getElementById('invoice-date')?.value,
                due_date: document.getElementById('due-date')?.value,
                status: document.getElementById('invoice-status')?.value,
                client_name: document.getElementById('client-name')?.value,
                client_email: document.getElementById('client-email')?.value,
                client_address: document.getElementById('client-address')?.value,
                project_name: document.getElementById('project-name')?.value,
                project_type: document.getElementById('project-type')?.value,
                project_description: document.getElementById('project-description')?.value,
                total_amount: parseFloat(document.getElementById('total-amount')?.value) || 0,
                amount_paid: parseFloat(document.getElementById('amount-paid')?.value) || 0,
                amount_due: parseFloat(document.getElementById('amount-due')?.value) || 0,
                tax_rate: parseFloat(document.getElementById('tax-rate')?.value) || 0,
                currency: document.getElementById('currency')?.value,
                notes: document.getElementById('invoice-notes')?.value
            };
            
            // Validate required fields
            if (!invoiceData.client_name || !invoiceData.project_name || !invoiceData.total_amount) {
                showNotification('Please fill in all required fields (Client Name, Project Name, Total Amount)', 'error');
                return;
            }
            
            try {
                const response = await fetch('api/save_invoice.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(invoiceData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Invoice saved successfully!', 'success');
                    
                    // Increment invoice number for next invoice
                    currentInvoiceNumber++;
                    generateNewInvoiceNumber();
                    
                    // Optionally reset form or keep data for editing
                    // resetInvoiceForm();
                } else {
                    showNotification('Error saving invoice: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Save error:', error);
                showNotification('Error saving invoice: ' + error.message, 'error');
            }
        }tart(2, '0');
            const counter = String(currentInvoiceNumber).padStart(4, '0');
            const invoiceNumber = `INV-${year}${month}-${counter}`;
            
            const invoiceNumberDisplay = document.getElementById('invoice-number-display');
            const invoiceNumberHidden = document.getElementById('invoice-number');
            
            if (invoiceNumberDisplay) {
                invoiceNumberDisplay.textContent = invoiceNumber;
            }
            if (invoiceNumberHidden) {
                invoiceNumberHidden.value = invoiceNumber;
            }
        }

        function calculateAmountDue() {
            const totalAmountEl = document.getElementById('total-amount');
            const amountPaidEl = document.getElementById('amount-paid');
            const taxRateEl = document.getElementById('tax-rate');
            const amountDueEl = document.getElementById('amount-due');
            
            if (!totalAmountEl || !amountPaidEl || !amountDueEl) {
                console.log('Required elements not found for calculation');
                return;
            }
            
            const totalAmount = parseFloat(totalAmountEl.value) || 0;
            const amountPaid = parseFloat(amountPaidEl.value) || 0;
            const taxRate = parseFloat(taxRateEl ? taxRateEl.value : 0) || 0;
            
            console.log('Calculating amount due:', { totalAmount, amountPaid, taxRate });
            
            const taxAmount = (totalAmount * taxRate) / 100;
            const totalWithTax = totalAmount + taxAmount;
            const amountDue = totalWithTax - amountPaid;
            
            const finalAmountDue = Math.max(0, amountDue);
            amountDueEl.value = finalAmountDue.toFixed(2);
            
            console.log('Amount due calculated:', finalAmountDue);
            
            // Update preview immediately
            updateInvoicePreview();
        }
        
        function updateInvoicePreview() {
            const preview = document.getElementById('invoice-preview');
            if (!preview) return;
            
            const invoiceNumber = document.getElementById('invoice-number')?.value || '';
            const clientName = document.getElementById('client-name')?.value || '';
            const projectName = document.getElementById('project-name')?.value || '';
            const projectType = document.getElementById('project-type')?.value || '';
            const totalAmount = parseFloat(document.getElementById('total-amount')?.value) || 0;
            const amountPaid = parseFloat(document.getElementById('amount-paid')?.value) || 0;
            const amountDue = parseFloat(document.getElementById('amount-due')?.value) || 0;
            const currency = document.getElementById('currency')?.value || 'USD';
            const invoiceDate = document.getElementById('invoice-date')?.value || '';
            const dueDate = document.getElementById('due-date')?.value || '';
            
            if (!clientName || !projectName || !totalAmount) {
                preview.innerHTML = `
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-file-invoice text-4xl mb-4"></i>
                        <p>Fill out the form to see preview</p>
                    </div>
                `;
                return;
            }
            
            preview.innerHTML = `
                <div class="border border-gray-300 p-4 bg-white text-xs">
                    <div class="text-center mb-4">
                        <h2 class="text-lg font-bold text-navy">App Craft Services</h2>
                        <p class="text-xs text-gray-600">Professional Web Development</p>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <div>
                            <p><strong>Invoice:</strong> ${invoiceNumber}</p>
                            <p><strong>Date:</strong> ${invoiceDate}</p>
                            <p><strong>Due:</strong> ${dueDate}</p>
                        </div>
                        <div>
                            <p><strong>Client:</strong> ${clientName}</p>
                            <p><strong>Project:</strong> ${projectName}</p>
                            <p><strong>Type:</strong> ${projectType}</p>
                        </div>
                    </div>
                    <div class="text-right border-t pt-2">
                        <p class="text-sm"><strong>Total: ${currency} ${totalAmount.toFixed(2)}</strong></p>
                        <p class="text-green-600 text-sm"><strong>Paid: ${currency} ${amountPaid.toFixed(2)}</strong></p>
                        <p class="text-red-600 text-sm"><strong>Due: ${currency} ${amountDue.toFixed(2)}</strong></p>
                    </div>
                </div>
            `;
        }

        // Make functions globally accessible
        window.previewInvoice = function() {
            console.log('Preview button clicked');
            updateInvoicePreview();
            showNotification('Invoice preview updated!', 'success');
        }
        
        window.saveInvoice = async function() {
            console.log('Save button clicked');
            
            // Get form data
            const invoiceData = {
                invoice_number: document.getElementById('invoice-number')?.value,
                invoice_date: document.getElementById('invoice-date')?.value,
                due_date: document.getElementById('due-date')?.value,
                status: document.getElementById('invoice-status')?.value || 'draft',
                client_name: document.getElementById('client-name')?.value,
                client_email: document.getElementById('client-email')?.value,
                client_address: document.getElementById('client-address')?.value,
                project_name: document.getElementById('project-name')?.value,
                project_type: document.getElementById('project-type')?.value,
                project_description: document.getElementById('project-description')?.value,
                total_amount: parseFloat(document.getElementById('total-amount')?.value) || 0,
                amount_paid: parseFloat(document.getElementById('amount-paid')?.value) || 0,
                amount_due: parseFloat(document.getElementById('amount-due')?.value) || 0,
                tax_rate: parseFloat(document.getElementById('tax-rate')?.value) || 0,
                currency: document.getElementById('currency')?.value || 'USD',
                notes: document.getElementById('invoice-notes')?.value
            };
            
            console.log('Invoice data:', invoiceData);
            
            // Validate required fields
            if (!invoiceData.client_name || !invoiceData.project_name || !invoiceData.total_amount) {
                showNotification('Please fill in all required fields (Client Name, Project Name, Total Amount)', 'error');
                return;
            }
            
            try {
                const response = await fetch('api/save_invoice.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(invoiceData)
                });
                
                const result = await response.json();
                console.log('Save response:', result);
                
                if (result.success) {
                    showNotification('Invoice saved successfully!', 'success');
                    currentInvoiceNumber++;
                    resetInvoiceForm();
                    if (typeof loadInvoices === 'function') {
                        loadInvoices();
                    }
                } else {
                    showNotification('Error saving invoice: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Save error:', error);
                showNotification('Error saving invoice: ' + error.message, 'error');
            }
        }
        
        window.generateInvoicePDF = function() {
            console.log('Export PDF button clicked');
            
            const invoiceData = {
                invoice_number: document.getElementById('invoice-number')?.value,
                invoice_date: document.getElementById('invoice-date')?.value,
                due_date: document.getElementById('due-date')?.value,
                client_name: document.getElementById('client-name')?.value,
                client_email: document.getElementById('client-email')?.value,
                client_address: document.getElementById('client-address')?.value,
                project_name: document.getElementById('project-name')?.value,
                project_type: document.getElementById('project-type')?.value,
                project_description: document.getElementById('project-description')?.value,
                total_amount: parseFloat(document.getElementById('total-amount')?.value) || 0,
                amount_paid: parseFloat(document.getElementById('amount-paid')?.value) || 0,
                amount_due: parseFloat(document.getElementById('amount-due')?.value) || 0,
                tax_rate: parseFloat(document.getElementById('tax-rate')?.value) || 0,
                currency: document.getElementById('currency')?.value || 'USD',
                notes: document.getElementById('invoice-notes')?.value
            };
            
            if (!invoiceData.client_name || !invoiceData.project_name) {
                showNotification('Please fill in client name and project name first', 'warning');
                return;
            }
            
            const taxAmount = (invoiceData.total_amount * invoiceData.tax_rate) / 100;
            const totalWithTax = invoiceData.total_amount + taxAmount;
            
            const pdfContent = `<!DOCTYPE html>
<html>
<head>
    <title>Invoice ${invoiceData.invoice_number}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #333; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 40px; border-bottom: 3px solid #1e3a8a; padding-bottom: 20px; }
        .company-name { font-size: 32px; font-weight: bold; color: #1e3a8a; margin-bottom: 8px; }
        .company-tagline { font-size: 16px; color: #666; margin-bottom: 12px; }
        .company-contact { font-size: 14px; color: #666; }
        .invoice-details { display: flex; justify-content: space-between; margin: 40px 0; }
        .invoice-info, .client-info { width: 45%; }
        .invoice-info h2 { color: #1e3a8a; font-size: 28px; margin-bottom: 15px; }
        .client-info h3 { color: #1e3a8a; font-size: 18px; margin-bottom: 12px; }
        .project-section { margin: 30px 0; padding: 20px; background-color: #f8fafc; border-left: 5px solid #3b82f6; }
        .invoice-table { width: 100%; border-collapse: collapse; margin: 30px 0; }
        .invoice-table th { background-color: #1e3a8a; color: white; padding: 15px; text-align: left; font-size: 16px; }
        .invoice-table td { border: 1px solid #ddd; padding: 15px; font-size: 14px; }
        .invoice-table tr:nth-child(even) { background-color: #f9f9f9; }
        .totals { text-align: right; margin-top: 30px; }
        .total-line { margin: 10px 0; font-size: 16px; }
        .grand-total { font-size: 20px; font-weight: bold; color: #1e3a8a; border-top: 2px solid #1e3a8a; padding-top: 15px; margin-top: 15px; }
        .amount-due { font-size: 24px; font-weight: bold; color: #dc2626; margin-top: 15px; }
        .notes { margin-top: 40px; padding: 20px; background-color: #f8fafc; border-radius: 8px; }
        .footer { margin-top: 60px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 20px; }
        @media print { body { margin: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">App Craft Services</div>
        <div class="company-tagline">Professional Web Development Services</div>
        <div class="company-contact">
            Email: williamsaonen@gmail.com | Phone: +2348061581916
        </div>
    </div>
    
    <div class="invoice-details">
        <div class="invoice-info">
            <h2>INVOICE</h2>
            <p><strong>Invoice Number:</strong> ${invoiceData.invoice_number}</p>
            <p><strong>Invoice Date:</strong> ${invoiceData.invoice_date}</p>
            <p><strong>Due Date:</strong> ${invoiceData.due_date}</p>
        </div>
        <div class="client-info">
            <h3>BILL TO</h3>
            <p><strong>${invoiceData.client_name}</strong></p>
            <p>${invoiceData.client_email}</p>
            ${invoiceData.client_address ? `<p>${invoiceData.client_address.replace(/\n/g, '<br>')}</p>` : ''}
        </div>
    </div>
    
    <div class="project-section">
        <h3 style="color: #1e3a8a; margin-bottom: 15px; font-size: 18px;">PROJECT DETAILS</h3>
        <p><strong>Project Name:</strong> ${invoiceData.project_name}</p>
        <p><strong>Project Type:</strong> ${invoiceData.project_type}</p>
        ${invoiceData.project_description ? `<p><strong>Description:</strong> ${invoiceData.project_description}</p>` : ''}
    </div>
    
    <table class="invoice-table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: right;">Amount</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>${invoiceData.project_type} Development</td>
                <td style="text-align: right;">${invoiceData.currency} ${invoiceData.total_amount.toFixed(2)}</td>
                <td style="text-align: right;">${invoiceData.currency} ${invoiceData.total_amount.toFixed(2)}</td>
            </tr>
        </tbody>
    </table>
    
    <div class="totals">
        <div class="total-line"><strong>Subtotal: ${invoiceData.currency} ${invoiceData.total_amount.toFixed(2)}</strong></div>
        ${invoiceData.tax_rate > 0 ? `<div class="total-line"><strong>Tax (${invoiceData.tax_rate}%): ${invoiceData.currency} ${taxAmount.toFixed(2)}</strong></div>` : ''}
        <div class="grand-total">Total: ${invoiceData.currency} ${totalWithTax.toFixed(2)}</div>
        <div class="total-line" style="color: #059669;"><strong>Amount Paid: ${invoiceData.currency} ${invoiceData.amount_paid.toFixed(2)}</strong></div>
        <div class="amount-due">Amount Due: ${invoiceData.currency} ${invoiceData.amount_due.toFixed(2)}</div>
    </div>
    
    ${invoiceData.notes ? `
    <div class="notes">
        <h4 style="color: #1e3a8a; margin-bottom: 15px;">Additional Notes</h4>
        <p>${invoiceData.notes.replace(/\n/g, '<br>')}</p>
    </div>
    ` : ''}
    
    <div class="footer">
        <p><strong>Thank you for choosing App Craft Services!</strong></p>
        <p>Payment terms: Net 30 days. Late payments may incur additional charges.</p>
        <p>For questions about this invoice, please contact us at williamsaonen@gmail.com</p>
    </div>
    
    <scr` + `ipt>
        window.onload = function() {
            window.print();
            window.onafterprint = function() {
                window.close();
            }
        }
    </scr` + `ipt>
</body>
</html>`;
            
            const printWindow = window.open('', '_blank');
            printWindow.document.write(pdfContent);
            printWindow.document.close();
            
            showNotification('Professional invoice PDF generated and ready for printing/saving!', 'success');
        }
        
        function resetInvoiceForm() {
            const form = document.getElementById('invoice-form');
            if (form) {
                form.reset();
                generateNewInvoiceNumber();
                
                // Set default dates
                const invoiceDateField = document.getElementById('invoice-date');
                const dueDateField = document.getElementById('due-date');
                
                if (invoiceDateField) {
                    invoiceDateField.value = new Date().toISOString().split('T')[0];
                }
                
                if (dueDateField) {
                    const dueDate = new Date();
                    dueDate.setDate(dueDate.getDate() + 30);
                    dueDateField.value = dueDate.toISOString().split('T')[0];
                }
                
                // Reset amount due
                const amountDueField = document.getElementById('amount-due');
                if (amountDueField) {
                    amountDueField.value = '0.00';
                }
                
                // Clear preview
                updateInvoicePreview();
            }
        }
        
        async function loadInvoices() {
            try {
                const response = await fetch('api/get_invoices.php');
                const invoices = await response.json();
                
                // Update invoice statistics
                const totalInvoices = invoices.length;
                const paidInvoices = invoices.filter(inv => inv.status === 'paid').length;
                const pendingInvoices = invoices.filter(inv => inv.status === 'sent' || inv.status === 'draft').length;
                const overdueInvoices = invoices.filter(inv => inv.status === 'overdue').length;
                
                document.getElementById('total-invoices').textContent = totalInvoices;
                document.getElementById('paid-invoices').textContent = paidInvoices;
                document.getElementById('pending-invoices').textContent = pendingInvoices;
                document.getElementById('overdue-invoices').textContent = overdueInvoices;
                
                // Display invoices list
                displayInvoicesList(invoices);
                
            } catch (error) {
                console.error('Error loading invoices:', error);
                showNotification('Error loading invoices', 'error');
            }
        }
        
        function displayInvoicesList(invoices) {
            const invoicesList = document.getElementById('invoices-list');
            
            if (invoices.length === 0) {
                invoicesList.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-file-invoice text-4xl mb-4"></i>
                        <p>No invoices created yet</p>
                    </div>
                `;
                return;
            }
            
            let html = `
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="text-left p-3 font-semibold">Invoice #</th>
                                <th class="text-left p-3 font-semibold">Client</th>
                                <th class="text-left p-3 font-semibold">Project</th>
                                <th class="text-left p-3 font-semibold">Amount</th>
                                <th class="text-left p-3 font-semibold">Due</th>
                                <th class="text-left p-3 font-semibold">Status</th>
                                <th class="text-left p-3 font-semibold">Date</th>
                                <th class="text-left p-3 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            invoices.forEach(invoice => {
                const statusColors = {
                    'draft': 'bg-gray-100 text-gray-800',
                    'sent': 'bg-blue-100 text-blue-800',
                    'paid': 'bg-green-100 text-green-800',
                    'overdue': 'bg-red-100 text-red-800'
                };
                
                const statusColor = statusColors[invoice.status] || 'bg-gray-100 text-gray-800';
                
                html += `
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3 font-medium">${invoice.invoice_number}</td>
                        <td class="p-3">${escapeHtml(invoice.client_name)}</td>
                        <td class="p-3">${escapeHtml(invoice.project_name)}</td>
                        <td class="p-3 font-semibold">${invoice.currency} ${parseFloat(invoice.total_amount).toFixed(2)}</td>
                        <td class="p-3 font-semibold ${invoice.amount_due > 0 ? 'text-red-600' : 'text-green-600'}">
                            ${invoice.currency} ${parseFloat(invoice.amount_due).toFixed(2)}
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium ${statusColor}">
                                ${invoice.status.charAt(0).toUpperCase() + invoice.status.slice(1)}
                            </span>
                        </td>
                        <td class="p-3 text-sm text-gray-600">${formatDate(invoice.invoice_date)}</td>
                        <td class="p-3">
                            <div class="flex space-x-1">
                                <button onclick="viewInvoice('${invoice.id}')" class="text-blue-600 hover:text-blue-800 p-1" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editInvoice('${invoice.id}')" class="text-green-600 hover:text-green-800 p-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="duplicateInvoice('${invoice.id}')" class="text-purple-600 hover:text-purple-800 p-1" title="Duplicate">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button onclick="deleteInvoice('${invoice.id}')" class="text-red-600 hover:text-red-800 p-1" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            invoicesList.innerHTML = html;
        }
        
        function refreshInvoices() {
            loadInvoices();
            showNotification('Invoices refreshed', 'success');
        }
        
        function viewInvoice(invoiceId) {
            showNotification('View invoice functionality coming soon!', 'info');
        }
        
        function editInvoice(invoiceId) {
            showNotification('Edit invoice functionality coming soon!', 'info');
        }
        
        function duplicateInvoice(invoiceId) {
            showNotification('Duplicate invoice functionality coming soon!', 'info');
        }
        
        function deleteInvoice(invoiceId) {
            if (confirm('Are you sure you want to delete this invoice?')) {
                showNotification('Delete invoice functionality coming soon!', 'info');
            }
        }
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString();
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        }
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing invoice functionality');
            
            // Load next invoice number
            loadNextInvoiceNumber();
            
            // Load existing invoices
            loadInvoices();
            
            // Set up form submission handler
            const invoiceForm = document.getElementById('invoice-form');
            if (invoiceForm) {
                invoiceForm.addEventListener('submit', saveInvoice);
                console.log('Added form submission handler');
            }
            
            // Set up event listeners for invoice form with multiple event types
            const totalAmountField = document.getElementById('total-amount');
            const amountPaidField = document.getElementById('amount-paid');
            const taxRateField = document.getElementById('tax-rate');
            
            if (totalAmountField) {
                totalAmountField.addEventListener('input', calculateAmountDue);
                totalAmountField.addEventListener('keyup', calculateAmountDue);
                totalAmountField.addEventListener('change', calculateAmountDue);
                totalAmountField.addEventListener('blur', calculateAmountDue);
                console.log('Added event listeners to total amount field');
            }
            
            if (amountPaidField) {
                amountPaidField.addEventListener('input', calculateAmountDue);
                amountPaidField.addEventListener('keyup', calculateAmountDue);
                amountPaidField.addEventListener('change', calculateAmountDue);
                amountPaidField.addEventListener('blur', calculateAmountDue);
                console.log('Added event listeners to amount paid field');
            }
            
            if (taxRateField) {
                taxRateField.addEventListener('input', calculateAmountDue);
                taxRateField.addEventListener('keyup', calculateAmountDue);
                taxRateField.addEventListener('change', calculateAmountDue);
                taxRateField.addEventListener('blur', calculateAmountDue);
                console.log('Added event listeners to tax rate field');
            }
            
            // Add event listeners to other fields for preview updates
            const clientNameField = document.getElementById('client-name');
            const projectNameField = document.getElementById('project-name');
            const projectTypeField = document.getElementById('project-type');
            
            if (clientNameField) {
                clientNameField.addEventListener('input', updateInvoicePreview);
                clientNameField.addEventListener('blur', updateInvoicePreview);
            }
            
            if (projectNameField) {
                projectNameField.addEventListener('input', updateInvoicePreview);
                projectNameField.addEventListener('blur', updateInvoicePreview);
            }
            
            if (projectTypeField) {
                projectTypeField.addEventListener('change', updateInvoicePreview);
            }
            
            // Set default dates
            const invoiceDateField = document.getElementById('invoice-date');
            const dueDateField = document.getElementById('due-date');
            
            if (invoiceDateField) {
                invoiceDateField.value = new Date().toISOString().split('T')[0];
            }
            
            if (dueDateField) {
                const dueDate = new Date();
                dueDate.setDate(dueDate.getDate() + 30);
                dueDateField.value = dueDate.toISOString().split('T')[0];
            }
            
            // Initialize amount due field
            const amountDueField = document.getElementById('amount-due');
            if (amountDueField) {
                amountDueField.value = '0.00';
            }
            
            console.log('Invoice functionality initialized successfully');
        });
    </script>

    <!-- Project Management Modal -->
    <div id="project-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800">Add New Project</h3>
                    <button onclick="hideAddProjectModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                </div>
                
                <form id="project-form" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Project Name</label>
                            <input type="text" id="project-name-input" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select id="project-category" name="category" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Category</option>
                                <option value="Health & Fitness">Health & Fitness</option>
                                <option value="Communication">Communication</option>
                                <option value="Restaurant & Food">Restaurant & Food</option>
                                <option value="Finance & AI">Finance & AI</option>
                                <option value="Business Management">Business Management</option>
                                <option value="Legal Tech">Legal Tech</option>
                                <option value="Education">Education</option>
                                <option value="Government & HR">Government & HR</option>
                                <option value="E-commerce">E-commerce</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="project-description" name="description" rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Client Name</label>
                            <input type="text" id="project-client" name="client" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Completion Date</label>
                            <input type="date" id="project-completion-date" name="completion_date" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Technologies (comma-separated)</label>
                        <input type="text" id="project-technologies" name="technologies" placeholder="React, Node.js, MongoDB" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Project Image</label>
                        <input type="file" id="project-image" name="image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-sm text-gray-500 mt-1">Upload project logo or screenshot</p>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="project-featured" name="featured" class="mr-2">
                        <label for="project-featured" class="text-sm font-medium text-gray-700">Featured Project</label>
                    </div>
                    
                    <div class="flex justify-end space-x-4 pt-4">
                        <button type="button" onclick="hideAddProjectModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Blog Post Management Modal -->
    <div id="blog-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800">Add New Blog Post</h3>
                    <button onclick="hideAddBlogModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                </div>
                
                <form id="blog-form" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Post Title</label>
                            <input type="text" id="blog-title" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Slug (URL)</label>
                            <input type="text" id="blog-slug" name="slug" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Excerpt</label>
                        <textarea id="blog-excerpt" name="excerpt" rows="2" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Brief description of the post"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                        <textarea id="blog-content" name="content" rows="10" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Write your blog post content here..."></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select id="blog-category" name="category" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Category</option>
                                <option value="Web Development">Web Development</option>
                                <option value="Mobile Apps">Mobile Apps</option>
                                <option value="Business Tips">Business Tips</option>
                                <option value="Technology">Technology</option>
                                <option value="Case Studies">Case Studies</option>
                                <option value="Tutorials">Tutorials</option>
                                <option value="Industry News">Industry News</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Author</label>
                            <input type="text" id="blog-author" name="author" value="App Craft Services" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="blog-status" name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tags (comma-separated)</label>
                        <input type="text" id="blog-tags" name="tags" placeholder="web development, react, javascript" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Featured Image</label>
                        <input type="file" id="blog-image" name="featured_image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-sm text-gray-500 mt-1">Upload featured image for the blog post</p>
                    </div>
                    
                    <div class="flex justify-end space-x-4 pt-4">
                        <button type="button" onclick="hideAddBlogModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>