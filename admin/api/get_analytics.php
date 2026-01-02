<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    // In a real application, this would fetch from Google Analytics API or database
    // For now, we'll return sample data
    
    $analytics = [
        'visitors' => [
            'total' => 1234,
            'monthly_growth' => 12
        ],
        'page_views' => [
            'total' => 5678,
            'monthly_growth' => 8
        ],
        'bounce_rate' => [
            'rate' => 32,
            'monthly_change' => -5
        ],
        'avg_session' => [
            'duration' => '2:45',
            'monthly_growth' => 15
        ],
        'top_pages' => [
            ['path' => '/', 'title' => 'Homepage', 'views' => 2345],
            ['path' => '/services', 'title' => 'Services', 'views' => 1234],
            ['path' => '/pricing', 'title' => 'Pricing', 'views' => 987],
            ['path' => '/contact', 'title' => 'Contact', 'views' => 654]
        ],
        'traffic_sources' => [
            ['source' => 'Direct', 'percentage' => 45],
            ['source' => 'Google', 'percentage' => 35],
            ['source' => 'Social Media', 'percentage' => 15],
            ['source' => 'Referrals', 'percentage' => 5]
        ],
        'device_types' => [
            ['type' => 'Desktop', 'percentage' => 60],
            ['type' => 'Mobile', 'percentage' => 35],
            ['type' => 'Tablet', 'percentage' => 5]
        ],
        'conversion_funnel' => [
            ['stage' => 'Visitors', 'count' => 1234, 'conversion' => 100],
            ['stage' => 'Page Views', 'count' => 456, 'conversion' => 37],
            ['stage' => 'Contact Forms', 'count' => 89, 'conversion' => 19],
            ['stage' => 'Leads', 'count' => 12, 'conversion' => 13]
        ]
    ];
    
    echo json_encode($analytics);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>