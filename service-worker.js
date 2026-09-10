/**
 * MeshSTL - PWA Service Worker (Zero-Cache Pass-Through)
 * Keeps the application installable as a PWA while ensuring 
 * all assets, data layers, and maps are served live from the network.
 */

// 1. Install event - Immediately skip the waiting phase to force activation
self.addEventListener('install', event => {
  console.log('[MeshSTL SW] Installing new worker...');
  self.skipWaiting();
});

// 2. Activate event - Clear out all existing caches across visitors' browsers
self.addEventListener('activate', event => {
  console.log('[MeshSTL SW] Activating worker and purging stale caches...');
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.map(key => {
          console.log(`[MeshSTL SW] Deleting cache storage: ${key}`);
          return caches.delete(key);
        })
      );
    })
  );
  // Claim all active browser tabs instantly to apply the zero-cache rules
  self.clients.claim();
});

// 3. Fetch event - Required for PWA status, handles all traffic as network-only
self.addEventListener('fetch', event => {
  // Pure pass-through: Bypasses browser Cache Storage completely to pull fresh data
  event.respondWith(
    fetch(event.request).catch(error => {
      console.warn(`[MeshSTL SW] Fetch failed for ${event.request.url}. Device might be offline.`, error);
      // Let the browser handle the offline state naturally
      return new Response('Network error occurred. Please check your connection.', {
        status: 503,
        statusText: 'Service Unavailable',
        headers: new Headers({ 'Content-Type': 'text/plain' })
      });
    })
  );
});
