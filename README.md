# ANHA

Sistem penjualan & loyalitas pelanggan untuk **ANHA Electrical**: aplikasi mobile, REST API backend, dan web admin.

## Struktur

| Folder | Deskripsi |
|---|---|
| `anha_mobile/` | Aplikasi mobile (Flutter) untuk pelanggan ritel & grosir: katalog, pesanan, poin reward, notifikasi |
| `api.anhagroup.com/` | Backend REST API (Laravel + Sanctum) |
| `anha-laravel-app/` | Web admin (Laravel), terhubung ke backend via API |

## Tech Stack

- **Mobile:** Flutter, Dio, Flutter Secure Storage, Firebase Cloud Messaging
- **Backend:** Laravel, Laravel Sanctum, MySQL
- **Web Admin:** Laravel, Vite

## Dokumentasi

- [Spesifikasi & mapping mobile](DOKUMEN_SPESIFIKASI_DAN_MAPPING_MOBILE_ANHA.md)
- [Panduan deploy (DevOps)](README_DEVOPS_PANDUAN_DEPLOY.md)
- [Upload backend ke shared hosting](DOKUMEN_PANDUAN_UPLOAD_BACKEND_SHARED_HOSTING.md)
- [Upload web admin ke shared hosting](DOKUMEN_PANDUAN_UPLOAD_WEB_ADMIN_SHARED_HOSTING.md)
- [Changelog rilis](DOKUMEN_PERBAIKAN_DAN_CHANGELOG_RILIS.md)
- [Rilis mobile (AAB)](DOKUMEN_RILIS_MOBILE_AAB.md)

## Menjalankan

```bash
# Backend / Web admin
composer install && cp .env.example .env && php artisan key:generate

# Mobile
cd anha_mobile && flutter pub get && flutter run
```

> File sensitif (`.env`, keystore, sertifikat) sengaja tidak disertakan di repository.
