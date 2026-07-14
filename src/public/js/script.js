// ==================== Global Variables ====================
let map;
let markerCluster; // Fitur 11: cluster group
let markers = [];
let currentSearchMarker = null;
let savedLocations = [];
const API_URL = '/api/lokasi';
let currentEditingId = null;

// ==================== Security Utilities ====================
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return String(unsafe)
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}
// ==================== Initialization ====================
document.addEventListener('DOMContentLoaded', () => {
    initMap();
    loadLocations();
    setupEventListeners();
    initDarkMode();    // Fitur 12
    initSearchHistory(); // Fitur 2
    initMobileToggle();
});

// ==================== Map Initialization ====================
function initMap() {
    map = L.map('map', { 
        attributionControl: false,
        zoomControl: false
    }).setView([-6.2088, 106.8456], 5);

    L.control.zoom({ position: 'bottomright' }).addTo(map);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>',
        maxZoom: 19, minZoom: 2
    }).addTo(map);

    // Fitur 11: Inisialisasi marker cluster group
    markerCluster = L.markerClusterGroup({
        showCoverageOnHover: false,
        maxClusterRadius: 60,
        iconCreateFunction: function(cluster) {
            const count = cluster.getChildCount();
            return L.divIcon({
                html: `<div class="cluster-icon">${count}</div>`,
                className: 'custom-cluster',
                iconSize: [40, 40]
            });
        }
    });
    map.addLayer(markerCluster);


}

// ==================== Event Listeners ====================
function setupEventListeners() {
    // Search form
    document.getElementById('searchForm').addEventListener('submit', handleSearch);

    // Clear all button
    const clearAllBtn = document.getElementById('clearAllBtn');
    clearAllBtn.addEventListener('click', handleClearAll);

    // Export CSV button
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    if (exportCsvBtn) {
        exportCsvBtn.addEventListener('click', handleExportCsv);
    }

    // Edit form submit
    const editForm = document.getElementById('editForm');
    if (editForm) {
        editForm.addEventListener('submit', handleEditSubmit);
        const editNama = document.getElementById('editNamaLokasi');
        if (editNama) editNama.addEventListener('input', () => editNama.classList.remove('is-invalid'));
    }

    const enableCoordEdit = document.getElementById('enableCoordEdit');
    if (enableCoordEdit) {
        enableCoordEdit.addEventListener('change', (e) => {
            setCoordinateEditingEnabled(e.target.checked);
        });
    }

    // Quick search chips
    document.querySelectorAll('.quick-chip').forEach((chip) => {
        chip.addEventListener('click', () => {
            const query = chip.getAttribute('data-query');
            document.getElementById('searchInput').value = query;
            document.getElementById('searchForm').requestSubmit();
        });
    });

    // Close modal when clicking outside
    window.addEventListener('click', (event) => {
        const modal = document.getElementById('editModal');
        const profileModal = document.getElementById('profileModal');
        if (event.target === modal) closeEditModal();
        if (event.target === profileModal) closeProfileModal();
    });

    // Filter bar
    const filterInput    = document.getElementById('filterInput');
    const filterKategori = document.getElementById('filterKategori');
    function applyFilter() {
        const text = filterInput    ? filterInput.value.trim() : '';
        const kat  = filterKategori ? filterKategori.value     : '';
        renderLocationsList(text, kat);
    }
    if (filterInput)    filterInput.addEventListener('input', applyFilter);
    if (filterKategori) filterKategori.addEventListener('change', applyFilter);

    // Fitur 12: Dark mode toggle
    const themeBtn = document.getElementById('themeToggleBtn');
    if (themeBtn) themeBtn.addEventListener('click', toggleDarkMode);

    // Tutup multi-result saat klik di luar
    document.addEventListener('click', (e) => {
        const list = document.getElementById('multiResultList');
        const form = document.getElementById('searchForm');
        if (list && form && !form.contains(e.target) && !list.contains(e.target)) {
            list.style.display = 'none';
        }
    });
}

// ==================== Search Functionality ====================
async function handleSearch(e) {
    e.preventDefault();

    const searchInput = document.getElementById('searchInput');
    const query = searchInput.value.trim();

    if (!query) {
        showToast('Masukkan nama lokasi yang valid', 'error');
        return;
    }

    // Simpan ke riwayat (Fitur 2)
    saveSearchHistory(query);
    hideSearchHistory();

    // Clear previous results and errors
    hideSearchError();
    hideSearchResult();
    hideMultiResultList();
    setSearchBusy(true);
    clearCurrentSearchMarker();

    try {
        const data = await searchLocation(query);

        if (!data || data.length === 0) {
            showSearchError(`Lokasi "${query}" tidak ditemukan. Coba nama lain.`);
            setSearchBusy(false);
            return;
        }

        if (data.length === 1) {
            // Langsung tampilkan jika hanya 1 hasil
            displaySearchResult(data[0]);
            addSearchMarkerToMap(data[0]);
        } else {
            // Fitur 7: Tampilkan daftar multi hasil
            displayMultiResultList(data);
        }

        setSearchBusy(false);

    } catch (error) {
        console.error('Error:', error);
        showSearchError('Gagal mencari lokasi. Periksa koneksi internet Anda.');
        setSearchBusy(false);
    }
}

