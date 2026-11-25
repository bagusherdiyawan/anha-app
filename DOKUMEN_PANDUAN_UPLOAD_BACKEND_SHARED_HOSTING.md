# Panduan Upload & Deployment Backend API ke Shared Hosting (cPanel)
**Sistem**: ANHA Electrical REST API Backend (`api.anhagroup.com`)  
**Framework**: Laravel 11 / PHP 8.2 - 8.4  
**Target Hosting**: Shared Hosting / cPanel (Cloud Hosting)  

---

## 1. Arsitektur Folder Aman di Shared Hosting

Pada shared hosting cPanel, **JANGAN PERNAH** menaruh seluruh folder Laravel langsung di dalam `public_html`. File konfigurasi rahasia seperti `.env` dan kode sistem bisa diunduh oleh publik jika web server salah konfigurasi.

Gunakan arsitektur folder terpisah berikut:
```text
/home/username/
├── anha_backend_core/              <-- Taruh SELURUH file Laravel di sini (DI LUAR public_html)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   ├── artisan
│   └── composer.json
└── public_html/ (atau sub-domain folder, misal: api.anhagroup.com/)
    ├── .htaccess                  <-- Dari folder 'public/' bawaan Laravel
    ├── favicon.ico
    ├── index.php                  <-- Diubah arah path-nya (lihat Bagian 4)
    ├── robots.txt
    └── storage/ (symlink)         <-- Tautan ke folder storage aplikasi
```

---

## 2. Checklist File yang HARUS Di-upload dan yang TIDAK BOLEH Di-upload

### A. File & Folder yang WAJIB Di-upload ke Hosting:
1. `app/` (Seluruh kode kontroler, model, middleware, services)
2. `bootstrap/` (Termasuk `bootstrap/app.php` dan `bootstrap/cache/`)
3. `config/` (Seluruh file konfigurasi Laravel)
4. `database/` (Migrations, seeders)
5. `routes/` (`api.php`, `web.php`, `console.php`)
6. `storage/` (Folder penyimpanan log, framework, dan app uploads)
7. `vendor/` (**PENTING**: Folder dependensi PHP. Karena shared hosting biasanya tidak memiliki akses composer update, folder `vendor/` harus di-compress zip dari komputer lokal dan di-upload lalu di-extract di cPanel).
8. `artisan` (Script CLI Laravel)
9. `composer.json` & `composer.lock`
10. `.env` (File konfigurasi kredensial produksi)
11. Seluruh isi folder `public/` (Dipindahkan ke folder domain tujuan di cPanel)

### B. File & Folder yang DILARANG / TIDAK PERLU Di-upload:
1. `.git/` & `.github/` (Sangat rahasia & boros memori)
2. `node_modules/` (Backend API tidak memerlukan node_modules di hosting)
3. `tests/` (Tidak diperlukan di server produksi)
4. `storage/logs/*.log` (Hapus isi log lama agar ukuran zip kecil)
5. `.DS_Store` / `Thumbs.db` (File sampah OS)
6. `.env.example` (Hanya opsional/dokumentasi)

---

## 3. Langkah-Langkah Upload & Ekstraksi File

1. **Kompresi File di Komputer Lokal**:
   - Di direktori backend `api.anhagroup.com`, pilih folder `app`, `bootstrap`, `config`, `database`, `routes`, `storage`, `vendor`, file `artisan`, `composer.json`, `composer.lock`.
   - Klik kanan &rarr; Compress / Buat file ZIP, beri nama misal: `backend-api-core.zip`.
   - Buat file ZIP terpisah untuk isi folder `public/`, beri nama misal: `backend-api-public.zip`.
2. **Unggah ke cPanel**:
   - Masuk ke cPanel &rarr; **File Manager**.
   - Buka direktori root `/home/username/`. Buat folder baru bernama `anha_backend_core`.
   - Masuk ke folder `anha_backend_core`, upload file `backend-api-core.zip`, lalu klik **Extract**.
   - Masuk ke folder dokumen web domain backend (misal: `/home/username/public_html/` atau `/home/username/api.anhagroup.com/`). Upload file `backend-api-public.zip` lalu klik **Extract**.

---

## 4. Penyesuaian File `index.php` pada Folder Web Publik

Buka dan edit file `index.php` yang berada di folder publik (`public_html` atau `api.anhagroup.com`). Sesuaikan baris `require` agar mengarah ke folder core di luar public:

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Tentukan mode maintenance jika ada
if (file_exists($maintenance = __DIR__.'/../anha_backend_core/storage/framework/maintenance.php')) {
    require $maintenance;
}

// 1. Arahkan autoload vendor ke folder anha_backend_core
require __DIR__.'/../anha_backend_core/vendor/autoload.php';

// 2. Arahkan bootstrap app ke folder anha_backend_core
$app = require_once __DIR__.'/../anha_backend_core/bootstrap/app.php';

$app->handleRequest(Request::capture());
```

---

## 5. Konfigurasi File `.env` Produksi

Di folder `anha_backend_core/`, buat atau sesuaikan file `.env` dengan kredensial server produksi:

```env
APP_NAME="ANHA Electrical API"
APP_ENV=production
APP_KEY=base64:... (Gunakan APP_KEY yang sudah ada dari project)
APP_DEBUG=false
APP_URL=https://api.anhagroup.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# KREDENSIAL DATABASE CPANEL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cpaneluser_anhadb
DB_USERNAME=cpaneluser_anhadbuser
DB_PASSWORD=PasswordDatabaseAnda!

# SANCTUM & CORS
SANCTUM_STATEFUL_DOMAINS=anhagroup.com,admin.anhagroup.com,api.anhagroup.com
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public
```

---

## 6. Pengaturan Izin Folder (File Permissions)

Pastikan izin folder disetel dengan benar di File Manager cPanel:
- Folder biasa: `0755`
- File biasa: `0644`
- Folder yang membutuhkan hak tulis:
  - `anha_backend_core/storage/` &rarr; `0775` (atau `0777` jika server tidak mengizinkan write php user)
  - `anha_backend_core/storage/app/` &rarr; `0775`
  - `anha_backend_core/storage/framework/` &rarr; `0775`
  - `anha_backend_core/storage/logs/` &rarr; `0775`
  - `anha_backend_core/bootstrap/cache/` &rarr; `0775`

---

## 7. Storage Symlink untuk Gambar & Dokumen Upload

Agar foto produk, banner, dan logo rekening yang di-upload bisa diakses publik:
1. **Jika cPanel memiliki fitur Terminal**:
   Buka menu **Terminal** di cPanel dan jalankan:
   ```bash
   cd /home/username/anha_backend_core
   php artisan storage:link
   ```
2. **Jika cPanel TIDAK memiliki akses Terminal**:
   Gunakan script PHP darurat. Buat file `symlink.php` di folder publik (`api.anhagroup.com`), isi kodenya:
   ```php
   <?php
   $targetFolder = '/home/username/anha_backend_core/storage/app/public';
   $linkFolder = __DIR__.'/storage';
   if (!file_exists($linkFolder)) {
       symlink($targetFolder, $linkFolder);
       echo 'Storage symlink berhasil dibuat!';
   } else {
       echo 'Symlink sudah ada.';
   }
   ```
   Akses `https://api.anhagroup.com/symlink.php` melalui browser sekali, lalu **SEGERA HAPUS** file `symlink.php` tersebut.

---

## 8. Pembersihan Cache (Clear Cache)

Jalankan perintah berikut di Terminal cPanel (atau via route script sementara) setiap kali selesai upload:
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```
Backend API kini siap melayani request dari Web Admin dan Aplikasi Mobile ANHA!

