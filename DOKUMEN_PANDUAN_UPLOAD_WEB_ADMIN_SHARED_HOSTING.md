# Panduan Upload & Deployment Web Admin (FE Dashboard) ke Shared Hosting (cPanel)
**Sistem**: ANHA Electrical Web Admin (`anha-laravel-app`)  
**Domain Tujuan**: `admin.anhagroup.com` (atau folder `public_html/admin`)  
**Framework**: Laravel 11 / PHP 8.2 - 8.4  
**Target Hosting**: Shared Hosting / cPanel  

---

## 1. Arsitektur Folder Aman di Shared Hosting

Sama seperti backend, letakkan file sistem (core) Web Admin di luar direktori publik agar kode controller, view, dan konfigurasi rahasia tidak terekspos.

Contoh struktur direktori di server cPanel:
```text
/home/username/
├── anha_webadmin_core/             <-- Taruh seluruh file aplikasi Web Admin di sini
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── resources/
│   │   └── views/                  <-- Template Blade Dashboard Admin
│   ├── routes/
│   ├── storage/
│   ├── vendor/                     <-- Dependensi PHP Web Admin
│   ├── .env
│   ├── artisan
│   └── composer.json
└── public_html/ (atau admin.anhagroup.com/)
    ├── .htaccess                   <-- Dari folder 'public/' bawaan Web Admin
    ├── build/ (atau assets/css/js)  <-- Asset frontend hasil kompilasi
    ├── favicon.ico
    └── index.php                   <-- Diubah arah path-nya (lihat Bagian 4)
```

---

## 2. Checklist File yang HARUS Di-upload dan yang TIDAK BOLEH Di-upload

### A. File & Folder yang WAJIB Di-upload:
1. `app/` (Seluruh controller admin, models, exceptions, middleware, services `ApiClient.php`)
2. `bootstrap/` (Termasuk `bootstrap/app.php` dan `bootstrap/cache/`)
3. `config/` (Konfigurasi Laravel & `config/anha.php`)
4. `resources/` (Folder `views/` yang berisi seluruh tampilan dashboard Blade: catalog, orders, customers, rekenings, banner, settings)
5. `routes/` (`web.php`, `console.php`)
6. `storage/` (Penyimpanan framework views, sessions, logs)
7. `vendor/` (**PENTING**: Kompres folder `vendor/` lokal menjadi zip, upload, dan extract di server)
8. `artisan` (Script CLI)
9. `composer.json` & `composer.lock`
10. `.env` (File konfigurasi server produksi Web Admin)
11. Seluruh isi folder `public/` (Upload ke folder web publik `admin.anhagroup.com` atau `public_html`)

### B. File & Folder yang DILARANG / TIDAK PERLU Di-upload:
1. `.git/` & `.github/` (Tidak diperlukan di produksi)
2. `node_modules/` (Asset build sudah siap di `public/`, node_modules tidak boleh di-upload)
3. `tests/` (Unit testing lokal)
4. `storage/logs/*.log` (Bersihkan log lama)
5. `.DS_Store` / `Thumbs.db`
6. `.env.example`

---

## 3. Langkah-Langkah Upload & Ekstraksi File

1. **Kompresi File di Komputer Lokal**:
   - Di direktori `anha-laravel-app`, pilih folder `app`, `bootstrap`, `config`, `resources`, `routes`, `storage`, `vendor`, file `artisan`, `composer.json`, `composer.lock`.
   - Compress menjadi file ZIP, beri nama misal: `webadmin-core.zip`.
   - Kompres isi folder `public/` secara terpisah, beri nama misal: `webadmin-public.zip`.
2. **Unggah ke cPanel**:
   - Buka **cPanel** &rarr; **File Manager**.
   - Buka direktori `/home/username/`. Buat folder baru bernama `anha_webadmin_core`.
   - Masuk ke folder `anha_webadmin_core`, upload file `webadmin-core.zip`, lalu klik **Extract**.
   - Masuk ke folder dokumen web domain Web Admin (misal: `/home/username/admin.anhagroup.com/` atau `/home/username/public_html/admin/`). Upload file `webadmin-public.zip` lalu klik **Extract**.

---

## 4. Penyesuaian File `index.php` pada Folder Web Publik

Buka dan edit file `index.php` pada folder web publik (`admin.anhagroup.com`):

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Tentukan mode maintenance jika ada
if (file_exists($maintenance = __DIR__.'/../anha_webadmin_core/storage/framework/maintenance.php')) {
    require $maintenance;
}

// 1. Arahkan autoload vendor ke folder anha_webadmin_core
require __DIR__.'/../anha_webadmin_core/vendor/autoload.php';

// 2. Arahkan bootstrap app ke folder anha_webadmin_core
$app = require_once __DIR__.'/../anha_webadmin_core/bootstrap/app.php';

$app->handleRequest(Request::capture());
```

---

## 5. Konfigurasi File `.env` Produksi untuk Web Admin

Di folder `anha_webadmin_core/`, buat file `.env` dengan konfigurasi berikut:

```env
APP_NAME="ANHA Electrical Admin"
APP_ENV=production
APP_KEY=base64:... (Gunakan APP_KEY project Web Admin)
APP_DEBUG=false
APP_URL=https://admin.anhagroup.com

LOG_CHANNEL=stack
LOG_LEVEL=error

# KONEKSI KE BACKEND API (PENTING!)
# Web Admin beroperasi sebagai client yang menembak REST API Backend:
ANHA_API_BASE_URL=https://api.anhagroup.com/api/v1

# SESSION DRIVER (Gunakan file atau cookie)
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

FILESYSTEM_DISK=public
```

> **CATATAN**: Web Admin ANHA menggunakan `ApiClient` untuk berkomunikasi dengan Backend API. Web Admin tidak memerlukan koneksi direct MySQL ke database backend, melainkan bertransaksi aman via REST API Sanctum dengan token bearer.

---

## 6. Pengaturan Izin Folder (Permissions)

Pastikan izin folder disetel dengan benar:
- Folder umum: `0755`
- File umum: `0644`
- Folder yang membutuhkan hak tulis:
  - `anha_webadmin_core/storage/` &rarr; `0775`
  - `anha_webadmin_core/storage/framework/views/` &rarr; `0775`
  - `anha_webadmin_core/storage/framework/sessions/` &rarr; `0775`
  - `anha_webadmin_core/storage/logs/` &rarr; `0775`
  - `anha_webadmin_core/bootstrap/cache/` &rarr; `0775`

---

## 7. Optimasi & Pembersihan Cache

Jalankan perintah berikut melalui Terminal cPanel (atau melalui skrip sementara):
```bash
cd /home/username/anha_webadmin_core
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Setelah langkah ini selesai, Web Admin dapat dibuka melalui browser (misal: `https://admin.anhagroup.com`) dan langsung terhubung secara *real-time* ke Backend API!

