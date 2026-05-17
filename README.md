# 🗺️ Sistem Pencarian & Simpan Lokasi Wisata

> Aplikasi web untuk mencari, menyimpan, dan mengelola lokasi wisata favorit berbasis peta interaktif.

---

## 👤 Tentang Pengembang

| | |
|---|---|
| **Nama** | Zefhana Ananda |
| **NIM** | 20240801047 |
| **Mata Kuliah** | Pemrograman Web |
| **Tahun** | 2026 |

---

## 📋 Deskripsi Proyek

Sistem Pencarian & Simpan Lokasi Wisata adalah aplikasi web berbasis Laravel yang memungkinkan pengguna untuk:

- 🔍 Mencari lokasi wisata di seluruh Indonesia menggunakan Nominatim API (OpenStreetMap)
- 📌 Menyimpan lokasi favorit ke dalam database
- ✏️ Mengedit nama dan deskripsi lokasi tersimpan
- 🗑️ Menghapus lokasi yang tidak diperlukan
- 📥 Mengekspor daftar lokasi ke file CSV
- 🗺️ Melihat lokasi di peta interaktif berbasis Leaflet.js

---

## 🛠️ Teknologi yang Digunakan

| Teknologi | Kegunaan |
|---|---|
| **Laravel 12** | Backend framework (PHP) |
| **MariaDB 10.11** | Database penyimpanan lokasi |
| **Filament v3** | Panel admin (manajemen user, log aktivitas) |
| **Filament Shield** | Manajemen permission berbasis role di admin |
| **Spatie Permission** | Sistem role: `super_admin` & `user` |
| **Spatie Activity Log** | Log aktivitas admin (akses, perubahan data) |
| **Leaflet.js** | Peta interaktif |
| **OpenStreetMap** | Tile peta |
| **Nominatim API** | Geocoding (pencarian lokasi) |
| **Vanilla JavaScript** | Logika frontend |
| **Inter (Google Fonts)** | Tipografi |
| **Docker** | Lingkungan pengembangan |

---

## ✨ Fitur Lengkap

### 🔐 Autentikasi
- Registrasi akun baru dengan validasi password strength
- Login / Masuk dengan session berbasis web (bukan token API)
- Logout / Keluar yang aman
- Halaman login & register dengan desain modern (split-layout)

### 🗺️ Peta & Pencarian
- Peta interaktif Indonesia dengan OpenStreetMap
- Pencarian lokasi real-time via Nominatim API
- Quick-chips untuk pencarian cepat (Monas, Borobudur, Bromo, Danau Toba)
- Marker pada peta untuk setiap lokasi tersimpan
- Popup informasi saat marker diklik

### 📁 Manajemen Lokasi
- Simpan lokasi beserta deskripsi pribadi
- Edit nama, koordinat, dan deskripsi lokasi
- Hapus lokasi satu per satu atau semua sekaligus
- Lokasi bersifat **privat** — hanya bisa dilihat oleh pemiliknya
- Export semua lokasi milik user ke file **CSV** (dengan BOM UTF-8 untuk kompatibilitas Excel)

### 🛡️ Panel Admin (Filament — `/admin`)
- Manajemen user: tambah, edit, hapus akun pengguna
- Manajemen role & permission via Filament Shield (`super_admin`, `user`)
- Widget log aktivitas terbaru (`LatestAccessLogs`)
- Edit profil admin langsung dari panel
- Hanya bisa diakses oleh user dengan role `super_admin`


## 🚀 Cara Menjalankan

### Prasyarat
- Docker & Docker Compose terinstal
- Git

### Langkah Instalasi

```bash
# 1. Clone repository
git clone <url-repository>
cd project_pemweb

# 2. Jalankan Docker
docker compose up -d

# 3. Masuk ke container PHP
docker exec -it project_pemweb_php bash

# 4. Install dependensi
composer install

# 5. Setup environment
cp .env.example .env
php artisan key:generate

# 6. Migrasi database
php artisan migrate

# 7. Seed akun demo (admin + user)
php artisan db:seed
```

### Akses Aplikasi
- **URL Utama**: `https://project_pemweb.test`
- **Dashboard Admin (Filament)**: `https://project_pemweb.test/admin`

### Akun Default (setelah `db:seed`)
| Role | Email | Password |
|---|---|---|
| Super Admin | `admin@admin.com` | `password` |
| User | `user@admin.com` | `password` |

