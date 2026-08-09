const CACHE_NAME = 'instataker_pwa_v2';
const ASSETS_TO_CACHE = [
    './',
    './index.php',
    './css/styles.css',
    './js/main.js',
    './js/script.js',
    './manifest.json'
];

self.addEventListener('install', event => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.map(key => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    // Only cache GET requests for static assets, skip API requests
    if (event.request.method !== 'GET' || event.request.url.includes('instagram.php') || event.request.url.includes('proxy.php')) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then(cachedResponse => {
            return cachedResponse || fetch(event.request).catch(() => {
                return caches.match('./index.php');
            });
        })
    );
});
