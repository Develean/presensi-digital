# DOKUMENTASI PROYEK: SISTEM PRESENSI DIGITAL & INFORMASI GURU

Dokumen ini berisi arsitektur sistem, alur proses (flowchart), rekomendasi teknologi (*tech stack*), skema basis data, dan spesifikasi fungsional untuk **Sistem Presensi Digital Sekolah** terintegrasi (Kiosk Face/Barcode + Notifikasi WhatsApp Otomatis) dan **Sistem Informasi Presensi Pengelolaan oleh Guru**.

---

## 1. Ringkasan Sistem (System Overview)

Sistem Presensi Digital ini dirancang untuk mengotomatisasi pencatatan kehadiran siswa di lingkungan sekolah secara akurat, cepat, dan transparan. Terdiri dari 3 subsistem utama:

1. **Kiosk Presensi Mandiri (Frontline Terminal)**:
   - Siswa melakukan presensi dua kali sehari: **Masuk** dan **Pulang**.
   - Metode identifikasi: **Scan Wajah (*Face Recognition*)** atau **Scan Kartu Barcode/QR Code Unik**.
   - Umpan balik langsung visual dan suara (*TTS / Audio buzzer*).
2. **Sistem Notifikasi WhatsApp Otomatis (Cron Scheduler & Queue)**:
   - Jika siswa belum presensi melewati batas toleransi jam masuk (misal: 07.15 WIB), sistem otomatis mengirim pesan WhatsApp ke nomor orang tua/wali untuk verifikasi apakah siswa Sakit, Izin, atau Alpa.
3. **Sistem Informasi & Pengelolaan (Admin & Guru Portal)**:
   - **Portal Admin**: Master data (siswa, kelas, kartu barcode, biometric wajah), pengaturan jam/jadwal, pemantauan dan log menyeluruh multi-filter.
   - **Portal Guru**: Manajemen presensi kelas/perwalian, verifikasi & koreksi kehadiran (jika salah scan/sakit mendadak), rekapitulasi absensi harian/bulanan, serta ekspor laporan (Excel/PDF).

---

## 2. Alur Sistem (System Flowchart & Sequence)

### 2.1. Alur Presensi Siswa (Masuk & Pulang di Kiosk)

```mermaid
flowchart TD
    Start([Siswa Berdiri di Depan Kiosk / Scanner]) --> ChooseMethod{Pilih Metode}
    
    ChooseMethod -->|Face Scan| Camera[Kamera Mendeteksi Wajah]
    Camera --> FaceMatch[Cocokkan Face Descriptor dengan Database]
    
    ChooseMethod -->|Barcode/QR| Scanner[Scan Barcode Kartu Siswa]
    Scanner --> TokenMatch[Validasi Barcode Token dengan Database]
    
    FaceMatch --> ValidIdentity{Identitas Ditemukan?}
    TokenMatch --> ValidIdentity
    
    ValidIdentity -->|Tidak| ShowError[Tampilkan Notifikasi 'Siswa Tidak Dikenali']
    ShowError --> Start
    
    ValidIdentity -->|Ya| CheckTime{Cek Waktu Saat Ini}
    
    CheckTime -->|Rentang Jam Masuk| CheckDoubleIn{Sudah Presensi Masuk Hari Ini?}
    CheckDoubleIn -->|Sudah| WarningDouble[Notifikasi: 'Sudah Presensi Masuk']
    CheckDoubleIn -->|Belum| SaveIn[Catat Presensi: Waktu Masuk, Status Hadir/Terlambat, Metode]
    SaveIn --> SuccessFeedback[Tampilkan Foto & Nama Siswa + Audio 'Selamat Pagi']
    
    CheckTime -->|Rentang Jam Pulang| CheckDoubleOut{Sudah Presensi Pulang Hari Ini?}
    CheckDoubleOut -->|Sudah| WarningDoubleOut[Notifikasi: 'Sudah Presensi Pulang']
    CheckDoubleOut -->|Belum| SaveOut[Catat Presensi: Waktu Pulang, Metode]
    SaveOut --> SuccessFeedbackOut[Tampilkan Nama Siswa + Audio 'Hati-hati di Jalan']
    
    CheckTime -->|Di luar Jam Masuk/Pulang| ShowOutsideRule[Notifikasi: 'Bukan Waktu Presensi Masuk/Pulang']
```

