const CACHE_NAME = 'meshstl-empty-v1';

// Install event - Skip waiting to activate immediately
self.addEventListener('install', event => {
  self.skipWaiting();
});

// Activate event - Forcefully delete all existing caches from visitors' browsers
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.map(key => {
          console.log('Clearing old PWA cache:', key);
          return caches.delete(key);
        })
      );
    })
  );
  self.clients.claim(); // Take control of open tabs immediately
});

// Fetch event - Required for PWA installation, but passes requests directly to the network
self.addEventListener('fetch', event => {
  // Pure pass-through: No caching, no storage, always live network
  event.respondWith(fetch(event.request));
});