// ==================== Nominatim API ====================
async function searchLocation(query) {
    // Fitur 7: limit=7 untuk mendapat banyak hasil
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=7&addressdetails=1&countrycodes=id`;

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 3000);

    try {
        const response = await fetch(url, { signal: controller.signal });
        clearTimeout(timeoutId);

        if (!response.ok) {
            throw new Error(`HTTP Error: ${response.status}`);
        }

        const data = await response.json();
        return data;

    } catch (error) {
        clearTimeout(timeoutId);
        if (error.name === 'AbortError') {
            console.error('API Error: Request timed out after 3 seconds.');
            throw new Error('Pencarian terlalu lama. Server sedang sibuk.');
        }
        console.error('API Error:', error);
        throw error;
    }
}

// ==================== Display Search Result ====================
function displaySearchResult(location) {
    const resultContent = document.getElementById('resultContent');
    const latitude = parseFloat(location.lat);
    const longitude = parseFloat(location.lon);
    const name = location.name || location.display_name;
    const safeName = escapeHtml(name);
    const safeAddr = escapeHtml(location.display_name);

    resultContent.innerHTML = `
        <div id="resultThumbnail" class="result-thumbnail" style="display:none;">
            <img id="resultThumbnailImg" src="" alt="Foto ${safeName}" class="thumbnail-img">
        </div>
        <div class="result-header">
            <h3>${safeName}</h3>
            <button type="button" class="result-cancel-btn" id="btnCancelResult" title="Batal cari" aria-label="Batal cari">&times;</button>
        </div>
        <div class="result-detail">
            <strong>Latitude:</strong> ${latitude.toFixed(6)}
        </div>
        <div class="result-detail">
            <strong>Longitude:</strong> ${longitude.toFixed(6)}
        </div>
        <div class="result-detail">
            <strong>Alamat:</strong> ${safeAddr}
        </div>
        <div class="result-detail">
            <label for="resultKategori"><strong>Kategori:</strong></label>
            <select id="resultKategori" class="filter-select" style="width:100%;margin-top:4px;">
                <option value="Pantai">Pantai</option>
                <option value="Gunung">Gunung</option>
                <option value="Kota">Kota</option>
                <option value="Budaya">Budaya</option>
                <option value="Kuliner">Kuliner</option>
                <option value="Alam">Alam</option>
                <option value="Lainnya" selected>Lainnya</option>
            </select>
        </div>
        <div class="result-detail">
            <label for="resultDeskripsi"><strong>Deskripsi (opsional):</strong></label>
            <textarea id="resultDeskripsi" rows="3" placeholder="Tambahkan deskripsi singkat"></textarea>
        </div>
        <div class="result-actions">
            <button id="btnSaveResult" class="btn btn-secondary btn-save">
                Simpan Lokasi
            </button>
            <a href="https://www.google.com/maps?q=${latitude},${longitude}" target="_blank" rel="noopener" class="btn-gmaps">
                Buka di Google Maps
            </a>
        </div>
    `;

    showSearchResult(true);

    // Fetch thumbnail dari Wikipedia
    fetchWikipediaThumbnail(name);

    // Tombol batal: tutup result card tanpa simpan
    document.getElementById('btnCancelResult')?.addEventListener('click', clearSearchForm);

    // attach event listener safely (avoid inline onclick quoting issues)
    const btn = document.getElementById('btnSaveResult');
    if (btn) {
        btn.addEventListener('click', () => {
            try {
                saveLokasi(name, latitude, longitude);
            } catch (e) {
                console.error('Error calling saveLokasi:', e);
                showSearchError('Gagal menyimpan lokasi (client error)', 'error');
            }
        });
    }
}

// ==================== Wikipedia Thumbnail ====================
async function fetchWikipediaThumbnail(query) {
    const url = `https://id.wikipedia.org/w/api.php?action=query&titles=${encodeURIComponent(query)}&prop=pageimages&format=json&pithumbsize=400&origin=*`;

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 3000);

    try {
        const res = await fetch(url, { signal: controller.signal });
        clearTimeout(timeoutId);
        const data = await res.json();

        const pages = data.query.pages;
        const pageId = Object.keys(pages)[0];

        if (pageId !== "-1" && pages[pageId].thumbnail) {
            const imgUrl = pages[pageId].thumbnail.source;
            const thumbEl = document.getElementById('resultThumbnail');
            const imgEl   = document.getElementById('resultThumbnailImg');
            if (thumbEl && imgEl) {
                imgEl.src = imgUrl;
                thumbEl.style.display = 'block';
            }
        }
    } catch (_) {
        // Jika gagal ambil foto, tidak ada masalah - tampilan tetap normal
    }
}

// ==================== Coordinate Validation ====================
function normalizeCoordinate(value) {
    return Number(parseFloat(value).toFixed(6));
}

function validateCoordinates(latitude, longitude) {
    if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
        return {
            isValid: false,
            message: 'Koordinat harus berupa angka yang valid'
        };
    }

    if (latitude < -90 || latitude > 90) {
        return {
            isValid: false,
            message: 'Latitude harus di antara -90 sampai 90'
        };
    }

    if (longitude < -180 || longitude > 180) {
        return {
            isValid: false,
            message: 'Longitude harus di antara -180 sampai 180'
        };
    }

    return { isValid: true, message: '' };
}

// ==================== Save Location ====================
async function saveLokasi(nama_lokasi, latitude, longitude) {
    // Cek status login via meta tag auth-status (lebih reliable dari form selector)
    const authMeta = document.querySelector('meta[name="auth-status"]');
    const isAuthenticated = authMeta && authMeta.content === 'authenticated';



    if (!isAuthenticated) {
        showSearchError('Silakan login terlebih dahulu untuk menyimpan lokasi', 'error');
        setTimeout(() => {
            window.location.href = '/login';
        }, 2000);
        return;
    }

    const parsedLatitude = Number(latitude);
    const parsedLongitude = Number(longitude);
    const normalizedLatitude = normalizeCoordinate(parsedLatitude);
    const normalizedLongitude = normalizeCoordinate(parsedLongitude);

    const validation = validateCoordinates(normalizedLatitude, normalizedLongitude);
    if (!validation.isValid) {
        showSearchError(validation.message, 'error');
        return;
    }

    // Check if location already exists
    const exists = savedLocations.some(
        loc => loc.latitude === normalizedLatitude && loc.longitude === normalizedLongitude
    );

    if (exists) {
        showSearchError('Lokasi ini sudah tersimpan!');
        return;
    }

    // clear previous messages and disable save button while saving
    hideSearchError();
    const saveBtnEl = document.getElementById('btnSaveResult');
    const originalSaveText = saveBtnEl ? saveBtnEl.innerHTML : '';
    if (saveBtnEl) {
        saveBtnEl.disabled = true;
        saveBtnEl.innerHTML = '<span class="spinner"></span> Menyimpan...';
    }

    try {
        // read optional description and kategori from search result
        const resultDescEl     = document.getElementById('resultDeskripsi');
        const resultKategoriEl = document.getElementById('resultKategori');
        const resultDeskripsi  = resultDescEl     ? resultDescEl.value.trim()  : '';
        const resultKategori   = resultKategoriEl ? resultKategoriEl.value      : 'Lainnya';

        // Send to API
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                nama_lokasi: nama_lokasi,
                latitude: normalizedLatitude,
                longitude: normalizedLongitude,
                deskripsi: resultDeskripsi,
                kategori:  resultKategori
            })
        });

        if (!response.ok) {
            if (response.status === 401) {
                showSearchError('Silakan login terlebih dahulu untuk menyimpan lokasi', 'error');
                setTimeout(() => {
                    window.location.href = '/login';
                }, 2000);
                return;
            }
            const text = await response.text();
            console.error('Server response not ok:', text);
            throw new Error('Gagal menyimpan lokasi');
        }

        const newLocation = await response.json();

        // Add to array and update UI
        savedLocations.push(newLocation);
        renderLocationsList();
        addMarkerToMap(newLocation);
        updateSavedCount();
        clearSearchForm();

        // Show success message
        showSearchError('Lokasi berhasil disimpan!', 'success');

    } catch (error) {
        console.error('Error:', error);
        showSearchError('Gagal menyimpan lokasi', 'error');
    } finally {
        if (saveBtnEl) {
            saveBtnEl.disabled = false;
            saveBtnEl.innerHTML = originalSaveText || 'Simpan Lokasi';
        }
    }
}

