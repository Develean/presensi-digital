<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SelfAttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Tampilkan halaman presensi mandiri (Mobile-first).
     */
    public function index(): View
    {
        $schedule = $this->attendanceService->getScheduleStatus();
        return view('presensi.index', compact('schedule'));
    }

    /**
     * Endpoint API untuk mengecek status jadwal & status kehadiran siswa hari ini.
     */
    public function checkStatus(Request $request): JsonResponse
    {
        $schedule = $this->attendanceService->getScheduleStatus();
        $nis = $request->input('nis');
        $deviceId = $request->input('device_id');

        $data = [
            'success' => true,
            'schedule' => $schedule,
            'student' => null,
            'attendance_today' => null,
            'device_status' => 'unidentified',
        ];

        if ($nis) {
            $student = Student::with('schoolClass')->where('nis', $nis)->first();

            if (! $student) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'STUDENT_NOT_FOUND',
                    'message' => 'Siswa dengan NIS tersebut tidak ditemukan.',
                ], 404);
            }

            if (! $student->is_active) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'STUDENT_INACTIVE',
                    'message' => 'Status siswa non-aktif. Silakan hubungi tata usaha.',
                ], 403);
            }

            // Cek kaitan perangkat
            $deviceStatus = 'unlocked';
            $deviceMatch = true;

            if ($student->isDeviceLocked()) {
                if ($deviceId && $student->device_id === $deviceId) {
                    $deviceStatus = 'matched';
                } else {
                    $deviceStatus = 'mismatch';
                    $deviceMatch = false;
                }
            } else {
                $deviceStatus = 'first_time';
            }

            // Ambil presensi hari ini
            $today = Carbon::now('Asia/Jakarta')->toDateString();
            $attendanceToday = Attendance::where('student_id', $student->id)
                ->whereDate('attendance_date', $today)
                ->first();

            $data['student'] = [
                'id' => $student->id,
                'nis' => $student->nis,
                'name' => $student->name,
                'class_name' => $student->schoolClass->name ?? '-',
                'is_device_locked' => $student->isDeviceLocked(),
                'locked_device_name' => $student->device_name,
            ];
            $data['device_status'] = $deviceStatus;
            $data['device_match'] = $deviceMatch;
            $data['attendance_today'] = $attendanceToday ? [
                'time_in' => $attendanceToday->time_in ? substr($attendanceToday->time_in, 0, 5) : null,
                'status_in' => $attendanceToday->status_in,
                'time_out' => $attendanceToday->time_out ? substr($attendanceToday->time_out, 0, 5) : null,
                'status_out' => $attendanceToday->status_out,
            ] : null;
        }

        return response()->json($data);
    }

    /**
     * Submit presensi mandiri dari HP siswa.
     */
    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string'],
            'device_id' => ['required', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ], [
            'nis.required' => 'Nomor Induk Siswa (NIS) wajib diisi.',
            'device_id.required' => 'ID Perangkat tidak terdeteksi. Pastikan browser mendukung LocalStorage.',
        ]);

        $student = Student::where('nis', $validated['nis'])->first();

        if (! $student) {
            return response()->json([
                'success' => false,
                'error_code' => 'STUDENT_NOT_FOUND',
                'message' => 'Siswa dengan NIS tersebut tidak ditemukan.',
            ], 404);
        }

        if (! $student->is_active) {
            return response()->json([
                'success' => false,
                'error_code' => 'STUDENT_INACTIVE',
                'message' => 'Status siswa non-aktif. Silakan hubungi tata usaha.',
            ], 403);
        }

        // Cek jam operasional presensi sekolah
        $schedule = $this->attendanceService->getScheduleStatus();
        if (!$schedule['is_open']) {
            $type = match ($schedule['phase'] ?? '') {
                'before_in' => 'early_in',
                'waiting_out' => 'late_limit_exceeded',
                'after_out' => 'after_out',
                'no_rule' => 'inactive_day',
                default => 'outside_schedule',
            };

            return response()->json([
                'success' => false,
                'error_code' => 'OUTSIDE_OPERATIONAL_HOURS',
                'type' => $type,
                'is_closed' => true,
                'message' => 'Presensi mandiri tidak dapat dilakukan. ' . $schedule['message'],
                'schedule' => $schedule,
            ], 422);
        }

        // Validasi Device Lock
        if ($student->isDeviceLocked()) {
            if ($student->device_id !== $validated['device_id']) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'DEVICE_MISMATCH',
                    'message' => "Presensi ditolak. Akun Anda telah terikat pada perangkat lain ({$student->device_name}). Hubungi Guru Piket jika Anda mengganti perangkat.",
                ], 403);
            }
        } else {
            // Pertama kali presensi dari HP: kunci perangkat ini ke siswa
            $deviceName = $validated['device_name'] ?? ('HP Siswa (' . ($request->userAgent() ?: 'Mobile') . ')');
            $student->lockToDevice(
                $validated['device_id'],
                $deviceName
            );
        }

        // Catat presensi menggunakan AttendanceService
        $result = $this->attendanceService->recordAttendance(
            $student,
            'self_wifi'
        );

        $statusCode = $result['success'] ? 200 : 422;

        return response()->json($result, $statusCode);
    }
}
