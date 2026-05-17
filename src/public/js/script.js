// ==================== Global Variables ====================
let map;
let markers = [];
let currentSearchMarker = null;
let savedLocations = [];
const API_URL = '/api/lokasi';
let currentEditingId = null;

// ==================== Initialization ====================
document.addEventListener('DOMContentLoaded', () => {
    initMap();
    loadLocations();
    setupEventListeners();
});

// ==================== Map Initialization ====================
function initMap() {
    map = L.map('map', { attributionControl: false }).setView([-6.2088, 106.8456], 5);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>',
        maxZoom: 19, minZoom: 2
    }).addTo(map);

    console.log('✓ Peta berhasil diinisialisasi');
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
        if (event.target === modal) {
            closeEditModal();
        }
    });
}

// ==================== Search Functionality ====================
async function handleSearch(e) {
    e.preventDefault();

    const searchInput = document.getElementById('searchInput');
    const query = searchInput.value.trim();

    if (!query) {
        showSearchError('Masukkan nama lokasi yang valid');
        return;
    }

    // Clear previous results and errors
    hideSearchError();
    hideSearchResult();
    setSearchBusy(true);
    clearCurrentSearchMarker();

    try {
        const data = await searchLocation(query);

        if (!data || data.length === 0) {
            showSearchError(`Lokasi "${query}" tidak ditemukan. Coba nama lain.`);
            setSearchBusy(false);
            return;
        }

        const location = data[0];
        displaySearchResult(location);
        addSearchMarkerToMap(location);
        setSearchBusy(false);

    } catch (error) {
        console.error('❌ Error:', error);
        showSearchError('Gagal mencari lokasi. Periksa koneksi internet Anda.');
        setSearchBusy(false);
    }
}

// ==================== Nominatim API ====================
async function searchLocation(query) {
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`;

    try {
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`HTTP Error: ${response.status}`);
        }

        const data = await response.json();
        return data;

    } catch (error) {
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

    resultContent.innerHTML = `
        <h3>${name}</h3>
        <div class="result-detail">
            <strong>Latitude:</strong> ${latitude.toFixed(6)}
        </div>
        <div class="result-detail">
            <strong>Longitude:</strong> ${longitude.toFixed(6)}
        </div>
        <div class="result-detail">
            <strong>Alamat:</strong> ${location.display_name}
        </div>
        <div class="result-detail">
            <label for="resultDeskripsi"><strong>Deskripsi (opsional):</strong></label>
            <textarea id="resultDeskripsi" rows="3" placeholder="Tambahkan deskripsi singkat"></textarea>
        </div>
        <button id="btnSaveResult" class="btn btn-secondary btn-save">
            Simpan Lokasi
        </button>
    `;

    showSearchResult(true);
    // attach event listener safely (avoid inline onclick quoting issues)
    const btn = document.getElementById('btnSaveResult');
    if (btn) {
        btn.addEventListener('click', () => {
            try {
                saveLokasi(name, latitude, longitude);
            } catch (e) {
                console.error('Error calling saveLokasi:', e);
                showSearchError('❌ Gagal menyimpan lokasi (client error)', 'error');
            }
        });
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
            message: '❌ Koordinat harus berupa angka yang valid'
        };
    }

    if (latitude < -90 || latitude > 90) {
        return {
            isValid: false,
            message: '❌ Latitude harus di antara -90 sampai 90'
        };
    }

    if (longitude < -180 || longitude > 180) {
        return {
            isValid: false,
            message: '❌ Longitude harus di antara -180 sampai 180'
        };
    }

    return { isValid: true, message: '' };
}

// ==================== Save Location ====================
async function saveLokasi(nama_lokasi, latitude, longitude) {
    // Cek status login via meta tag auth-status (lebih reliable dari form selector)
    const authMeta = document.querySelector('meta[name="auth-status"]');
    const isAuthenticated = authMeta && authMeta.content === 'authenticated';

    console.log(`📌 Auth status meta: "${authMeta?.content}" → isAuthenticated: ${isAuthenticated}`);

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
    if (saveBtnEl) {
        saveBtnEl.disabled = true;
        saveBtnEl.textContent = 'Menyimpan...';
    }

    try {
        // read optional description from search result textarea
        const resultDescEl = document.getElementById('resultDeskripsi');
        const resultDeskripsi = resultDescEl ? resultDescEl.value.trim() : '';

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
                deskripsi: resultDeskripsi
            })
        });

        if (!response.ok) {
            if (response.status === 401) {
                showSearchError('Please login to save this location', 'error');
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
        showSearchError('✓ Lokasi berhasil disimpan!', 'success');
        console.log('✓ Lokasi disimpan:', newLocation);
    } catch (error) {
        console.error('❌ Error:', error);
        showSearchError('❌ Gagal menyimpan lokasi', 'error');
    } finally {
        if (saveBtnEl) {
            saveBtnEl.disabled = false;
            saveBtnEl.textContent = 'Simpan Lokasi';
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
        console.log(`✓ ${savedLocations.length} lokasi dimuat dari database`);
        
        // Check if authenticated based on locations count
        // If we got locations, user is either authenticated (own locations) or guest (all locations)
        const authStatusMeta = document.querySelector('meta[name="auth-status"]')?.content;
        if (authStatusMeta === 'guest') {
            window.isAuthenticated = false;
        } else {
            window.isAuthenticated = true;
        }
    } catch (error) {
        console.error('❌ Error loading locations:', error);
        savedLocations = [];
        window.isAuthenticated = false;
    }

    renderLocationsList();
    renderMarkersOnMap();
    updateSavedCount();
}

// ==================== Render Locations List ====================
function renderLocationsList() {
    const locationsList = document.getElementById('locationsList');
    const clearAllBtn = document.getElementById('clearAllBtn');
    updateSavedCount();

    if (savedLocations.length === 0) {
        locationsList.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">🗺️</div>
                <p class="empty-title">Belum ada lokasi tersimpan</p>
                <p class="empty-hint">Cari lokasi wisata di atas,<br>lalu klik <strong>Simpan Lokasi</strong></p>
            </div>`;
        clearAllBtn.style.display = 'none';
        return;
    }

    clearAllBtn.style.display = 'block';

    const listHTML = savedLocations.map(location => {
        const lat = Number(location.latitude);
        const lng = Number(location.longitude);
        const latStr = Number.isFinite(lat) ? lat.toFixed(4) : '';
        const lngStr = Number.isFinite(lng) ? lng.toFixed(4) : '';
        const safeName = (location.nama_lokasi || '').replace(/'/g, "\\'").replace(/\n/g, ' ');
        const desc = location.deskripsi ? location.deskripsi : '';

        return `
        <li class="location-item" data-location-id="${location.id}">
            <div class="location-item-content" tabindex="0" onclick="focusLocation(${location.id}, ${lat || 0}, ${lng || 0})">
                <div class="location-item-name">${location.nama_lokasi}</div>
                <div class="location-item-coords">
                    ${latStr}, ${lngStr}
                </div>
                <div class="location-item-desc">${desc}</div>
            </div>
            <div class="location-item-actions">
                <button type="button" aria-label="Lihat ${safeName}" class="btn-view" onclick="focusLocation(${location.id}, ${lat || 0}, ${lng || 0})">
                    Lihat
                </button>
                <button type="button" aria-label="Edit ${safeName}" class="btn-edit" onclick="openEditModal(${location.id}, '${safeName}', ${lat || 0}, ${lng || 0})">
                    Edit
                </button>
                <button type="button" aria-label="Hapus ${safeName}" class="btn-delete" onclick="deleteLokasi(${location.id})">
                    Hapus
                </button>
            </div>
        </li>
    `;
    }).join('');

    locationsList.innerHTML = `<ul class="locations-list-ul">${listHTML}</ul>`;

    console.log(`✓ ${savedLocations.length} lokasi ditampilkan`);
}