// ==================== Load Locations from API ====================
async function loadLocations() {
    try {
        const response = await fetch(API_URL);
        
        if (!response.ok) {
            throw new Error('Gagal memuat lokasi');
        }

        savedLocations = await response.json();

        
        // Check if authenticated based on locations count
        // If we got locations, user is either authenticated (own locations) or guest (all locations)
        const authStatusMeta = document.querySelector('meta[name="auth-status"]')?.content;
        if (authStatusMeta === 'guest') {
            window.isAuthenticated = false;
        } else {
            window.isAuthenticated = true;
        }
    } catch (error) {
        console.error('Error loading locations:', error);
        savedLocations = [];
        window.isAuthenticated = false;
    }

    renderLocationsList();
    renderMarkersOnMap();
    updateSavedCount();
}

// ==================== Render Locations List ====================
function renderLocationsList(filterText = '', filterKat = '') {
    const locationsList = document.getElementById('locationsList');
    const clearAllBtn = document.getElementById('clearAllBtn');
    updateSavedCount();

    // Apply filters
    const filtered = savedLocations.filter(loc => {
        const matchText = !filterText || (loc.nama_lokasi || '').toLowerCase().includes(filterText.toLowerCase());
        const matchKat  = !filterKat  || (loc.kategori || 'Lainnya') === filterKat;
        return matchText && matchKat;
    });

    if (savedLocations.length === 0) {
        locationsList.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/><line x1="1" y1="6" x2="8" y2="2"/><line x1="8" y1="2" x2="16" y2="6"/><line x1="16" y1="6" x2="23" y2="2"/></svg></div>
                <p class="empty-title">Belum ada lokasi tersimpan</p>
                <p class="empty-hint">Cari lokasi wisata di atas,<br>lalu klik <strong>Simpan Lokasi</strong></p>
            </div>`;
        clearAllBtn.style.display = 'none';
        return;
    }

    if (filtered.length === 0) {
        locationsList.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></div>
                <p class="empty-title">Tidak ada hasil</p>
                <p class="empty-hint">Coba ubah kata kunci atau kategori filter</p>
            </div>`;
        clearAllBtn.style.display = 'block';
        return;
    }

    clearAllBtn.style.display = 'block';

    // Map kategori to color + initial — konsisten sama marker di peta
    const listHTML = filtered.map(location => {
        const lat = Number(location.latitude);
        const lng = Number(location.longitude);
        const latStr = Number.isFinite(lat) ? lat.toFixed(4) : '';
        const lngStr = Number.isFinite(lng) ? lng.toFixed(4) : '';
        const safeName = (location.nama_lokasi || '').replace(/'/g, "\\'").replace(/\n/g, ' ');
        const eName = escapeHtml(location.nama_lokasi || '');
        const eKat = escapeHtml(location.kategori || 'Lainnya');
        const eDesc = escapeHtml(location.deskripsi ? location.deskripsi : '');
        const kat  = location.kategori  || 'Lainnya';
        const badgeColor = getMarkerPinColor(kat);
        const initial = escapeHtml(getMarkerInitial(kat));
        const mapsUrl  = `https://www.google.com/maps?q=${lat},${lng}`;
        const shareUrl = `/lokasi/${location.id}`;

        return `
        <li class="location-item" data-location-id="${location.id}" data-kategori="${eKat}">
            <div class="location-item-content" tabindex="0" onclick="focusLocation(${location.id}, ${lat || 0}, ${lng || 0})">
                <div class="location-item-header">
                    <div class="location-item-name">${eName}</div>
                    <span class="kategori-badge"><span style="display:inline-block;width:16px;height:16px;border-radius:50%;background:${badgeColor};color:#fff;text-align:center;line-height:16px;font-size:9px;font-weight:700;margin-right:6px;vertical-align:middle;">${initial}</span>${eKat}</span>
                </div>
                <div class="location-item-coords">
                    ${latStr}, ${lngStr}
                </div>
                <div class="location-item-desc">${eDesc}</div>
            </div>
            <div class="location-item-actions">
                <button type="button" aria-label="Lihat ${safeName}" class="btn-view" onclick="focusLocation(${location.id}, ${lat || 0}, ${lng || 0})">
                    Lihat
                </button>
                <button type="button" aria-label="Edit ${safeName}" class="btn-edit" onclick="openEditModal(${location.id}, '${safeName}', ${lat || 0}, ${lng || 0})">
                    Edit
                </button>
                <a href="${mapsUrl}" target="_blank" rel="noopener" class="btn-gmaps-sm" aria-label="Buka di Google Maps" title="Google Maps">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
                </a>
                <a href="${shareUrl}" target="_blank" rel="noopener" class="btn-share-sm" aria-label="Bagikan ${safeName}" title="Bagikan">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                </a>
                <button type="button" aria-label="Hapus ${safeName}" class="btn-delete" onclick="deleteLokasi(${location.id}, this)">
                    Hapus
                </button>
            </div>
        </li>
    `;
    }).join('');

    locationsList.innerHTML = `<ul class="locations-list-ul">${listHTML}</ul>`;


}

// ==================== Delete Location ====================
async function deleteLokasi(id, btnElement = null) {
    const index = savedLocations.findIndex(loc => loc.id === id);

    if (index === -1) {
        console.error('Lokasi tidak ditemukan');
        return;
    }

    // Use SweetAlert2 for confirmation
    const result = await Swal.fire({
        title: 'Hapus Lokasi?',
        text: "Lokasi ini akan dihapus dari daftar Anda.",
        icon: 'warning',
        background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#ffffff',
        color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#f8fafc' : '#0f172a',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#3b82f6',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    });

    if (!result.isConfirmed) return;

    const originalText = btnElement ? btnElement.innerHTML : 'Hapus';
    if (btnElement) {
        btnElement.disabled = true;
        btnElement.innerHTML = '<span class="spinner"></span>';
    }

    try {
        const response = await fetch(`${API_URL}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });

        if (!response.ok) {
            if (response.status === 401) {
                showSearchError('Silakan login terlebih dahulu untuk menghapus lokasi', 'error');
                return;
            }
            throw new Error('Gagal menghapus lokasi');
        }

        const deletedLocation = savedLocations[index];
        savedLocations.splice(index, 1);

        // Remove marker
        removeMarkerFromMap(id);

        // Update UI
        renderLocationsList();
        renderMarkersOnMap();

        showSearchError('Lokasi berhasil dihapus!', 'success');

    } catch (error) {
        console.error('Error:', error);
        showSearchError('Gagal menghapus lokasi', 'error');
    } finally {
        if (btnElement) {
            btnElement.disabled = false;
            btnElement.innerHTML = originalText;
        }
    }
}

// ==================== Export CSV ====================
async function handleExportCsv() {
    // Cek login
    const authMeta = document.querySelector('meta[name="auth-status"]');
    if (!authMeta || authMeta.content !== 'authenticated') {
        showSearchError('Silakan login terlebih dahulu untuk mengekspor CSV', 'error');
        return;
    }

    // Cek apakah ada lokasi
    if (savedLocations.length === 0) {
        showSearchError('Tidak ada lokasi tersimpan untuk diekspor', 'error');
        return;
    }

    try {
        const response = await fetch(`${API_URL}/export`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });

        if (response.status === 401) {
            showSearchError('Silakan login terlebih dahulu untuk mengekspor CSV', 'error');
            return;
        }

        if (!response.ok) {
            const txt = await response.text();
            console.error('Export failed:', txt);
            showSearchError('Gagal mengekspor CSV', 'error');
            return;
        }

        const blob = await response.blob();
        const disposition = response.headers.get('Content-Disposition') || '';
        let filename = 'lokasi_wisata.csv';
        const m = /filename="?([^";]+)"?/.exec(disposition);
        if (m) filename = decodeURIComponent(m[1]);

        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
        showSearchError(`CSV berhasil diunduh (${savedLocations.length} lokasi)`, 'success');
    } catch (error) {
        console.error('Export error:', error);
        showSearchError('Gagal mengekspor CSV', 'error');
    }
}

// ==================== Clear All Locations ====================
async function handleClearAll() {
    if (savedLocations.length === 0) return;

    const confirmed = confirm('Apakah Anda yakin ingin menghapus semua lokasi tersimpan?');

    if (!confirmed) return;

    try {
        // Delete all locations
        const deletePromises = savedLocations.map(loc => 
            fetch(`${API_URL}/${loc.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            }).then(res => {
                if (!res.ok) throw new Error('Delete failed for ' + loc.id);
                return res;
            })
        );

        await Promise.all(deletePromises);

        savedLocations = [];
        renderLocationsList();
        clearAllMarkersFromMap();

        showSearchError('Semua lokasi berhasil dihapus!', 'success');

    } catch (error) {
        console.error('Error:', error);
        showSearchError('Gagal menghapus semua lokasi', 'error');
    }
}

// ==================== Update Location ====================
async function updateLokasi(id, nama_lokasi, latitude, longitude, deskripsi = '', kategori = 'Lainnya') {
    const index = savedLocations.findIndex(loc => loc.id === id);

    if (index === -1) {
        showSearchError('Lokasi tidak ditemukan', 'error');
        console.error('Lokasi tidak ditemukan');
        return;
    }

    const cleanName = nama_lokasi.trim();
    if (!cleanName) {
        document.getElementById('editNamaLokasi')?.classList.add('is-invalid');
        showSearchError('Nama lokasi tidak boleh kosong', 'error');
        return;
    } else {
        document.getElementById('editNamaLokasi')?.classList.remove('is-invalid');
    }

    const parsedLatitude = Number(latitude);
    const parsedLongitude = Number(longitude);
    const normalizedLatitude = normalizeCoordinate(parsedLatitude);
    const normalizedLongitude = normalizeCoordinate(parsedLongitude);

    const validation = validateCoordinates(normalizedLatitude, normalizedLongitude);
    if (!validation.isValid) {
        showSearchError(validation.message, 'error');
        return;
    }

    const editFormSubmitBtn = document.querySelector('#editForm button[type="submit"]');
    const originalText = editFormSubmitBtn ? editFormSubmitBtn.innerHTML : 'Simpan';
    if (editFormSubmitBtn) {
        editFormSubmitBtn.disabled = true;
        editFormSubmitBtn.innerHTML = '<span class="spinner"></span> Menyimpan...';
    }

    try {
        // Send to API
        const response = await fetch(`${API_URL}/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
                body: JSON.stringify({
                    nama_lokasi: cleanName,
                    latitude: normalizedLatitude,
                    longitude: normalizedLongitude,
                    deskripsi: deskripsi || (savedLocations[index].deskripsi || ''),
                    kategori:  kategori  || (savedLocations[index].kategori  || 'Lainnya')
                })
        });

        if (!response.ok) {
            throw new Error('Gagal mengupdate lokasi');
        }

        const updatedLocation = await response.json();

        // Update location data
        const oldLocation = savedLocations[index];
        savedLocations[index] = updatedLocation;

        // Update UI and markers
        renderLocationsList();
        renderMarkersOnMap();
        zoomToLocation(normalizedLatitude, normalizedLongitude);

        // Show success message
        showSearchError('Lokasi berhasil diubah!', 'success');

    } catch (error) {
        console.error('Error:', error);
        showSearchError('Gagal mengupdate lokasi', 'error');
    } finally {
        if (editFormSubmitBtn) {
            editFormSubmitBtn.disabled = false;
            editFormSubmitBtn.innerHTML = originalText;
        }
    }
}

// ==================== Map Markers ====================
// Warna pin untuk marker — color-coded per kategori
function getMarkerPinColor(kategori) {
    const colors = {
        'Pantai': '#0d9488',
        'Gunung': '#ca8a04',
        'Kota': '#7c3aed',
        'Alam': '#166534',
        'Budaya': '#b45309',
        'Kuliner': '#ea580c',
        'Lainnya': '#94a3b8'
    };
    return colors[kategori] || '#94a3b8';
}

// Inisial kategori buat label kecil di marker
function getMarkerInitial(kategori) {
    const initials = {
        'Pantai': 'P',
        'Gunung': 'G',
        'Kota': 'K',
        'Alam': 'A',
        'Budaya': 'B',
        'Kuliner': 'U',
        'Lainnya': '·'
    };
    return initials[kategori] || '·';
}

function addMarkerToMap(location) {
    const lat = Number(location.latitude);
    const lng = Number(location.longitude);
    const pinColor = getMarkerPinColor(location.kategori);
    const initial = getMarkerInitial(location.kategori);
    const eName = escapeHtml(location.nama_lokasi || '');
    const eKat = escapeHtml(location.kategori || 'Lainnya');
    const eDesc = location.deskripsi ? escapeHtml(location.deskripsi) : '';
    const latStr = Number.isFinite(lat) ? lat.toFixed(5) : '';
    const lngStr = Number.isFinite(lng) ? lng.toFixed(5) : '';

    const marker = L.marker([lat, lng], {
        icon: L.divIcon({
            className: 'petawisata-marker',
            html: `<div style="position:relative;width:30px;height:42px;"><span style="position:absolute;top:0;left:0;width:30px;height:30px;background:${pinColor};border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 2px 5px rgba(0,0,0,.4);border:2px solid #fff;"></span><span style="position:absolute;top:7px;left:0;width:30px;text-align:center;color:#fff;font-size:13px;font-weight:700;font-family:'Inter',sans-serif;line-height:1;">${escapeHtml(initial)}</span></div>`,
            iconSize: [30, 42],
            iconAnchor: [15, 42],
            popupAnchor: [0, -38]
        })
    }).bindPopup(`
        <div style="text-align: center; min-width: 180px;">
            <h4 style="margin: 5px 0; font-size: 15px; color: #1e293b;">${eName}</h4>
            <span style="display:inline-block;background:${pinColor};color:#fff;padding:2px 10px;border-radius:12px;font-size:11px;font-weight:600;margin:4px 0;">${eKat}</span>
            ${eDesc ? `<p style="margin: 8px 0 4px; font-size: 12px; color: #64748b; text-align:left;">${eDesc}</p>` : ''}
            <p style="margin: 4px 0; font-size: 11px; color: #94a3b8; font-family: monospace;">${latStr}, ${lngStr}</p>
            <a href="/lokasi/${location.id}" style="display:inline-block;margin-top:6px;font-size:12px;color:#3b82f6;text-decoration:none;font-weight:600;">Lihat detail →</a>
        </div>
    `);

    marker.locationId = location.id;
    markers.push(marker);
    markerCluster.addLayer(marker); // Fitur 11: tambahkan ke cluster


}

function renderMarkersOnMap() {
    clearAllMarkersFromMap();
    savedLocations.forEach(location => addMarkerToMap(location));
}

function removeMarkerFromMap(id) {
    const index = markers.findIndex(m => m.locationId === id);
    if (index !== -1) {
        markerCluster.removeLayer(markers[index]); // Fitur 11
        markers.splice(index, 1);

    }
}

function clearAllMarkersFromMap() {
    markerCluster.clearLayers(); // Fitur 11
    markers = [];
}

// ==================== Search Marker Handling ====================
function addSearchMarkerToMap(location) {
    clearCurrentSearchMarker();

    const latitude = parseFloat(location.lat);
    const longitude = parseFloat(location.lon);
    const name = location.name || location.display_name;

    currentSearchMarker = L.marker([latitude, longitude], {
        icon: L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        })
    }).bindPopup(`
        <div style="text-align: center;">
            <h4 style="margin: 5px 0; color: #667eea;">${escapeHtml(name)}</h4>
            <p style="margin: 5px 0; font-size: 12px; color: #666;">
                Hasil Pencarian
            </p>
        </div>
    `);

    currentSearchMarker.addTo(map);
    map.flyTo([latitude, longitude], 13);
    currentSearchMarker.openPopup();


}

function clearCurrentSearchMarker() {
    if (currentSearchMarker) {
        map.removeLayer(currentSearchMarker);
        currentSearchMarker = null;
    }
}

// ==================== Zoom to Location ====================
function zoomToLocation(latitude, longitude) {
    map.flyTo([latitude, longitude], 15, {
        duration: 1.5
    });

    // Find and open marker popup
    markers.forEach(marker => {
        if (Math.abs(marker.getLatLng().lat - latitude) < 0.0001 &&
            Math.abs(marker.getLatLng().lng - longitude) < 0.0001) {
            setTimeout(() => marker.openPopup(), 800);
        }
    });


}

function focusLocation(id, latitude, longitude) {
    highlightActiveLocation(id);
    zoomToLocation(latitude, longitude);
}

// ==================== UI Helper Functions ====================
function showSearchResult(show = true) {
    document.getElementById('searchResult').style.display = show ? 'block' : 'none';
    // result-active: dulu sembunyikan Tersimpan di mobile, sekarang Tersimpan tetap ada.
    // Saat result ditutup, scroll panel ke atas Tersimpan biar list update kelihatan.
    document.querySelector('.left-panel')?.classList.toggle('result-active', show);
    if (!show) {
        const lp = document.querySelector('.left-panel');
        const ls = document.querySelector('.locations-section');
        if (lp && ls) lp.scrollTo({ top: ls.offsetTop - lp.offsetTop, behavior: 'smooth' });
    }
}

function hideSearchResult() {
    showSearchResult(false);
}

// ==================== Multi Result List (Fitur 7) ====================
function displayMultiResultList(results) {
    const list = document.getElementById('multiResultList');
    if (!list) return;

    const typeLabel = (type) => {
        const map = {
            'tourism': '\ud83c\udfd6\ufe0f Wisata', 'natural': '\ud83c\udf3f Alam',
            'historic': '\ud83c\udfdb\ufe0f Sejarah', 'leisure': '\ud83c\udfd9\ufe0f Rekreasi',
            'mountain': '\ud83c\udfd4\ufe0f Gunung', 'peak': '\ud83c\udfd4\ufe0f Puncak',
            'beach': '\ud83c\udfd6\ufe0f Pantai', 'city': '\ud83c\udfd9\ufe0f Kota',
            'town': '\ud83c\udfe0 Kota Kecil', 'village': '\ud83c\udfe1 Desa',
            'administrative': '\ud83d\udccd Wilayah',
        };
        for (const [key, val] of Object.entries(map)) {
            if (type && type.toLowerCase().includes(key)) return val;
        }
        return '\ud83d\udccd Lokasi';
    };

    list.innerHTML = results.map((r, i) => {
        const shortAddrRaw = r.display_name.split(',').slice(0, 3).join(', ');
        const shortAddr = escapeHtml(shortAddrRaw);
        const nameRaw = r.name || shortAddrRaw;
        const name = escapeHtml(nameRaw);
        const type = escapeHtml(typeLabel(r.type || r.class));
        const lat = parseFloat(r.lat).toFixed(4);
        const lon = parseFloat(r.lon).toFixed(4);
        return `
            <div class="multi-result-item" tabindex="0" role="button"
                 onclick="selectMultiResult(${i})" onkeydown="if(event.key==='Enter')selectMultiResult(${i})">
                <div class="multi-result-type">${type}</div>
                <div class="multi-result-name">${name}</div>
                <div class="multi-result-addr">${shortAddr}</div>
                <div class="multi-result-coords">${lat}, ${lon}</div>
            </div>
        `;
    }).join('');

    // Simpan hasil di dataset untuk referensi saat dipilih
    list.dataset.results = JSON.stringify(results);
    list.style.display = 'block';
}

function selectMultiResult(index) {
    const list = document.getElementById('multiResultList');
    if (!list) return;
    const results = JSON.parse(list.dataset.results || '[]');
    const location = results[index];
    if (!location) return;

    hideMultiResultList();
    displaySearchResult(location);
    addSearchMarkerToMap(location);
}

function hideMultiResultList() {
    const list = document.getElementById('multiResultList');
    if (list) list.style.display = 'none';
}

function showSearchError(message, type = 'error') {
    // Bersihkan prefix emoji dari pesan (karena sering di-hardcode)
    message = message.replace(/^([❌✓⚠️✅ℹ️]\s*)/, '');

    // Fitur 1: gunakan toast untuk pesan sukses, error tetap pakai inline
    if (type === 'success') {
        showToast(message, 'success');
        return;
    }
    const errorDiv = document.getElementById('searchError');
    if (!errorDiv) return;
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    errorDiv.style.background = '#ffe0e0';
    errorDiv.style.borderLeftColor = '#ff6b6b';
    errorDiv.style.color = '#c92a2a';
}

function hideSearchError() {
    document.getElementById('searchError').style.display = 'none';
}

function showLoadingSpinner(show = true) {
    document.getElementById('loadingSpinner').style.display = show ? 'flex' : 'none';
}

function setSearchBusy(isBusy) {
    const searchBtn = document.getElementById('searchBtn');
    showLoadingSpinner(isBusy);

    if (searchBtn) {
        searchBtn.disabled = isBusy;
        searchBtn.textContent = isBusy ? 'Mencari...' : 'Cari Lokasi';
    }
}

function updateSavedCount() {
    const badge = document.getElementById('savedCountBadge');
    if (badge) {
        badge.textContent = String(savedLocations.length);
    }
    const exportBtn = document.getElementById('exportCsvBtn');
    if (exportBtn) {
        exportBtn.disabled = savedLocations.length === 0;
    }
}

function highlightActiveLocation(id) {
    document.querySelectorAll('.location-item').forEach((item) => {
        item.classList.remove('is-active');
    });

    const activeItem = document.querySelector(`.location-item[data-location-id="${id}"]`);
    if (activeItem) {
        activeItem.classList.add('is-active');
        activeItem.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
}

function clearSearchForm() {
    document.getElementById('searchInput').value = '';
    hideSearchResult();
}

// ==================== Modal Functions ====================
function openEditModal(id, nama_lokasi, latitude, longitude) {
    currentEditingId = id;
    document.getElementById('editNamaLokasi').value = nama_lokasi;
    document.getElementById('editLatitude').value = latitude;
    document.getElementById('editLongitude').value = longitude;
    const currentLocation = savedLocations.find(loc => loc.id === id) || {};
    if (document.getElementById('editDeskripsi')) {
        document.getElementById('editDeskripsi').value = currentLocation.deskripsi || '';
    }
    // Set kategori dropdown
    const editKategoriEl = document.getElementById('editKategori');
    if (editKategoriEl) {
        editKategoriEl.value = currentLocation.kategori || 'Lainnya';
    }
    document.getElementById('enableCoordEdit').checked = false;
    setCoordinateEditingEnabled(false);
    document.getElementById('editModal').style.display = 'flex';

}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    document.getElementById('enableCoordEdit').checked = false;
    setCoordinateEditingEnabled(false);
    currentEditingId = null;

}

function handleEditSubmit(e) {
    e.preventDefault();

    if (currentEditingId === null) {
        return;
    }

    const nama_lokasi = document.getElementById('editNamaLokasi').value.trim();
    const isCoordEditEnabled = document.getElementById('enableCoordEdit').checked;

    let latitude;
    let longitude;

    if (isCoordEditEnabled) {
        latitude = parseFloat(document.getElementById('editLatitude').value);
        longitude = parseFloat(document.getElementById('editLongitude').value);
    } else {
        // Keep current coordinates
        const currentLocation = savedLocations.find(loc => loc.id === currentEditingId);
        latitude = currentLocation.latitude;
        longitude = currentLocation.longitude;
    }

    const editDeskripsi = document.getElementById('editDeskripsi') ? document.getElementById('editDeskripsi').value.trim() : '';
    const editKategori  = document.getElementById('editKategori')  ? document.getElementById('editKategori').value  : 'Lainnya';
    updateLokasi(currentEditingId, nama_lokasi, latitude, longitude, editDeskripsi, editKategori);
    closeEditModal();
}

function setCoordinateEditingEnabled(enabled) {
    document.getElementById('editLatitude').disabled = !enabled;
    document.getElementById('editLongitude').disabled = !enabled;

    if (enabled) {
        document.getElementById('editLatitude').style.opacity = '1';
        document.getElementById('editLongitude').style.opacity = '1';
    } else {
        document.getElementById('editLatitude').style.opacity = '0.5';
        document.getElementById('editLongitude').style.opacity = '0.5';
    }
}

// ==================== Dark Mode (Fitur 12) ====================
function initDarkMode() {
    const saved = localStorage.getItem('theme');
    const isDark = saved === 'dark';
    applyTheme(isDark);
}

function toggleDarkMode() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    applyTheme(!isDark);
    localStorage.setItem('theme', !isDark ? 'dark' : 'light');
}

function applyTheme(isDark) {
    document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
    const icon = document.querySelector('.theme-icon');
    if (icon) icon.innerHTML = isDark
        ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>'
        : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';

    // Update Leaflet tile layer for dark mode (switch to dark tiles)
    if (map) {
        // Hapus tile layer lama
        map.eachLayer(layer => {
            if (layer instanceof L.TileLayer) map.removeLayer(layer);
        });

        if (isDark) {
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap &copy; CARTO',
                maxZoom: 19, minZoom: 2
            }).addTo(map);
        } else {
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>',
                maxZoom: 19, minZoom: 2
            }).addTo(map);
        }
    }


}

// ==================== Toast Notification (Fitur 1) ====================

function showToast(titleOrMessage, messageOrType = 'success', type) {
    // Support both (message, type) and (title, message, type)
    let title, msg, toastType;
    if (type !== undefined) {
        title      = titleOrMessage;
        msg        = messageOrType;
        toastType  = type;
    } else {
        title      = null;
        msg        = titleOrMessage;
        toastType  = messageOrType;
    }

    let msgClean = escapeHtml(msg.replace(/^([❌✓⚠️✅ℹ️]\s*)/, ''));
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast-item toast-${toastType}`;
    
    // Ganti emoji dengan SVG
    const icons = { 
        success: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color:#4ade80"><polyline points="20 6 9 17 4 12"/></svg>', 
        error: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color:#f87171"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>', 
        info: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color:#60a5fa"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>', 
        warning: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color:#fbbf24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>' 
    };
    const icon  = icons[toastType] || icons['info'];
    const titleHtml = title ? `<strong>${escapeHtml(title)}:</strong> ` : '';
    toast.innerHTML = `<span class="toast-icon" style="display:flex;align-items:center;">${icon}</span><span class="toast-msg" style="font-family:'Inter',sans-serif;font-size:13.5px;font-weight:500;">${titleHtml}${msgClean}</span>`;
    container.appendChild(toast);

    requestAnimationFrame(() => toast.classList.add('show'));

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 350);
    }, 4000);
}

// ==================== Search History (Fitur 2) ====================
const HISTORY_KEY = 'lokasi_search_history';
const MAX_HISTORY = 6;

function initSearchHistory() {
    const searchInput = document.getElementById('searchInput');
    const historyEl   = document.getElementById('searchHistoryDropdown');
    if (!searchInput || !historyEl) return;

    // Tampilkan saat fokus
    searchInput.addEventListener('focus', () => renderSearchHistory());

    // Tutup jika mengklik di luar kotak pencarian dan dropdown
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !historyEl.contains(e.target)) {
            hideSearchHistory();
        }
    });

    // Filter saat mengetik
    searchInput.addEventListener('input', () => {
        if (searchInput.value.trim() === '') renderSearchHistory();
        else hideSearchHistory();
    });
}

