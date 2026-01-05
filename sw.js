// Service Worker for App Craft Services - Network First Strategy
// Fixes "stale content" issues by prioritizing the network over the cache

const CACHE_NAME = 'appcraft-v3-network-first'; // Incremented version
const DYNAMIC_CACHE = 'appcraft-dynamic-v3';

// Core assets to cache immediately
const STATIC_ASSETS = [
    '/',
    '/index.html',
    '/assets/styles.css',
    '/assets/script.js',
    '/assets/logo.png',
    '/assets/favicon.ico',
    '/contact/',
    '/services/',
    '/pricing/',
    '/process/'
];

// Install: Cache core files
self.addEventListener('install', (event) => {
    // skipWaiting forces this new SW to become active immediately
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
});

// Activate: Clean up ALL old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    // Delete any cache that isn't the current V3 cache
                    if (key !== CACHE_NAME && key !== DYNAMIC_CACHE) {
                        console.log('[Service Worker] Removing old cache:', key);
                        return caches.delete(key);
                    }
                })
            );
        })
    );
    // Take control of all clients immediately
    return self.clients.claim();
});

// Fetch: Network First, Fallback to Cache
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // 1. Ignore API calls, Admin panel, and POST requests
    if (
        event.request.method === 'POST' ||
        url.pathname.includes('/api/') ||
        url.pathname.includes('/admin/')
    ) {
        return;
    }

    // 2. Try Network first
    event.respondWith(
        fetch(event.request)
            .then((fetchRes) => {
                // Check if valid response
                if (!fetchRes || fetchRes.status !== 200 || fetchRes.type !== 'basic') {
                    return fetchRes;
                }

                // Clone and cache the fresh version
                const responseToCache = fetchRes.clone();
                caches.open(DYNAMIC_CACHE).then((cache) => {
                    cache.put(event.request, responseToCache);
                });

                return fetchRes;
            })
            .catch(() => {
                // 3. If offline/network fails, use Cache
                return caches.match(event.request).then((response) => {
                    if (response) {
                        return response;
                    }
                    // Optional: Return a custom offline page for navigation
                    if (event.request.mode === 'navigate') {
                        return caches.match('/');
                    }
                });
            })
    );
});