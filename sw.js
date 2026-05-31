const CACHE_NAME = 'eurotaxi-cache-v2.8';
const FALLBACK_URL = '/offline-fallback';

// Assets required for the core app shell to load the UI offline
const STATIC_ASSETS = [
    '/',
    '/offline-fallback',
    '/assets/app.css',
    '/assets/app.js',
    '/assets/tailwind.min.js',
    '/assets/lucide.min.js',
    '/assets/inter/inter.css',
    '/favicon_euro_transparent.png'
];

// 1. Install Event - Pre-cache the App Shell layout
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            console.log('[Service Worker] Pre-caching Core App Shell...');
            return cache.addAll(STATIC_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// 2. Activate Event - Clean up legacy caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.map(key => {
                    if (key !== CACHE_NAME) {
                        console.log('[Service Worker] Removing stale cache:', key);
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// 3. Fetch Event Interceptor - Network-first for dynamic content, Cache-first for core assets
self.addEventListener('fetch', event => {
    // Only intercept standard GET requests
    if (event.request.method !== 'GET') return;

    // Filter out non-http/https requests (chrome extensions, local assets, websocket upgrades)
    if (!event.request.url.startsWith('http')) return;

    const url = new URL(event.request.url);

    // Bypass caching for live real-time notification polling endpoints
    if (url.pathname.includes('/web-notifications/')) {
        return;
    }

    // Bypass caching for dynamic administrative panels and dynamic AJAX detail cards
    // This guarantees that any changes to blade views or dynamic records load immediately and live!
    if (url.pathname.includes('/units') || 
        url.pathname.includes('/details') || 
        url.pathname.includes('/history') || 
        url.pathname.includes('/dashboard') || 
        url.pathname.includes('/drivers') || 
        url.pathname.includes('/rules') || 
        url.pathname.includes('/notifications')) {
        
        // Serve from network directly (completely bypass any cached page state)
        event.respondWith(
            fetch(event.request).catch(err => {
                console.warn('[Service Worker] Network failed for dynamic view, sending fallback:', err);
                if (event.request.mode === 'navigate') {
                    return caches.match(FALLBACK_URL);
                }
                throw err;
            })
        );
        return;
    }

    event.respondWith(
        caches.open(CACHE_NAME).then(cache => {
            return cache.match(event.request).then(cachedResponse => {
                // Return cached response instantly if we have it (Stale-While-Revalidate)
                if (cachedResponse) {
                    fetch(event.request).then(networkResponse => {
                        if (networkResponse.status === 200) {
                            cache.put(event.request, networkResponse.clone());
                        }
                    }).catch(err => console.log('Background fetch sync failed (offline):', err));
                    
                    return cachedResponse;
                }

                // If not in cache, retrieve from network
                return fetch(event.request).then(networkResponse => {
                    if (networkResponse.status === 200) {
                        cache.put(event.request, networkResponse.clone());
                    }
                    return networkResponse;
                }).catch(err => {
                    console.warn('[Service Worker] Network fetch failed:', err);
                    
                    // Return offline fallback page for page-level navigation
                    if (event.request.mode === 'navigate') {
                        return cache.match(FALLBACK_URL);
                    }
                    
                    // Propagate the actual network exception so AJAX requests trigger natural catch blocks
                    throw err;
                });
            });
        })
    );
});
