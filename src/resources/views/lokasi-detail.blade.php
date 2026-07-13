<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $lokasi->nama_lokasi }} — Lokasi Wisata</title>
    <meta name="description" content="{{ $lokasi->deskripsi ?: 'Lihat detail lokasi '.$lokasi->nama_lokasi.' di peta interaktif.' }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://petawisata.my.id/lokasi/{{ $lokasi->id }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $lokasi->nama_lokasi }} — Petawisata">
    <meta property="og:description" content="{{ $lokasi->deskripsi ?: 'Detail lokasi wisata '.$lokasi->nama_lokasi.'.' }}">
    <meta property="og:url" content="https://petawisata.my.id/lokasi/{{ $lokasi->id }}">
    <meta property="og:site_name" content="Petawisata">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #0c2d3a;
            color: #111827;
            min-height: 100vh;
        }

        /* ===== HEADER ===== */
        .header {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 56px;
            z-index: 200;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            background: rgba(13, 17, 23, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 9px;
            text-decoration: none;
        }

        .header-logo {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: rgba(30,64,175,.3);
            border: 1px solid rgba(30,64,175,.4);
            display: flex; align-items: center; justify-content: center;
        }

        .header-name {
            font-size: 14px;
            font-weight: 700;
            color: rgba(255,255,255,.9);
            letter-spacing: -.01em;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 7px;
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.15);
            color: rgba(255,255,255,.8);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all .2s;
            font-family: 'Inter', sans-serif;
        }
        .btn-back:hover {
            background: rgba(255,255,255,.18);
            color: #fff;
        }

        /* ===== HERO ===== */
        .hero {
            position: relative;
            height: 300px;
            margin-top: 56px;
            overflow: hidden;
            background:
                radial-gradient(circle at 20% 50%, rgba(30,64,175,.22), transparent 55%),
                radial-gradient(circle at 80% 30%, rgba(99,102,241,.18), transparent 50%),
                #0c2d3a;
        }
        .hero-empty-pattern {
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
            background-size: 44px 44px;
            mask-image: linear-gradient(to bottom, transparent 10%, #000 70%);
        }

        .hero-thumbnail {
            width: 100%; height: 100%;
            object-fit: cover;
            opacity: .45;
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom,
                rgba(13,17,23,.1) 0%,
                rgba(13,17,23,.65) 100%
            );
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 32px 40px;
        }

        .hero-kategori {
            display: inline-flex;
            align-items: center;
            padding: 3px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.25);
            color: rgba(255,255,255,.9);
            font-size: 11.5px;
            font-weight: 600;
            letter-spacing: .04em;
            margin-bottom: 10px;
            width: fit-content;
        }

        .hero-title {
            font-size: clamp(24px, 4vw, 36px);
            font-weight: 800;
            color: #fff;
            letter-spacing: -.5px;
            text-shadow: 0 2px 12px rgba(0,0,0,.3);
            line-height: 1.15;
        }

        .hero-coords {
            font-size: 12.5px;
            color: rgba(255,255,255,.55);
            margin-top: 7px;
            font-family: 'SF Mono', 'Fira Code', monospace;
            letter-spacing: .02em;
        }

        /* ===== MAIN LAYOUT ===== */
        .page-bg {
            background: #f3f4f6;
            padding: 28px 16px 60px;
        }

        .main {
            max-width: 920px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
        }

        /* ===== CARDS ===== */
        .card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 16px rgba(0,0,0,.07);
            overflow: hidden;
        }

        .card-header {
            padding: 13px 18px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 12.5px;
            font-weight: 700;
            color: #374151;
            display: flex;
            align-items: center;
            gap: 7px;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        #map { width: 100%; height: 340px; }

        /* ===== SIDEBAR ===== */
        .info-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 16px rgba(0,0,0,.07);
            overflow: hidden;
        }

        .info-header {
            padding: 13px 18px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 12.5px;
            font-weight: 700;
            color: #374151;
            display: flex;
            align-items: center;
            gap: 7px;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .info-list {
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .info-row { display: flex; flex-direction: column; gap: 3px; }

        .info-label {
            font-size: 10.5px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .info-value {
            font-size: 13.5px;
            color: #111827;
            font-weight: 500;
            line-height: 1.5;
        }

        .info-value.mono {
            font-family: 'SF Mono', 'Fira Code', monospace;
            font-size: 12px;
            color: #374151;
            background: #f9fafb;
            padding: 4px 8px;
            border-radius: 5px;
            border: 1px solid #e5e7eb;
            width: fit-content;
        }

        .info-desc {
            font-size: 13px;
            color: #374151;
            line-height: 1.75;
        }

        .kategori-pill {
            display: inline-flex;
            align-items: center;
            padding: 3px 11px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #0c2d3a;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid #e2e8f0;
        }

        /* ===== ACTIONS ===== */
        .action-section {
            display: flex;
            flex-direction: column;
            gap: 9px;
            padding: 0 18px 18px;
        }

        .btn-action {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 16px;
            border-radius: 9px;
            font-size: 13.5px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            text-decoration: none;
            transition: all .2s;
            cursor: pointer;
            border: 1.5px solid;
        }

        .btn-gmaps-full {
            background: #0c2d3a;
            color: #fff;
            border-color: #0c2d3a;
        }
        .btn-gmaps-full:hover {
            background: #1e293b;
            border-color: #1e293b;
        }

        .btn-share {
            background: #fff;
            color: #0c2d3a;
            border-color: #e2e8f0;
            width: 100%;
        }
        .btn-share:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        /* ===== FOOTER ===== */
        .footer {
            background: #0c2d3a;
            color: rgba(255,255,255,.3);
            text-align: center;
            padding: 16px;
            font-size: 12px;
        }

        /* ===== TOAST ===== */
        #copyToast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #1e293b;
            color: rgba(255,255,255,.9);
            padding: 11px 18px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 8px 30px rgba(0,0,0,.3);
            display: flex;
            align-items: center;
            gap: 8px;
            opacity: 0;
            transform: translateY(10px);
            transition: all .25s ease;
            pointer-events: none;
            z-index: 999;
            border: 1px solid rgba(255,255,255,.08);
        }
        #copyToast.show { opacity: 1; transform: translateY(0); }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 700px) {
            .main { grid-template-columns: 1fr; }
            .hero { height: 220px; }
            .hero-overlay { padding: 20px 24px; }
            #map { height: 260px; }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <header class="header">
        <a href="/" class="header-brand">
            <div class="header-logo">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.9)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>
                    <line x1="9" y1="3" x2="9" y2="18"/>
                    <line x1="15" y1="6" x2="15" y2="21"/>
                </svg>
            </div>
            <span class="header-name">Lokasi Wisata</span>
        </a>
        <a href="/" class="btn-back">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            Kembali
        </a>
    </header>

    {{-- HERO --}}
    <section class="hero">
        @if($thumbnail)
            <img src="{{ $thumbnail }}" alt="{{ $lokasi->nama_lokasi }}" class="hero-thumbnail">
        @else
            <div class="hero-empty-pattern" aria-hidden="true"></div>
        @endif
        <div class="hero-overlay">
            <span class="hero-kategori">{{ $lokasi->kategori ?? 'Lokasi' }}</span>
            <h1 class="hero-title">{{ $lokasi->nama_lokasi }}</h1>
            <p class="hero-coords">{{ number_format($lokasi->latitude, 5) }}, {{ number_format($lokasi->longitude, 5) }}</p>
        </div>
    </section>

    {{-- CONTENT --}}
    <div class="page-bg">
        <main class="main">

            {{-- MAP --}}
            <div>
                <div class="card">
                    <div class="card-header">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>
                            <line x1="9" y1="3" x2="9" y2="18"/>
                            <line x1="15" y1="6" x2="15" y2="21"/>
                        </svg>
                        Lokasi di Peta
                    </div>
                    <div id="map"></div>
                </div>
            </div>

            {{-- SIDEBAR --}}
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div class="info-card">
                    <div class="info-header">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        Informasi
                    </div>
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
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            Buka di Google Maps
                        </a>
                        @auth
                            <button class="btn-action btn-save" onclick="saveThisLocation()" style="background:#2563eb;color:#fff;border-color:#2563eb;width:100%;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                </svg>
                                Simpan Lokasi Ini
                            </button>
                        @else
                            <a class="btn-action btn-save" href="{{ route('login') }}" style="background:#2563eb;color:#fff;border-color:#2563eb;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                </svg>
                                Simpan Lokasi Ini
                            </a>
                        @endauth
                        <button class="btn-action btn-share" onclick="copyShareLink()">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="18" cy="5" r="3"/>
                                <circle cx="6" cy="12" r="3"/>
                                <circle cx="18" cy="19" r="3"/>
                                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                            </svg>
                            Salin Link
                        </button>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <footer class="footer">
        © {{ date('Y') }} Sistem Pencarian & Simpan Lokasi Wisata
    </footer>

    <div id="copyToast">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
        </svg>
        Link berhasil disalin
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const LAT  = {{ $lokasi->latitude }};
        const LNG  = {{ $lokasi->longitude }};
        const NAME = @json($lokasi->nama_lokasi);
        const KATEGORI = @json($lokasi->kategori);

        const map = L.map('map', { attributionControl: false }).setView([LAT, LNG], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

        const markerColors = {
            'Pantai': '#0d9488', 'Gunung': '#ca8a04', 'Kota': '#7c3aed',
            'Alam': '#166534', 'Budaya': '#b45309', 'Kuliner': '#ea580c', 'Lainnya': '#94a3b8'
        };
        const pinColor = markerColors[KATEGORI] || '#94a3b8';
        const pinIcon = L.divIcon({
            className: 'custom-pin',
            html: `<div style="
                width:24px;height:24px;border-radius:50% 50% 50% 0;
                background:${pinColor};border:2px solid #fff;
                transform:rotate(-45deg);
                box-shadow:0 2px 6px rgba(0,0,0,.4);">
                <div style="
                    position:absolute;top:4px;left:4px;width:12px;height:12px;
                    border-radius:50%;background:#fff;transform:rotate(45deg);
                    display:flex;align-items:center;justify-content:center;
                    font-size:7px;font-weight:700;color:${pinColor};">●</div>
            </div>`,
            iconSize: [24, 24],
            iconAnchor: [12, 24],
            popupAnchor: [0, -24]
        });

        const marker = L.marker([LAT, LNG], { icon: pinIcon }).addTo(map);
        marker.bindPopup(`<strong style="font-family:Inter,sans-serif;font-size:13px;">${NAME}</strong>`).openPopup();

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
            setTimeout(() => t.classList.remove('show'), 2800);
        }

        async function saveThisLocation() {
            try {
                const response = await fetch('/api/lokasi', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        nama_lokasi: NAME,
                        latitude: LAT,
                        longitude: LNG,
                        kategori: KATEGORI,
                        deskripsi: @json($lokasi->deskripsi)
                    })
                });

                if (response.ok) {
                    window.location.href = '/';
                } else if (response.status === 409) {
                    Swal.fire({
                        title: 'Gagal Menyimpan',
                        text: 'Lokasi ini sudah ada di daftar simpanan Anda!',
                        icon: 'info',
                        background: '#1e293b',
                        color: '#f8fafc',
                        confirmButtonColor: '#3b82f6',
                        confirmButtonText: 'Oke'
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat menyimpan lokasi.',
                        icon: 'error',
                        background: '#1e293b',
                        color: '#f8fafc',
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (error) {
                console.error('Error saving location:', error);
                Swal.fire({
                    title: 'Gagal',
                    text: 'Koneksi terputus. Gagal menyimpan lokasi.',
                    icon: 'error',
                    background: '#1e293b',
                    color: '#f8fafc',
                    confirmButtonColor: '#ef4444'
                });
            }
        }
    </script>
</body>
</html>
