# ⚡ Dokumen Cetak Biru & Pemetaan Fitur Mobile App ANHA Electrical
**Paket Aplikasi:** `com.electrical.anha_mobile`  
**Teknologi:** Flutter 3.24+ (Dart 3.5+)  
**Backend:** Laravel REST API (`api.anhagroup.com`) + Sanctum Auth  
**Peta & Geospasial:** OpenStreetMap (OSM) via `flutter_map`  
**Metode Pembayaran Utama:** Cash on Delivery (COD)  

---

## 1. Analisis Status Google Play Store & Strategi Penimpaan (App Overwrite)

> **Jawaban Tegas: YA, APLIKASI BISA DITIMPA (UPDATE/REPLACE) DI GOOGLE PLAY STORE MESKIPUN FILE PROJECT LAMA HILANG.**

Aplikasi yang saat ini berada di Play Store (Internal Testing / Closed Testing) dapat langsung diperbarui dengan project Flutter baru yang dibangun ulang, asalkan memenuhi 3 syarat teknis berikut:

### 1.1 Tiga Syarat Mutlak Penimpaan di Play Store
1. **Application ID / Package Name Wajib Identik**  
   - Wajib menggunakan: `com.electrical.anha_mobile`
   - Dikonfigurasi pada `android/app/build.gradle` (`applicationId "com.electrical.anha_mobile"`).
2. **Version Code Harus Lebih Tinggi**  
   - Nilai integer `versionCode` pada build baru harus lebih besar dari versi yang telah di-upload sebelumnya.
   - Contoh: Jika rilis tester sebelumnya `versionCode 1`, maka rilis baru wajib `versionCode: 2` (atau lebih tinggi) dan `versionName: "1.0.1"`.
3. **Kunci Penandatanganan (Signing Keystore)**  
   - **Kondisi Umum (Play App Signing Aktif - Standar Default Play Console Modern):**  
     Google Play menyimpan kunci rilis utama di cloud Google. Yang digunakan di lokal hanyalah *Upload Key*.  
     Jika file `.jks` lama hilang bersama laptop, pemilik akun Play Console dapat melakukan **Reset Upload Key** secara resmi dalam hitungan jam:
     1. Masuk ke **Google Play Console** → Pilih aplikasi ANHA Electrical.
     2. Buka menu **Setup** → **App Integrity** (Integritas Aplikasi).
     3. Pada tab *App Signing*, klik **Request upload key reset** (Minta reset kunci upload).
     4. Generate keystore baru di lokal, ekstrak sertifikat `.pem`, dan unggah ke Google Play Console.
     5. Setelah disetujui (biasanya 24–48 jam atau instan untuk akun tertentu), file AAB baru siap di-upload.
   - **Jika Anda masih memiliki backup file `.jks` lama:** Cukup pasang kembali ke folder `android/app` dan build release langsung.

---

## 2. Penyesuaian Alur Autentikasi (Sesuai Karakteristik Sistem)

> Pada sistem ANHA Electrical, **tidak ada form registrasi mandiri publik yang terbuka bebas**.  
> Akun pengguna dibuat dan dikelola langsung melalui Web Admin Dashboard. Admin men-generate **Kode Akses / Kode Login Unik** untuk masing-masing pelanggan (baik Ritel maupun Grosir).

### 2.1 Alur Login Kode Akses
- Admin Web membuat user atau men-generate kode unik (4-8 karakter) di menu pelanggan / generate kode.
- Pelanggan membuka aplikasi mobile ANHA dan hanya memasukkan **Kode Akses** tersebut.
- Aplikasi memanggil endpoint backend Laravel: `POST /api/v1/login` dengan payload:
  ```json
  {
    "login_code": "KODE_DARI_ADMIN"
  }
  ```
- Backend memvalidasi kode di tabel `users` (kolom `email` atau `kode_grosir`), mengecek status aktif, dan mengembalikan token Sanctum Bearer (dengan masa aktif 365 hari agar sesi pelanggan awet dan tidak mudah logout sendiri), lengkap beserta data profil, role (`ritel` / `grosir` / `admin`), dan saldo poin.
- Token disimpan aman di perangkat menggunakan secure storage.

---

## 3. Integrasi Peta OpenStreetMap (OSM)

Sesuai instruksi, seluruh fitur geospasial menggunakan **OpenStreetMap (OSM)**:
- **Library Flutter:** `flutter_map` + `latlong2`
- **Tile Layer Server:** OpenStreetMap Standard Tiles (`https://tile.openstreetmap.org/{z}/{x}/{y}.png`)
- **Keunggulan OSM untuk ANHA:**
  - 100% Gratis dan Tanpa Batasan Kuota API Key Google Maps.
  - Ringan, cepat, dan tidak memerlukan konfigurasi Google Billing yang rumit.
