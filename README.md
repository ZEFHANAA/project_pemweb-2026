# Sistem Pencarian & Simpan Lokasi Wisata

Aplikasi web untuk mencari, menyimpan, dan mengelola lokasi wisata favorit berbasis peta interaktif.

**Pengembang:** Zefhana Ananda (20240801047)  
**Mata Kuliah:** Pemrograman Web — 2026

🌍 **Live Demo:** [https://petawisata.my.id](https://petawisata.my.id)

---

## Tentang Aplikasi

Aplikasi ini dibuat menggunakan Laravel 12 dengan database MariaDB. Di sisi frontend, peta interaktif ditampilkan menggunakan Leaflet.js dan data pencarian lokasi diambil dari Nominatim API (OpenStreetMap). Seluruh logika frontend ditulis dalam JavaScript tanpa framework tambahan.

Untuk panel admin, digunakan Filament v3 yang bisa diakses di `/admin`. Panel ini hanya bisa diakses oleh user dengan role `super_admin`. Manajemen role dan permission menggunakan Spatie Permission + Filament Shield.

Lingkungan pengembangan menggunakan Docker (PHP + Nginx + MariaDB).

---

## Fitur

**Autentikasi**
- Register, login, logout
- Edit profil dan ganti password
- Halaman login & register dengan layout split-screen

**Peta & Pencarian**
- Peta interaktif dengan tile dari OpenStreetMap
- Pencarian lokasi via Nominatim API
- Foto/thumbnail lokasi otomatis diambil dari Wikipedia API
- Riwayat pencarian tersimpan di browser (bisa dihapus satu per satu)
- Tombol pencarian cepat (Borobudur, Raja Ampat, Gunung Bromo, Danau Toba)
- Warna pin/marker di peta berbeda tergantung kategori lokasi
- Dark Mode / Light Mode dengan preferensi tersimpan di browser

**Manajemen Lokasi**
- Simpan lokasi beserta deskripsi dan kategori
- Filter daftar lokasi berdasarkan kategori (Gunung, Pantai, dll)
- Edit dan hapus lokasi (satuan maupun semua sekaligus)
- Berbagi lokasi via link publik (bisa dilihat tanpa login)
- Buka koordinat langsung di Google Maps
- Export semua lokasi ke file Excel (.xls)

**Panel Admin (Filament — `/admin`)**
- CRUD user dan lokasi
- Dashboard dengan statistik (total user, total lokasi, kategori terpopuler, dll)
- Log aktivitas menggunakan Spatie Activity Log
- Manajemen role & permission via Filament Shield
- Edit profil admin

---

## Tech Stack

| Komponen | Teknologi |
|---|---|
| Backend | Laravel 12, PHP 8.2 |
| Database | MariaDB 10.11 |
| Frontend | Blade Templating, CSS, JavaScript |
| Peta | Leaflet.js, OpenStreetMap, Nominatim API |
| Admin Panel | Filament v3 |
| Permission | Spatie Permission, Filament Shield |
| Activity Log | Spatie Activity Log (filament-logger) |
| Font | Inter (Google Fonts) |
| Dev Environment | Docker (PHP + Nginx + MariaDB) |

---

## Cara Menjalankan

### Pakai Docker (Recommended)

```bash
# Clone repo
git clone <url-repository>
cd project_pemweb

# Jalankan container
docker compose up -d

# Masuk ke container PHP
docker exec -it project_pemweb_php bash

# Install dependencies
composer install

# Setup env
cp .env.example .env
php artisan key:generate

# Migrasi + seed data awal
php artisan migrate:fresh --seed
```

Akses di browser: `https://project_pemweb.test`

### Pakai Laragon / XAMPP

1. Atur koneksi database di file `.env`
2. Jalankan `composer install`
3. Jalankan `php artisan key:generate`
4. Jalankan `php artisan migrate:fresh --seed`
5. Jalankan `php artisan serve`
6. Buka `http://127.0.0.1:8000`

### Akun Default

| Role | Email | Password |
|---|---|---|
| Super Admin | admin@admin.com | password |
| User | user@admin.com | password |

Akun Super Admin sudah terisi beberapa lokasi wisata dummy untuk keperluan demo.

---

## Struktur Folder

```
project_pemweb/
├── docker-compose.yml
├── nginx/                          # Konfigurasi Nginx
├── php/                            # Dockerfile PHP
├── src/
│   ├── app/
│   │   ├── Filament/Admin/
│   │   │   ├── Resources/          # CRUD User & Lokasi di panel admin
│   │   │   └── Widgets/            # Widget statistik dashboard
│   │   ├── Http/Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── LokasiController.php
│   │   │   └── ProfileController.php
│   │   └── Models/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── public/
│   │   ├── css/style.css
│   │   └── js/script.js
│   ├── resources/views/
│   │   ├── auth/                   # Login & Register
│   │   ├── welcome.blade.php       # Halaman utama (peta)
│   │   └── lokasi-detail.blade.php # Halaman share lokasi publik
│   └── routes/web.php
└── README.md
```

---

## API Endpoints

Semua endpoint lokasi menggunakan session-based auth (cookie), bukan token.

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | /api/lokasi | Ambil semua lokasi milik user |
| POST | /api/lokasi | Simpan lokasi baru |
| GET | /api/lokasi/{id} | Detail satu lokasi |
| PUT | /api/lokasi/{id} | Update lokasi |
| DELETE | /api/lokasi/{id} | Hapus lokasi |
| GET | /api/lokasi/export | Export Excel (.xls) |
| PUT | /api/profile | Update profil |
| PUT | /api/profile/password | Ganti password |

Header `X-CSRF-TOKEN` wajib disertakan di request POST, PUT, dan DELETE.

---

## Keamanan

- CSRF protection di semua form dan API
- Password di-hash pakai bcrypt
- Data lokasi terisolasi per user (tidak bisa akses data user lain)
- Export CSV hanya berisi data milik user yang login
- Panel admin hanya bisa diakses role `super_admin`
