<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $stats = [
            'total_students' => Student::where('is_active', true)->count(),
            'total_teachers' => User::where('role', 'guru')->count(),
            'total_classes' => SchoolClass::count(),
            'attendance_today' => [
                'hadir' => Attendance::where('attendance_date', $today)->where('status_in', 'hadir')->count(),
                'terlambat' => Attendance::where('attendance_date', $today)->where('status_in', 'terlambat')->count(),
                'izin' => Attendance::where('attendance_date', $today)->where('status_in', 'izin')->count(),
                'sakit' => Attendance::where('attendance_date', $today)->where('status_in', 'sakit')->count(),
                'alpa' => Attendance::where('attendance_date', $today)->where('status_in', 'alpa')->count(),
            ],
        ];

        // Recent presensi today
        $recentAttendances = Attendance::with(['student.schoolClass'])
            ->where('attendance_date', $today)
            ->latest('time_in')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentAttendances'));
    }
}
