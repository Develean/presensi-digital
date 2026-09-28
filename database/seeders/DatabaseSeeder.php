<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceRule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@sekolah.sch.id'],
            [
                'name' => 'Administrator Presensi',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // 2. Akun Guru / Wali Kelas
        $guru = User::firstOrCreate(
            ['email' => 'guru@sekolah.sch.id'],
            [
                'name' => 'Budi Santoso, S.Pd',
                'password' => Hash::make('password'),
                'role' => 'guru',
            ]
        );

        // 3. Data Kelas
        $kelasRpl = SchoolClass::firstOrCreate(
            ['name' => 'X-RPL 1'],
            [
                'level' => 'X',
                'teacher_id' => $guru->id,
            ]
        );

        // 4. Data Siswa Contoh (Lengkap dengan Token QR & Vektor Biometrik Wajah 128-float)
        $siswa1 = Student::firstOrCreate(
            ['nis' => '2026001'],
            [
                'nisn' => '0081234567',
                'name' => 'Muhammad Fauzan',
                'gender' => 'L',
                'class_id' => $kelasRpl->id,
                'barcode_token' => 'STU-2026-001',
                'face_descriptor' => json_encode(array_fill(0, 128, 0.12345)),
                'parent_name' => 'Ahmad Subagyo',
                'parent_phone' => '6281234567890',
                'is_active' => true,
            ]
        );

        $siswa2 = Student::firstOrCreate(
            ['nis' => '2026002'],
            [
                'nisn' => '0081234568',
                'name' => 'Siti Aisyah',
                'gender' => 'P',
                'class_id' => $kelasRpl->id,
                'barcode_token' => 'STU-2026-002',
                'face_descriptor' => json_encode(array_fill(0, 128, -0.12345)),
                'parent_name' => 'Rahmat Hidayat',
                'parent_phone' => '6281234567891',
                'is_active' => true,
            ]
        );

        // 5. Aturan Jam Presensi Sekolah
        AttendanceRule::firstOrCreate(
            ['name' => 'Reguler (Senin - Kamis)'],
            [
                'time_in_start' => '06:00:00',
                'time_in_deadline' => '07:15:00',
                'time_in_late_limit' => '07:30:00',
                'time_out_start' => '15:00:00',
                'time_out_end' => '17:00:00',
                'active_days' => [1, 2, 3, 4],
                'is_active' => true,
            ]
        );

        AttendanceRule::firstOrCreate(
            ['name' => 'Jumat'],
            [
                'time_in_start' => '06:00:00',
                'time_in_deadline' => '07:15:00',
                'time_in_late_limit' => '07:30:00',
                'time_out_start' => '11:30:00',
                'time_out_end' => '13:00:00',
                'active_days' => [5],
                'is_active' => true,
            ]
        );
    }
}
