# Catatan Perbaikan & Changelog Rilis v1.0.6 (Build 11)
**Aplikasi**: ANHA Electrical (Mobile, Web Admin & Backend API)  
**Tanggal Rilis**: 4 November 2026  
**Target Platform**: Android (AAB), Web Dashboard, Backend REST API  

---

## 1. Ringkasan Rilis (Release Summary)
Rilis versi **v1.0.6 (Build 11)** berfokus pada penyempurnaan pengalaman pengguna ritel (*retail customer journey*), perbaikan bug kritis kalkulasi poin loyalitas pasca-checkout, pengamanan validasi voucher promo langsung ke server, serta penambahan modul pengelolaan rekening toko & metode pembayaran pada panel Web Admin.

---

## 2. Rincian Perbaikan & Fitur Baru (Detailed Changelog)

### A. Penghapusan Skema Harga Grosir B2B pada Tampilan Detail Produk
- **Deskripsi Masalah**: Pada layar detail produk sebelumnya, terdapat card/tabel *"SKEMA HARGA GROSIR B2B"* yang menampilkan tingkatan harga kuantiti (min. 10 pcs, min. 50 pcs, min. 100 pcs). Hal ini membingungkan konsumen umum/ritel yang bertransaksi eceran di aplikasi mobile.
- **Tindakan Perbaikan**:
  - Menghapus komponen card skema harga grosir dari tampilan detail produk di Mobile App.
  - Memastikan transisi layout dari info harga resmi langsung terhubung ke deskripsi produk dan ulasan.
  - Skema data di database & API tetap terjaga tanpa merusak fungsionalitas transaksi massal di level backend/admin.
- **File Terdampak**:
  - `anha_mobile/lib/views/browse/product_detail_screen.dart`

---

### B. Perbaikan Bug Poin Listrik (Poin = 0 di Halaman Pesanan Berhasil)
- **Deskripsi Masalah**: Saat pengguna berada di layar Keranjang Belanja, estimasi poin terhitung dengan benar (misal `+12 POIN LISTRIK`). Namun, setelah menekan tombol checkout dan diarahkan ke layar **Pesanan Berhasil**, tampilan menunjukkan `+0 Poin Listrik`.
- **Akar Masalah (*Root Cause*)**:
  - State lokal keranjang (`cart.clearCartLocal()`) dipanggil mendahului pembacaan nilai `cart.estimatedPoints`, sehingga nilai poin ter-reset menjadi 0 saat dipassing ke konstruktor `OrderSuccessScreen`.
  - Fallback parsing key response backend pada model order belum mencakup variasi key `estimated_points`, `estimasi_poin`, dan `points`.
- **Tindakan Perbaikan**:
  - Nilai `earnedPoints` dan `totalAmount` dicatat ke variabel lokal terlebih dahulu sebelum state lokal keranjang dibersihkan.
  - Menambahkan dukungan multi-key parsing (`estimated_points ?? estimasi_poin ?? points`) pada model order.
  - Menambahkan verifikasi unit test otomatis untuk menjamin poin sukses tampil sesuai perhitungan keranjang.
- **File Terdampak**:
  - `anha_mobile/lib/views/cart/cart_screen.dart`
  - `anha_mobile/lib/models/order_model.dart`
  - `anha_mobile/test/order_success_screen_test.dart`

---

### C. Validasi Server-Side Kode Voucher Promo di Keranjang
- **Deskripsi Masalah**: Pada form input voucher di keranjang belanja, pengguna dapat memasukkan teks sembarang (contoh: `"asdf"`) dan tombol `Pakai` langsung memberikan potongan harga Rp 10.000 secara lokal tanpa validasi ke server.
- **Akar Masalah (*Root Cause*)**:
  - `CartProvider::applyPromo()` sebelumnya menggunakan nilai dummy default `10000` secara sinkron tanpa melakukan request verifikasi ke API backend.
- **Tindakan Perbaikan**:
  - Menghapus total potongan harga lokal/dummy.
  - Mengubah `applyPromo()` menjadi *asynchronous* yang memanggil API endpoint `GET /api/v1/checkout-pre?voucher_code={code}`.
  - Pada Backend API (`CheckoutController::ctrl_checkout_pre`), diterapkan fungsi `resolveVoucher()` yang memvalidasi kepemilikan voucher user, masa berlaku (*expiry date*), dan syarat minimal belanja.
  - Jika kode voucher tidak sah, API mengembalikan `voucher_valid: false` beserta pesan error spesifik, promo dibatalkan, diskon di-reset ke Rp 0, dan aplikasi mobile menampilkan SnackBar peringatan berwarna merah.
- **File Terdampak**:
  - `anha_mobile/lib/providers/cart_provider.dart`
  - `anha_mobile/lib/views/cart/cart_screen.dart`
  - `anha_mobile/lib/views/loyalty/vouchers_screen.dart`
  - `api.anhagroup.com/app/Http/Controllers/Api/V1/user/CheckoutController.php`
  - `anha_mobile/test/cart_voucher_test.dart`

---

### D. Fitur Pengelolaan Rekening Toko & Cara Bayar di Web Admin
- **Deskripsi Kebutuhan**: Admin toko memerlukan menu di Web Dashboard untuk mengatur nomor rekening bank resmi, pembayaran tunai (COD), dan QRIS yang tampil di aplikasi mobile pelanggan.
- **Implementasi**:
  - **Menu Baru**: Menambahkan item menu **Rekening Toko** di sidebar Web Dashboard pada grup navigasi **SISTEM** (`/sistem/rekening`).
  - **CRUD & Status Toggle**: Membuat `RekeningController` di Web Admin dengan kemampuan melihat daftar rekening, tambah rekening baru, edit rekening, upload logo bank/QRIS, hapus rekening, dan toggle status aktif/non-aktif dengan satu klik.
  - **Endpoint API**: Menambahkan route `PATCH /api/v1/admin-rekening/{id}/toggle` pada backend API untuk aktivasi instan tanpa reload formulir.
  - **Sinkronisasi Otomatis**: Aplikasi mobile via endpoint `GET /api/v1/rekening-dan-cara-bayar` hanya mengambil rekening yang berstatus aktif (`status_active = 1`). Setiap perubahan admin langsung tersinkron ke mobile.
- **File Terdampak**:
  - `anha-laravel-app/app/Http/Controllers/Admin/RekeningController.php`
  - `anha-laravel-app/resources/views/admin/system/rekenings/index.blade.php`
  - `anha-laravel-app/resources/views/admin/system/rekenings/form.blade.php`
  - `anha-laravel-app/resources/views/layouts/dashboard.blade.php`
  - `anha-laravel-app/routes/web.php`
  - `api.anhagroup.com/app/Http/Controllers/Api/V1/admin/Rekening_tokoController.php`
  - `api.anhagroup.com/routes/api.php`

---

## 3. Hasil Pengujian & Kualitas Kode (Quality Assurance)
1. **Flutter Analyzer**: `0 errors, 0 warnings`
2. **Flutter Automated Unit Tests**: `17/17 tests passed (100%)`
3. **Web Admin PHP Syntax Check**: `19/19 controllers bersih (0 syntax errors)`
4. **Backend API PHP Syntax Check**: `45 controllers + 35 models bersih (0 syntax errors)`
5. **Laravel Routing**: Seluruh 109 route web dan 150 route API terdaftar dan tervalidasi dengan benar.

