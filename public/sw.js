// public/sw.js
const CACHE_NAME = 'condoagenda-v1';
const STATIC_ASSETS = [
  './index.php?page=login',
  './css/style.css',
  './js/app.js',
  './manifest.json',
  './icons/icon-192.svg',
  './icons/icon-512.svg',
];

// Install: pre-cache static assets
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS);
    }).then(() => self.skipWaiting())
  );
});

// Activate: clean old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
      )
    ).then(() => self.clients.claim())
  );
});

// Fetch: Network-first para PHP, Cache-first para assets estáticos
self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);

  // Ignora requisições não-GET e de outras origens
  if (event.request.method !== 'GET') return;
  if (!url.pathname) return;

  const isStatic = /\.(css|js|svg|png|jpg|ico|woff2?)$/.test(url.pathname);

  if (isStatic) {
    // Cache-first para arquivos estáticos
    event.respondWith(
      caches.match(event.request).then((cached) => {
        if (cached) return cached;
        return fetch(event.request).then((response) => {
          if (response.ok) {
            const clone = response.clone();
            caches.open(CACHE_NAME).then(c => c.put(event.request, clone));
          }
          return response;
        });
      })
    );
  } else {
    // Network-first para PHP (dados dinâmicos)
    event.respondWith(
      fetch(event.request)
        .then((response) => {
          // Cacheia a página de login para offline
          if (event.request.url.includes('page=login') && response.ok) {
            const clone = response.clone();
            caches.open(CACHE_NAME).then(c => c.put(event.request, clone));
          }
          return response;
        })
        .catch(() => {
          // Offline: tenta retornar cache
          return caches.match(event.request)
            .then(cached => cached || caches.match('./index.php?page=login'));
        })
    );
  }
});

// Recebe mensagens do cliente
self.addEventListener('message', (event) => {
  if (event.data === 'skipWaiting') self.skipWaiting();
});
