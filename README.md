# BimCheck API

Web Service mandiri untuk validasi kehadiran bimbingan skripsi berbasis QR Code.

---

## 📖 Penjelasan Proyek

BimCheck API adalah layanan RESTful murni berbasis JSON yang dipisahkan dari aplikasi web utamanya. API ini secara khusus bertugas untuk:
1. Melakukan autentikasi mahasiswa (menggunakan Token).
2. Menerima data hasil scan QR Code dari aplikasi klien.
3. Memvalidasi QR Code (termasuk pengecekan waktu kadaluarsa).
4. Mencatat kehadiran mahasiswa ke database (mengubah status antrian menjadi "proses").
5. Menyediakan daftar riwayat bimbingan mahasiswa.

---

## 🚀 Cara Penggunaan

### 1. Persiapan & Instalasi
1. Letakkan folder repositori ini (misal: `Bimcheck-API`) di dalam folder server lokal Anda (contoh: `C:\laragon\www\Bimcheck-API`).
2. Buka phpMyAdmin, buat database baru dengan nama `bimcheck`.
3. Import file `bimcheck.sql` yang sudah disediakan di folder ini.
4. Pastikan Apache dan MySQL sudah berjalan di Laragon/XAMPP Anda.

### 2. Cara Mengetes API (menggunakan Postman)

#### A. Login (Mendapatkan Token Akses)
Anda diwajibkan untuk login terlebih dahulu untuk mendapatkan Token.
*   **Method**: `POST`
*   **URL**: `http://localhost/Bimcheck-API/api/?action=login`
*   **Body (raw -> JSON)**:
    ```json
    {
        "npm": "714240020",
        "password": "Samuel"
    }
    ```
> 📌 **PENTING**: Saat Anda klik "Send", API akan merespon dengan token panjang. **Copy token tersebut** untuk digunakan pada langkah B dan C.

#### B. Check-In (Validasi QR Code)
Mengirimkan data hasil scan QR untuk memproses kehadiran.
*   **Method**: `POST`
*   **URL**: `http://localhost/Bimcheck-API/api/?action=checkin`
*   **Headers**: 
    *   *Key*: `Authorization`
    *   *Value*: `Bearer <PASTE_TOKEN_YANG_ANDA_COPY_DI_SINI>`
*   **Body (raw -> JSON)**:
    ```json
    {
        "mahasiswa_id": 4,
        "qr_code": "5"
    }
    ```

#### C. Lihat Riwayat Bimbingan
Menampilkan riwayat bimbingan yang sudah selesai.
*   **Method**: `GET`
*   **URL**: `http://localhost/Bimcheck-API/api/?action=history&mahasiswa_id=4`
*   **Headers**: 
    *   *Key*: `Authorization`
    *   *Value*: `Bearer <PASTE_TOKEN_YANG_ANDA_COPY_DI_SINI>`
