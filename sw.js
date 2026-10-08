const CACHE_NAME = 'iebs-portal-v1';
const ASSETS_TO_CACHE = [
    './',
    'index.php',
    'notices.php',
    'results.php',
    'contact.php',
    'assets/css/style.css',
    'assets/js/main.js',
    'assets/images/logo.png',
    'assets/icons/icon-192.png',
    'assets/icons/icon-512.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    // Network-first strategy for dynamic PHP content, falling back to cache
    if (event.request.mode === 'navigate' || event.request.url.endsWith('.php')) {
        event.respondWith(
            fetch(event.request).catch(() => {
                return caches.match(event.request).then(response => {
                    return response || caches.match('index.php');
                });
            })
        );
    } else {
        // Cache-first for static assets
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                return fetch(event.request);
            })
        );
    }
});
