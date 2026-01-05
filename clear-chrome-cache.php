<?php
// Clear Chrome security cache helper
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('Clear-Site-Data: "cache", "storage"');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

// Force Chrome to re-evaluate the site
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chrome Cache Clear - App Craft Services</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
</head>
<body>
    <script>
        // Clear Chrome's security cache and redirect to homepage
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for(let registration of registrations) {
                    registration.unregister();
                }
            });
        }
        
        // Clear all storage
        if ('caches' in window) {
            caches.keys().then(function(names) {
                for (let name of names) {
                    caches.delete(name);
                }
            });
        }
        
        // Clear local storage
        try {
            localStorage.clear();
            sessionStorage.clear();
        } catch(e) {}
        
        // Redirect to homepage after clearing
        setTimeout(function() {
            window.location.href = '/';
        }, 1000);
    </script>
    
    <div style="text-align: center; padding: 50px; font-family: Arial, sans-serif;">
        <h1>Clearing Chrome Cache...</h1>
        <p>Redirecting to homepage in a moment...</p>
        <p><a href="/">Click here if not redirected automatically</a></p>
    </div>
</body>
</html>