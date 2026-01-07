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
    // Read the pricing page HTML
    $pricingFile = '../../pricing/index.html';
    
    if (!file_exists($pricingFile)) {
        throw new Exception('Pricing page not found');
    }
    
    $pricingContent = file_get_contents($pricingFile);
    
    // Extract pricing information using regex patterns
    $packages = [];
    
    // Extract Essential App pricing
    if (preg_match('/<div class="text-4xl font-bold mb-2">\$(\d+(?:,\d+)*)\s*-\s*\$(\d+(?:,\d+)*)<\/div>/', $pricingContent, $matches)) {
        $packages['essential'] = [
            'name' => 'Essential App',
            'price' => '$' . $matches[1] . ' - $' . $matches[2],
            'range' => '$' . $matches[1] . ' - $' . $matches[2],
            'description' => 'Perfect for startups with defined scopes and simple solutions'
        ];
    }
    
    // Extract Custom/Enterprise pricing
    if (preg_match('/<h2 class="text-2xl font-bold mb-2">Custom\/Enterprise<\/h2>\s*<div class="text-4xl font-bold mb-2">(Custom Quote)<\/div>/', $pricingContent, $matches)) {
        $packages['enterprise'] = [
            'name' => 'Custom Enterprise',
            'price' => $matches[1],
            'range' => $matches[1],
            'description' => 'For complex startup platforms and specific integrations'
        ];
    }
    
    // Extract Maintenance pricing from the maintenance section
    if (preg_match('/Starting at \$(\d+)\/month/', $pricingContent, $matches)) {
        $packages['maintenance'] = [
            'name' => 'Maintenance & Support',
            'price' => 'Starting at $' . $matches[1] . '/month',
            'range' => 'Monthly Plans',
            'description' => 'Comprehensive ongoing support and maintenance services'
        ];
    }
    
    // If we couldn't extract from HTML, use fallback values
    if (empty($packages)) {
        $packages = [
            'essential' => [
                'name' => 'Essential App',
                'price' => '$800 - $2,000',
                'range' => '$800 - $2,000',
                'description' => 'Perfect for startups with defined scopes and simple solutions'
            ],
            'enterprise' => [
                'name' => 'Custom Enterprise',
                'price' => 'Custom Quote',
                'range' => 'Custom Quote',
                'description' => 'For complex startup platforms and specific integrations'
            ],
            'maintenance' => [
                'name' => 'Maintenance & Support',
                'price' => 'Monthly Plans',
                'range' => 'Starting at $150/month',
                'description' => 'Comprehensive ongoing support and maintenance services'
            ]
        ];
    }
    
    // Load existing settings
    $settingsFile = '../../data/settings.json';
    $settings = [];
    
    if (file_exists($settingsFile)) {
        $settings = json_decode(file_get_contents($settingsFile), true);
        if (!$settings) {
            $settings = [];
        }
    }
    
    // Update package pricing in settings
    if (!isset($settings['package_pricing'])) {
        $settings['package_pricing'] = [];
    }
    
    foreach ($packages as $packageId => $packageData) {
        $settings['package_pricing'][$packageId] = [
            'price' => $packageData['price'],
            'range' => $packageData['range'],
            'description' => $packageData['description'],
            'synced_at' => date('Y-m-d H:i:s')
        ];
    }
    
    // Update timestamp
    $settings['updated_at'] = date('Y-m-d H:i:s');
    
    // Save settings
    if (file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT))) {
        echo json_encode([
            'success' => true,
            'message' => 'Pricing synced successfully from pricing page',
            'packages' => $packages
        ]);
    } else {
        throw new Exception('Failed to save settings file');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>