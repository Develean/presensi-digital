<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceRule;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceService
{
    /**
     * Catat presensi siswa (Masuk atau Pulang) secara otomatis berdasarkan jam aturan sekolah.
     */
    public function recordAttendance(Student $student, string $method, ?string $photoBase64 = null, ?Carbon $currentTime = null, bool $bypassHours = false): array
    {
        $now = $currentTime ?? Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $currentTimeStr = $now->toTimeString(); // 'H:i:s'
        $currentDayOfWeek = (int) $now->isoFormat('E'); // 1 = Senin, 7 = Minggu

        // 1. Cari aturan jam presensi yang berlaku untuk hari ini
        $rules = AttendanceRule::where('is_active', true)->get();
        $activeRule = $rules->first(function ($rule) use ($currentDayOfWeek) {
            $days = is_array($rule->active_days) ? $rule->active_days : json_decode($rule->active_days, true) ?? [];
            if (empty($days)) {
                return true;
            }
            return in_array($currentDayOfWeek, $days);
        });

        if (!$activeRule && !$bypassHours) {
            $dayNames = [
                1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
                4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
            ];
            $todayName = $dayNames[$currentDayOfWeek] ?? 'Hari Ini';
            return [
                'success' => false,
                'type' => 'inactive_day',
                'is_closed' => true,
                'message' => "Hari {$todayName} bukan hari operasional presensi sekolah. Sistem presensi ditutup.",
                'student' => $student,
            ];
        }

        // Aturan fallback jika bypass mode di luar hari aktif
        if (!$activeRule) {
            $activeRule = AttendanceRule::where('is_active', true)->first() ?? AttendanceRule::first();
        }

        // Ambil atau inisialisasi record presensi siswa hari ini
        $attendance = Attendance::where('student_id', $student->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if (!$attendance) {
            $attendance = new Attendance([
                'student_id' => $student->id,
                'attendance_date' => $today,
            ]);
        }

        // Simpan foto bukti jika ada
        $photoPath = null;
        if ($photoBase64 && str_contains($photoBase64, ',')) {
            try {
                $imageParts = explode(';base64,', $photoBase64);
                $imageTypeAux = explode('image/', $imageParts[0]);
                $imageType = $imageTypeAux[1] ?? 'png';
                $imageBase64 = base64_decode($imageParts[1]);
                $fileName = 'attendances/' . $today . '/' . $student->nis . '_' . time() . '.' . $imageType;
                Storage::disk('public')->put($fileName, $imageBase64);
                $photoPath = $fileName;
            } catch (\Exception $e) {
                // Ignore photo saving error
            }
        }

        // Jika dalam mode bypass (khusus testing petugas / admin di luar jam operasional)
        if ($bypassHours) {
            if ($attendance->time_in === null) {
                $attendance->time_in = $currentTimeStr;
                $attendance->status_in = 'hadir';
                $attendance->method_in = $method;
                if ($photoPath) $attendance->photo_in_path = $photoPath;
                $attendance->save();

                return [
                    'success' => true,
                    'action' => 'masuk',
                    'status' => 'hadir',
                    'time' => substr($currentTimeStr, 0, 5),
                    'message' => "[Mode Bypass] Presensi Masuk Berhasil! Selamat Pagi, {$student->name}.",
                    'student' => $student->load('schoolClass'),
                    'attendance' => $attendance,
                ];
            }

            if ($attendance->time_out === null) {
                $attendance->time_out = $currentTimeStr;
                $attendance->status_out = 'pulang_normal';
                $attendance->method_out = $method;
                if ($photoPath) $attendance->photo_out_path = $photoPath;
                $attendance->save();

                return [
                    'success' => true,
                    'action' => 'pulang',
                    'status' => 'pulang_normal',
                    'time' => substr($currentTimeStr, 0, 5),
                    'message' => "[Mode Bypass] Presensi Pulang Berhasil! Hati-hati di jalan, {$student->name}.",
                    'student' => $student->load('schoolClass'),
                    'attendance' => $attendance,
                ];
            }

            return [
                'success' => false,
                'type' => 'already_complete',
                'message' => "Siswa {$student->name} sudah lengkap presensi masuk dan pulang hari ini.",
                'student' => $student,
                'attendance' => $attendance,
            ];
        }

        // 2. Evaluasi: Apakah berada dalam rentang JAM MASUK?
        // Jam masuk: dari time_in_start s/d time_in_late_limit
        $inStart = $activeRule->time_in_start;
        $inDeadline = $activeRule->time_in_deadline;
        $inLateLimit = $activeRule->time_in_late_limit;
        $outStart = $activeRule->time_out_start;
        $outEnd = $activeRule->time_out_end;

        // Cek jika sebelum jam masuk
        if ($currentTimeStr < $inStart) {
            return [
                'success' => false,
                'type' => 'early_in',
                'is_closed' => true,
                'message' => 'Presensi masuk belum dibuka. Gerbang presensi dibuka pukul ' . substr($inStart, 0, 5) . ' WIB.',
                'student' => $student,
            ];
        }

        // Cek jika siswa belum presensi masuk dan waktu berada di rentang masuk (sebelum batas telat)
        if ($currentTimeStr <= $inLateLimit) {
            // Evaluasi Presensi Masuk
            if ($attendance->time_in !== null) {
                return [
                    'success' => false,
                    'type' => 'already_in',
                    'message' => "Siswa {$student->name} sudah melakukan presensi masuk pada pukul " . substr($attendance->time_in, 0, 5) . " WIB.",
                    'student' => $student,
                    'attendance' => $attendance,
                ];
            }

            // Tentukan status kehadiran (Hadir Tepat Waktu atau Terlambat)
            $statusIn = ($currentTimeStr <= $inDeadline) ? 'hadir' : 'terlambat';

            $attendance->time_in = $currentTimeStr;
            $attendance->status_in = $statusIn;
            $attendance->method_in = $method;
            if ($photoPath) {
                $attendance->photo_in_path = $photoPath;
            }
            $attendance->save();

            return [
                'success' => true,
                'action' => 'masuk',
                'status' => $statusIn,
                'time' => substr($currentTimeStr, 0, 5),
                'message' => $statusIn === 'hadir' 
                    ? "Presensi Masuk Berhasil! Selamat Pagi, {$student->name}." 
                    : "Presensi Masuk Berhasil (Terlambat). Segera menuju kelas, {$student->name}.",
                'student' => $student->load('schoolClass'),
                'attendance' => $attendance,
            ];
        }

        // Jika lewat batas maksimal masuk tapi sebelum jam pulang
        if ($currentTimeStr > $inLateLimit && $currentTimeStr < $outStart) {
            if ($attendance->time_in !== null) {
                return [
                    'success' => false,
                    'type' => 'already_in_waiting_out',
                    'message' => "Anda sudah presensi masuk pukul " . substr($attendance->time_in, 0, 5) . " WIB. Presensi pulang dibuka pukul " . substr($outStart, 0, 5) . " WIB.",
                    'student' => $student,
                    'attendance' => $attendance,
                ];
            }

            return [
                'success' => false,
                'type' => 'late_limit_exceeded',
                'message' => "Presensi masuk mandiri telah ditutup pukul " . substr($inLateLimit, 0, 5) . " WIB. Silakan lapor ke Guru Piket.",
                'student' => $student,
            ];
        }

        // 3. Evaluasi: Apakah berada dalam rentang JAM PULANG?
        if ($currentTimeStr >= $outStart && $currentTimeStr <= $outEnd) {
            if ($attendance->time_out !== null) {
                return [
                    'success' => false,
                    'type' => 'already_out',
                    'message' => "Siswa {$student->name} sudah melakukan presensi pulang pada pukul " . substr($attendance->time_out, 0, 5) . " WIB.",
                    'student' => $student,
                    'attendance' => $attendance,
                ];
            }

            $attendance->time_out = $currentTimeStr;
            $attendance->status_out = 'pulang_normal';
            $attendance->method_out = $method;
            if ($photoPath) {
                $attendance->photo_out_path = $photoPath;
            }
            $attendance->save();

            return [
                'success' => true,
                'action' => 'pulang',
                'status' => 'pulang_normal',
                'time' => substr($currentTimeStr, 0, 5),
                'message' => "Presensi Pulang Berhasil! Hati-hati di jalan, {$student->name}.",
                'student' => $student->load('schoolClass'),
                'attendance' => $attendance,
            ];
        }

        // 4. Di luar jam masuk dan jam pulang
        return [
            'success' => false,
            'type' => 'outside_schedule',
            'is_closed' => true,
            'message' => "Saat ini (" . substr($currentTimeStr, 0, 5) . " WIB) berada di luar jam operasional presensi sekolah.",
            'student' => $student,
        ];
    }

    /**
     * Dapatkan status jam operasional saat ini berdasarkan aturan aktif.
     */
    public function getScheduleStatus(?Carbon $currentTime = null): array
    {
        $now = $currentTime ?? Carbon::now('Asia/Jakarta');
        $currentTimeStr = $now->toTimeString();
        $currentDayOfWeek = (int) $now->isoFormat('E');

        $rules = AttendanceRule::where('is_active', true)->get();
        $activeRule = $rules->first(function ($rule) use ($currentDayOfWeek) {
            $days = is_array($rule->active_days) ? $rule->active_days : json_decode($rule->active_days, true) ?? [];
            if (empty($days)) {
                return true;
            }
            return in_array($currentDayOfWeek, $days);
        });

        if (!$activeRule) {
            $dayNames = [
                1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
                4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
            ];
            $todayName = $dayNames[$currentDayOfWeek] ?? 'Hari Ini';
            return [
                'is_open' => false,
                'active' => false,
                'phase' => 'no_rule',
                'server_time' => $now->format('H:i:s'),
                'server_time_display' => $now->format('H:i') . ' WIB',
                'server_date_display' => $now->locale('id')->isoFormat('dddd, D MMMM Y'),
                'message' => "Hari {$todayName} bukan hari operasional presensi sekolah.",
                'can_in' => false,
                'can_out' => false,
                'current_session' => 'closed',
            ];
        }

        $inStart = $activeRule->time_in_start;
        $inDeadline = $activeRule->time_in_deadline;
        $inLateLimit = $activeRule->time_in_late_limit;
        $outStart = $activeRule->time_out_start;
        $outEnd = $activeRule->time_out_end;

        $canIn = ($currentTimeStr >= $inStart && $currentTimeStr <= $inLateLimit);
        $canOut = ($currentTimeStr >= $outStart && $currentTimeStr <= $outEnd);
        $isOpen = ($canIn || $canOut);

        $phase = 'outside';
        $message = 'Presensi saat ini ditutup.';
        $currentSession = 'closed';

        if ($currentTimeStr < $inStart) {
            $phase = 'before_in';
            $message = 'Presensi masuk akan dibuka pukul ' . substr($inStart, 0, 5) . ' WIB.';
        } elseif ($canIn) {
            $phase = 'in_open';
            $currentSession = 'masuk';
            $message = $currentTimeStr <= $inDeadline
                ? 'Presensi Masuk Tepat Waktu (s/d ' . substr($inDeadline, 0, 5) . ' WIB)'
                : 'Presensi Masuk Terlambat (Batas akhir ' . substr($inLateLimit, 0, 5) . ' WIB)';
        } elseif ($currentTimeStr > $inLateLimit && $currentTimeStr < $outStart) {
            $phase = 'waiting_out';
            $message = 'Presensi masuk telah ditutup. Presensi pulang dibuka pukul ' . substr($outStart, 0, 5) . ' WIB.';
        } elseif ($canOut) {
            $phase = 'out_open';
            $currentSession = 'pulang';
            $message = 'Presensi Pulang Terbuka (s/d ' . substr($outEnd, 0, 5) . ' WIB)';
        } else {
            $phase = 'after_out';
            $message = 'Presensi hari ini telah berakhir / ditutup.';
        }

        return [
            'is_open' => $isOpen,
            'active' => true,
            'phase' => $phase,
            'rule_name' => $activeRule->name,
            'server_time' => $now->format('H:i:s'),
            'server_time_display' => $now->format('H:i') . ' WIB',
            'server_date_display' => $now->locale('id')->isoFormat('dddd, D MMMM Y'),
            'message' => $message,
            'can_in' => $canIn,
            'can_out' => $canOut,
            'current_session' => $currentSession,
            'in_start' => substr($inStart, 0, 5),
            'in_deadline' => substr($inDeadline, 0, 5),
            'in_late_limit' => substr($inLateLimit, 0, 5),
            'out_start' => substr($outStart, 0, 5),
            'out_end' => substr($outEnd, 0, 5),
        ];
    }
}
