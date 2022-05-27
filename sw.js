const cacheName = 'pwa_v1';
const includeToCache = [
  '/',
  '/index.php',
  '/image/instagram.svg',
  '/css/styles.css',
  '/js/main.js'
];

/* Start the service worker and cache all of the app's content */
self.addEventListener('install', e => {

  self.skipWaiting();

  e.waitUntil(
    caches.open(cacheName).then(cache => {
      return cache.addAll(includeToCache);
    })
  );
});

/* Serve cached content when offline */
self.addEventListener('fetch', e => {
  e.respondWith(
    caches.match(e.request).then(response => {
      return response || fetch(e.request);
    })
  );
});

self.addEventListener('activate', event => {
  // delete any caches that aren't in cacheName
  // which will get rid of version
  event.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys.map(key => {
        if (cacheName !== key) {
          return caches.delete(key);
        }
      })
    )).then(() => {
      console.log(cacheName + ' now ready to handle fetches!');
    })
  );
});
