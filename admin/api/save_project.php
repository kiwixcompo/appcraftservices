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
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }
    
    // Validate required fields
    $required = ['name', 'description', 'category', 'technologies', 'client', 'completion_date'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            exit;
        }
    }
    
    $projectsFile = __DIR__ . '/../../data/projects.json';
    
    // Load existing projects
    $projects = [];
    if (file_exists($projectsFile)) {
        $projects = json_decode(file_get_contents($projectsFile), true);
        if (!is_array($projects)) {
            $projects = [];
        }
    }
    
    // Generate ID if not provided (for new projects)
    if (empty($input['id'])) {
        $input['id'] = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $input['name'])) . '-' . time();
    }
    
    // Set default values
    $project = [
        'id' => $input['id'],
        'name' => $input['name'],
        'description' => $input['description'],
        'image' => $input['image'] ?? 'assets/projects/default.png',
        'category' => $input['category'],
        'technologies' => is_array($input['technologies']) ? $input['technologies'] : explode(',', $input['technologies']),
        'completion_date' => $input['completion_date'],
        'client' => $input['client'],
        'status' => $input['status'] ?? 'completed',
        'featured' => isset($input['featured']) ? (bool)$input['featured'] : true,
        'created_at' => $input['created_at'] ?? date('Y-m-d H:i:s')
    ];
    
    // Check if updating existing project
    $projectIndex = -1;
    foreach ($projects as $index => $existingProject) {
        if ($existingProject['id'] === $project['id']) {
            $projectIndex = $index;
            break;
        }
    }
    
    if ($projectIndex >= 0) {
        // Update existing project
        $projects[$projectIndex] = $project;
        $message = 'Project updated successfully';
    } else {
        // Add new project
        array_unshift($projects, $project);
        $message = 'Project added successfully';
    }
    
    // Save projects
    if (file_put_contents($projectsFile, json_encode($projects, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true, 'message' => $message, 'project' => $project]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save project']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>