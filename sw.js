// Service Worker for App Craft Services - Cache Busting Aware
const CACHE_NAME = 'appcraft-v4-cache-bust';
const DYNAMIC_CACHE = 'appcraft-dynamic-v4';

// Development mode detection
const isDevelopment = self.location.hostname === 'localhost' || 
                     self.location.hostname === '127.0.0.1' || 
                     self.location.hostname.includes('192.168.');

const STATIC_ASSETS = [
    '/',
    '/index.html',
    '/assets/styles.css',
    '/assets/script.js',
    '/assets/cache-buster.js',
    '/assets/cache-status.css',
    '/assets/logo.png',
    '/assets/favicon.ico',
    '/contact/',
    '/services/',
    '/pricing/',
    '/process/'
];

// Install: Cache core files with cache busting awareness
self.addEventListener('install', (event) => {
    console.log('SW: Installing with cache busting support');
    self.skipWaiting();
    
    if (isDevelopment) {
        // In development, skip caching to avoid cache issues
        console.log('SW: Development mode - skipping cache');
        return;
    }
    
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
});

// Activate: Clean up ALL old caches aggressively
self.addEventListener('activate', (event) => {
    console.log('SW: Activating with aggressive cache cleanup');
    
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    // Delete ALL caches in development
                    if (isDevelopment || (key !== CACHE_NAME && key !== DYNAMIC_CACHE)) {
                        console.log('SW: Deleting cache:', key);
                        return caches.delete(key);
                    }
                })
            );
        })
    );
    return self.clients.claim();
});

// Fetch: Network First with Cache Busting Support
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);
    
    // Skip caching for:
    // - POST requests
    // - API calls
    // - Admin pages
    // - Cache busting URLs (with cb or v parameters)
    if (
        event.request.method === 'POST' ||
        url.pathname.includes('/api/') ||
        url.pathname.includes('/admin/') ||
        url.searchParams.has('cb') ||
        url.searchParams.has('v') ||
        url.searchParams.has('force') ||
        isDevelopment
    ) {
        // Always fetch fresh in these cases
        event.respondWith(fetch(event.request));
        return;
    }

    // For other requests, use network first strategy
    event.respondWith(
        fetch(event.request)
            .then((fetchRes) => {
                // Only cache successful responses
                if (!fetchRes || fetchRes.status !== 200 || fetchRes.type !== 'basic') {
                    return fetchRes;
                }
                
                // Clone response for caching
                const responseToCache = fetchRes.clone();
                
                // Cache the response
                caches.open(DYNAMIC_CACHE).then((cache) => {
                    cache.put(event.request, responseToCache);
                });
                
                return fetchRes;
            })
            .catch(() => {
                // Fallback to cache only if network fails
                return caches.match(event.request).then((response) => {
                    if (response) {
                        console.log('SW: Serving from cache:', event.request.url);
                        return response;
                    }
                    
                    // For navigation requests, fallback to index
                    if (event.request.mode === 'navigate') {
                        return caches.match('/');
                    }
                });
            })
    );
});

// Listen for cache clearing messages
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'CLEAR_CACHE') {
        console.log('SW: Received cache clear request');
        
        event.waitUntil(
            caches.keys().then((keys) => {
                return Promise.all(
                    keys.map((key) => {
                        console.log('SW: Clearing cache:', key);
                        return caches.delete(key);
                    })
                );
            }).then(() => {
                console.log('SW: All caches cleared');
                event.ports[0].postMessage({ success: true });
            })
        );
    }
    
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

// Handle updates
self.addEventListener('updatefound', () => {
    console.log('SW: Update found, installing new version');
});

console.log('SW: Service Worker loaded', {
    isDevelopment,
    cacheStrategy: isDevelopment ? 'disabled' : 'network-first'
});