---

### 2.2. Alur Notifikasi Otomatis WhatsApp ke Orang Tua (Cron Scheduler)

```mermaid
sequenceDiagram
    autonumber
    actor Scheduler as Cron Job Server
    participant DB as Database Presensi
    participant Queue as WA Queue Worker
    participant WAGateway as WhatsApp Gateway
    actor Parent as Orang Tua Siswa
    actor Teacher as Guru Kelas

    Note over Scheduler: Dijalankan setiap hari sekolah pukul Batas Jam Masuk (Contoh: 07.30 WIB)
    Scheduler->>DB: Query Siswa Aktif yang BELUM memiliki record presensi masuk hari ini
    DB-->>Scheduler: List Siswa (Nama, Kelas, No HP Orang Tua)
    
    loop Setiap Siswa yang Belum Hadir
        Scheduler->>DB: Simpan Status Awal = 'Belum Hadir / Pending'
        Scheduler->>Queue: Push Task Pengiriman Pesan WA
        Queue->>WAGateway: Kirim template pesan konfirmasi
        WAGateway->>Parent: "Pemberitahuan: [Nama Siswa] belum presensi masuk hingga 07.30. Mohon konfirmasi jika Sakit/Izin."
        WAGateway-->>Queue: Status Terkirim (Sent/Delivered)
    end
    
    Note over Parent,Teacher: Orang tua menghubungi sekolah / wali kelas via WA
    Parent-->>Teacher: Memberikan Surat / Chat Konfirmasi Sakit / Izin
    Teacher->>DB: Guru memperbarui status siswa menjadi 'Sakit' / 'Izin' beserta keterangan
```

---

### 2.3. Alur Kerja Guru (Koreksi & Rekapitulasi Kehadiran)

```mermaid
flowchart TD
    Login[Guru Login ke Portal Guru] --> SelectClass[Pilih Kelas Binaan / Mata Pelajaran]
    SelectClass --> ViewRoster[Tampilkan Daftar Siswa & Status Hari Ini]
    
    ViewRoster --> ActionChoice{Pilih Aksi}
    
    ActionChoice -->|Input/Koreksi Kehadiran| EditStatus[Pilih Siswa -> Ubah Status: Hadir/Izin/Sakit/Alpa]
    EditStatus --> InputNote[Input Alasan / Keterangan / Upload Surat Dokter]
    InputNote --> SaveUpdate[Simpan Perubahan ke Database]
    SaveUpdate --> UpdateLog[Audit Log: Dicatat Siapa Guru yang Mengubah & Waktunya]
    
    ActionChoice -->|Lihat Rekapitulasi| FilterRekap[Pilih Periode: Mingguan / Bulanan / Semester]
    FilterRekap --> DisplayChart[Tampilkan Grafik & Statistik Kehadiran Persentase]
    DisplayChart --> ExportDoc{Ekspor Dokumen?}
    ExportDoc -->|Excel| DownloadXLS[Download Rekap format .XLSX]
    ExportDoc -->|PDF| DownloadPDF[Cetak Laporan Format .PDF Siap Tanda Tangan]
```

---

## 3. Rekomendasi Tech Stack

Mengingat proyek ini dijalankan pada lingkungan **Laragon (Windows)** yang sudah memiliki **PHP, Node.js, Python, dan MySQL/Redis**, berikut arsitektur teknologi yang paling efisien, stabil, dan mudah dirawat:

### 3.1. Arsitektur Komponen

