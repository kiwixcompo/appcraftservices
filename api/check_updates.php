<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

try {
    // Get the last modification time of key files
    $filesToCheck = [
        '../index.html',
        '../assets/script.js',
        '../assets/styles.css',
        '../admin/index.php',
        '../admin/admin.js',
        '../data/settings.json',
        '../data/website_content.json'
    ];
    
    $latestModTime = 0;
    $modifiedFiles = [];
    
    foreach ($filesToCheck as $file) {
        if (file_exists($file)) {
            $modTime = filemtime($file);
            if ($modTime > $latestModTime) {
                $latestModTime = $modTime;
            }
            
            // Check if file was modified in the last 30 seconds
            if ($modTime > (time() - 30)) {
                $modifiedFiles[] = basename($file);
            }
        }
    }
    
    // Check if client's last check time is provided
    $clientLastCheck = isset($_GET['last_check']) ? intval($_GET['last_check']) : 0;
    
    // Determine if reload is needed
    $shouldReload = false;
    
    if ($clientLastCheck > 0 && $latestModTime > $clientLastCheck) {
        $shouldReload = true;
    } elseif (count($modifiedFiles) > 0) {
        $shouldReload = true;
    }
    
    // Force reload if specific parameter is set
    if (isset($_GET['force']) && $_GET['force'] === '1') {
        $shouldReload = true;
    }
    
    echo json_encode([
        'success' => true,
        'shouldReload' => $shouldReload,
        'latestModTime' => $latestModTime,
        'modifiedFiles' => $modifiedFiles,
        'timestamp' => time(),
        'message' => $shouldReload ? 'Updates detected' : 'No updates'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'shouldReload' => false,
        'error' => $e->getMessage()
    ]);
}
?>