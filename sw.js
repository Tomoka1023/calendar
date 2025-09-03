const VERSION = 'v1.0.0';
const STATIC_CACHE = `static-${VERSION}`;
const HTML_CACHE = `html-${VERSION}`;

const STATIC_ASSETS = [
  '',                 // ルート
  'index.php',        // トップ
  'style.css',
  'countdown.js',
  'favicon.png',
  'manifest.webmanifest',
  'offline.html'
];

// インストール時：静的アセットを事前キャッシュ
self.addEventListener('install', (e) => {
  e.waitUntil(caches.open(STATIC_CACHE).then((c) => c.addAll(STATIC_ASSETS)));
  self.skipWaiting();
});

// 古いキャッシュ削除
self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys
        .filter(k => ![STATIC_CACHE, HTML_CACHE].includes(k))
        .map(k => caches.delete(k))
      )
    )
  );
  self.clients.claim();
});

// フェッチ戦略
self.addEventListener('fetch', (e) => {
  const req = e.request;
  const url = new URL(req.url);

  // HTML（PHP）はネットワーク優先＋オフライン時にキャッシュ/オフラインページ
  const isHTML = req.mode === 'navigate' ||
                 (req.headers.get('accept') || '').includes('text/html');

  if (isHTML) {
    e.respondWith(
      fetch(req)
        .then(res => {
          const copy = res.clone();
          caches.open(HTML_CACHE).then(c => c.put(req, copy));
          return res;
        })
        .catch(async () => {
          const cached = await caches.match(req);
          return cached || caches.match('offline.html');
        })
    );
    return;
  }

  // 画像/CSS/JS はキャッシュ優先（あれば即返す）
  e.respondWith(
    caches.match(req).then(cached => {
      if (cached) return cached;
      return fetch(req).then(res => {
        const copy = res.clone();
        caches.open(STATIC_CACHE).then(c => c.put(req, copy));
        return res;
      });
    })
  );
});