- **Implementasi pada 2 Fitur Utama:**
  1. **Pin Point Alamat Pengiriman (Screen 28):** Pelanggan dapat melihat peta OSM interaktif, menggeser marker ke posisi rumah/gudang, dan sistem otomatis merekam koordinat `latitude` dan `longitude` ke endpoint `/api/v1/alamat-pengiriman`.
  2. **Tracking Kurir COD (Screen 19):** Menampilkan rute pengantaran dari Toko ANHA Electrical ke titik alamat pengiriman pelanggan di atas layer peta OSM.

---

## 4. Pemetaan Lengkap 32 Halaman Wireframe ke Fitur Mobile App

| No | Nama Screen di Wireframe | Modul | Fitur Utama & Elemen UI | Endpoint API Backend Terkait |
|:---:|:---|:---|:---|:---|
| **1** | Splash Screen | Auth | Logo ANHA Electrical ⚡, animasi denyut, cek token tersimpan di Secure Storage. Auto redirect ke Home jika sudah login, atau ke Onboarding/Login jika belum. | Local Storage Check |
| **2** | Onboarding Slide 1 | Auth | Ilustrasi Belanja Mudah, benefit ribuan produk listrik berkualitas, COD tersedia, tombol "Lewati" & "Selanjutnya". | Static Assets |
| **3** | Onboarding Slide 2 | Auth | Ilustrasi Kumpulkan Poin (Belanja Rp40.000 = 1 Poin), tukar hadiah barang di toko. | Static Assets |
| **4** | Onboarding Slide 3 | Auth | Ilustrasi COD Terpercaya (Bayar Tunai saat barang sampai), tombol "Mulai Belanja" menuju Login Kode. | Static Assets |
| **5** | Login Kode Akses (Adaptasi) | Auth | Input Kode Login (dari Admin Web), validasi instan, auto-paste, tombol Hubungi Admin via WhatsApp jika belum memiliki akun. | `POST /v1/login` |
| **6** | Info B2B / Grosir | Auth | Informasi akun mitra bisnis / toko rekanan, nomor langganan khusus grosir. | `POST /v1/login` |
| **7** | Panduan Akun Baru | Auth | Penjelasan alur aktivasi pelanggan baru via Admin / Toko Cabang terdekat. | `GET /v1/contact-info` |
| **8** | Input Verifikasi Kode | Auth | Box OTP / Kode interaktif dengan auto-focus dan visual feedback saat valid. | `POST /v1/login` |
| **9** | Bantuan Akses & Reset | Auth | Hubungi CS / Admin untuk reset kode atau aktivasi ulang akun. | `GET /v1/contact-info` |
| **10** | **Home Screen (Beranda)** | Home | Header profil sapaan "Halo, [Nama]", badge icon keranjang & notifikasi, search bar, Carousel Banner dinamis, Grid 8 Kategori, Rekomendasi Produk Terbaru, Floating WhatsApp Button di pojok kanan bawah. | `GET /v1/barang-user-landing`<br>`GET /v1/banner-all`<br>`GET /v1/kategori-barang-landing`<br>`GET /v1/contact-info` |
| **11** | Semua Kategori | Browse | Grid 12+ icon kategori barang listrik (Kabel, Stop Kontak, MCB, Lampu, Saklar, Panel, Fitting, dll). Urutan sesuai pengaturan Admin. | `GET /v1/kategori-barang-all` |
| **12** | List Produk by Kategori | Browse | Filter bar (Harga termurah/tertinggi, stok tersedia, terlaris), Grid 2 kolom kartu produk dengan gambar, merek, harga, dan tombol quick `+ Tambah`. | `GET /v1/barang-user-kategori/{id}` |
| **13** | Hasil Pencarian | Search | Search bar real-time, pencarian berdasarkan nama produk atau **kode barang 6 digit**, filter ketersediaan stok (Tersedia / Terbatas / Habis). | `GET /v1/barang-user-search`<br>`GET /v1/barang-user-barcode/{code}` |
| **14** | Detail Produk Ritel (B2C) | Product | Slider galeri foto (min. 2 foto) dengan zoom, badge rating bintang (1-5), badge stok real-time, nama & kode 6 digit, deskripsi lengkap, counter kuantitas, tombol *Tambah Keranjang* & *Beli Sekarang*. | `GET /v1/barang-user/{id}` |
| **15** | Detail Produk Grosir (B2B) | Product | Menampilkan **Tabel Skema Harga Grosir Bertingkat** (Contoh: Beli 5 = Rp10.000, Beli 10 = Rp9.800, dst), minimal order grosir, kalkulasi otomatis subtotal. | `GET /v1/barang-user/{id}` |
| **16** | Keranjang Belanja | Cart | List item keranjang belanja dengan tombol (+/-) qty, swipe-to-delete, pemilihan alamat aktif, input kode promo/voucher diskon, kalkulasi poin yang didapatkan, tombol *Bayar COD*. | `GET /v1/keranjang`<br>`PUT /v1/keranjang/{id}`<br>`DELETE /v1/keranjang/{id}`<br>`GET /v1/checkout-pre` |
| **17** | Pesanan Berhasil! | Order | Notifikasi sukses animasi centang hijau, nomor invoice pesanan `#ORD-...`, estimasi waktu tiba kurir, info perolehan poin pending, tombol *Lacak Pesanan*. | `POST /v1/checkout` |
| **18** | Riwayat Pesanan | Order | Tab status: Semua, Aktif (Diproses/Dikirim), Selesai, Dibatalkan. Kartu pesanan dengan info tanggal, total nominal COD, dan tombol *Beli Lagi (Reorder)*. | `GET /v1/order` |
| **19** | Status & Tracking Pesanan (COD) | Tracking | Estimasi jam tiba, Kartu Sopir/Kurir (Nama, foto, tombol call 📞 & chat WA 💬), Peta OSM rute pengantaran, 5-Step Tracker (Confirmed → Processing → In Delivery → COD Payment → Done), tombol konfirmasi terima barang. | `GET /v1/order/{id}`<br>`PATCH /v1/order/{id}/confirm-received` |
| **20** | Poin & Reward Saya | Loyalty | Kartu Emas Saldo Poin (1 poin = Rp500), ringkasan Masuk/Keluar/Hangus, preview 2 katalog hadiah terpopuler, riwayat perolehan poin dari transaksi. | `GET /v1/loyalty/summary`<br>`GET /v1/loyalty/transactions` |
| **21** | Katalog Reward Lengkap | Loyalty | Filter kategori hadiah (Semua, Voucher PLN/Diskon, Hadiah Fisik/Barang, Potongan Langsung). Item dengan poin tidak cukup otomatis dinonaktifkan (greyed-out). | `GET /v1/loyalty/rewards` |
| **22** | Modal Konfirmasi Penukaran | Loyalty | Bottom sheet dialog menampilkan detail barang, poin yang dipotong, sisa poin setelah tukar, tombol *Tukar Sekarang*. | `POST /v1/loyalty/redeem/{reward}` |
| **23** | Detail Penukaran & Kode Klaim | Loyalty | Badge status pengambilan (Belum Diambil / Sudah Diambil), **Kode Klaim Unik Alfanumerik (misal: `ANHA-4K9X`)**, **QR Code Klaim** untuk di-scan oleh admin toko, petunjuk penukaran di toko offline ANHA Electrical. | `GET /v1/loyalty/redemptions/{id}` |
| **24** | Riwayat Penukaran Poin | Loyalty | Tab filter (Semua, Belum Diambil, Sudah Diambil), daftar histori hadiah yang pernah ditukar beserta tanggal pengambilan. | `GET /v1/loyalty/redemptions` |
| **25** | Halaman Profil | Profile | Avatar pengguna, badge tipe akun (Ritel / Grosir / Admin), ringkasan total pesanan & saldo poin, menu edit profil, daftar alamat, pengaturan notifikasi, kritik & saran, logout. | `GET /v1/profil`<br>`POST /v1/logout` |
| **26** | Edit Profil | Profile | Ubah foto profil (kamera/galeri), ubah nama lengkap, nomor telepon aktif, dan ganti password/PIN. | `PUT /v1/profil/{id}`<br>`PUT /v1/profil-ganti-foto/{id}` |
| **27** | Daftar Alamat Pengiriman | Address | List alamat tersimpan (Rumah, Kantor, Proyek), tanda alamat default, tombol tambah, edit, dan hapus alamat. | `GET /v1/alamat-pengiriman`<br>`PATCH /v1/alamat-pengiriman-default/{id}` |
| **28** | Tambah / Edit Alamat (Map OSM) | Address | Input label alamat, penerima, telepon, jalan, kelurahan/kecamatan, kota, kode pos + **Interaktif OpenStreetMap Picker** (drag marker untuk set titik latitude/longitude akurat). | `POST /v1/alamat-pengiriman`<br>`PUT /v1/alamat-pengiriman/{id}` |
| **29** | Halaman Notifikasi | Notif | List notifikasi status pesanan COD, notifikasi poin reward masuk, info promo, dan balasan kritik & saran. | `GET /v1/notifications`<br>`PATCH /v1/notifications/{id}/read` |
| **30** | Kritik & Saran (Feedback List) | Feedback | Riwayat masukan dari pelanggan ritel, badge status (Dikirim, Dibaca, Ditanggapi), dan kotak balasan resmi dari Admin. | `GET /v1/feedbacks`<br>`GET /v1/feedbacks/{id}` |
| **31** | Form Kirim Kritik & Saran | Feedback | Pilihan kategori (Produk, Pengiriman, Pelayanan, Aplikasi, Lainnya), textarea masukan (min. 20 karakter), upload hingga 3 foto lampiran. | `POST /v1/feedbacks` |
| **32** | Pengaturan Notifikasi | Settings | Switch toggle notifikasi pesanan, poin reward, reward baru, promo diskon, dan balasan kritik saran. | Local Settings & FCM Token |
| **33** | *Fitur Khusus Admin:* Input Produk | Admin | Menu khusus jika user yang login memiliki role `admin`. Form input kode produk 6 digit, nama barang, kategori, harga retail & grosir, stok awal, rating bintang (1-5), upload foto langsung dari kamera. | `POST /v1/barangs`<br>`GET /v1/kategori-barang-all` |

