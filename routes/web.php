<?php

use App\Http\Controllers\Admin\AttendanceRuleController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Kiosk Terminal Presensi (Layar Penuh Gerbang Sekolah)
Route::get('/kiosk', [\App\Http\Controllers\KioskController::class, 'index'])->name('kiosk.index');
Route::prefix('api/kiosk')->name('kiosk.api.')->group(function () {
    Route::post('/scan-barcode', [\App\Http\Controllers\KioskController::class, 'scanBarcode'])->name('scan-barcode');
    Route::post('/scan-face', [\App\Http\Controllers\KioskController::class, 'scanFace'])->name('scan-face');
    Route::get('/descriptors', [\App\Http\Controllers\KioskController::class, 'getFaceDescriptors'])->name('descriptors');
});

// Presensi Mandiri Siswa (Mobile-First via Wi-Fi Sekolah)
Route::middleware(['school.network'])->group(function () {
    Route::get('/presensi', [\App\Http\Controllers\SelfAttendanceController::class, 'index'])->name('presensi.index');
    Route::post('/api/presensi/status', [\App\Http\Controllers\SelfAttendanceController::class, 'checkStatus'])->name('presensi.api.status');
    Route::post('/api/presensi/submit', [\App\Http\Controllers\SelfAttendanceController::class, 'submit'])->name('presensi.api.submit');
});

// Admin Routes (Role: admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Attendance History & Multi-Filter Report
    Route::get('/attendances', [\App\Http\Controllers\Admin\AttendanceReportController::class, 'index'])->name('attendances.index');

    // Classes Management
    Route::resource('classes', SchoolClassController::class)->except(['create', 'show', 'edit']);

    // Students Management
    Route::resource('students', StudentController::class);
    Route::get('students/{student}/card', [StudentController::class, 'card'])->name('students.card');
    Route::get('students/{student}/enroll-face', [StudentController::class, 'enrollFace'])->name('students.enroll-face');
    Route::post('students/{student}/save-face', [StudentController::class, 'saveFace'])->name('students.save-face');
    Route::post('students/{student}/reset-device', [StudentController::class, 'resetDevice'])->name('students.reset-device');

    // Data Guru
    Route::resource('teachers', \App\Http\Controllers\Admin\TeacherController::class)->except(['create', 'show', 'edit']);

    // Generate QR Code
    Route::get('qrcode', [\App\Http\Controllers\Admin\QrCodeController::class, 'index'])->name('qrcode.index');
    Route::post('qrcode/batch-print', [\App\Http\Controllers\Admin\QrCodeController::class, 'batchPrint'])->name('qrcode.batch-print');

    // Generate Laporan
    Route::get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');

    // Data Petugas
    Route::resource('officers', \App\Http\Controllers\Admin\OfficerController::class)->except(['create', 'show', 'edit']);

    // Pengaturan
    Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');

    // Attendance Rules Management
    Route::resource('rules', AttendanceRuleController::class)->except(['create', 'show', 'edit']);
});

// Guru Routes (Role: guru)
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');
    Route::post('/students/{student}/reset-device', [StudentController::class, 'resetDevice'])->name('students.reset-device');
});