| Komponen | Pilihan Teknologi Utama | Alternatif | Alasan Pemilihan |
| :--- | :--- | :--- | :--- |
| **Backend Framework** | **Laravel 11 (PHP 8.2+)** | Node.js (Express / NestJS) | Sangat stabil di Laragon, memiliki *Task Scheduler* bawaan untuk cron job WhatsApp, Queue system handal, ORM Eloquent yang kaya untuk filter presensi multi-dimensi, dan otentikasi role-permission yang matang. |
| **Database** | **MySQL 8.0 / MariaDB** | PostgreSQL | Bawaan native Laragon, cepat, andal untuk relasi data siswa, kelas, jadwal, dan puluhan ribu log presensi tahunan. |
| **Frontend UI** | **Tailwind CSS + Alpine.js / Blade** *(atau Inertia.js + Vue 3)* | Bootstrap 5 | Desain modern, bersih, ringan, responsif untuk layar HP guru maupun monitor dashboard admin. |
| **Face Recognition Engine** | **Client-side: `face-api.js` (TensorFlow.js WebGL)** | MediaPipe | Menggunakan TinyFaceDetector + FaceLandmark68Tiny + FaceRecognitionNet untuk pencocokan vektor 128-float langsung pada browser Kiosk dan halaman perekaman biometrik secara *realtime* (< 200ms latency). |
| **QR Code Engine & Scanner** | **QRCode.js** + **jsQR Library** + **Dukungan Scanner USB 2D (HID Keyboard)** | HTML5-QRCode | Kartu pelajar mencetak QR Code beresolusi tinggi. Kamera terminal Kiosk memindai QR Code langsung lewat webcam secara *realtime* maupun lewat scanner tembak USB fisik. |
| **WhatsApp Gateway** | **Baileys (`@whiskeysockets/baileys`)** *(Self-hosted Node.js)* ATAU **Fonnte API / Wawp** | Twilio WhatsApp API | **Baileys**: Gratis, open source, menghubungkan nomor WhatsApp sekolah melalui scan QR (bisa berjalan sebagai service background di Node.js Laragon). Jika ingin tanpa risiko nomor terblokir, dapat menggunakan API Gateway lokal seperti Fonnte. |
| **Queue & Cache** | **Redis** *(Bawaan Laragon)* atau Database Queue | Sync | Mencegah bottleneck saat pengiriman massal pesan WhatsApp agar aplikasi kiosk dan web tetap responsif. |
| **Export Reporting** | **Maatwebsite Excel & DomPDF (Laravel)** | Snappy PDF | Menghasilkan rekap absensi standar format dinas pendidikan dalam format Excel dan PDF siap cetak. |

---

## 4. Perancangan Basis Data (Database Schema)

### 4.1. Entity Relationship Overview

```mermaid
erDiagram
    USERS ||--o{ TEACHERS : profile
    CLASSES ||--o{ STUDENTS : contains
    CLASSES ||--o{ TEACHERS : homeroom_teacher
    STUDENTS ||--o{ ATTENDANCES : has
    STUDENTS ||--o{ WA_LOGS : receives
    ATTENDANCE_RULES ||--o{ ATTENDANCES : applies
    USERS ||--o{ ATTENDANCES : updated_by

    USERS {
        bigint id PK
        string name
        string email
        string password
        enum role "admin, guru"
        datetime created_at
    }

    CLASSES {
        bigint id PK
        string name "Misal: X-RPL 1"
        string level "X, XI, XII"
        bigint teacher_id FK "Wali Kelas"
    }

    STUDENTS {
        bigint id PK
        string nis UK
        string nisn UK
        string name
        enum gender "L, P"
        bigint class_id FK
        string barcode_token UK
        text face_descriptor "Vektor Wajah 128-float JSON"
        string parent_name
        string parent_phone "Nomor WA 628xxx"
        boolean is_active
    }

    ATTENDANCE_RULES {
        bigint id PK
        string name "Senin - Kamis, Jumat"
        time time_in_start "06:00:00"
        time time_in_deadline "07:15:00"
        time time_in_late_limit "07:30:00"
        time time_out_start "15:00:00"
        time time_out_end "17:00:00"
        json active_days "[1,2,3,4,5]"
    }

    ATTENDANCES {
        bigint id PK
        bigint student_id FK
        date attendance_date
        time time_in
        time time_out
        enum status_in "hadir, terlambat, izin, sakit, alpa"
        enum status_out "pulang_normal, pulang_awal, belum_pulang"
        enum method_in "face, barcode, manual"
        enum method_out "face, barcode, manual"
        string photo_in_path "Snapshot bukti scan"
        string photo_out_path
        text notes "Keterangan sakit/izin"
        bigint updated_by FK "ID Guru/Admin jika ada koreksi manual"
        timestamps created_at
    }

    WA_LOGS {
        bigint id PK
        bigint student_id FK
        string phone_number
        text message_content
        enum status "pending, sent, failed"
        datetime sent_at
    }
```

