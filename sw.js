// Service Worker for App Craft Services - Network First Strategy
const CACHE_NAME = 'appcraft-v3-network-first'; 
const DYNAMIC_CACHE = 'appcraft-dynamic-v3';

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
                    if (key !== CACHE_NAME && key !== DYNAMIC_CACHE) {
                        return caches.delete(key);
                    }
                })
            );
        })
    );
    return self.clients.claim();
});

// Fetch: Network First, Fallback to Cache
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    if (
        event.request.method === 'POST' ||
        url.pathname.includes('/api/') ||
        url.pathname.includes('/admin/')
    ) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((fetchRes) => {
                if (!fetchRes || fetchRes.status !== 200 || fetchRes.type !== 'basic') {
                    return fetchRes;
                }
                const responseToCache = fetchRes.clone();
                caches.open(DYNAMIC_CACHE).then((cache) => {
                    cache.put(event.request, responseToCache);
                });
                return fetchRes;
            })
            .catch(() => {
                return caches.match(event.request).then((response) => {
                    if (response) return response;
                    if (event.request.mode === 'navigate') return caches.match('/');
                });
            })
    );
});