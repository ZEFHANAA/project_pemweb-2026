<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $lokasi->nama_lokasi }} — Lokasi Wisata</title>
    <meta name="description" content="{{ $lokasi->deskripsi ?: 'Lihat detail lokasi wisata '.$lokasi->nama_lokasi.' di peta interaktif.' }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: #f0f2f5;
            color: #111827;
            min-height: 100vh;
        }

        /* ---- HEADER ---- */
        .header {
            background: #1e3a8a;
            padding: 0 24px;
            height: 60px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,.25);
        }
        .header-brand {
            display: flex; align-items: center; gap: 10px;
            text-decoration: none;
        }
        .header-logo {
            width: 34px; height: 34px; border-radius: 8px;
            background: rgba(255,255,255,.2); border: 1px solid rgba(255,255,255,.3);
            display: flex; align-items: center; justify-content: center; font-size: 17px;
        }
        .header-name {
            font-size: 15px; font-weight: 700; color: #fff;
        }
        .btn-back {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 16px; border-radius: 8px;
            background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.3);
            color: #fff; font-size: 13px; font-weight: 600;
            text-decoration: none; transition: all .2s;
            font-family: 'Inter', sans-serif;
        }
        .btn-back:hover { background: rgba(255,255,255,.25); }

        /* ---- HERO ---- */
        .hero {
            position: relative;
            height: 280px;
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
            overflow: hidden;
        }
        .hero-thumbnail {
            width: 100%; height: 100%;
            object-fit: cover; opacity: .55;
        }
        .hero-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to bottom, rgba(15,23,42,.2) 0%, rgba(15,23,42,.7) 100%);
            display: flex; flex-direction: column;
            justify-content: flex-end; padding: 28px 32px;
        }
        .hero-kategori {
            display: inline-flex; align-items: center;
            padding: 4px 12px; border-radius: 999px;
            background: rgba(255,255,255,.2); border: 1px solid rgba(255,255,255,.3);
            color: #fff; font-size: 12px; font-weight: 700;
            letter-spacing: .04em; margin-bottom: 10px;
            width: fit-content;
        }
        .hero-title {
            font-size: clamp(24px, 5vw, 36px);
            font-weight: 800; color: #fff;
            text-shadow: 0 2px 8px rgba(0,0,0,.4);
        }
        .hero-coords {
            font-size: 13px; color: rgba(255,255,255,.75);
            margin-top: 6px; font-family: monospace;
        }

        /* ---- MAIN LAYOUT ---- */
        .main {
            max-width: 900px;
            margin: 0 auto;
            padding: 28px 16px 40px;
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
        }
        @media (max-width: 680px) {
            .main { grid-template-columns: 1fr; }
            .hero { height: 200px; }
            .hero-overlay { padding: 20px; }
        }

        /* ---- MAP ---- */
        .card {
            background: #fff; border-radius: 12px;
            border: 1px solid #e5e7eb; box-shadow: 0 2px 12px rgba(0,0,0,.07);
            overflow: hidden;
        }
        .card-header {
            padding: 14px 18px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px; font-weight: 700; color: #374151;
            display: flex; align-items: center; gap: 6px;
        }
        #map { width: 100%; height: 320px; }

        /* ---- SIDE INFO ---- */
        .info-card { background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 2px 12px rgba(0,0,0,.07); overflow: hidden; }
        .info-header { padding: 14px 18px; border-bottom: 1px solid #f3f4f6; font-size: 13px; font-weight: 700; color: #374151; }
        .info-list { padding: 16px 18px; display: flex; flex-direction: column; gap: 14px; }
        .info-row { display: flex; flex-direction: column; gap: 3px; }
        .info-label { font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .05em; }
        .info-value { font-size: 13.5px; color: #111827; font-weight: 500; }
        .info-value.mono { font-family: monospace; font-size: 12.5px; }
        .info-desc { font-size: 13px; color: #374151; line-height: 1.7; }

        .kategori-pill {
            display: inline-flex; align-items: center;
            padding: 4px 12px; border-radius: 999px;
            background: #eff6ff; color: #1d4ed8;
            font-size: 12px; font-weight: 700;
            border: 1px solid #bfdbfe;
        }

        /* ---- ACTIONS ---- */
        .action-section { display: flex; flex-direction: column; gap: 10px; padding: 0 18px 18px; }
        .btn-action {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            padding: 11px 16px; border-radius: 8px; font-size: 13.5px; font-weight: 600;
            text-decoration: none; transition: all .2s; font-family: 'Inter', sans-serif;
            cursor: pointer; border: none;
        }
        .btn-gmaps-full { background: #f0fdf4; color: #16a34a; border: 1.5px solid #bbf7d0; }
        .btn-gmaps-full:hover { background: #16a34a; color: #fff; }
        .btn-share { background: #eff6ff; color: #1d4ed8; border: 1.5px solid #bfdbfe; width: 100%; }
        .btn-share:hover { background: #1d4ed8; color: #fff; }

        /* ---- FOOTER ---- */
        .footer {
            background: #1e3a8a; color: rgba(255,255,255,.5);
            text-align: center; padding: 14px; font-size: 12px; margin-top: 20px;
        }

        /* ---- COPY TOAST ---- */
        #copyToast {
            position: fixed; bottom: 28px; right: 28px;
            background: #1e293b; color: #fff;
            padding: 12px 20px; border-radius: 10px;
            font-size: 13px; font-weight: 600;
            box-shadow: 0 8px 30px rgba(0,0,0,.3);
            opacity: 0; transform: translateY(12px);
            transition: all .3s cubic-bezier(.34,1.56,.64,1);
            pointer-events: none; z-index: 999;
        }
        #copyToast.show { opacity: 1; transform: translateY(0); }
    </style>
</head>
<body>

    <header class="header">
        <a href="/" class="header-brand">
            <div class="header-logo">🗺️</div>
            <span class="header-name">Lokasi Wisata</span>
        </a>
        <a href="/" class="btn-back">← Kembali ke Peta</a>
    </header>

    {{-- HERO --}}
    <section class="hero">
        @if($thumbnail)
            <img src="{{ $thumbnail }}" alt="{{ $lokasi->nama_lokasi }}" class="hero-thumbnail">
        @endif
        <div class="hero-overlay">
            <span class="hero-kategori">{{ $lokasi->kategori ?? 'Lokasi' }}</span>
            <h1 class="hero-title">{{ $lokasi->nama_lokasi }}</h1>
            <p class="hero-coords">{{ number_format($lokasi->latitude, 4) }}, {{ number_format($lokasi->longitude, 4) }}</p>
        </div>
    </section>

    {{-- MAIN CONTENT --}}
    <main class="main">

        {{-- MAP --}}
        <div>
            <div class="card">
                <div class="card-header">🗺️ Lokasi di Peta</div>
                <div id="map"></div>
            </div>
        </div>

        {{-- SIDEBAR --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            <div class="info-card">
                <div class="info-header">📋 Informasi</div>
                <div class="info-list">
                    <div class="info-row">
                        <span class="info-label">Nama Lokasi</span>
                        <span class="info-value">{{ $lokasi->nama_lokasi }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Kategori</span>
                        <span class="kategori-pill">{{ $lokasi->kategori ?? 'Lainnya' }}</span>
                    </div>
                    @if($lokasi->deskripsi)
                    <div class="info-row">
                        <span class="info-label">Deskripsi</span>
                        <span class="info-desc">{{ $lokasi->deskripsi }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">Koordinat</span>
                        <span class="info-value mono">{{ $lokasi->latitude }}, {{ $lokasi->longitude }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Disimpan</span>
                        <span class="info-value">{{ $lokasi->created_at->format('d M Y') }}</span>
                    </div>
                </div>

                <div class="action-section">
                    <a class="btn-action btn-gmaps-full"
                       href="https://www.google.com/maps/search/?api=1&query={{ $lokasi->latitude }},{{ $lokasi->longitude }}"
                       target="_blank" rel="noopener">
                        🗺️ Buka di Google Maps
                    </a>
                    <button class="btn-action btn-share" onclick="copyShareLink()">
                        🔗 Salin Link Berbagi
                    </button>
                </div>
            </div>

        </div>
    </main>

    <footer class="footer">
        © {{ date('Y') }} Sistem Pencarian &amp; Simpan Lokasi Wisata
    </footer>

    <div id="copyToast">✓ Link berhasil disalin!</div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const LAT = {{ $lokasi->latitude }};
        const LNG = {{ $lokasi->longitude }};
        const NAME = @json($lokasi->nama_lokasi);

        const map = L.map('map', { attributionControl: false }).setView([LAT, LNG], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

        const marker = L.marker([LAT, LNG], {
            icon: L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
            })
        }).addTo(map);
        marker.bindPopup(`<strong>${NAME}</strong>`).openPopup();

        function copyShareLink() {
            const url = window.location.href;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(showToast);
            } else {
                const ta = document.createElement('textarea');
                ta.value = url;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                ta.remove();
                showToast();
            }
        }

        function showToast() {
            const t = document.getElementById('copyToast');
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 2500);
        }
    </script>
</body>
</html>