---

## 5. Fitur Rinci per Modul

### 5.1. Kiosk Presensi Mandiri (Frontline Terminal)
- **Tampilan Khusus Layar Penuh (Fullscreen Kiosk Mode)**:
  - Antarmuka ramah pengguna dengan jam digital sinkron server.
  - Dua jendela input: Viewfinder Kamera (untuk Face Recognition) dan Listener Input Barcode (USB scanner / kamera QR).
- **Deteksi Otomatis Masuk vs Pulang**:
  - Jam presensi ditentukan secara dinamis berdasarkan jam berjalan sesuai tabel aturan jam presensi.
  - Jam 06.00 - 09.00: Otomatis memproses **Presensi Masuk**.
  - Jam 14.00 - 17.00: Otomatis memproses **Presensi Pulang**.
- **Pencegahan Double-Tap (Anti Ganda)**:
  - Menolak siswa yang melakukan scan berulang kali dalam jeda singkat jika status masuk/pulangnya sudah tercatat.
- **Visual & Audio Feedback**:
  - Menampilkan foto profil, nama siswa, kelas, status (Hadir / Terlambat), dan waktu presensi.
  - Suara selamat datang yang ramah.

### 5.2. Mesin Notifikasi Otomatis WhatsApp
- **Jadwal Otomatis (Cron Job)**:
  - Berjalan pada menit tertentu setelah jam toleransi masuk berakhir (misal pukul 07.20 WIB).
- **Seleksi Target Cerdas**:
  - Memfilter seluruh siswa aktif pada hari itu yang belum memiliki rekam jejak `time_in` dan belum memiliki keterangan (bukan status izin/sakit yang sudah di-input sebelumnya).
- **Format Pesan Personalisasi**:
  ```text
  Yth. Bapak/Ibu Wali Murid dari *[Nama Siswa]* (*[Kelas]*),

  Kami menginformasikan bahwa ananda belum tercatat melakukan presensi masuk hingga pukul *07.15 WIB* hari ini (*[Tanggal]*).

  Mohon konfirmasi status ananda (Sakit/Izin/Lainnya) dengan membalas pesan ini atau menghubungi wali kelas Bapak/Ibu: [Nama Wali Kelas] ([No WA Wali Kelas]).

  Terima kasih,
  Sistem Presensi Digital - [Nama Sekolah]
  ```
- **Monitoring Antrean**:
  - Dasbor untuk memantau status pengiriman pesan (Pending, Sent, Failed).

### 5.3. Portal Pengelolaan oleh Admin (Struktur 9 Menu Utama)
Sesuai rancangan antarmuka standar sekolah, navigasi admin disusun menjadi 9 menu utama yang bersih, konsisten, dan mudah diakses:
1. **Dashboard (`/admin/dashboard`)**:
   - Menampilkan metrik KPI kehadiran harian real-time (total siswa, hadir tepat waktu, terlambat, izin, sakit, alpa).
   - Indikator status operasional jam presensi dan pintasan cepat Kiosk Terminal.
2. **Presensi Siswa (`/admin/attendances`)**:
   - Pemantauan rekam jejak presensi harian siswa secara multi-dimensi.
   - Filter tanggal, siswa/NIS, rombel kelas, status kehadiran, dan metode scan (Face Recognition vs Barcode/QR vs Presensi Mandiri Siswa).
   - Log snapshot foto wajah saat presensi dilakukan.
3. **Data Siswa (`/admin/students`)**:
   - Manajemen siklus hidup data siswa (CRUD).
   - Fitur pendaftaran biometrik wajah (*Face Enrollment*) via kamera langsung.
   - Pembuatan token barcode unik dan pencetakan kartu presensi satuan.
   - Fitur *Reset Device Lock* untuk siswa yang mengganti perangkat smartphone.
