# 🏫 Sistem Presensi Digital & Informasi Pengelolaan Presensi Sekolah

[![Laravel 11/12](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)

Sistem Presensi Digital berbasis web modern yang dirancang untuk mengotomatisasi pencatatan kehadiran siswa secara cepat, akurat, dan transparan di lingkungan sekolah. Sistem ini mengintegrasikan **Kiosk Terminal Presensi Mandiri** (Face Recognition AI offline & Scanner Barcode/QR Code), **Portal Administrasi & Guru**, serta rencana integrasi **WhatsApp Otomatis** ke orang tua siswa.

---

## 📌 Fitur Unggulan

### 1. 🖥️ Terminal Kiosk Mandiri (`/kiosk`)
- **Mode Layar Penuh (Fullscreen Kiosk Mode)** dengan antarmuka futuristik & jam digital sinkron server realtime.
- **Biometric Face Recognition AI Offline**: Pemindaian wajah cepat dan aman tanpa koneksi internet pihak ketiga menggunakan model `@vladmandic/face-api` lokal (`/models/face-api`).
- **QR Code / Barcode Scanner Cepat**: Scanner berkecepatan 30 FPS hardware-accelerated (`Html5Qrcode`) dengan dynamic scan box dan respons instan.
- **Deteksi Otomatis Jam Masuk & Pulang**: Sistem secara pintar membedakan sesi masuk atau pulang berdasarkan tabel aturan jam aktif.
- **Proteksi Anti Double-Tap**: Mencegah kecurangan atau scan ganda berulang dalam rentang waktu yang sama.
- **Audio & Visual Feedback**: Menampilkan foto siswa, nama, kelas, status kehadiran (Tepat Waktu / Terlambat), disertai sambutan audio ramah (Text-to-Speech).

### 2. 🪪 Manajemen Master Data & Kartu Pelajar
- **CRUD Siswa & Kelas**: Pengelolaan data siswa lengkap dengan NIS, NISN, rombel kelas, dan nomor WhatsApp orang tua format internasional.
- **Face Enrollment (Pendaftaran Wajah)**: Modul pendaftaran biometrik wajah siswa melalui kamera web untuk mengekstraksi 128-float face descriptors.
- **Cetak Kartu Pelajar Instan**: Fitur cetak kartu pelajar otomatis dengan **QR Code Jumbo beresolusi tinggi (240x240 px)** dan barcode unik siswa, siap cetak fisik.

### 3. ⚙️ Master Aturan Jam Presensi
- Pengaturan fleksibel untuk jam buka presensi masuk, batas toleransi tepat waktu, batas akhir terlambat, dan jam buka/tutup presensi pulang.
- Dukungan aturan khusus per hari aktif (misal: jam pulang lebih awal di hari Jumat).

### 4. 📊 Monitoring & Riwayat Presensi Multi-Dimensi (`/admin/attendances`)
- Pemantauan kehadiran lengkap dengan filter:
  - Rentang tanggal (harian / custom range).
  - Siswa / NIS / Kelas.
  - Status kehadiran (Hadir Tepat Waktu, Terlambat, Izin, Sakit, Alpa).
  - Metode scan (Face Recognition vs Barcode/QR vs Koreksi Manual Guru).
  - Snapshot foto bukti scan saat presensi dilakukan.

### 5. 👨‍🏫 Portal Pengelolaan oleh Guru (Dalam Pengembangan)
- Dasbor kehadiran per rombel/kelas binaan secara realtime.
- Koreksi & verifikasi status kehadiran (Izin, Sakit, Alpa) disertai catatan dan unggahan surat keterangan.
- Rekapitulasi absensi bulanan dan ekspor laporan format **Excel (.xlsx)** & **PDF**.

### 6. 📱 Notifikasi WhatsApp Otomatis (Rencana Fase 5)
- Pengiriman pesan WhatsApp terjadwal (Scheduler/Cron Job) ke orang tua bagi siswa yang belum hadir melewati batas waktu presensi masuk.

---

## 🛠️ Tech Stack

| Lapisan | Teknologi |
| :--- | :--- |
| **Backend Framework** | Laravel 11 / 12 |
| **Bahasa Pemrograman** | PHP 8.2+ |
| **Basis Data** | MySQL / MariaDB |
| **Frontend & Styling** | Blade Templates, Tailwind CSS, Alpine.js, Lucide Icons |
| **Face Recognition AI** | Face-API.js (`@vladmandic/face-api`) model lokal offline |
| **Barcode & QR Scanner** | `html5-qrcode` & `JsBarcode` |
| **Asset Bundler** | Vite |

---

## 🚀 Panduan Instalasi & Menjalankan Aplikasi

### 1. Prasyarat Sistem
Pastikan telah terpasang:
- PHP >= 8.2 (dengan ekstensi `pdo_mysql`, `mbstring`, `fileinfo`, `gd`, `openssl`)
- Composer
- Node.js & NPM
- MySQL Server (misal via Laragon, XAMPP, atau Docker)

### 2. Kloning Repositori
```bash
git clone https://github.com/Develean/presensi-digital.git
cd presensi-digital
```

### 3. Instalasi Dependensi
```bash
# Instal dependensi PHP
composer install

# Instal dependensi Node.js
npm install
```

### 4. Konfigurasi Lingkungan (.env)
Salin berkas konfigurasi lingkungan:
```bash
cp .env.example .env
```
Sesuaikan konfigurasi database pada berkas `.env`:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=presensi_digital
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Generate Application Key & Storage Link
```bash
php artisan key:generate
php artisan storage:link
```

### 6. Migrasi & Seeding Database
Jalankan migrasi tabel dan data awal (admin, guru, contoh kelas, aturan jam, dan siswa dummy):
```bash
php artisan migrate:fresh --seed
```

### 7. Build Aset Frontend
```bash
# Untuk mode pengembangan (hot reload):
npm run dev

# ATAU untuk build siap produksi:
npm run build
```

### 8. Menjalankan Server Lokal
```bash
php artisan serve
```
Akses aplikasi melalui browser:
- **Halaman Utama / Login**: [http://localhost:8000/login](http://localhost:8000/login)
- **Kiosk Terminal Presensi**: [http://localhost:8000/kiosk](http://localhost:8000/kiosk)

---

## 🔐 Akun Default (Hasil Seeder)

| Role | Email | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@sekolah.sch.id` | `password` | Akses penuh master data, aturan presensi, cetak kartu, dan log |
| **Guru** | `guru@sekolah.sch.id` | `password` | Wali kelas & monitoring absensi siswa |

---

## 🧪 Menjalankan Pengujian Otomatis (Automated Tests)

Aplikasi telah dilengkapi unit & feature tests menggunakan PHPUnit:

```bash
php artisan test
```

Uji fitur yang mencakup:
- `tests/Feature/DatabaseStructureTest.php` (Validasi skema tabel & relasi Eloquent)
- `tests/Feature/MasterDataTest.php` (Validasi CRUD Siswa, Kelas, & Aturan Jam)
- `tests/Feature/KioskAttendanceTest.php` (Validasi alur presensi, validasi jam, dan anti-double scan)

---

## 📷 Rekomendasi Perangkat Keras Terminal Kiosk

Untuk penggunaan optimal di gerbang/area masuk sekolah:
1. **Perangkat Kiosk**: Mini PC / PC Desktop (Intel Core i3 Gen 8+ atau setara, RAM 8GB, SSD).
2. **Kamera Web**: Webcam Full HD 1080p dengan autofokus yang stabil (misal: Logitech C920/C922).
3. **Pemindai Barcode**: 2D Omnidirectional Desktop Barcode / QR Scanner (USB Plug & Play) atau kamera sekunder.
4. **Jaringan**: Sambungan kabel LAN untuk latensi rendah ke server lokal.

---

## 📄 Lisensi

Proyek ini dikembangkan di bawah lisensi [MIT License](LICENSE).
