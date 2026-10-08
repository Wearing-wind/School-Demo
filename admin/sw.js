const CACHE_NAME = 'apex-admin-pwa-v1';
const ADMIN_ASSETS = [
    'dashboard.php',
    'login.php',
    'manage_notices.php',
    'manage_events.php',
    'manifest.json',
    '../assets/css/style.css',
    '../assets/images/logo.png',
    '../assets/icons/icon-192.png',
    '../assets/icons/icon-512.png'
];

self.addEventListener('install', (e) => {
    e.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ADMIN_ASSETS);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (e) => {
    e.respondWith(
        fetch(e.request).catch(() => caches.match(e.request))
    );
});