4. **Data Guru (`/admin/teachers`)**:
   - Manajemen akun guru dan staf pendidik.
   - Penugasan guru sebagai wali kelas pada rombel tertentu.
   - Modal tambah/edit akun guru dan pengelolaan hak akses portal.
5. **Data Kelas & Jurusan (`/admin/classes`)**:
   - Manajemen daftar rombel kelas dan kompetensi keahlian/jurusan sekolah (misal: X PPLG 1, XI TJAT 2, XII AKL 1).
   - Pemetaan wali kelas dan rekapitulasi jumlah siswa aktif per kelas.
6. **Generate QR Code (`/admin/qrcode` & `/admin/qrcode/batch-print`)**:
   - Pembuatan kartu identitas presensi berbasis QR Code secara instan menggunakan `qrcodejs`.
   - Filter pemilihan berdasarkan rombel kelas dan pencarian NIS/nama siswa.
   - Fitur *Batch Printing* (Cetak Massal) dengan tata letak optimal kertas A4 dan *automatic print dialog*.
   - Fitur cetak satuan dan unduh berkas kartu.
7. **Generate Laporan (`/admin/reports`)**:
   - Generator laporan rekapitulasi kehadiran berkala (harian, mingguan, bulanan, atau rentang tanggal bebas).
   - Statistik agregat per kelas: H (Hadir), T (Terlambat), I (Izin), S (Sakit), A (Alpa), dan Persentase Tingkat Kehadiran.
   - Lembar cetak laporan resmi (`@media print`) yang dilengkapi Kop Resmi Dinas Pendidikan Provinsi DKI Jakarta / SMKN 77 Jakarta serta kolom tanda tangan Waka Kesiswaan dan Kepala Sekolah.
8. **Data Petugas (`/admin/officers`)**:
   - Pengelolaan akun administrator, operator sistem, dan petugas piket gerbang sekolah.
   - Proteksi keamanan: akun aktif tidak dapat menghapus dirinya sendiri dan sistem menjaga minimal 1 akun admin aktif.
9. **Pengaturan (`/admin/settings`)**:
   - Tab 1: **Aturan Jam Presensi** (buka jam presensi, toleransi tepat waktu, batas terlambat, jam pulang, dan hari aktif).
   - Tab 2: **Profil Sekolah** (Nama instansi, alamat, tahun ajaran aktif, dan zona waktu).
   - Tab 3: **Keamanan Jaringan Wi-Fi** (Validasi IP Subnet sekolah untuk mencegah titip absen pada presensi mandiri).
   - Tab 4: **Terminal Kiosk** (Pintasan dan panduan operasional terminal gerbang).

### 5.4. Portal Pengelolaan oleh Guru (Sistem Informasi Presensi Guru)
1. **Dasbor Kelas Binaan / Mata Pelajaran**:
   - Menampilkan ringkasan kehadiran siswa kelas binaannya secara real-time (Berapa anak yang sudah hadir di kelas, berapa yang belum datang).
2. **Pencatatan Kehadiran Cepat (Manual Attendance / Roll Call)**:
   - Guru dapat melakukan absensi manual di dalam kelas jika siswa kelupaan membawa kartu atau terdapat kendala scan wajah.
3. **Koreksi Status Kehadiran**:
   - Mengubah status siswa dari Alpa menjadi Sakit atau Izin saat orang tua mengonfirmasi.
   - Dilengkapi kolom catatan alasan dan unggah foto surat izin/surat keterangan dokter.
4. **Rekapitulasi & Statistik**:
   - Rekap persentase kehadiran per siswa dalam 1 bulan (menghitung persentase kehadiran untuk syarat kelayakan ujian).
5. **Ekspor & Pelaporan**:
   - Unduh rekap format **Microsoft Excel (.xlsx)** dengan pewarnaan otomatis (H, I, S, A).
   - Unduh **PDF Laporan Bulanan** resmi yang dilengkapi kolom tanda tangan wali kelas dan kepala sekolah.

---

## 6. Tahapan Pengembangan (Implementation Roadmap)

