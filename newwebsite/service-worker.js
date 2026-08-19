const CACHE_VERSION = "cabit-pwa-20260819-13";
const BASE_URL = new URL("./", self.registration.scope);
const OFFLINE_URL = new URL("offline.html", BASE_URL).href;
const HOME_URL = BASE_URL.href;
const PRECACHE_URLS = [
  HOME_URL,
  OFFLINE_URL,
  new URL("assets/css/cabit-home.min.css?v=20260819-5", BASE_URL).href,
  new URL("assets/css/cabit-next.min.css?v=20260819-16", BASE_URL).href,
  new URL("assets/js/cabit-next.min.js?v=20260819-20", BASE_URL).href,
  new URL("assets/img/brand/cab-it-header-symbol-clean.webp", BASE_URL).href,
  new URL("assets/img/brand/cab-it-c-symbol-app-v7.png", BASE_URL).href
];

self.addEventListener("install", (event) => {
  event.waitUntil(caches.open(CACHE_VERSION).then((cache) => cache.addAll(PRECACHE_URLS)).then(() => self.skipWaiting()));
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((key) => key.startsWith("cabit-pwa-") && key !== CACHE_VERSION).map((key) => caches.delete(key))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener("fetch", (event) => {
  const request = event.request;
  if (request.method !== "GET") return;

  if (request.mode === "navigate") {
    event.respondWith(
      fetch(request)
        .then((response) => {
          if (response.ok) {
            const copy = response.clone();
            event.waitUntil(caches.open(CACHE_VERSION).then((cache) => cache.put(request, copy)));
          }
          return response;
        })
        .catch(async () => {
          return (await caches.match(request, { ignoreSearch: true })) || (await caches.match(HOME_URL)) || caches.match(OFFLINE_URL);
        })
    );
    return;
  }

  const requestUrl = new URL(request.url);
  if (requestUrl.origin !== self.location.origin || !["style", "script", "font", "image"].includes(request.destination)) return;

  event.respondWith(
    caches.match(request).then((cached) => {
      const refreshed = fetch(request).then((response) => {
        if (response.ok && response.type === "basic") {
          const copy = response.clone();
          event.waitUntil(caches.open(CACHE_VERSION).then((cache) => cache.put(request, copy)));
        }
        return response;
      }).catch(() => cached);
      return cached || refreshed;
    })
  );
});
