<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="auth-status" content="{{ Auth::check() ? 'authenticated' : 'guest' }}">
    <title>Sistem Pencarian &amp; Simpan Lokasi Wisata</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .tab-btn { background:none; border:none; padding:10px 15px; cursor:pointer; color:var(--t4); font-weight:600; border-bottom:2px solid transparent; transition:all .18s; }
        .tab-btn.active { color:var(--blue-btn); border-bottom-color:var(--blue-btn); }
        .tab-btn:hover { color:var(--blue-btn); }
    </style>
    <script>
        // Terapkan dark mode sebelum render untuk cegah flash
        (function() {
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
</head>
<body>
    <a class="skip-link" href="#main-content">Lewati ke konten utama</a>
    <div class="container">

        <!-- Header -->
        <header class="header" role="banner">
            <div class="header-content">
                <div class="header-title">
                    <div class="header-logo">🗺️</div>
                    <div class="header-text">
                        <h1>Pencarian Lokasi Wisata</h1>
                        <p class="subtitle">Temukan dan simpan lokasi favorit Anda</p>
                    </div>
                </div>
                <div class="header-auth">
                    <button id="themeToggleBtn" class="btn-theme-toggle" title="Toggle Dark Mode" aria-label="Toggle dark mode">
                        <span class="theme-icon">🌙</span>
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

        @guest
        {{-- Hero Banner untuk tamu (belum login) --}}
        <div class="guest-hero" id="guestHero">
            <div class="guest-hero-bg"></div>
            <div class="guest-hero-content">
                <div class="guest-hero-badge">✨ Sistem Pencarian Lokasi Wisata Indonesia</div>
                <h2 class="guest-hero-title">Temukan & Simpan Lokasi<br>Wisata Impian Anda</h2>
                <p class="guest-hero-sub">Cari, simpan, dan bagikan ribuan destinasi wisata di seluruh Nusantara. Gratis selamanya.</p>
                <div class="guest-hero-actions">
                    <a href="/register" class="hero-btn-primary">🚀 Daftar Gratis</a>
                    <a href="/login" class="hero-btn-secondary">Masuk →</a>
                </div>
                <p class="guest-hero-hint">💡 Anda bisa mencoba pencarian tanpa login terlebih dahulu</p>
            </div>
            <button class="hero-close-btn" onclick="document.getElementById('guestHero').style.display='none'" aria-label="Tutup banner">×</button>
        </div>
        @endguest

        <!-- Main Content -->
        <main id="main-content" class="main-content" role="main">

            <!-- Left Panel -->
            <aside class="left-panel">

                <!-- Search Section -->
                <section class="search-section">
                    <h2>🔍 Cari Lokasi Wisata</h2>
                    <form id="searchForm" class="search-form">
                        <input
                            type="text"
                            id="searchInput"
                            class="search-input"
                            placeholder="Cari lokasi wisata... (misal: Monas Jakarta)"
                            autocomplete="off"
                            required
                        >
                        <button type="submit" class="btn btn-primary btn-search" id="searchBtn">
                            Cari Lokasi
                        </button>
                    </form>

                    <!-- Riwayat Pencarian (Fitur 2) -->
                    <div id="searchHistoryDropdown" class="search-history-dropdown" style="display:none;"></div>

                    <!-- Quick chips -->
                    <div class="quick-search" role="group" aria-label="Pencarian cepat">
                        <span class="quick-label">Coba:</span>
                        <button type="button" class="quick-chip" data-query="Monas Jakarta">Monas</button>
                        <button type="button" class="quick-chip" data-query="Candi Borobudur">Borobudur</button>
                        <button type="button" class="quick-chip" data-query="Gunung Bromo">Bromo</button>
                        <button type="button" class="quick-chip" data-query="Danau Toba">Danau Toba</button>
                    </div>

                    <!-- Results / Error / Loading -->
                    <!-- Multi-result dropdown -->
                    <div id="multiResultList" class="multi-result-list" style="display:none;"></div>

                    <div id="searchResult" class="search-result" style="display:none;">
                        <div id="resultContent"></div>
                    </div>
                    <div id="searchError" class="search-error" role="status" aria-live="polite" style="display:none;"></div>
                    <div id="loadingSpinner" class="loading-spinner" style="display:none;">
                        <span>Mencari...</span>
                    </div>
                </section>

                <!-- Saved Locations Section -->
                <section class="locations-section">
                    <div class="section-title-row">
                        <h2>📌 Lokasi Tersimpan</h2>
                        <span id="savedCountBadge" class="count-badge">0</span>
                    </div>

                    <!-- Filter Bar -->
                    <div class="filter-bar">
                        <input
                            type="text"
                            id="filterInput"
                            class="filter-input"
                            placeholder="🔍 Cari dari daftar..."
                            autocomplete="off"
                        >
                        <select id="filterKategori" class="filter-select">
                            <option value="">Semua Kategori</option>
                            <option value="Pantai">🏖️ Pantai</option>
                            <option value="Gunung">🏔️ Gunung</option>
                            <option value="Kota">🏙️ Kota</option>
                            <option value="Budaya">🏛️ Budaya</option>
                            <option value="Kuliner">🍜 Kuliner</option>
                            <option value="Alam">🌿 Alam</option>
                            <option value="Lainnya">📍 Lainnya</option>
                        </select>
                    </div>

                    <div id="locationsList" class="locations-list">
                        <!-- Empty state -->
                        <div class="empty-state">
                            <div class="empty-icon">🗺️</div>
                            <p class="empty-title">Belum ada lokasi tersimpan</p>
                            <p class="empty-hint">Cari lokasi wisata di atas, lalu klik <strong>Simpan Lokasi</strong></p>
                        </div>
                    </div>

                    <!-- Bottom actions -->
                    <div class="panel-actions">
                        <button id="clearAllBtn" type="button" class="btn btn-danger btn-sm" style="display:none;">
                            🗑️ Hapus Semua
                        </button>
                        <button id="exportCsvBtn" type="button" class="btn btn-secondary btn-sm">
                            📊 Export Excel
                        </button>
                    </div>
                </section>

                @guest
                <!-- Login nudge for guests -->
                <div class="login-nudge">
                    <p>💡 <a href="/login" style="color:var(--blue-btn);font-weight:600;">Masuk</a> untuk menyimpan lokasi favoritmu!</p>
                </div>
                @endguest
            </aside>

            <!-- Right Panel: Map -->
            <aside class="right-panel">
                <div id="map" class="map-container"></div>
                <p class="map-attribution" role="note">
                    Peta oleh <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap contributors</a>
                    &bull; Library <a href="https://leafletjs.com" target="_blank" rel="noopener">Leaflet</a>
                </p>
            </aside>

        </main>

        <!-- Footer -->
        <footer class="footer">
            <p>© 2026 Sistem Pencarian Lokasi Wisata &bull; Menggunakan OpenStreetMap &amp; Nominatim API</p>
        </footer>

        <!-- Edit Modal -->
        <div id="editModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="editModalTitle">Edit Lokasi</h2>
                    <button type="button" class="modal-close" onclick="closeEditModal()" aria-label="Tutup">&times;</button>
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
                        <textarea id="editDeskripsi" rows="3" placeholder="Tambahkan catatan singkat tentang lokasi ini..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editKategori">Kategori</label>
                        <select id="editKategori" class="filter-select" style="width:100%;padding:8px 12px;">
                            <option value="Pantai">🏖️ Pantai</option>
                            <option value="Gunung">🏔️ Gunung</option>
                            <option value="Kota">🏙️ Kota</option>
                            <option value="Budaya">🏛️ Budaya</option>
                            <option value="Kuliner">🍜 Kuliner</option>
                            <option value="Alam">🌿 Alam</option>
                            <option value="Lainnya" selected>📍 Lainnya</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Profile Modal -->
        <div id="profileModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="profileModalTitle">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="profileModalTitle">Pengaturan Akun</h2>
                    <button type="button" class="modal-close" onclick="closeProfileModal()" aria-label="Tutup">&times;</button>
                </div>
                
                <div style="display:flex; border-bottom:1px solid var(--border); margin-bottom:15px;">
                    <button id="tabInfoBtn" class="tab-btn active" onclick="switchProfileTab('info')">Profil Utama</button>
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
                        <label for="newPassword">Password Baru (min. 6 karakter)</label>
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

    </div><!-- /.container -->

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script src="{{ asset('js/script.js') }}"></script>
</body>
</html>
