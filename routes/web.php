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

    // Attendance Rules Management
    Route::resource('rules', AttendanceRuleController::class)->except(['create', 'show', 'edit']);
});

// Guru Routes (Role: guru)
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');
});
