// Service Worker for App Craft Services
// Provides caching for better mobile performance

const CACHE_NAME = 'app-craft-services-v1';
const STATIC_CACHE_URLS = [
    '/appcraftservices/',
    '/appcraftservices/index.html',
    '/appcraftservices/assets/styles.css',
    '/appcraftservices/assets/script.js',
    '/appcraftservices/assets/logo.png',
    '/appcraftservices/assets/favicon.ico',
    '/appcraftservices/contact/',
    '/appcraftservices/services/',
    '/appcraftservices/pricing/',
    '/appcraftservices/process/'
];

// Install event - cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Caching static assets');
                return cache.addAll(STATIC_CACHE_URLS);
            })
            .catch(error => {
                console.log('Cache install failed:', error);
            })
    );
    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', event => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Skip external requests
    if (!event.request.url.startsWith(self.location.origin)) {
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then(response => {
                // Return cached version if available
                if (response) {
                    return response;
                }

                // Clone the request for network fetch
                const fetchRequest = event.request.clone();

                return fetch(fetchRequest).then(response => {
                    // Check if valid response
                    if (!response || response.status !== 200 || response.type !== 'basic') {
                        return response;
                    }

                    // Clone the response for caching
                    const responseToCache = response.clone();

                    // Cache the response for future use
                    caches.open(CACHE_NAME)
                        .then(cache => {
                            // Only cache GET requests for same origin
                            if (event.request.method === 'GET' && 
                                event.request.url.startsWith(self.location.origin)) {
                                cache.put(event.request, responseToCache);
                            }
                        });

                    return response;
                }).catch(error => {
                    console.log('Fetch failed:', error);
                    
                    // Return offline page for navigation requests
                    if (event.request.mode === 'navigate') {
                        return caches.match('/appcraftservices/');
                    }
                    
                    throw error;
                });
            })
    );
});

// Background sync for form submissions (if supported)
self.addEventListener('sync', event => {
    if (event.tag === 'contact-form-sync') {
        event.waitUntil(syncContactForm());
    }
});

async function syncContactForm() {
    try {
        // Get pending form submissions from IndexedDB
        const pendingForms = await getPendingFormSubmissions();
        
        for (const form of pendingForms) {
            try {
                const response = await fetch('/appcraftservices/api/contact.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(form.data)
                });
                
                if (response.ok) {
                    // Remove from pending submissions
                    await removePendingFormSubmission(form.id);
                }
            } catch (error) {
                console.log('Form sync failed:', error);
            }
        }
    } catch (error) {
        console.log('Background sync failed:', error);
    }
}

// Helper functions for IndexedDB operations
async function getPendingFormSubmissions() {
    // Simplified implementation - in production, use IndexedDB
    return [];
}

async function removePendingFormSubmission(id) {
    // Simplified implementation - in production, use IndexedDB
    return true;
}

// Push notification handling (for future use)
self.addEventListener('push', event => {
    if (event.data) {
        const data = event.data.json();
        const options = {
            body: data.body,
            icon: '/appcraftservices/assets/logo.png',
            badge: '/appcraftservices/assets/favicon.ico',
            vibrate: [100, 50, 100],
            data: {
                dateOfArrival: Date.now(),
                primaryKey: data.primaryKey
            },
            actions: [
                {
                    action: 'explore',
                    title: 'View Details',
                    icon: '/appcraftservices/assets/favicon.ico'
                },
                {
                    action: 'close',
                    title: 'Close',
                    icon: '/appcraftservices/assets/favicon.ico'
                }
            ]
        };
        
        event.waitUntil(
            self.registration.showNotification(data.title, options)
        );
    }
});

// Notification click handling
self.addEventListener('notificationclick', event => {
    event.notification.close();
    
    if (event.action === 'explore') {
        event.waitUntil(
            clients.openWindow('/appcraftservices/')
        );
    }
});