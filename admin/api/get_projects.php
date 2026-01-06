<?php
session_start();

// Disable error display to prevent JSON corruption
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $projectsFile = __DIR__ . '/../../data/projects.json';
    
    if (!file_exists($projectsFile)) {
        echo json_encode([]);
        exit;
    }
    
    $projects = json_decode(file_get_contents($projectsFile), true);
    
    if (!is_array($projects)) {
        echo json_encode([]);
        exit;
    }
    
    // Sort by creation date (newest first)
    usort($projects, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    echo json_encode($projects);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>