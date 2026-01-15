// sw.js - Service Worker GORE Pasco
const CACHE_NAME = 'gore-patrimonio-v1';
const urlsToCache = [
  './',
  './css/estilos.css',
  './css/login.css',
  './css/ver_activo.css',
  './img/logo_gore.png',
  './img/fondo_login.jpg',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// 1. INSTALACIÓN: Guardamos lo estático
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

// 2. ACTIVACIÓN: Limpiamos cachés viejos
self.addEventListener('activate', event => {
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

// 3. INTERCEPTAR PETICIONES (Estrategia Híbrida)
self.addEventListener('fetch', event => {
  // Para archivos PHP (datos dinámicos), intentamos Red primero, si falla, error.
  if (event.request.url.indexOf('.php') > -1) {
    event.respondWith(fetch(event.request));
  } 
  // Para todo lo demás (CSS, Imágenes), buscamos en Caché primero
  else {
    event.respondWith(
      caches.match(event.request)
        .then(response => {
          return response || fetch(event.request);
        })
    );
  }
});