| Tahap | Status | Milestone & Aktivitas Utama |
| :--- | :--- | :--- |
| **Fase 1: Fondasi & Basis Data** | **SELESAI (100% Passed)** | Inisialisasi framework Laravel, konfigurasi database, pembuatan 6 migrasi tabel utama (`users` role admin/guru, `classes`, `students`, `attendance_rules`, `attendances`, `wa_logs`), implementasi Model Eloquent + Relasi, Seeder data awal, dan pengujian otomatis via `tests/Feature/DatabaseStructureTest.php` (6 tests, 20 assertions passed). |
| **Fase 2: Master Data & Enrollment** | **SELESAI (100% Passed)** | Sistem Autentikasi multi-role (Admin & Guru), CRUD Rombel Kelas & penugasan wali kelas, CRUD Siswa dengan generator barcode unik otomatis & format nomor WhatsApp 62..., Fitur cetak kartu pelajar standar dengan barcode (JsBarcode), Modul pendaftaran biometrik wajah (*Face Enrollment*) via kamera, CRUD Aturan Jam Presensi (masuk, deadline, late limit, pulang, hari aktif), serta verifikasi otomatis via `tests/Feature/MasterDataTest.php` (11 tests, 40 assertions passed). |
| **Fase 3: Terminal Kiosk Presensi & Optimasi Biometrik/QR** | **SELESAI (100% Passed)** | Antarmuka Kiosk layar penuh gerbang sekolah (`/kiosk`), integrasi **Face AI offline lokal** (`/models/face-api`) dengan HUD feedback realtime (deteksi wajah, status unknown vs matched), pemindai **QR Code 30 FPS hardware-accelerated** (`Html5Qrcode`) dengan dynamic scan box & cooldown responsif 1.5 detik, mesin validasi jam masuk & pulang (`AttendanceService`), proteksi *anti double-tap*, umpan balik audio TTS & visual, modul cetak kartu pelajar dengan **QR Code Jumbo beresolusi tinggi (240px &times; 240px)** siap cetak kertas fisik, halaman Riwayat & Log Presensi Multi-Filter Admin (`/admin/attendances`), serta verifikasi otomatis via `tests/Feature/KioskAttendanceTest.php` (18 tests, 65 assertions passed). |
| **Fase 3.5: Presensi Mandiri Mobile Anti-Antrean (Wi-Fi Sekolah & Device Lock)** | **SELESAI (100% Passed)** | Solusi penghilang antrean gerbang: Siswa presensi langsung via HP (`/presensi`), proteksi subnet Wi-Fi sekolah (`VerifySchoolNetwork`), kunci perangkat keras unik (*Device Lock* 1 HP = 1 Akun), tombol reset kaitan perangkat di Admin & Guru Dashboard, pembatasan ketat jam operasional server (*server-side clock*), serta verifikasi otomatis via `tests/Feature/SelfAttendanceTest.php` (26 tests, 103 assertions passed). |
| **Fase 4: Restrukturisasi 9 Menu Sidebar & Fitur Pelaporan SMKN 77** | **SELESAI (100% Passed)** | Restrukturisasi menu navigasi admin sesuai referensi visual (Dashboard, Presensi Siswa, Data Siswa, Data Guru, Data Kelas & Jurusan, Generate QR Code, Generate Laporan, Data Petugas, Pengaturan). Desain sidebar *frosted glass* dengan latar belakang gedung SMKN 77 Jakarta dan aksen teal `#26a69a`. Implementasi modul Data Guru CRUD, Data Petugas CRUD, Generator Cetak Massal QR Code (`batch-print`), Laporan Resmi Kop Dinas & Tanda Tangan, serta panel Pengaturan Terpadu. Seluruh fitur terverifikasi via `tests/Feature/NewMenuNavigationTest.php` (29 tests, 127 assertions passed). |
| **Fase 4.5: Penegakan Jam Operasional, Kotak Scanner Bersih & Optimasi Responsif** | **SELESAI (100% Passed)** | Penolakan presensi otomatis jika berada di luar jam operasional atau hari non-aktif pada Terminal Kiosk dan Presensi Mandiri Siswa (HTTP 422 `outside_operational_hours` / `is_closed: true`) dengan dukungan toggle mode bypass petugas (`bypass_hours`). Desain antarmuka pemindai **Kotak Bersih (*Clean Viewfinder*)** tanpa garis laser tebal, digantikan 4 sudut penanda halus (*sleek corner brackets*) dan siluet oval Face ID transparan. Peningkatan drastis responsivitas scan QR (Chromium `BarcodeDetector` native acceleration + synthesizer Web Audio API 0ms latency) dan Face AI TinyFaceDetector `inputSize: 160` (~25ms inferensi frame). Seluruh fitur terverifikasi via `KioskAttendanceTest` dan `SelfAttendanceTest` (**Total test suite: 33 tests, 141 assertions passed, 100% Green**). |
| **Fase 5: WhatsApp Gateway & Scheduler** | Mendatang | Integrasi Node.js Baileys / Fonnte API, setup Laravel Scheduler & Queue Worker untuk pesan otomatis terlambat/alpa ke orang tua. |
| **Fase 6: Pengujian Beban & Peluncuran** | Mendatang | Uji coba *load test* kecepatan scan pada jam sibuk masuk sekolah (06.30 - 07.15), validasi akurasi wajah, dan panduan operasional. |

