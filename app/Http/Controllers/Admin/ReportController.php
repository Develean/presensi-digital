<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now('Asia/Jakarta')->toDateString());
        $classId = $request->input('class_id');
        $status = $request->input('status');

        $query = Attendance::with(['student.schoolClass', 'updater'])
            ->whereBetween('attendance_date', [$startDate, $endDate]);

        if (!empty($classId)) {
            $query->whereHas('student', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        if (!empty($status)) {
            $query->where('status_in', $status);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->orderBy('time_in', 'desc')
            ->get();

        // Summary counts
        $totalLogs = $attendances->count();
        $totalHadir = $attendances->where('status_in', 'hadir')->count();
        $totalTerlambat = $attendances->where('status_in', 'terlambat')->count();
        $totalIzin = $attendances->where('status_in', 'izin')->count();
        $totalSakit = $attendances->where('status_in', 'sakit')->count();
        $totalAlpa = $attendances->where('status_in', 'alpa')->count();

        $attendanceRate = $totalLogs > 0 
            ? round((($totalHadir + $totalTerlambat) / $totalLogs) * 100, 1) 
            : 0;

        // Group summary per student
        $studentQuery = Student::with('schoolClass')->orderBy('name');
        if (!empty($classId)) {
            $studentQuery->where('class_id', $classId);
        }
        $studentsList = $studentQuery->get();

        $studentRecaps = [];
        foreach ($studentsList as $st) {
            $stLogs = $attendances->where('student_id', $st->id);
            $h = $stLogs->where('status_in', 'hadir')->count();
            $t = $stLogs->where('status_in', 'terlambat')->count();
            $i = $stLogs->where('status_in', 'izin')->count();
            $s = $stLogs->where('status_in', 'sakit')->count();
            $a = $stLogs->where('status_in', 'alpa')->count();
            $totalRecorded = $h + $t + $i + $s + $a;

            $studentRecaps[] = [
                'student' => $st,
                'hadir' => $h,
                'terlambat' => $t,
                'izin' => $i,
                'sakit' => $s,
                'alpa' => $a,
                'total' => $totalRecorded,
                'persentase' => $totalRecorded > 0 ? round((($h + $t) / $totalRecorded) * 100, 1) : 0,
            ];
        }

        $classes = SchoolClass::orderBy('name')->get();
        $selectedClass = $classId ? SchoolClass::find($classId) : null;

        return view('admin.reports.index', compact(
            'attendances',
            'studentRecaps',
            'classes',
            'selectedClass',
            'startDate',
            'endDate',
            'classId',
            'status',
            'totalLogs',
            'totalHadir',
            'totalTerlambat',
            'totalIzin',
            'totalSakit',
            'totalAlpa',
            'attendanceRate'
        ));
    }
}
