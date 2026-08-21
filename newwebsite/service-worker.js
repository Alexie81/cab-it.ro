const CACHE_VERSION = "cabit-pwa-20260821-27";
const BASE_URL = new URL("./", self.registration.scope);
const OFFLINE_URL = new URL("offline.html", BASE_URL).href;
const HOME_URL = BASE_URL.href;
const PRECACHE_URLS = [
  HOME_URL,
  OFFLINE_URL,
  new URL("assets/css/cabit-home.min.css?v=20260819-5", BASE_URL).href,
  new URL("assets/js/cabit-next.min.js?v=20260821-26", BASE_URL).href,
  new URL("assets/js/cabit-assistant-engine.min.js?v=20260821-26", BASE_URL).href,
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
      caches.match(request, { ignoreSearch: true }).then((cached) => {
        const refreshed = fetch(request).then((response) => {
          if (response.ok) {
            const copy = response.clone();
            event.waitUntil(caches.open(CACHE_VERSION).then((cache) => cache.put(request, copy)));
          }
          return response;
        }).catch(async () => cached || (await caches.match(HOME_URL)) || caches.match(OFFLINE_URL));
        return cached || refreshed;
      })
    );
    return;
  }

  const requestUrl = new URL(request.url);
  if (requestUrl.origin !== self.location.origin || !["style", "script", "font", "image"].includes(request.destination)) return;

  const sharedScript = requestUrl.pathname.match(/\/(cabit-(?:next|assistant-engine)\.min\.js)$/);
  if (sharedScript) {
    const latestUrl = new URL("assets/js/" + sharedScript[1] + "?v=20260821-26", BASE_URL).href;
    event.respondWith(
      caches.open(CACHE_VERSION).then(async (cache) => {
        const cached = await cache.match(latestUrl);
        if (cached) return cached;
        const response = await fetch(latestUrl);
        if (response.ok && response.type === "basic") await cache.put(latestUrl, response.clone());
        return response;
      })
    );
    return;
  }

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
