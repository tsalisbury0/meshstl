const CACHE_NAME = 'meshstl';

// Install event – cache essential files
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
  );
  self.skipWaiting(); // Activate service worker immediately
});

// Activate event – remove old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
      )
    )
  );
  self.clients.claim();
});

// Fetch event – handles requests differently based on type
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);

  if (url.pathname.endsWith('.php')) {
    // Network-first strategy for PHP files
    event.respondWith(
      fetch(event.request)
        .then(networkResponse => {
          return caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, networkResponse.clone());
            return networkResponse;
          });
        })
        .catch(() => caches.match(event.request))
    );

  } else if (url.pathname.endsWith('data.json')) {
    // Always fetch fresh data.json with fallback for offline
    event.respondWith(
      fetch(event.request)
        .then(response => response)
        .catch(() => {
          return new Response('[]', {
            headers: { 'Content-Type': 'application/json' }
          });
        })
    );

  } else {
    // Cache-first strategy for all other requests
    event.respondWith(
      caches.match(event.request).then(response =>
        response || fetch(event.request)
      )
    );
  }
});
