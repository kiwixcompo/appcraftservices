<?php
// Test security headers
header('Content-Type: application/json');

$headers = [];
foreach (getallheaders() as $name => $value) {
    $headers[strtolower($name)] = $value;
}

$response = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
    'is_chrome' => strpos($_SERVER['HTTP_USER_AGENT'] ?? '', 'Chrome') !== false,
    'is_https' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'headers_sent' => headers_list(),
    'message' => 'Security headers test successful'
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>