function saveSearchHistory(query) {
    let history = getSearchHistory();
    history = history.filter(h => h.toLowerCase() !== query.toLowerCase()); // hapus duplikat
    history.unshift(query);
    if (history.length > MAX_HISTORY) history = history.slice(0, MAX_HISTORY);
    localStorage.setItem(HISTORY_KEY, JSON.stringify(history));
}

function getSearchHistory() {
    try { return JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]'); }
    catch { return []; }
}

function removeHistoryItem(query) {
    let history = getSearchHistory();
    history = history.filter(h => h.toLowerCase() !== query.toLowerCase());
    localStorage.setItem(HISTORY_KEY, JSON.stringify(history));
    renderSearchHistory();
    // Jika riwayat habis setelah menghapus satuan, sembunyikan dropdown
    if (history.length === 0) hideSearchHistory();
}

function renderSearchHistory() {
    const historyEl = document.getElementById('searchHistoryDropdown');
    if (!historyEl) return;
    const history = getSearchHistory();
    if (history.length === 0) { hideSearchHistory(); return; }

    historyEl.innerHTML = `
        <div class="history-header">
            <span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:6px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Riwayat Pencarian</span>
            <button class="history-clear-btn" onclick="clearSearchHistory()">Hapus Semua</button>
        </div>
        ${history.map(q => {
            const eq = escapeHtml(q);
            const sq = q.replace(/'/g, "\\'");
            return `
            <div class="history-item" onclick="pickHistory('${sq}')">
                <span class="history-icon"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
                <span class="history-text">${eq}</span>
                <button type="button" class="history-item-del" aria-label="Hapus dari riwayat" onclick="event.stopPropagation(); removeHistoryItem('${sq}')">×</button>
            </div>
        `;}).join('')}
    `;
    historyEl.style.display = 'block';
}

