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
    public function recordAttendance(Student $student, string $method, ?string $photoBase64 = null, ?Carbon $currentTime = null): array
    {
        $now = $currentTime ?? Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $currentTimeStr = $now->toTimeString(); // 'H:i:s'
        $currentDayOfWeek = (int) $now->isoFormat('E'); // 1 = Senin, 7 = Minggu

        // 1. Cari aturan jam presensi yang berlaku untuk hari ini
        $rules = AttendanceRule::where('is_active', true)->get();
        $activeRule = $rules->first(function ($rule) use ($currentDayOfWeek) {
            return in_array($currentDayOfWeek, $rule->active_days ?? []);
        }) ?? $rules->first();

        if (!$activeRule) {
            return [
                'success' => false,
                'type' => 'no_rule',
                'message' => 'Belum ada aturan jam presensi yang aktif untuk hari ini.',
            ];
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

        // 2. Evaluasi: Apakah berada dalam rentang JAM MASUK?
        // Jam masuk: dari time_in_start s/d time_in_late_limit
        $inStart = $activeRule->time_in_start;
        $inDeadline = $activeRule->time_in_deadline;
        $inLateLimit = $activeRule->time_in_late_limit;
        $outStart = $activeRule->time_out_start;
        $outEnd = $activeRule->time_out_end;

        // Cek jika siswa belum presensi masuk dan waktu berada di rentang masuk ATAU sebelum jam pulang
        if ($currentTimeStr < $outStart) {
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
            'message' => "Saat ini ({$currentTimeStr} WIB) berada di luar jam operasional presensi masuk maupun pulang.",
            'student' => $student,
        ];
    }
}
