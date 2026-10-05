// AcademyHub Progressive Web App Service Worker (v3)
const CACHE_NAME = 'academyhub-static-v3';
const DYNAMIC_CACHE = 'academyhub-pages-v3';
const OFFLINE_URL = '/offline';

// Core assets to pre-cache on install
const PRECACHE_ASSETS = [
    '/offline',
    '/manifest.json',
    '/favicon.ico',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/apple-touch-icon.png',
    '/full.png',
    '/build/assets/app-BtCz8vtz.css',
    '/build/assets/app-CtG3gbz_.js'
];

// 1. Install Event: Pre-cache critical offline shell
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(async (cache) => {
            // Use Promise.allSettled so an optional failed asset does not block SW installation
            const promises = PRECACHE_ASSETS.map((url) => {
                return cache.add(url).catch((err) => {
                    console.warn('[PWA SW] Precache skipped for:', url, err);
                });
            });
            await Promise.allSettled(promises);
        }).then(() => self.skipWaiting())
    );
});

// 2. Activate Event: Purge old cache stores
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME && key !== DYNAMIC_CACHE) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// 3. Fetch Event
self.addEventListener('fetch', (event) => {
    // Only handle GET requests
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // Only intercept HTTP/HTTPS
    if (!url.protocol.startsWith('http')) return;

    // Do NOT intercept Livewire component update POSTs, API health checks, or Reverb WebSockets
    if (url.pathname.startsWith('/livewire/update') || url.pathname.startsWith('/api/health') || url.pathname.startsWith('/app/')) {
        return;
    }

    // A. HTML Navigation Requests (Page Visits) - Network-first with dynamic cache fallback
    if (event.request.mode === 'navigate' || (event.request.headers.get('accept') && event.request.headers.get('accept').includes('text/html'))) {
        event.respondWith(
            fetch(event.request)
                .then((networkResponse) => {
                    // Cache successful HTML page navigation
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(DYNAMIC_CACHE).then((cache) => {
                            cache.put(event.request, responseClone);
                        });
                    }
                    return networkResponse;
                })
                .catch(async () => {
                    // Network unavailable — Try cached version of THIS page first
                    const cachedPage = await caches.match(event.request);
                    if (cachedPage) {
                        return cachedPage;
                    }

                    // Fallback to pre-cached offline page
                    const offlinePage = await caches.match(OFFLINE_URL);
                    if (offlinePage) {
                        return offlinePage;
                    }

                    // Emergency inline HTML fallback if offline page was not yet cached
                    return new Response(
                        `<!DOCTYPE html>
                        <html lang="en">
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
                            <title>Offline — AcademyHub</title>
                            <style>
                                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #0f172a; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; text-align: center; }
                                .card { background: #fff; padding: 36px 28px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); max-width: 400px; width: 100%; border: 1px solid #e2e8f0; }
                                h1 { font-size: 20px; font-weight: 800; margin-bottom: 8px; }
                                p { font-size: 13px; color: #64748b; margin-bottom: 24px; line-height: 1.5; }
                                button { background: #7c3aed; color: #fff; border: none; padding: 12px 24px; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: pointer; width: 100%; }
                            </style>
                        </head>
                        <body>
                            <div class="card">
                                <h1>Connection Lost</h1>
                                <p>You are currently offline. Please check your network connection and retry.</p>
                                <button onclick="window.location.reload()">Check Connection & Retry</button>
                            </div>
                        </body>
                        </html>`,
                        {
                            headers: { 'Content-Type': 'text/html; charset=utf-8' }
                        }
                    );
                })
        );
        return;
    }

    // B. Static Assets: JS, CSS, fonts, images, icons, and Livewire runtime scripts
    const isStaticAsset = url.pathname.startsWith('/build/') ||
                          url.pathname.startsWith('/icons/') ||
                          url.pathname.startsWith('/vendor/') ||
                          url.pathname.startsWith('/livewire/') ||
                          url.pathname.endsWith('.js') ||
                          url.pathname.endsWith('.css') ||
                          url.pathname.endsWith('.png') ||
                          url.pathname.endsWith('.jpg') ||
                          url.pathname.endsWith('.jpeg') ||
                          url.pathname.endsWith('.svg') ||
                          url.pathname.endsWith('.webp') ||
                          url.pathname.endsWith('.ico') ||
                          url.pathname.endsWith('.woff2') ||
                          url.pathname.endsWith('.woff') ||
                          url.pathname.endsWith('.ttf');

    if (isStaticAsset) {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                if (cachedResponse) {
                    // Stale-while-revalidate in background
                    fetch(event.request).then((networkResponse) => {
                        if (networkResponse && networkResponse.status === 200) {
                            const clone = networkResponse.clone();
                            caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
                        }
                    }).catch(() => {});
                    return cachedResponse;
                }

                // If not in cache, fetch from network and cache for next time
                return fetch(event.request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, responseClone));
                    }
                    return networkResponse;
                }).catch(() => {
                    return new Response('', { status: 408, statusText: 'Request timed out / offline' });
                });
            })
        );
        return;
    }

    // C. Default: Network-First with Cache Fallback
    event.respondWith(
        fetch(event.request)
            .then((networkResponse) => {
                if (networkResponse && networkResponse.status === 200) {
                    const clone = networkResponse.clone();
                    caches.open(DYNAMIC_CACHE).then((cache) => cache.put(event.request, clone));
                }
                return networkResponse;
            })
            .catch(() => caches.match(event.request))
    );
});

// Web Push Notifications
self.addEventListener('push', function(event) {
    let data = {};
    try {
        data = event.data ? event.data.json() : {};
    } catch(e) {
        data = { title: 'School Notification', body: event.data ? event.data.text() : 'You have a new update' };
    }

    const title = data.title || 'AcademyHub Notification';
    const options = {
        body: data.body || 'You have an update from school',
        icon: data.icon || '/icons/icon-192.png',
        badge: '/icons/icon-192.png',
        vibrate: [150, 80, 150],
        data: {
            url: data.url || '/'
        }
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Push notification tap
self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    const targetUrl = event.notification.data ? event.notification.data.url : '/';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (client.url.includes(targetUrl) && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