function hideSearchHistory() {
    const historyEl = document.getElementById('searchHistoryDropdown');
    if (historyEl) historyEl.style.display = 'none';
}

function pickHistory(query) {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.value = query;
        hideSearchHistory();
        document.getElementById('searchForm').dispatchEvent(new Event('submit', { cancelable: true }));
    }
}

function clearSearchHistory() {
    localStorage.removeItem(HISTORY_KEY);
    hideSearchHistory();
    showToast('Riwayat pencarian dihapus', 'info');
}

// ==================== Profile Management ====================
function openProfileModal() {
    const modal = document.getElementById('profileModal');
    if (modal) modal.style.display = 'flex';
}

function closeProfileModal() {
    const modal = document.getElementById('profileModal');
    if (modal) modal.style.display = 'none';
}

function switchProfileTab(tab) {
    const btnInfo = document.getElementById('tabInfoBtn');
    const btnPass = document.getElementById('tabPassBtn');
    const formInfo = document.getElementById('profileInfoForm');
    const formPass = document.getElementById('profilePassForm');

    if (tab === 'info') {
        btnInfo.classList.add('active');
        btnPass.classList.remove('active');
        formInfo.style.display = 'block';
        formPass.style.display = 'none';
    } else {
        btnPass.classList.add('active');
        btnInfo.classList.remove('active');
        formPass.style.display = 'block';
        formInfo.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const infoForm = document.getElementById('profileInfoForm');
    if (infoForm) {
        infoForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = infoForm.querySelector('button');
            const originalText = btn.innerText;
            btn.innerText = 'Menyimpan...';
            btn.disabled = true;

            try {
                const response = await fetch('/api/profile', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        name: document.getElementById('profileName').value,
                        email: document.getElementById('profileEmail').value
                    })
                });
                
                const data = await response.json();
                if (response.ok) {
                    showToast('Sukses', 'Profil berhasil diperbarui.', 'success');
                    // Update UI name
                    document.querySelectorAll('.user-name').forEach(el => el.innerText = data.user.name);
                    document.querySelectorAll('.user-avatar').forEach(el => el.innerText = data.user.name.substring(0,1).toUpperCase());
                    closeProfileModal();
                } else {
                    showToast('Gagal', data.message || 'Gagal memperbarui profil.', 'error');
                }
            } catch (err) {
                showToast('Error', 'Terjadi kesalahan jaringan.', 'error');
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        });
    }

    const passForm = document.getElementById('profilePassForm');
    if (passForm) {
        passForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = passForm.querySelector('button');
            const originalText = btn.innerText;
            
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const newPasswordConfirm = document.getElementById('newPasswordConfirm').value;

            if (newPassword !== newPasswordConfirm) {
                showToast('Gagal', 'Konfirmasi password baru tidak cocok.', 'warning');
                return;
            }

            btn.innerText = 'Menyimpan...';
            btn.disabled = true;

            try {
                const response = await fetch('/api/profile/password', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        current_password: currentPassword,
                        password: newPassword,
                        password_confirmation: newPasswordConfirm
                    })
                });
                
                const data = await response.json();
                if (response.ok) {
                    showToast('Sukses', 'Password berhasil diubah.', 'success');
                    passForm.reset();
                    closeProfileModal();
                } else {
                    // Tampilkan pesan error spesifik dari Laravel
                    let errMsg = data.message || 'Terjadi kesalahan.';
                    if (data.errors) {
                        const firstKey = Object.keys(data.errors)[0];
                        errMsg = data.errors[firstKey][0];
                    }
                    // Terjemahkan pesan umum Laravel ke Bahasa Indonesia
                    errMsg = errMsg
                        .replace('The password field must be at least 6 characters.', 'Password baru minimal 6 karakter.')
                        .replace('The current password is incorrect.', 'Password saat ini salah.')
                        .replace('The password field confirmation does not match.', 'Konfirmasi password tidak cocok.');
                    showToast('Gagal', errMsg, 'error');
                }
            } catch (err) {
                showToast('Error', 'Terjadi kesalahan jaringan.', 'error');
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        });
    }
});

