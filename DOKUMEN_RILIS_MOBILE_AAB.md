# Panduan & Spesifikasi Rilis Android App Bundle (.aab)
**Aplikasi**: ANHA Electrical Mobile App  
**Paket Aplikasi**: `com.electrical.anha_mobile`  
**Versi Rilis**: `1.0.7` (Build Number: `12`)  
**Format Rilis**: Android App Bundle (`.aab`) & Android APK (`.apk`)  

---

## 1. Spesifikasi File Build

| Parameter | Keterangan / Nilai |
| :--- | :--- |
| **Nama File AAB & APK** | 1. `app-release-v1.0.7.aab` (Google Play Console)<br>2. `app-release-v1.0.7.apk` (Install Langsung / Tester) |
| **Lokasi File Output** | 1. `/Users/macbook/Documents/ANHA/app-release-v1.0.7.aab`<br>2. `/Users/macbook/Documents/ANHA/app-release-v1.0.7.apk`<br>3. `anha_mobile/build/app/outputs/bundle/release/app-release.aab`<br>4. `anha_mobile/build/app/outputs/flutter-apk/app-release.apk` |
| **Ukuran File** | AAB: `~24.5 MB` \| APK: `~52.0 MB` |
| **Versi Aplikasi (Version Name)** | `1.0.7` |
| **Kode Build (Version Code)** | `12` |
| **Minimum SDK** | `Android 7.0 (API level 24)` |
| **Target SDK** | `Android 14+ / Android 15 ready (API level 36)` |
| **Arsitektur CPU yang didukung** | `arm64-v8a`, `armeabi-v7a`, `x86_64` |
| **Status Penandatanganan (Signing)**| `Signed Release Keystore (Google Play Upload Key)` |

---

## 2. Informasi Keystore & Penandatanganan (Signing)
File AAB & APK telah ditandatangani menggunakan release keystore resmi:
- **Keystore File**: `android/app/upload-keystore.jks`
- **Key Alias**: `upload`
- **File Konfigurasi**: `android/key.properties`
- **Tipe Sertifikat**: RSA 2048-bit (Google Play App Signing Compatible)

> **PENTING**: Simpan file `upload-keystore.jks` dan kredensial di `key.properties` di tempat aman. Kunci ini digunakan oleh Google Play Console untuk memverifikasi keaslian pengunggah.

---

## 3. Catatan Rilis untuk Google Play Console (What's New)

Teks berikut dapat langsung disalin ke kolom **Release notes** di Google Play Console:

### Bahasa Indonesia (id-ID):
```text
Pembaruan ANHA Electrical v1.0.7:
- Sinkronisasi antrean keranjang belanja (cart lock & sequential mutation) saat hapus item dan checkout.
- Auto-refresh otomatis saldo Poin Listrik dan ringkasan Masuk/Keluar/Hangus pasca pesanan diselesaikan admin.
- Penyesuaian katalog reward hadiah fisik dan voucher belanja resmi.
- Pembersihan menu profil untuk mendukung pengalaman login praktis menggunakan Kode Akses Admin.
- Peningkatan stabilitas aplikasi dan performa umum.
```

---

## 4. Langkah-Langkah Unggah ke Google Play Console

1. **Buka Google Play Console**:
   Kunjungi [Google Play Console](https://play.google.com/console) dan masuk menggunakan akun developer Google Anda.
2. **Pilih Aplikasi**:
   Pilih aplikasi **ANHA Electrical** dari daftar aplikasi.
3. **Pilih Jalur Rilis**:
   - Untuk rilis langsung ke publik: Masuk ke menu **Release** &rarr; **Production**.
   - Untuk pengujian internal terlebih dahulu: Masuk ke **Testing** &rarr; **Internal testing**.
4. **Buat Rilis Baru**:
   - Klik tombol **Create new release** di pojok kanan atas.
5. **Unggah Berkas AAB**:
   - Tarik atau klik upload untuk mengunggah file `app-release.aab` dari direktori `anha_mobile/build/app/outputs/bundle/release/app-release.aab`.
   - Tunggu hingga verifikasi file dan signature selesai diproses oleh Google Play Console.
6. **Isi Catatan Rilis (Release Notes)**:
   - Paste teks catatan rilis yang telah disediakan di atas pada kolom Bahasa Indonesia.
7. **Tinjau dan Rilis**:
   - Klik tombol **Next / Review release**.
   - Pastikan tidak ada peringatan fatal (*errors*).
   - Klik **Save** lalu klik **Start rollout to Production** (atau Rollout to Internal Testing).
