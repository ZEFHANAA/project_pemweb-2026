<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="auth-status" content="{{ Auth::check() ? 'authenticated' : 'guest' }}">
    <title>Sistem Pencarian & Simpan Lokasi Wisata</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
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
                    @auth
                        <div class="user-badge">
                            <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                            <span class="user-name">{{ Auth::user()->name }}</span>
                        </div>
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

                    <!-- Quick chips -->
                    <div class="quick-search" role="group" aria-label="Pencarian cepat">
                        <span class="quick-label">Coba:</span>
                        <button type="button" class="quick-chip" data-query="Monas Jakarta">Monas</button>
                        <button type="button" class="quick-chip" data-query="Candi Borobudur">Borobudur</button>
                        <button type="button" class="quick-chip" data-query="Gunung Bromo">Bromo</button>
                        <button type="button" class="quick-chip" data-query="Danau Toba">Danau Toba</button>
                    </div>

                    <!-- Results / Error / Loading -->
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
                            📥 Export CSV
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

    </div><!-- /.container -->

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/script.js') }}"></script>
</body>
</html>
