// Service Worker for PWA Offline Support
const CACHE_NAME = 'absensi-mifare-v1';
const OFFLINE_URL = '/offline.html';

// Assets to cache on install
const PRECACHE_ASSETS = [
    '/',
    '/offline.html',
    '/build/manifest.webmanifest',
    '/icon-192x192.png',
    '/icon-512x512.png',
];

// Install event - precache essential assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        })
    );
    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Skip chrome extensions and other non-http(s) requests
    if (!event.request.url.startsWith('http')) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            // Return cached version if available
            if (cachedResponse) {
                return cachedResponse;
            }

            // Otherwise fetch from network
            return fetch(event.request)
                .then((response) => {
                    // Don't cache non-successful responses
                    if (!response || response.status !== 200 || response.type === 'error') {
                        return response;
                    }

                    // Clone the response
                    const responseToCache = response.clone();

                    // Cache the fetched response for future use
                    caches.open(CACHE_NAME).then((cache) => {
                        // Only cache GET requests
                        if (event.request.method === 'GET') {
                            cache.put(event.request, responseToCache);
                        }
                    });

                    return response;
                })
                .catch(() => {
                    // If both cache and network fail, show offline page
                    if (event.request.mode === 'navigate') {
                        return caches.match(OFFLINE_URL);
                    }
                });
        })
    );
});

// Background Sync - for offline attendance submissions
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-attendance') {
        event.waitUntil(syncAttendance());
    }
});

async function syncAttendance() {
    // Get pending attendance records from IndexedDB
    const db = await openDB();
    const pendingRecords = await getAllPendingRecords(db);

    // Try to sync each record
    for (const record of pendingRecords) {
        try {
            const response = await fetch(record.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': record.csrfToken,
                },
                body: JSON.stringify(record.data),
            });

            if (response.ok) {
                // Remove synced record from IndexedDB
                await removeRecord(db, record.id);
            }
        } catch (error) {
            console.error('Sync failed for record:', record.id, error);
        }
    }
}

// IndexedDB helpers
function openDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('AbsensiMIFARE', 1);

        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);

        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('pendingSync')) {
                db.createObjectStore('pendingSync', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

function getAllPendingRecords(db) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['pendingSync'], 'readonly');
        const store = transaction.objectStore('pendingSync');
        const request = store.getAll();

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

function removeRecord(db, id) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['pendingSync'], 'readwrite');
        const store = transaction.objectStore('pendingSync');
        const request = store.delete(id);

        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
    });
}

// Push notification handler
self.addEventListener('push', (event) => {
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'Absensi MIFARE';
    const options = {
        body: data.message || 'You have a new notification',
        icon: '/icon-192x192.png',
        badge: '/icon-192x192.png',
        vibrate: [200, 100, 200],
        data: {
            url: data.url || '/',
        },
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    event.waitUntil(
        clients.openWindow(event.notification.data.url || '/')
    );
});
