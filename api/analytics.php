<?php
// Website Analytics API for App Craft Services
// Tracks page views, traffic sources, user behavior, and provides filtering

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Analytics data file
$dataDir = __DIR__ . '/../data';
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0755, true);
}

$analyticsFile = $dataDir . '/analytics.json';

// Helper function to get client IP
function getClientIP() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

// Helper function to detect device type
function getDeviceType($userAgent) {
    if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
        if (preg_match('/iPad/', $userAgent)) return 'tablet';
        return 'mobile';
    }
    return 'desktop';
}

// Helper function to detect browser
function getBrowser($userAgent) {
    if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
    if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
    if (strpos($userAgent, 'Safari') !== false) return 'Safari';
    if (strpos($userAgent, 'Edge') !== false) return 'Edge';
    if (strpos($userAgent, 'Opera') !== false) return 'Opera';
    return 'Other';
}

// Helper function to get country from IP (simplified)
function getCountryFromIP($ip) {
    // This is a simplified version. For production, use a proper GeoIP service
    if ($ip === '127.0.0.1' || $ip === 'unknown') return 'Local';
    
    // You can integrate with services like:
    // - ipapi.co
    // - ipgeolocation.io
    // - MaxMind GeoIP
    
    // For now, return 'Unknown' - you can enhance this later
    return 'Unknown';
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Track page view
        $input = json_decode(file_get_contents('php://input'), true);
        
        $pageView = [
            'id' => uniqid('view_', true),
            'timestamp' => date('Y-m-d H:i:s'),
            'date' => date('Y-m-d'),
            'time' => date('H:i:s'),
            'page' => $input['page'] ?? '/',
            'title' => $input['title'] ?? 'Unknown',
            'referrer' => $input['referrer'] ?? '',
            'source' => $input['source'] ?? 'direct',
            'medium' => $input['medium'] ?? 'none',
            'campaign' => $input['campaign'] ?? '',
            'ip_address' => getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'device_type' => getDeviceType($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'browser' => getBrowser($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'country' => getCountryFromIP(getClientIP()),
            'session_id' => $input['session_id'] ?? uniqid('session_', true),
            'is_new_visitor' => $input['is_new_visitor'] ?? true,
            'screen_resolution' => $input['screen_resolution'] ?? '',
            'viewport_size' => $input['viewport_size'] ?? '',
            'load_time' => $input['load_time'] ?? 0
        ];
        
        // Determine traffic source
        $referrer = $pageView['referrer'];
        if (empty($referrer) || strpos($referrer, $_SERVER['HTTP_HOST']) !== false) {
            $pageView['source'] = 'direct';
            $pageView['medium'] = 'none';
        } elseif (strpos($referrer, 'google.com') !== false) {
            $pageView['source'] = 'google';
            $pageView['medium'] = 'organic';
        } elseif (strpos($referrer, 'facebook.com') !== false || strpos($referrer, 'fb.com') !== false) {
            $pageView['source'] = 'facebook';
            $pageView['medium'] = 'social';
        } elseif (strpos($referrer, 'twitter.com') !== false || strpos($referrer, 't.co') !== false) {
            $pageView['source'] = 'twitter';
            $pageView['medium'] = 'social';
        } elseif (strpos($referrer, 'linkedin.com') !== false) {
            $pageView['source'] = 'linkedin';
            $pageView['medium'] = 'social';
        } elseif (strpos($referrer, 'github.com') !== false) {
            $pageView['source'] = 'github';
            $pageView['medium'] = 'referral';
        } else {
            $pageView['source'] = parse_url($referrer, PHP_URL_HOST) ?? 'unknown';
            $pageView['medium'] = 'referral';
        }
        
        // Load existing analytics data
        $analytics = [];
        if (file_exists($analyticsFile)) {
            $jsonContent = file_get_contents($analyticsFile);
            $analytics = json_decode($jsonContent, true) ?: [];
        }
        
        // Add new page view
        $analytics[] = $pageView;
        
        // Keep only last 10,000 entries to prevent file from getting too large
        if (count($analytics) > 10000) {
            $analytics = array_slice($analytics, -10000);
        }
        
        // Save analytics data
        file_put_contents($analyticsFile, json_encode($analytics, JSON_PRETTY_PRINT));
        
        echo json_encode(['success' => true, 'message' => 'Page view tracked']);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get analytics data with filtering
        $filter = $_GET['filter'] ?? 'all';
        $period = $_GET['period'] ?? '30'; // days
        $page = $_GET['page'] ?? '';
        $source = $_GET['source'] ?? '';
        
        // Load analytics data
        $analytics = [];
        if (file_exists($analyticsFile)) {
            $jsonContent = file_get_contents($analyticsFile);
            $analytics = json_decode($jsonContent, true) ?: [];
        }
        
        // Apply date filter
        $startDate = date('Y-m-d', strtotime("-{$period} days"));
        $filteredData = array_filter($analytics, function($item) use ($startDate) {
            return $item['date'] >= $startDate;
        });
        
        // Apply additional filters
        if (!empty($page)) {
            $filteredData = array_filter($filteredData, function($item) use ($page) {
                return strpos($item['page'], $page) !== false;
            });
        }
        
        if (!empty($source)) {
            $filteredData = array_filter($filteredData, function($item) use ($source) {
                return $item['source'] === $source;
            });
        }
        
        // Calculate statistics
        $totalViews = count($filteredData);
        $uniqueVisitors = count(array_unique(array_column($filteredData, 'ip_address')));
        
        // Page views by date
        $viewsByDate = [];
        foreach ($filteredData as $item) {
            $date = $item['date'];
            $viewsByDate[$date] = ($viewsByDate[$date] ?? 0) + 1;
        }
        
        // Top pages
        $pageViews = [];
        foreach ($filteredData as $item) {
            $page = $item['page'];
            $pageViews[$page] = ($pageViews[$page] ?? 0) + 1;
        }
        arsort($pageViews);
        $topPages = array_slice($pageViews, 0, 10, true);
        
        // Traffic sources
        $sources = [];
        foreach ($filteredData as $item) {
            $source = $item['source'];
            $sources[$source] = ($sources[$source] ?? 0) + 1;
        }
        arsort($sources);
        
        // Device types
        $devices = [];
        foreach ($filteredData as $item) {
            $device = $item['device_type'];
            $devices[$device] = ($devices[$device] ?? 0) + 1;
        }
        
        // Browsers
        $browsers = [];
        foreach ($filteredData as $item) {
            $browser = $item['browser'];
            $browsers[$browser] = ($browsers[$browser] ?? 0) + 1;
        }
        
        // Countries
        $countries = [];
        foreach ($filteredData as $item) {
            $country = $item['country'];
            $countries[$country] = ($countries[$country] ?? 0) + 1;
        }
        arsort($countries);
        
        // Hourly distribution
        $hourlyViews = array_fill(0, 24, 0);
        foreach ($filteredData as $item) {
            $hour = (int)date('H', strtotime($item['timestamp']));
            $hourlyViews[$hour]++;
        }
        
        // Calculate bounce rate (simplified - single page sessions)
        $sessions = [];
        foreach ($filteredData as $item) {
            $sessionId = $item['session_id'];
            $sessions[$sessionId] = ($sessions[$sessionId] ?? 0) + 1;
        }
        $singlePageSessions = count(array_filter($sessions, function($count) { return $count === 1; }));
        $bounceRate = $totalViews > 0 ? round(($singlePageSessions / count($sessions)) * 100, 2) : 0;
        
        // Average load time
        $loadTimes = array_filter(array_column($filteredData, 'load_time'), function($time) { return $time > 0; });
        $avgLoadTime = count($loadTimes) > 0 ? round(array_sum($loadTimes) / count($loadTimes), 2) : 0;
        
        // Recent visitors
        $recentVisitors = array_slice(array_reverse($filteredData), 0, 20);
        
        $response = [
            'success' => true,
            'period' => $period,
            'total_views' => $totalViews,
            'unique_visitors' => $uniqueVisitors,
            'bounce_rate' => $bounceRate,
            'avg_load_time' => $avgLoadTime,
            'views_by_date' => $viewsByDate,
            'top_pages' => $topPages,
            'traffic_sources' => $sources,
            'device_types' => $devices,
            'browsers' => $browsers,
            'countries' => $countries,
            'hourly_distribution' => $hourlyViews,
            'recent_visitors' => $recentVisitors,
            'raw_data' => array_slice($filteredData, 0, 100) // Limited for performance
        ];
        
        echo json_encode($response);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Analytics error: ' . $e->getMessage()
    ]);
}
?>