// ==================== Mobile Panel Toggle ====================
function initMobileToggle() {
    if (window.innerWidth <= 768) {
        const leftPanel = document.querySelector('.left-panel');
        if (!leftPanel) return;
        
        const handle = document.createElement('div');
        handle.className = 'panel-handle';
        leftPanel.prepend(handle);
        
        let startY = 0;
        let startHeight = 0;
        let isDragging = false;
        let hasDragged = false;
        
        handle.addEventListener('touchstart', (e) => {
            startY = e.touches[0].clientY;
            startHeight = leftPanel.getBoundingClientRect().height;
            isDragging = true;
            hasDragged = false;
        }, { passive: true });
        
        handle.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            const currentY = e.touches[0].clientY;
            const diffY = startY - currentY;
            
            // Berikan toleransi 5px agar tap tidak tidak sengaja terbaca sebagai drag
            if (!hasDragged && Math.abs(diffY) > 5) {
                hasDragged = true;
                // Kunci height awal sebelum class collapsed dilepas agar tidak lompat
                leftPanel.style.height = `${startHeight}px`;
                leftPanel.classList.add('dragging');
                leftPanel.classList.remove('collapsed');
            }
            
            if (hasDragged) {
                let newHeight = startHeight + diffY;
                const minHeight = 28;
                const maxHeight = window.innerHeight * 0.85;
                
                if (newHeight < minHeight) newHeight = minHeight;
                if (newHeight > maxHeight) newHeight = maxHeight;
                
                leftPanel.style.height = `${newHeight}px`;
            }
        }, { passive: true });
        
        handle.addEventListener('touchend', () => {
            if (!isDragging) return;
            isDragging = false;
            
            if (!hasDragged) return; // Biarkan event click yang menangani jika hanya tap
            
            leftPanel.classList.remove('dragging');
            
            const currentHeight = leftPanel.getBoundingClientRect().height;
            const threshold = window.innerHeight * 0.25;
            
            // Snap logic
            if (currentHeight < threshold) {
                leftPanel.style.height = '';
                leftPanel.classList.add('collapsed');
            } else if (currentHeight > window.innerHeight * 0.6) {
                leftPanel.style.height = '80vh';
            } else {
                leftPanel.style.height = '42vh';
            }
        });
        
        handle.addEventListener('click', () => {
            if (hasDragged) {
                hasDragged = false;
                return;
            }
            if (leftPanel.classList.contains('collapsed')) {
                leftPanel.classList.remove('collapsed');
                leftPanel.style.height = '42vh';
            } else {
                leftPanel.style.height = '';
                leftPanel.classList.add('collapsed');
            }
        });
    }
}

// Global toggle password visibility — dipanggil dari auth blade views (login, register, reset-password)
function togglePass(id, btn) {
    const inp = document.getElementById(id);
    const on  = inp.type === 'text';
    inp.type = on ? 'password' : 'text';
    btn.querySelector('.eye-on').style.display  = on ? 'block' : 'none';
    btn.querySelector('.eye-off').style.display = on ? 'none' : 'block';
}