---

### 5.5. Aturan Ketat Jam Operasional & Kotak Pemindai Bersih (Kiosk & Mandiri)
1. **Penegakan Jam Operasional Sekolah**:
   - Jadwal masuk (`time_in_start` s/d `time_in_late_limit`) dan jadwal pulang (`time_out_start` s/d `time_out_end`) dievaluasi secara *server-side* berdasarkan aturan aktif (`AttendanceRule`) dan hari kalender (`active_days`).
   - Jika sistem diakses di luar rentang waktu tersebut (sebelum masuk, jeda siang, setelah pulang, atau hari libur):
     - **Backend**: Mengembalikan HTTP 422 dengan pesan edukatif (contoh: "Presensi masuk belum dibuka. Sesi dibuka pukul 06:00 WIB" atau "Presensi hari ini telah ditutup").
     - **Terminal Kiosk**: Menampilkan *Clean Operational Overlay* "PRESENSI SEDANG DITUTUP" dengan badge status dan pengingat jadwal berikutnya.
     - **Mode Bypass Petugas**: Disediakan toggle switch aman `[Mode Bypass Jam Petugas]` bagi operator untuk keperluan pengujian/simulasi di malam hari atau luar jam sekolah.
   - Pengecualian CSRF pada route `api/kiosk/*` dan `api/presensi/*` di `bootstrap/app.php` untuk mencegah session expire (419) pada terminal Kiosk yang menyala seharian di gerbang.
2. **Kotak Pemindai Bersih (*Clean Minimalist Viewfinder*)**:
   - Menghilangkan animasi laser line tebal yang membuat area pemindaian terkesan kotor/ramai.
   - Kotak pemindai dibuat 100% bersih dan jernih (*crystal-clear center*) dengan 4 sudut penanda halus (*sleek corner brackets*) bergaya modern.
   - Pada pemindai Wajah: siluet wajah oval Face ID tipis transparan dengan transisi glow neon halus (*subtle emerald pulse*) ketika wajah siswa berhasil dicocokkan.
3. **Optimasi Responsivitas Pemindai (Zero-Latency Scanning)**:
   - **QR Code**: Pemanfaatan akselerasi hardware native `BarcodeDetector` Chromium (`experimentalFeatures: { useBarCodeDetectorIfSupported: true }`), *full-frame scanning*, synthesizer audio nada instan Web Audio API (0ms latency), dan cooldown dipercepat ke 1.0 detik.
   - **Face AI**: Pengurangan ukuran komputasi `TinyFaceDetectorOptions` menjadi `inputSize: 160` (kecepatan inferensi ~25-35ms per frame) dan penerapan flag guard `isProcessingFaceFrame` untuk mencegah stuttering frame.

---

