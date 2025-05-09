const CACHE_NAME = "dynamic-schedule-cache-v1";
const urlsToCache = [
  "/",
  "/index.php",
  "/style.css",
  "/script.js",
  "/ntilogo.svg",
  "/manifest.json",
  "/admin/class_schedules/EE22.csv",
  "/admin/class_schedules/EE23.csv",
  "/admin/class_schedules/EE24.csv",
  "/admin/class_schedules/ES22.csv",
  "/admin/class_schedules/ES23.csv",
  "/admin/class_schedules/ES24.csv",
  "/admin/class_schedules/TE22.csv",
  "/admin/class_schedules/TE23.csv",
  "/admin/class_schedules/TE24.csv",
  "/admin/combined_schedule/schedule.csv",
];

self.addEventListener("install", (event) => {
  // Perform install steps
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log("Opened cache");
      return cache.addAll(urlsToCache);
    })
  );
});

self.addEventListener("fetch", (event) => {
  event.respondWith(
    caches.match(event.request).then((response) => {
      // Cache hit - return response
      if (response) {
        return response;
      }
      return fetch(event.request);
    })
  );
});

self.addEventListener("activate", (event) => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});
