<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="auth-status" content="{{ Auth::check() ? 'authenticated' : 'guest' }}">
    <title>Petawisata — Temukan Wisata Indonesia</title>
    <meta name="description" content="Petawisata: cari, simpan, dan bagi lokasi wisata di Indonesia. Peta interaktif, bisa dicoba tanpa login.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://petawisata.my.id/">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Petawisata — Temukan Wisata Indonesia">
    <meta property="og:description" content="Cari, simpan, dan bagi lokasi wisata di Indonesia pada peta interaktif.">
    <meta property="og:url" content="https://petawisata.my.id/">
    <meta property="og:site_name" content="Petawisata">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Petawisata — Temukan Wisata Indonesia">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
    <style>
        .tab-btn { background:none; border:none; padding:10px 15px; cursor:pointer; color:var(--t4); font-weight:600; border-bottom:2px solid transparent; transition:background .15s; font-family:'Inter',sans-serif; font-size:13px; }
        .tab-btn.active { color:var(--blue-btn); border-bottom-color:var(--blue-btn); }
        .tab-btn:hover { color:var(--blue-btn); }
        .hero-btn-primary, .hero-btn-secondary { transition: background .15s; }
    </style>
    <script>
        (function() {
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
</head>
<body>
    <a class="skip-link" href="#main-content">Lewati ke konten utama</a>

    {{-- MAP — Full Screen --}}
    <div id="map"></div>

    {{-- HEADER — Floating --}}
    <header class="header" role="banner">
        <div class="header-content">
            <div class="header-title">
                <div class="header-logo">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#fff"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/></svg>
                </div>
                <div class="header-text">
                    <div class="header-brand">Petawisata</div>
                    <p class="subtitle">Peta wisata Indonesia</p>
                </div>
            </div>
            <div class="header-auth">
                <button id="themeToggleBtn" class="btn-theme-toggle" title="Toggle Dark Mode" aria-label="Toggle dark mode">
                    <svg class="icon-moon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    <svg class="icon-sun" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                </button>
                @auth
                    <button class="user-badge" onclick="openProfileModal()">
                        <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                        <span class="user-name">{{ Auth::user()->name }}</span>
                    </button>
                    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-outline-white">Keluar</button>
                    </form>
                @else
                    <a href="/login"    class="btn btn-outline-white" style="text-decoration:none;">Masuk</a>
                    <a href="/register" class="btn btn-white"         style="text-decoration:none;">Daftar</a>
                @endauth
            </div>
        </div>
    </header>

    {{-- GUEST HERO --}}
    @guest
    <div class="guest-hero" id="guestHero">
        <div class="guest-hero-content">
            <h1 class="guest-hero-title">Temukan Wisata Favoritmu</h1>
            <p class="guest-hero-sub">Cari wisata di Indonesia, simpan yang menarik, lihat di peta. Coba tanpa login dulu.</p>
            <div class="guest-hero-actions">
                <a href="/register" class="hero-btn-primary">Buat Akun</a>
                <a href="/login" class="hero-btn-secondary">Masuk</a>
            </div>
        </div>
        <button class="hero-close-btn" onclick="document.getElementById('guestHero').style.display='none'" aria-label="Tutup banner">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    @endguest

    {{-- FLOATING LEFT PANEL --}}
    <main id="main-content" class="main-content" role="main">
        <aside class="left-panel">

            {{-- Search --}}
            <section class="search-section">
                <h2>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Cari Lokasi
                </h2>
                <form id="searchForm" class="search-form">
                    <input type="text" id="searchInput" class="search-input"
                        placeholder="Monas Jakarta, Pantai Kuta..." autocomplete="off" required>
                    <button type="submit" class="btn btn-primary btn-search" id="searchBtn">Cari Lokasi</button>
                </form>

                <div id="searchHistoryDropdown" class="search-history-dropdown" style="display:none;"></div>

                <div class="quick-search" role="group" aria-label="Pencarian cepat">
                    <span class="quick-label">Coba:</span>
                    <button type="button" class="quick-chip" data-query="Borobudur">Borobudur</button>
                    <button type="button" class="quick-chip" data-query="Raja Ampat">Raja Ampat</button>
                    <button type="button" class="quick-chip" data-query="Mount Bromo">Gunung Bromo</button>
                    <button type="button" class="quick-chip" data-query="Lake Toba">Danau Toba</button>
                </div>

                <div id="multiResultList" class="multi-result-list" style="display:none;"></div>
                <div id="searchResult" class="search-result" style="display:none;">
                    <div id="resultContent"></div>
                </div>
                <div id="searchError" class="search-error" role="status" aria-live="polite" style="display:none;"></div>
                <div id="loadingSpinner" class="loading-spinner" style="display:none;">
                    <span>Mencari...</span>
                </div>
            </section>

            {{-- Saved Locations --}}
            <section class="locations-section">
                <div class="section-title-row">
                    <h2>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Lokasi Tersimpan
                    </h2>
                    <span id="savedCountBadge" class="count-badge">0</span>
                </div>

                <div class="filter-bar">
                    <input type="text" id="filterInput" class="filter-input"
                        placeholder="Cari dari daftar..." autocomplete="off">
                    <select id="filterKategori" class="filter-select">
                        <option value="">Semua Kategori</option>
                        <option value="Pantai">Pantai</option>
                        <option value="Gunung">Gunung</option>
                        <option value="Kota">Kota</option>
                        <option value="Budaya">Budaya</option>
                        <option value="Kuliner">Kuliner</option>
                        <option value="Alam">Alam</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div id="locationsList" class="locations-list">
                    <div class="empty-state">
                        <div class="empty-icon">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--t4)"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/></svg>
                        </div>
                        <p class="empty-title">Belum ada lokasi tersimpan</p>
                        <p class="empty-hint">Cari lokasi di atas, lalu klik <strong>Simpan Lokasi</strong></p>
                    </div>
                </div>

                <div class="panel-actions">
                    <button id="clearAllBtn" type="button" class="btn btn-danger btn-sm" style="display:none;">Hapus Semua</button>
                    <button id="exportCsvBtn" type="button" class="btn btn-secondary btn-sm" disabled>Export XLS</button>
                </div>
            </section>

        </aside>
    </main>

    {{-- EDIT MODAL --}}
    <div id="editModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="editModalTitle">Edit Lokasi</h2>
                <button type="button" class="modal-close" onclick="closeEditModal()" aria-label="Tutup">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <form id="editForm" class="modal-form">
                <div class="form-group">
                    <label for="editNamaLokasi">Nama Lokasi</label>
                    <input type="text" id="editNamaLokasi" placeholder="Nama lokasi wisata" required>
                </div>
                <label class="toggle-row" for="enableCoordEdit">
                    <input type="checkbox" id="enableCoordEdit">
                    <span>Ubah koordinat juga</span>
                </label>
                <div class="form-group">
                    <label for="editLatitude">Latitude</label>
                    <input type="number" id="editLatitude" step="0.0001" min="-90" max="90" required>
                </div>
                <div class="form-group">
                    <label for="editLongitude">Longitude</label>
                    <input type="number" id="editLongitude" step="0.0001" min="-180" max="180" required>
                </div>
                <div class="form-group">
                    <label for="editDeskripsi">Deskripsi <span style="font-weight:400;color:var(--t4);">(opsional)</span></label>
                    <textarea id="editDeskripsi" rows="3" placeholder="Catatan singkat tentang lokasi ini..."></textarea>
                </div>
                <div class="form-group">
                    <label for="editKategori">Kategori</label>
                    <select id="editKategori" class="filter-select" style="width:100%;padding:8px 12px;">
                        <option value="Pantai">Pantai</option>
                        <option value="Gunung">Gunung</option>
                        <option value="Kota">Kota</option>
                        <option value="Budaya">Budaya</option>
                        <option value="Kuliner">Kuliner</option>
                        <option value="Alam">Alam</option>
                        <option value="Lainnya" selected>Lainnya</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- PROFILE MODAL --}}
    <div id="profileModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="profileModalTitle">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="profileModalTitle">Pengaturan Akun</h2>
                <button type="button" class="modal-close" onclick="closeProfileModal()" aria-label="Tutup">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div style="display:flex; border-bottom:1px solid var(--border); margin-bottom:15px;">
                <button id="tabInfoBtn" class="tab-btn active" onclick="switchProfileTab('info')">Profil</button>
                <button id="tabPassBtn" class="tab-btn" onclick="switchProfileTab('password')">Ganti Password</button>
            </div>
            <form id="profileInfoForm" class="modal-form">
                <div class="form-group">
                    <label for="profileName">Nama Lengkap</label>
                    <input type="text" id="profileName" value="{{ Auth::check() ? Auth::user()->name : '' }}" required>
                </div>
                <div class="form-group">
                    <label for="profileEmail">Alamat Email</label>
                    <input type="email" id="profileEmail" value="{{ Auth::check() ? Auth::user()->email : '' }}" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" style="width:100%">Simpan Profil</button>
                </div>
            </form>
            <form id="profilePassForm" class="modal-form" style="display:none;">
                <div class="form-group">
                    <label for="currentPassword">Password Saat Ini</label>
                    <input type="password" id="currentPassword" required>
                </div>
                <div class="form-group">
                    <label for="newPassword">Password Baru (min. 8 karakter)</label>
                    <input type="password" id="newPassword" required>
                </div>
                <div class="form-group">
                    <label for="newPasswordConfirm">Konfirmasi Password Baru</label>
                    <input type="password" id="newPasswordConfirm" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" style="width:100%">Ganti Password</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script src="{{ asset('js/script.js') }}?v={{ time() }}"></script>
    <script>
        // Sync sun/moon icon with theme
        (function() {
            const btn = document.getElementById('themeToggleBtn');
            if (!btn) return;
            function syncIcons() {
                const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                btn.querySelector('.icon-moon').style.display = isDark ? 'none' : 'block';
                btn.querySelector('.icon-sun').style.display  = isDark ? 'block' : 'none';
            }
            syncIcons();
            btn.addEventListener('click', () => setTimeout(syncIcons, 50));
        })();
    </script>
</body>
</html>
