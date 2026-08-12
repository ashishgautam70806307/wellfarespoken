const CACHE_NAME = 'wellfare-spoken-static-v166';
const STATIC_ASSETS = [
  './manifest.webmanifest',
  './assets/css/phase123-shell.min.css',
  './assets/css/wf-design-tokens.min.css',
  './assets/css/wf-components.min.css',
  './assets/css/phase137-visual-repair.min.css',
  './assets/css/phase146-admin-login.min.css',
  './assets/css/phase138-mobile-ux.min.css',
  './assets/css/phase139-mobile-learning.min.css',
  './assets/css/phase140-contact-page.min.css',
  './assets/css/phase141-learning-pages-mobile.min.css',
  './assets/css/phase142-interaction-fixes.min.css',
  './assets/css/phase143-practice-stability.min.css',
  './assets/css/phase143-roadmap-practice.min.css',
  './assets/css/phase145-student-test.min.css',
  './assets/css/phase146-weekly-test.min.css',
  './assets/css/phase147-student-accounts.min.css',
  './assets/css/phase149-admin-resilience.min.css',
  './assets/css/phase149-student-auth.min.css',
  './assets/css/phase150-security-ui.min.css',
  './assets/css/phase155-pwa-footer.min.css',
  './assets/css/phase158-test-results.min.css',
  './assets/css/phase159-admin-weekly-papers.min.css',
  './assets/css/phase166-admin-workflow.min.css',
  './assets/css/phase161-upcoming-performance.min.css',
  './assets/css/phase162-dashboard-performance.min.css',
  './assets/css/phase154-exam-mobile.min.css',
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
  './assets/css/phase133-controlled-ui.min.css',
  './assets/js/phase130-ui.js',
  './assets/js/phase133-controlled-ui.js',
  './assets/js/phase138-mobile-ux.js',
  './assets/js/phase139-mobile-learning.js',
  './assets/js/phase143-spoken-practice.js',
  './assets/js/phase143-roadmap-practice.js',
  './assets/js/phase146-weekly-test.js',
  './assets/js/phase149-admin-resilience.js',
  './assets/js/phase166-time12.js',
  './assets/js/phase149-password-toggle.js',
  './assets/js/phase158-test-results.js',
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
