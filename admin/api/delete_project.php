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

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Project ID is required']);
        exit;
    }
    
    $projectId = $input['id'];
    $projectsFile = __DIR__ . '/../../data/projects.json';
    
    if (!file_exists($projectsFile)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Projects file not found']);
        exit;
    }
    
    $projects = json_decode(file_get_contents($projectsFile), true);
    
    if (!is_array($projects)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Invalid projects data']);
        exit;
    }
    
    // Find and remove the project
    $projectFound = false;
    $filteredProjects = [];
    
    foreach ($projects as $project) {
        if (isset($project['id']) && $project['id'] === $projectId) {
            $projectFound = true;
            // Skip this project (delete it)
            continue;
        }
        $filteredProjects[] = $project;
    }
    
    if (!$projectFound) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Project not found']);
        exit;
    }
    
    // Save updated projects
    if (file_put_contents($projectsFile, json_encode($filteredProjects, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true, 'message' => 'Project deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save changes']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>