// Service worker mínimo (fase 0 de la PWA): solo hace instalable la app y
// cachea el "shell" estático (JS/CSS compilados por Vite + íconos) con una
// estrategia cache-first. Deliberadamente NO cachea ninguna respuesta de
// rutas dinámicas (donaciones, usuarios, formularios, etc.) — eso queda
// para una fase 2 con una estrategia de sincronización real, cachear datos
// ahora solo daría información desactualizada sin forma de refrescarla.

const CACHE_NAME = 'shell-v1';
const STATIC_PREFIXES = ['/build/', '/icons/'];
const STATIC_EXTRA_URLS = ['/manifest.json'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        (async () => {
            const cache = await caches.open(CACHE_NAME);
            const urlsToCache = [...STATIC_EXTRA_URLS];

            // El manifest de Vite tiene los nombres de archivo con hash del
            // build actual; se lee en tiempo de instalación en vez de
            // hardcodearlos, porque cambian en cada despliegue.
            try {
                const manifestResponse = await fetch('/build/manifest.json');
                if (manifestResponse.ok) {
                    const manifest = await manifestResponse.json();
                    const entry = manifest['resources/js/app.js'];
                    if (entry?.file) {
                        urlsToCache.push(`/build/${entry.file}`);
                    }
                    (entry?.css ?? []).forEach((cssFile) => {
                        urlsToCache.push(`/build/${cssFile}`);
                    });
                }
            } catch (error) {
                // Sin red o manifest no disponible: seguimos con la
                // instalación igual. El resto de assets estáticos se van
                // cacheando al vuelo desde el evento fetch.
            }

            await cache.addAll(urlsToCache);
        })(),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        (async () => {
            const cacheNames = await caches.keys();
            await Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name)),
            );
        })(),
    );
});

function isShellAsset(url) {
    if (url.origin !== self.location.origin) {
        return false;
    }

    if (STATIC_EXTRA_URLS.includes(url.pathname)) {
        return true;
    }

    return STATIC_PREFIXES.some((prefix) => url.pathname.startsWith(prefix));
}

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);

    // Deja pasar directo a la red todo lo que no sea un asset estático del
    // shell: páginas de Inertia, donaciones, usuarios, envíos de
    // formularios, etc. Nunca deben servirse desde caché.
    if (!isShellAsset(url)) {
        return;
    }

    event.respondWith(
        (async () => {
            const cached = await caches.match(event.request);
            if (cached) {
                return cached;
            }

            const response = await fetch(event.request);
            if (response.ok) {
                const cache = await caches.open(CACHE_NAME);
                cache.put(event.request, response.clone());
            }
            return response;
        })(),
    );
});