### 5.6. Tema Visual Solid Midnight Navy & Emerald Green (Layout Bersih & Rapi)
1. **Latar Belakang Sidebar (*Solid Midnight Navy #0f172a*)**:
   - Sidebar navigasi menggunakan warna Midnight Navy solid murni (`#0f172a`) tanpa latar belakang foto yang bising, menghasilkan keterbacaan teks 100% jernih, tajam, dan profesional.
   - Border kanan `border-slate-800` memberikan batas visual yang tegas dan rapi terhadap area konten utama.
2. **Aksen Warna Emerald Green (Segar & Berkelas)**:
   - Menu aktif dihiasi warna Emerald Green yang tegas (`bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/30`), dengan ikon putih solid yang kontras.
   - Ikon menu inaktif (`text-slate-400`) menyala menjadi hijau emerald lembut saat disorot kursor (*hover effect*).
3. **Canvas Utama & Topbar**:
   - Canvas dasar aplikasi menggunakan `bg-slate-100` yang sejuk dan bersih, membuat seluruh kartu-kartu data (`bg-white shadow-sm border border-slate-200/80 rounded-2xl`) memiliki kontras yang sempurna dan tidak berantakan.
   - Topbar berwarna putih bersih (`bg-white border-b border-slate-200`) yang dilengkapi tombol toggle hamburger, judul halaman tebal, dan kapsul tanggal beraksen emerald hijau.

---

### 5.7. Sidebar Buka-Tutup (Smooth Slide Toggle & Mobile Drawer)
1. **Mekanisme Buka-Tutup Sidebar yang Kokoh**:
   - **Mode Desktop (Slide Collapse / Expand)**:
     - Sidebar dapat disembunyikan ke kiri (`-ml-64`) atau dimunculkan penuh (`w-64`) secara mulus dengan transisi `transition-all duration-300 ease-in-out` tanpa merusak tata letak teks menu.
     - Saat sidebar ditutup, area kerja konten otomatis melebar penuh 100% sehingga pengguna dapat fokus memeriksa tabel data presensi yang lebar.
     - Status buka-tutup disimpan secara persisten di browser pengguna via `localStorage ('presensi_sidebar_open')`.
   - **Mode Mobile (Off-Canvas Drawer)**:
     - Pada layar ponsel (< 768px), sidebar berfungsi sebagai drawer yang meluncur dari kiri dengan *backdrop blur* gelap (`bg-slate-900/60`).
     - Terdapat tombol silang (X) dan klik backdrop untuk menutup drawer dengan cepat.
   - **Tombol Toggle Terintegrasi**:
     - Tombol Hamburger di Topbar berfungsi sebagai pengendali utama untuk membuka atau menutup sidebar kapan pun.
2. **Animasi Menarik & Responsif**:
   - Menu navigasi memiliki transisi gerak halus ke kanan saat di-hover (`.nav-transition`).
   - Kartu statistik dan status presensi memiliki efek angkat halus (`.card-hover: translateY(-2px)`).
   - Tombol Kiosk dan tombol aksi memiliki efek tekanan taktil saat diklik (`.btn-bounce: active:scale-97`).
   - Efek dot indikator hijau berdenyut (*pulse*) pada tanggal aktif.

---

## 7. Rekomendasi Perangkat Keras (Hardware Requirements)

Untuk terminal kiosk di gerbang sekolah:
1. **Komputer Kiosk**: PC/Mini PC (Intel Core i3 Gen 8+ atau setara, RAM 8GB, SSD) dengan OS Windows.
2. **Kamera Web (Webcam)**: Full HD 1080p dengan autofokus dan pencahayaan yang stabil (misal: Logitech C920/C922).
3. **Scanner Barcode/QR**: 2D Omnidirectional Desktop Barcode Scanner (USB Plug & Play) agar siswa cukup meletakkan kartu di depan sensor tanpa harus memegang alat.
4. **Koneksi Jaringan**: Kabel LAN (Ethernet) untuk stabilitas koneksi ke server lokal Laragon / Cloud.


saat melakukan sesuatu seperti membuat fitur atau yang lainnya tolong anailis dlu dan cari data/referensi yang sesuai.
serta pastikan untuk melakukan Test-Driven Loop yaitu:
1. write/modify
2. test
3. analisis jika tes gagal
4. repeat sampai berhasil dan sempurna.

dan Selalu update file dokumentasi serta selalu baca ulang file ini ketika mulai melakukan sesuatu.