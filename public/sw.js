const CACHE_NAME = 'euro-taxi-cache-v3';
const assetsToCache = [
  '/',
  '/manifest.json',
  '/css/app.css',
  '/js/app.js'
];

self.addEventListener('install', event => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(assetsToCache))
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(clients.claim());
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

self.addEventListener('fetch', event => {
  // Try network first, then fallback to cache for better freshness
  event.respondWith(
    fetch(event.request).catch(() => caches.match(event.request))
  );
});