// ==================== Delete Location ====================
async function deleteLokasi(id) {
    const index = savedLocations.findIndex(loc => loc.id === id);

    if (index === -1) {
        console.error('❌ Lokasi tidak ditemukan');
        return;
    }

    const confirmed = confirm('Hapus lokasi ini dari daftar?');
    if (!confirmed) return;

    try {
        const response = await fetch(`${API_URL}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });

        if (!response.ok) {
            if (response.status === 401) {
                showSearchError('⚠️ Silakan login terlebih dahulu untuk menghapus lokasi', 'error');
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

        showSearchError('✓ Lokasi berhasil dihapus!', 'success');
        console.log('✓ Lokasi dihapus:', deletedLocation.nama_lokasi);
    } catch (error) {
        console.error('❌ Error:', error);
        showSearchError('❌ Gagal menghapus lokasi', 'error');
    }
}

// ==================== Export CSV ====================
async function handleExportCsv() {
    // Cek login
    const authMeta = document.querySelector('meta[name="auth-status"]');
    if (!authMeta || authMeta.content !== 'authenticated') {
        showSearchError('⚠️ Silakan login terlebih dahulu untuk mengekspor CSV', 'error');
        return;
    }

    // Cek apakah ada lokasi
    if (savedLocations.length === 0) {
        showSearchError('⚠️ Tidak ada lokasi tersimpan untuk diekspor', 'error');
        return;
    }

    try {
        const response = await fetch(`${API_URL}/export`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });

        if (response.status === 401) {
            showSearchError('⚠️ Silakan login terlebih dahulu untuk mengekspor CSV', 'error');
            return;
        }

        if (!response.ok) {
            const txt = await response.text();
            console.error('Export failed:', txt);
            showSearchError('❌ Gagal mengekspor CSV', 'error');
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
        showSearchError(`✓ CSV berhasil diunduh (${savedLocations.length} lokasi)`, 'success');
    } catch (error) {
        console.error('Export error:', error);
        showSearchError('❌ Gagal mengekspor CSV', 'error');
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
            })
        );

        await Promise.all(deletePromises);

        savedLocations = [];
        renderLocationsList();
        clearAllMarkersFromMap();

        showSearchError('✓ Semua lokasi berhasil dihapus!', 'success');
        console.log('✓ Semua lokasi dihapus');
    } catch (error) {
        console.error('❌ Error:', error);
        showSearchError('❌ Gagal menghapus semua lokasi', 'error');
    }
}

// ==================== Update Location ====================
async function updateLokasi(id, nama_lokasi, latitude, longitude, deskripsi = '') {
    const index = savedLocations.findIndex(loc => loc.id === id);

    if (index === -1) {
        showSearchError('❌ Lokasi tidak ditemukan', 'error');
        console.error('❌ Lokasi tidak ditemukan');
        return;
    }

    const cleanName = nama_lokasi.trim();
    if (!cleanName) {
        showSearchError('❌ Nama lokasi tidak boleh kosong', 'error');
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
                    deskripsi: deskripsi || (savedLocations[index].deskripsi || '')
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
        showSearchError('✓ Lokasi berhasil diubah!', 'success');
        console.log('✓ Lokasi diupdate dari:', oldLocation.nama_lokasi, 'menjadi:', savedLocations[index].nama_lokasi);
    } catch (error) {
        console.error('❌ Error:', error);
        showSearchError('❌ Gagal mengupdate lokasi', 'error');
    }
}

// ==================== Map Markers ====================
function addMarkerToMap(location) {
    const lat = Number(location.latitude);
    const lng = Number(location.longitude);
    const marker = L.marker([lat, lng], {
        icon: L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        })
    }).bindPopup(`
        <div style="text-align: center;">
            <h4 style="margin: 5px 0; color: #333;">${location.nama_lokasi}</h4>
            <p style="margin: 5px 0; font-size: 12px; color: #666;">
                Lat: ${Number.isFinite(lat) ? lat.toFixed(4) : ''}<br>
                Lon: ${Number.isFinite(lng) ? lng.toFixed(4) : ''}
            </p>
            ${location.deskripsi ? `<p style="margin:5px 0; font-size:12px; color:#555;">${location.deskripsi}</p>` : ''}
        </div>
    `);

    marker.locationId = location.id;
    markers.push(marker);
    marker.addTo(map);

    console.log('✓ Marker ditambahkan:', location.nama_lokasi);
}

function renderMarkersOnMap() {
    clearAllMarkersFromMap();
    savedLocations.forEach(location => addMarkerToMap(location));
}

function removeMarkerFromMap(id) {
    const index = markers.findIndex(m => m.locationId === id);
    if (index !== -1) {
        map.removeLayer(markers[index]);
        markers.splice(index, 1);
        console.log('✓ Marker dihapus');
    }
}

function clearAllMarkersFromMap() {
    markers.forEach(marker => map.removeLayer(marker));
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
            <h4 style="margin: 5px 0; color: #667eea;">${name}</h4>
            <p style="margin: 5px 0; font-size: 12px; color: #666;">
                Hasil Pencarian
            </p>
        </div>
    `);

    currentSearchMarker.addTo(map);
    map.flyTo([latitude, longitude], 13);
    currentSearchMarker.openPopup();

    console.log('✓ Marker pencarian ditambahkan');
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

    console.log(`🔍 Zoom ke lokasi: ${latitude.toFixed(4)}, ${longitude.toFixed(4)}`);
}

function focusLocation(id, latitude, longitude) {
    highlightActiveLocation(id);
    zoomToLocation(latitude, longitude);
}

// ==================== UI Helper Functions ====================
function showSearchResult(show = true) {
    document.getElementById('searchResult').style.display = show ? 'block' : 'none';
}

function hideSearchResult() {
    showSearchResult(false);
}

function showSearchError(message, type = 'error') {
    const errorDiv = document.getElementById('searchError');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';

    if (type === 'success') {
        errorDiv.style.background = '#e7f5e7';
        errorDiv.style.borderLeftColor = '#51cf66';
        errorDiv.style.color = '#2d7a2d';
    } else {
        errorDiv.style.background = '#ffe0e0';
        errorDiv.style.borderLeftColor = '#ff6b6b';
        errorDiv.style.color = '#c92a2a';
    }

    // Auto-hide success messages after 3 seconds
    if (type === 'success') {
        setTimeout(() => hideSearchError(), 3000);
    }
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
    document.getElementById('enableCoordEdit').checked = false;
    setCoordinateEditingEnabled(false);
    document.getElementById('editModal').style.display = 'flex';
    console.log('✓ Edit modal dibuka untuk lokasi ID:', id);
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    document.getElementById('enableCoordEdit').checked = false;
    setCoordinateEditingEnabled(false);
    currentEditingId = null;
    console.log('✓ Edit modal ditutup');
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
    updateLokasi(currentEditingId, nama_lokasi, latitude, longitude, editDeskripsi);
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