---

## 📁 Struktur Proyek

```
project_pemweb/
├── src/
│   ├── app/
│   │   ├── Filament/
│   │   │   ├── Admin/
│   │   │   │   ├── Resources/
│   │   │   │   │   └── UserResource.php        # CRUD user di panel admin
│   │   │   │   └── Widgets/
│   │   │   │       └── LatestAccessLogs.php    # Widget log aktivitas
│   │   │   └── Pages/Auth/
│   │   │       └── EditProfile.php             # Halaman edit profil admin
│   │   ├── Http/
│   │   │   └── Controllers/
│   │   │       ├── AuthController.php          # Login, register, logout
│   │   │       └── LokasiController.php        # CRUD lokasi + export CSV
│   │   ├── Models/
│   │   │   ├── User.php
│   │   │   └── Lokasi.php
│   │   └── Providers/
│   │       └── Filament/
│   │           └── AdminPanelProvider.php      # Konfigurasi panel admin
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   │       ├── DatabaseSeeder.php
│   │       ├── RoleSeeder.php                  # Seed role: super_admin, user
│   │       └── UserSeeder.php                  # Seed akun demo
│   ├── public/
│   │   ├── css/
│   │   │   └── style.css                      # Stylesheet utama
│   │   └── js/
│   │       └── script.js                      # Logika frontend
│   ├── resources/
│   │   └── views/
│   │       ├── auth/
│   │       │   ├── login.blade.php             # Halaman masuk
│   │       │   └── register.blade.php          # Halaman daftar
│   │       └── welcome.blade.php               # Halaman utama (peta)
│   └── routes/
│       ├── web.php                             # Route utama + API lokasi
│       └── api.php
└── README.md
```

---

## 🔌 API Endpoints

Semua endpoint menggunakan **web middleware** (session-based) sehingga autentikasi menggunakan cookie session Laravel.

| Method | Endpoint | Deskripsi | Auth |
|---|---|---|---|
| `GET` | `/api/lokasi` | Ambil semua lokasi user | ✅ Required |
| `POST` | `/api/lokasi` | Simpan lokasi baru | ✅ Required |
| `GET` | `/api/lokasi/{id}` | Detail satu lokasi | ✅ Required |
| `PUT` | `/api/lokasi/{id}` | Update lokasi | ✅ Required |
| `DELETE` | `/api/lokasi/{id}` | Hapus lokasi | ✅ Required |
| `GET` | `/api/lokasi/export` | Export CSV lokasi user | ✅ Required |

> **Catatan**: Header `X-CSRF-TOKEN` wajib disertakan pada semua request mutasi (POST, PUT, DELETE).

---

## 🎨 Desain UI

- **Header**: Biru gelap (`#1e3a8a`) dengan logo, judul, dan tombol Masuk/Daftar
- **Panel Kiri**: Panel putih berisi form pencarian, chip cepat, dan daftar lokasi tersimpan
- **Panel Kanan**: Peta interaktif OpenStreetMap yang mengisi sisa area
- **Font**: Inter (Google Fonts) untuk keterbacaan optimal
- **Responsive**: Mendukung tampilan mobile dan tablet

---

## 🔒 Keamanan

- CSRF Protection pada semua form dan API request
- Password di-hash menggunakan bcrypt
- Lokasi terisolasi per user (tidak bisa akses data user lain)
- Guest mendapat respons kosong `[]` pada endpoint lokasi
- Export CSV hanya berisi data milik user yang sedang login

---

## 📌 Catatan Pengembangan

- Route API lokasi ditempatkan di `web.php` (bukan `api.php`) agar mendapatkan akses session untuk autentikasi berbasis cookie
- Route `/api/lokasi/export` harus didaftarkan **sebelum** route `{lokasi}` untuk menghindari konflik parameter
- File CSV yang diekspor menyertakan BOM UTF-8 (`\xEF\xBB\xBF`) agar karakter Indonesia terbaca dengan benar di Microsoft Excel

---

## 📸 Tampilan Aplikasi

| Halaman | Deskripsi |
|---|---|
| **Beranda** | Peta interaktif + panel pencarian & daftar lokasi |
| **Masuk** | Form login dengan split-layout modern |
| **Daftar** | Form registrasi dengan indikator kekuatan password |

---

*Dibuat sebagai tugas Pemrograman Web — 2026*