---

## 5. Struktur Folder Project Flutter

```
anha_mobile/
├── android/
│   └── app/
│       ├── build.gradle (applicationId "com.electrical.anha_mobile", versionCode 2, versionName "1.0.1")
│       └── src/main/AndroidManifest.xml (Internet, Location, Camera permissions)
├── lib/
│   ├── main.dart
│   ├── core/
│   │   ├── constants/
│   │   │   ├── api_endpoints.dart
│   │   │   ├── app_colors.dart (Biru #1B5E8C, Emas #FACC15, Accent #1D4ED8)
│   │   │   └── app_typography.dart
│   │   ├── network/
│   │   │   ├── api_client.dart (Dio with Interceptors & Sanctum Bearer Token)
│   │   │   └── api_response.dart
│   │   ├── storage/
│   │   │   └── local_storage.dart (FlutterSecureStorage for auth token & user session)
│   │   └── utils/
│   │       ├── currency_formatter.dart (Rupiah formatting)
│   │       └── date_formatter.dart
│   ├── models/
│   │   ├── user_model.dart
│   │   ├── product_model.dart
│   │   ├── category_model.dart
│   │   ├── cart_model.dart
│   │   ├── order_model.dart
│   │   ├── address_model.dart
│   │   ├── loyalty_model.dart
│   │   └── feedback_model.dart
│   ├── providers/
│   │   ├── auth_provider.dart
│   │   ├── product_provider.dart
│   │   ├── cart_provider.dart
│   │   ├── order_provider.dart
│   │   ├── loyalty_provider.dart
│   │   └── address_provider.dart
│   ├── views/
│   │   ├── auth/ (splash, onboarding, code_login)
│   │   ├── home/ (main_nav, home, widgets: banner, category_grid, prod_card, wa_fab)
│   │   ├── browse/ (categories, product_list, product_detail, product_search)
│   │   ├── cart/ (cart, checkout, order_success)
│   │   ├── orders/ (order_history, order_tracking with OSM)
│   │   ├── loyalty/ (loyalty_home, reward_catalog, redemption_detail, redemption_history)
│   │   ├── profile/ (profile, edit_profile, address_list, address_form_osm, notifications, feedback)
│   │   └── admin/ (admin_add_product)
│   └── widgets/
│       ├── custom_button.dart
│       ├── custom_text_field.dart
│       ├── osm_map_widget.dart
│       └── status_badge.dart
```

---

## 6. Palet Warna & Identitas Desain (Mengacu Wireframe)

- **Header & Navbar:** `#1B5E8C` (Navy Deep Blue listrik)
- **Tombol Aksi Utama & Poin:** `#FACC15` (Kuning Petir Listrik) & `#1D4ED8` (Royal Blue)
- **Background Utama:** `#EFF4FA` (Ice Light Blue) & `#FFFFFF` (Putih Bersih)
- **Teks & Label:** `#1F2937` (Dark Slate Charcoal) & `#6B7280` (Muted Grey)
- **Status & Badges:**
  - Selesai / Stok Tersedia: `#10B981` (Emerald Green)
  - Dalam Pengantaran: `#1D4ED8` (Blue)
  - Belum Diambil / Menunggu Pembayaran COD: `#CA8A04` / `#FEF3C7` (Amber Gold)
  - Dibatalkan / Stok Habis: `#EF4444` (Coral Red)
