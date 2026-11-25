# Panduan Deployment & Update Patch (DevOps / Server Engineer)
**Proyek**: ANHA Electrical (Backend API & Web Admin)  
**Versi Update**: v1.0.7 Patch  
**Tanggal**: 14 November 2026  
**Target Server**: Shared Hosting (cPanel) / Cloud VPS Linux (PHP 8.2 - 8.4)  

---

## 1. Ringkasan Perubahan (Changelog Patch)

Update patch ini memuat perbaikan kritis sinkronisasi backend dan view web admin:
1. **Loyalty Summary**: Penambahan agregasi `total_earned`, `total_spent`, dan `expired` dari relasi transaksi poin pelanggan (`LoyaltyController.php`).
2. **Keranjang & Checkout**: Resolving harga aktif peran (`ritel` / `grosir`), validasi voucher server-side, serta kalkulasi ongkos kirim ekspres (`KeranjangController.php`, `CheckoutController.php`).
3. **Detail Order & Satuan**: Enrichment relasi item order, foto produk, dan join tabel `barangs_satuan` agar unit barang (Roll, Meter, Pcs) tampil dengan nama aslinya bukan ID angka (`OrderController.php`, `BarangFrontController.php`, `PromoFrontController.php`, `BarangResource.php`, `Barang.php`).
4. **Database Migration**: Penambahan kolom `shipping_cost` pada tabel `orders`.
5. **Web Admin Dashboard**: Penambahan baris rincian "Ongkos Kirim" pada invoice order di Web Admin (`orders/show.blade.php`).

---

## 2. Struktur File Paket Update

Semua file dalam paket ini sudah diletakkan sesuai path direktori aslinya di Laravel:

```text
UPDATE_PACKAGE/
├── api.anhagroup.com/                                          <-- Backend REST API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/V1/
│   │   │   │   ├── admin/
│   │   │   │   │   └── Kategori_barangController.php
│   │   │   │   └── user/
│   │   │   │       ├── BarangFrontController.php
│   │   │   │       ├── CheckoutController.php
│   │   │   │       ├── KeranjangController.php
│   │   │   │       ├── LoyaltyController.php
│   │   │   │       ├── OrderController.php
│   │   │   │       └── PromoFrontController.php
│   │   │   └── Resources/
│   │   │       ├── BarangPerKategoriResource.php
│   │   │       └── BarangResource.php
│   │   └── Models/
│   │       ├── Barang.php
│   │       └── Order.php
│   └── database/
│       └── migrations/
│           └── 2027_05_10_000300_add_shipping_cost_to_orders_table.php
├── anha-laravel-app/                                           <-- Web Admin Dashboard
│   └── resources/
│       └── views/
│           └── admin/
│               └── transactions/
│                   └── orders/
│                       └── show.blade.php
├── migration_shipping_cost.sql                                 <-- Script SQL manual jika tanpa CLI
└── README_DEVOPS_PANDUAN_DEPLOY.md                             <-- Dokumen ini
```

---

## 3. Langkah-Langkah Deployment (Step-by-Step)

### Langkah 1: Backup Pengaman (Safety First)
1. **Export Database**:
   ```bash
   mysqldump -u <db_user> -p <db_name> > backup_before_update_$(date +%Y%m%d_%H%M%S).sql
   ```
   *(Atau lewat phpMyAdmin > Export).*
2. **Backup file lama** yang akan ditimpa (terutama controller dan model di atas).

---

### Langkah 2: Copy / Timpa File ke Server Produksi

#### Untuk Backend API:
Timpa file-file dari folder `api.anhagroup.com/` ke direktori backend core di server (misal: `/home/username/anha_backend_core/` atau root app backend).

#### Untuk Web Admin:
Timpa file `show.blade.php` dari `anha-laravel-app/resources/views/admin/transactions/orders/show.blade.php` ke direktori web admin di server (misal: `/home/username/anha_webadmin_core/resources/views/admin/transactions/orders/show.blade.php`).

---

### Langkah 3: Eksekusi Migrasi Database

Pilih salah satu metode berikut:

* **Opsi A: Menggunakan Artisan CLI (Direkomendasikan)**:
  Masuk ke direktori core backend API di server:
  ```bash
  cd /home/username/anha_backend_core
  php artisan migrate --force
  ```

* **Opsi B: Menggunakan SQL Query Langsung (via phpMyAdmin / MySQL CLI)**:
  Jika hosting tidak memiliki akses Terminal/SSH, buka phpMyAdmin atau MySQL console lalu jalankan file `migration_shipping_cost.sql`:
  ```sql
  ALTER TABLE `orders` 
  ADD COLUMN IF NOT EXISTS `shipping_cost` DECIMAL(15, 2) NOT NULL DEFAULT 0.00 
  AFTER `discount_amount`;
  ```

---

### Langkah 4: Clear & Re-cache Konfigurasi Laravel

Jalankan perintah pembersihan cache agar perubahan file controller, model, dan view langsung terbaca oleh PHP OPcache & Laravel runtime:

#### Pada Backend API:
```bash
cd /home/username/anha_backend_core
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
```

#### Pada Web Admin:
```bash
cd /home/username/anha_webadmin_core
php artisan optimize:clear
php artisan view:clear
```

---

## 4. Verifikasi Pasca Deploy (Smoke Test)

1. **Uji Endpoint Loyalty Summary**:
   ```bash
   curl -s -H "Authorization: Bearer <TOKEN_USER>" https://api.anhagroup.com/api/v1/loyalty/summary
   ```
   *Pastikan response JSON memuat key: `points_balance`, `total_earned`, `total_spent`, `expired`.*
2. **Uji Endpoint Landing / Kategori Barang**:
   ```bash
   curl -s https://api.anhagroup.com/api/v1/kategori-barang-landing
   ```
   *Pastikan status HTTP 200 OK.*
3. **Uji Halaman Detail Order di Web Admin**:
   Buka salah satu transaksi pesanan di URL Web Admin:
   `https://admin.anhagroup.com/transactions/orders/<id>`
   *Pastikan halaman tampil normal dan rincian ongkos kirim tampil jika ada.*

---

## 5. Rencana Rollback (Jika Diperlukan)

Jika terjadi kendala saat verifikasi:
1. Kembalikan file controller/model lama dari backup cadangan.
2. Jika perlu me-revert kolom database:
   ```sql
   ALTER TABLE `orders` DROP COLUMN `shipping_cost`;
   ```
3. Jalankan kembali `php artisan optimize:clear`.

