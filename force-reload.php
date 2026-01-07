<?php
// Enhanced Force Reload - Clear all caches and force fresh load
header('Cache-Control: no-cache, no-store, must-revalidate, private, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');

// Clear all cached data
header('Clear-Site-Data: "cache", "cookies", "storage", "executionContexts"');

// Force Chrome to re-evaluate security
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\';');

// Add timestamp to force fresh evaluation
$timestamp = time();
$randomId = uniqid();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cache Cleared - App Craft Services</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="cache-control" content="no-cache">
    <meta name="expires" content="0">
    <meta name="pragma" content="no-cache">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 700px;
            margin: 50px auto;
            padding: 20px;
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
        }
        .loading {
            font-size: 18px;
            color: #007bff;
            margin: 20px 0;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #2196f3;
        }
        .countdown {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Cache Successfully Cleared!</h1>
        <div class="spinner"></div>
        <div class="loading">All caches have been cleared and invalidated</div>
        
        <div class="info">
            <h3>✅ What was cleared:</h3>
            <ul style="text-align: left; display: inline-block;">
                <li>Browser cache</li>
                <li>Local storage</li>
                <li>Session storage</li>
                <li>Service workers</li>
                <li>HTTP cache</li>
                <li>Security cache</li>
            </ul>
        </div>
        
        <div class="success">
            <p>Your changes should now be visible immediately!</p>
        </div>
        
        <p>Redirecting to main website in <span class="countdown" id="countdown">3</span> seconds...</p>
        
        <button onclick="redirectNow()" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-top: 10px;">
            Go Now
        </button>
    </div>

    <script>
        console.log('🚀 Enhanced Force Reload executed at: <?php echo $timestamp; ?>');
        console.log('🔧 Random ID: <?php echo $randomId; ?>');
        
        // Comprehensive cache clearing
        async function clearAllCaches() {
            try {
                // Clear service workers
                if ('serviceWorker' in navigator) {
                    const registrations = await navigator.serviceWorker.getRegistrations();
                    for(let registration of registrations) {
                        await registration.unregister();
                        console.log('✅ Service worker unregistered');
                    }
                }
                
                // Clear all storage
                try {
                    localStorage.clear();
                    sessionStorage.clear();
                    console.log('✅ Storage cleared');
                } catch(e) {
                    console.log('⚠️ Storage clear failed:', e);
                }
                
                // Clear cache API
                if ('caches' in window) {
                    const cacheNames = await caches.keys();
                    for (let name of cacheNames) {
                        await caches.delete(name);
                        console.log('✅ Cache deleted:', name);
                    }
                }
                
                // Clear IndexedDB
                if ('indexedDB' in window) {
                    try {
                        const databases = await indexedDB.databases();
                        for (let db of databases) {
                            indexedDB.deleteDatabase(db.name);
                        }
                        console.log('✅ IndexedDB cleared');
                    } catch(e) {
                        console.log('⚠️ IndexedDB clear failed:', e);
                    }
                }
                
                console.log('🎉 All caches cleared successfully!');
                
            } catch (error) {
                console.error('❌ Cache clearing error:', error);
            }
        }
        
        // Countdown timer
        let countdown = 3;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                redirectNow();
            }
        }, 1000);
        
        // Redirect function
        function redirectNow() {
            const timestamp = new Date().getTime();
            const randomParam = Math.random().toString(36).substr(2, 9);
            const targetUrl = `/?cb=${timestamp}&r=${randomParam}&cleared=1`;
            
            console.log('🔄 Redirecting to:', targetUrl);
            window.location.replace(targetUrl);
        }
        
        // Execute cache clearing
        clearAllCaches();
        
        // Prevent back button
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };
    </script>
</body>
</html>