const CACHE_NAME = 'euro-taxi-cache-v1';
const assetsToCache = [
  '/',
  '/assets/app.css',
  '/assets/app.js',
  '/assets/tailwind.min.js',
  '/assets/lucide.min.js',
  '/assets/chart.min.js',
  '/assets/chartjs-plugin-datalabels.min.js',
  '/favicon_euro_transparent.png',
  '/image/logo.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(assetsToCache))
  );
});

self.addEventListener('fetch', event => {
  // Skip cross-origin requests and API calls
  if (!event.request.url.startsWith(self.location.origin) || event.request.url.includes('/api/')) {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then(response => response || fetch(event.request))
  );
});
