<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with(['student.schoolClass', 'updater'])->latest('attendance_date');

        // 1. Filter Tanggal (Default: Hari Ini)
        $date = $request->input('date', Carbon::now('Asia/Jakarta')->toDateString());
        if ($request->filled('date')) {
            $query->whereDate('attendance_date', $request->date);
        }

        // 2. Filter Siswa (Pencarian Nama / NIS)
        if ($request->filled('student_search')) {
            $search = $request->input('student_search');
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // 3. Filter Kelas
        if ($request->filled('class_id')) {
            $classId = $request->input('class_id');
            $query->whereHas('student', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        // 4. Filter Status Kehadiran (hadir, terlambat, izin, sakit, alpa)
        if ($request->filled('status')) {
            $query->where('status_in', $request->input('status'));
        }

        // 5. Filter Waktu (Masuk, Pulang, Belum Pulang)
        if ($request->filled('time_filter')) {
            $timeFilter = $request->input('time_filter');
            if ($timeFilter === 'has_in') {
                $query->whereNotNull('time_in');
            } elseif ($timeFilter === 'has_out') {
                $query->whereNotNull('time_out');
            } elseif ($timeFilter === 'no_out') {
                $query->whereNotNull('time_in')->whereNull('time_out');
            }
        }

        // 6. Filter Metode (face, barcode, manual)
        if ($request->filled('method')) {
            $method = $request->input('method');
            $query->where(function ($q) use ($method) {
                $q->where('method_in', $method)
                  ->orWhere('method_out', $method);
            });
        }

        $attendances = $query->paginate(20)->withQueryString();
        $classes = SchoolClass::orderBy('name')->get();

        return view('admin.attendances.index', compact('attendances', 'classes', 'date'));
    }
}
