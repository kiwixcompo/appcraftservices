<?php
// Force Chrome to bypass security cache by using different headers
header('Cache-Control: no-cache, no-store, must-revalidate, private, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');

// Clear any existing security policies
header('Clear-Site-Data: "cache", "cookies", "storage", "executionContexts"');

// Force Chrome to re-evaluate security
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\';');

// Add timestamp to force fresh evaluation
$timestamp = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Force Chrome Reload - App Craft Services</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .loading {
            font-size: 18px;
            color: #007bff;
            margin: 20px 0;
        }
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #007bff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Forcing Chrome Security Re-evaluation</h1>
        <div class="spinner"></div>
        <div class="loading">Clearing Chrome security cache...</div>
        <p>This page forces Chrome to re-evaluate the domain security.</p>
        <p><strong>Redirecting to main website in 3 seconds...</strong></p>
    </div>

    <script>
        // Force Chrome to clear any cached security decisions
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for(let registration of registrations) {
                    registration.unregister();
                }
            });
        }
        
        // Clear all storage
        try {
            localStorage.clear();
            sessionStorage.clear();
        } catch(e) {}
        
        // Clear any cached data
        if ('caches' in window) {
            caches.keys().then(function(names) {
                for (let name of names) {
                    caches.delete(name);
                }
            });
        }
        
        // Force redirect with cache-busting parameter
        setTimeout(function() {
            const timestamp = new Date().getTime();
            window.location.replace('/?cb=' + timestamp);
        }, 3000);
        
        console.log('Force reload executed at: <?php echo $timestamp; ?>');
    </script>
</body>
</html>