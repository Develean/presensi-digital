<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRule;
use App\Models\Student;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KioskController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Render Kiosk Fullscreen UI.
     */
    public function index()
    {
        $now = Carbon::now('Asia/Jakarta');
        $currentDayOfWeek = (int) $now->isoFormat('E');

        $rules = AttendanceRule::where('is_active', true)->get();
        $activeRule = $rules->first(function ($rule) use ($currentDayOfWeek) {
            return in_array($currentDayOfWeek, $rule->active_days ?? []);
        }) ?? $rules->first();

        return view('kiosk.index', compact('activeRule'));
    }

    /**
     * API: Scan via Barcode / QR Code.
     */
    public function scanBarcode(Request $request)
    {
        $request->validate([
            'barcode_token' => ['required', 'string'],
            'photo' => ['nullable', 'string'],
        ]);

        $student = Student::where('barcode_token', $request->barcode_token)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'type' => 'not_found',
                'message' => 'Kartu Barcode tidak terdaftar dalam sistem.',
            ], 404);
        }

        if (!$student->is_active) {
            return response()->json([
                'success' => false,
                'type' => 'inactive',
                'message' => 'Status siswa non-aktif. Hubungi pihak sekolah.',
            ], 403);
        }

        $result = $this->attendanceService->recordAttendance(
            student: $student,
            method: 'barcode',
            photoBase64: $request->input('photo')
        );

        return response()->json($result);
    }

    /**
     * API: Scan via Face Recognition.
     */
    public function scanFace(Request $request)
    {
        $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'photo' => ['nullable', 'string'],
        ]);

        $student = Student::find($request->student_id);

        if (!$student || !$student->is_active) {
            return response()->json([
                'success' => false,
                'type' => 'inactive',
                'message' => 'Siswa tidak ditemukan atau non-aktif.',
            ], 404);
        }

        $result = $this->attendanceService->recordAttendance(
            student: $student,
            method: 'face',
            photoBase64: $request->input('photo')
        );

        return response()->json($result);
    }

    /**
     * API: Mengambil seluruh vektor biometrik wajah siswa untuk pencocokan cepat di browser kiosk.
     */
    public function getFaceDescriptors()
    {
        $students = Student::with('schoolClass')
            ->where('is_active', true)
            ->whereNotNull('face_descriptor')
            ->select('id', 'name', 'nis', 'class_id', 'barcode_token', 'face_descriptor')
            ->get();

        return response()->json($students);
    }
}
