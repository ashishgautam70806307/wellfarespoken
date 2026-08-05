const CACHE_NAME = 'wellfare-spoken-static-v131';
const STATIC_ASSETS = [
  './manifest.webmanifest',
  './assets/css/phase123-shell.min.css',
  './assets/css/phase123-ui-core.min.css',
  './assets/css/phase130-design-system.min.css',
  './assets/css/phase130-public-pages.min.css',
  './assets/css/phase126-home.min.css',
  './assets/css/phase126-roadmap.min.css',
  './assets/css/phase130-admission.min.css',
  './assets/css/phase130-materials.min.css',
  './assets/css/phase130-roadmap-lesson.min.css',
  './assets/css/phase130-weekly-test.min.css',
  './assets/css/phase130-online-class.min.css',
  './assets/js/phase130-ui.js',
  './assets/js/phase130-weekly-test.js',
  './assets/js/phase126-home.js',
  './assets/js/main.js',
  './assets/uploads/banners/home-banner-speaking-desktop.webp',
  './assets/uploads/banners/home-banner-speaking-mobile.webp',
  './assets/uploads/banners/home-banner-online-class-desktop.webp',
  './assets/uploads/banners/home-banner-online-class-mobile.webp',
  './assets/uploads/brand/wf-pwa-icon-192.png',
  './assets/uploads/brand/wf-pwa-icon-512.png'
];

const NEVER_CACHE_PATHS = [
  '/admin/', 'student-', 'weekly-result.php', 'weekly-exam-room.php',
  'weekly-test-api.php', '-api.php', 'logout.php', 'student-auth.php'
];

function isStaticAsset(requestUrl) {
  if (requestUrl.origin !== self.location.origin) return false;
  const path = requestUrl.pathname.toLowerCase();
  if (NEVER_CACHE_PATHS.some(part => path.includes(part))) return false;
  return /\.(?:css|js|png|jpe?g|gif|webp|ico|woff2?|ttf|webmanifest)$/.test(path);
}

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => Promise.allSettled(STATIC_ASSETS.map(asset => cache.add(asset))))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys()
      .then(keys => Promise.all(keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return;
  const url = new URL(event.request.url);

  // PHP, student, admin and API responses remain network-only.
  if (!isStaticAsset(url)) {
    event.respondWith(fetch(event.request));
    return;
  }

  const isCodeAsset = /\.(?:css|js)$/.test(url.pathname.toLowerCase());
  if (isCodeAsset) {
    // Network-first keeps versioned CSS/JS fresh and prevents an old design from
    // being returned after a deployment. Exact request matching preserves ?v=.
    event.respondWith(
      fetch(event.request).then(response => {
        if (response && response.ok && response.type === 'basic') {
          caches.open(CACHE_NAME).then(cache => cache.put(event.request, response.clone())).catch(() => {});
        }
        return response;
      }).catch(() => caches.match(event.request))
    );
    return;
  }

  event.respondWith(
    caches.match(event.request).then(cached => cached || fetch(event.request).then(response => {
      if (response && response.ok && response.type === 'basic') {
        caches.open(CACHE_NAME).then(cache => cache.put(event.request, response.clone())).catch(() => {});
      }
      return response;
    }))
  );
});
