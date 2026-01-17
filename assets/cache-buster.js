/**
 * Cache Busting Utility for App Craft Services
 * Automatically handles cache invalidation for development and production
 */

(function() {
    'use strict';
    
    // Cache busting configuration
    const CACHE_CONFIG = {
        // Enable aggressive cache busting in development
        isDevelopment: window.location.hostname === 'localhost' || 
                      window.location.hostname === '127.0.0.1' || 
                      window.location.hostname.includes('192.168.'),
        
        // Version timestamp (updated automatically)
        version: Date.now(),
        
        // Files to cache bust
        bustableExtensions: ['css', 'js', 'json'],
        
        // Cache bust interval (in milliseconds)
        bustInterval: 30000 // 30 seconds for development
    };
    
    /**
     * Add cache busting parameters to URLs
     */
    function addCacheBuster(url, forceReload = false) {
        if (!url) return url;
        
        const separator = url.includes('?') ? '&' : '?';
        const timestamp = forceReload ? Date.now() : CACHE_CONFIG.version;
        
        // Add cache busting parameter
        return `${url}${separator}v=${timestamp}&cb=${Math.random().toString(36).substr(2, 9)}`;
    }
    
    /**
     * Reload CSS files with cache busting
     */
    function reloadCSS() {
        const links = document.querySelectorAll('link[rel="stylesheet"]');
        links.forEach(link => {
            const href = link.href.split('?')[0]; // Remove existing parameters
            link.href = addCacheBuster(href, true);
        });
    }
    
    /**
     * Reload JavaScript files with cache busting
     */
    function reloadJS() {
        const scripts = document.querySelectorAll('script[src]');
        scripts.forEach(script => {
            if (script.src && !script.src.includes('cdn.') && !script.src.includes('googleapis')) {
                const newScript = document.createElement('script');
                const src = script.src.split('?')[0]; // Remove existing parameters
                newScript.src = addCacheBuster(src, true);
                newScript.async = script.async;
                newScript.defer = script.defer;
                
                // Replace old script
                script.parentNode.insertBefore(newScript, script);
                script.parentNode.removeChild(script);
            }
        });
    }
    
    /**
     * Force reload the entire page with cache busting
     */
    function forceReload() {
        const currentUrl = window.location.href.split('?')[0];
        const newUrl = addCacheBuster(currentUrl, true);
        window.location.href = newUrl;
    }
    
    /**
     * Check if page needs to be reloaded due to updates
     */
    async function checkForUpdates() {
        if (!CACHE_CONFIG.isDevelopment) return;
        
        try {
            // Determine the correct path based on current location
            const isAdminArea = window.location.pathname.includes('/admin/');
            const apiPath = isAdminArea ? '../api/check_updates.php' : '/api/check_updates.php';
            
            const response = await fetch(apiPath, {
                method: 'GET',
                cache: 'no-cache',
                headers: {
                    'Cache-Control': 'no-cache',
                    'Pragma': 'no-cache'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.shouldReload) {
                    console.log('Updates detected, reloading page...');
                    forceReload();
                }
            }
        } catch (error) {
            console.log('Update check failed:', error);
        }
    }
    
    /**
     * Add cache busting to all fetch requests
     */
    function interceptFetch() {
        const originalFetch = window.fetch;
        
        window.fetch = function(url, options = {}) {
            // Add cache busting to API calls and local resources
            if (typeof url === 'string' && 
                (url.startsWith('/') || url.includes(window.location.hostname))) {
                
                url = addCacheBuster(url);
                
                // Ensure no-cache headers
                options.headers = {
                    'Cache-Control': 'no-cache',
                    'Pragma': 'no-cache',
                    ...options.headers
                };
            }
            
            return originalFetch.call(this, url, options);
        };
    }
    
    /**
     * Add cache busting to XMLHttpRequest
     */
    function interceptXHR() {
        const originalOpen = XMLHttpRequest.prototype.open;
        
        XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
            if (typeof url === 'string' && 
                (url.startsWith('/') || url.includes(window.location.hostname))) {
                url = addCacheBuster(url);
            }
            
            return originalOpen.call(this, method, url, async, user, password);
        };
    }
    
    /**
     * Add meta tags to prevent caching
     */
    function addNoCacheMetaTags() {
        const metaTags = [
            { 'http-equiv': 'Cache-Control', content: 'no-cache, no-store, must-revalidate' },
            { 'http-equiv': 'Pragma', content: 'no-cache' },
            { 'http-equiv': 'Expires', content: '0' },
            { name: 'cache-control', content: 'no-cache' },
            { name: 'expires', content: '0' },
            { name: 'pragma', content: 'no-cache' }
        ];
        
        metaTags.forEach(attrs => {
            const existing = document.querySelector(`meta[${Object.keys(attrs)[0]}="${Object.values(attrs)[0]}"]`);
            if (!existing) {
                const meta = document.createElement('meta');
                Object.keys(attrs).forEach(key => {
                    meta.setAttribute(key, attrs[key]);
                });
                document.head.appendChild(meta);
            }
        });
    }
    
    /**
     * Initialize cache busting system
     */
    function init() {
        console.log('Cache Buster initialized', {
            isDevelopment: CACHE_CONFIG.isDevelopment,
            version: CACHE_CONFIG.version
        });
        
        // Add no-cache meta tags
        addNoCacheMetaTags();
        
        // Intercept network requests
        interceptFetch();
        interceptXHR();
        
        // Development-specific features
        if (CACHE_CONFIG.isDevelopment) {
            // Check for updates periodically
            setInterval(checkForUpdates, CACHE_CONFIG.bustInterval);
            
            // Add keyboard shortcut for manual reload (Ctrl+Shift+R)
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.shiftKey && e.key === 'R') {
                    e.preventDefault();
                    console.log('Manual cache bust triggered');
                    forceReload();
                }
            });
            
            // Add visual indicator for cache busting
            const indicator = document.createElement('div');
            indicator.id = 'cache-buster-indicator';
            indicator.style.cssText = `
                position: fixed;
                top: 10px;
                right: 10px;
                background: #4CAF50;
                color: white;
                padding: 5px 10px;
                border-radius: 3px;
                font-size: 12px;
                z-index: 10000;
                opacity: 0.7;
                font-family: monospace;
                display: none;
            `;
            indicator.textContent = 'Cache Buster Active';
            document.body.appendChild(indicator);
            
            // Show indicator briefly on load
            setTimeout(() => {
                indicator.style.display = 'block';
                setTimeout(() => {
                    indicator.style.display = 'none';
                }, 2000);
            }, 1000);
        }
        
        // Global cache busting functions
        window.cacheBuster = {
            reload: forceReload,
            reloadCSS: reloadCSS,
            reloadJS: reloadJS,
            checkUpdates: checkForUpdates,
            version: CACHE_CONFIG.version
        };
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();