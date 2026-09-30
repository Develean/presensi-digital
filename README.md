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
- **Kotak Pemindai Bersih (*Clean Viewfinder*)**: Tampilan kotak pemindaian bersih dan jernih (*crystal-clear*) dengan 4 sudut penanda halus (*sleek corner brackets*), tanpa garis laser tebal yang mengaburkan kartu.
- **Penegakan Jam Operasional Ketat**: Sistem otomatis menolak presensi jika dilakukan di luar jam masuk atau jam pulang yang ditentukan (`is_closed: true` / HTTP 422). Dilengkapi overlay edukatif status penutupan presensi dan toggle *Mode Bypass Petugas* untuk simulasi/pengujian.
- **Biometric Face Recognition AI Offline Cepat**: Siluet oval wajah modern transparan dengan inferensi TinyFaceDetector yang dioptimalkan (`inputSize: 160`, ~25ms latensi) dan model `@vladmandic/face-api` lokal (`/models/face-api`).
- **QR Code Scanner Ultra-Responsif**: Scanner berkecepatan 30 FPS hardware-accelerated (`Html5Qrcode`) dengan Chromium native `BarcodeDetector`, full-frame scan, synthesizer audio instan (Web Audio API 0ms latency), dan cooldown cepat 1.0 detik.
- **Deteksi Otomatis Jam Masuk & Pulang**: Sistem secara pintar membedakan sesi masuk atau pulang berdasarkan tabel aturan jam aktif.
- **Proteksi Anti Double-Tap**: Mencegah kecurangan atau scan ganda berulang dalam rentang waktu yang sama.
- **Audio & Visual Feedback**: Menampilkan foto siswa, nama, kelas, status kehadiran (Tepat Waktu / Terlambat), disertai sambutan audio ramah (Text-to-Speech).

### 2. 🧭 Sidebar Buka-Tutup (*Smooth Slide Toggle & Drawer*)
- **Solid Midnight Navy (#0f172a)**: Tampilan sidebar bersih dan kontras tanpa foto bising di latar belakang, membuat 9 menu navigasi terbaca 100% tajam dan jelas.
- **Mode Desktop (Slide In/Out)**: Sidebar dapat disembunyikan ke kiri (`-ml-64`) atau dimunculkan penuh (`w-64`) dengan transisi halus (`transition-all duration-300`). Area konten otomatis melebar 100% untuk kenyamanan membaca tabel data. Status disimpan di `localStorage`.
- **Mode Mobile (Off-Canvas Drawer)**: Sidebar tersembunyi di luar layar dan dapat dibuka via tombol hamburger topbar dengan *backdrop blur*.
- **Animasi Ringan & Rapi**: Transisi hover menu (`.nav-transition`), efek angkat kartu halus (`.card-hover`), dan efek taktil tombol (`.btn-bounce`).

### 3. 🪪 Manajemen Master Data & Pendidik
- **Data Siswa (`/admin/students`)**: Pengelolaan data siswa lengkap dengan NIS, NISN, rombel kelas, pendaftaran biometrik wajah (*Face Enrollment*), kartu QR, serta tombol *Reset Device Lock*.
- **Data Guru (`/admin/teachers`)**: Manajemen akun guru pengajar dan penugasan sebagai wali kelas.
- **Data Kelas & Jurusan (`/admin/classes`)**: Manajemen rombongan belajar (rombel) dan kompetensi keahlian/jurusan sekolah.
- **Data Petugas (`/admin/officers`)**: Manajemen akun administrator dan petugas piket gerbang sekolah.

### 3. 🔲 Generator & Cetak Massal QR Code (`/admin/qrcode`)
- Filter siswa berdasarkan rombel kelas dan pencarian NIS/nama.
- Preview kode QR instan per siswa menggunakan `qrcodejs`.
- Fitur **Cetak Massal (Batch Print)** kartu pelajar dengan layout A4 siap cetak dan dialog cetak otomatis.
- Fitur cetak kartu satuan dengan layout kartu identitas presensi sekolah.

### 4. 📈 Generator Laporan Presensi Resmi (`/admin/reports`)
- Rekapitulasi absensi siswa harian, mingguan, bulanan, atau rentang tanggal bebas.
- Statistik agregat per kelas: H (Hadir), T (Terlambat), I (Izin), S (Sakit), A (Alpa), dan Persentase Kehadiran.
- Format cetak resmi standar Dinas Pendidikan Provinsi DKI Jakarta / SMKN 77 Jakarta dengan kop surat kedinasan dan kolom tanda tangan Waka Kesiswaan serta Kepala Sekolah.

### 5. ⚙️ Pengaturan Terpadu (`/admin/settings`)
- **Aturan Jam Presensi**: Jam buka, batas tepat waktu, toleransi terlambat, jam pulang, dan hari aktif.
- **Profil Sekolah**: Nama resmi instansi, alamat sekolah, dan tahun ajaran aktif.
- **Keamanan Wi-Fi**: Validasi IP subnet lokal sekolah untuk keamanan presensi mandiri.
- **Terminal Kiosk**: Tautan dan panduan pengoperasian terminal presensi gerbang.

### 6. 📱 Presensi Mandiri Mobile Anti-Antrean (`/presensi`)
- Presensi langsung via smartphone siswa di area sekolah.
- Verifikasi jaringan Wi-Fi sekolah (*Network Subnet Verification*).
- Kunci perangkat keras (*Hardware Device Lock* 1 Akun = 1 Smartphone).

### 7. 👨‍🏫 Portal Pengelolaan oleh Guru (`/guru/dashboard`)
- Dasbor kehadiran per rombel/kelas binaan secara realtime.
- Koreksi & verifikasi status kehadiran (Izin, Sakit, Alpa) disertai catatan dan unggahan surat keterangan.
- Rekapitulasi absensi bulanan dan pemantauan status kaitan perangkat siswa.

### 8. 📲 Notifikasi WhatsApp Otomatis (Rencana Fase 5)
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
- `tests/Feature/KioskAttendanceTest.php` (Validasi alur presensi, penegakan jam operasional masuk/pulang, penolakan luar jam, mode bypass, dan anti-double scan)
- `tests/Feature/SelfAttendanceTest.php` (Validasi presensi mandiri HP siswa, Device Lock, dan verifikasi subnet Wi-Fi sekolah)
- `tests/Feature/NewMenuNavigationTest.php` (Validasi 9 modul menu sidebar, CRUD Guru, Petugas, Batch Print QR, Laporan Resmi Kop Dinas)

> **Status Test Suite**: 33 tests passed, 141 assertions passed (100% Green).

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
