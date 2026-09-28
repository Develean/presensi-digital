<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $teacher = Auth::user();
        $myClass = SchoolClass::where('teacher_id', $teacher->id)->first();
        $today = now()->toDateString();

        $stats = [
            'my_class' => $myClass,
            'total_students' => $myClass ? $myClass->students()->where('is_active', true)->count() : 0,
            'hadir_today' => 0,
            'terlambat_today' => 0,
            'izin_today' => 0,
            'sakit_today' => 0,
            'alpa_today' => 0,
        ];

        if ($myClass) {
            $studentIds = $myClass->students()->pluck('id');
            $stats['hadir_today'] = Attendance::whereIn('student_id', $studentIds)->where('attendance_date', $today)->where('status_in', 'hadir')->count();
            $stats['terlambat_today'] = Attendance::whereIn('student_id', $studentIds)->where('attendance_date', $today)->where('status_in', 'terlambat')->count();
            $stats['izin_today'] = Attendance::whereIn('student_id', $studentIds)->where('attendance_date', $today)->where('status_in', 'izin')->count();
            $stats['sakit_today'] = Attendance::whereIn('student_id', $studentIds)->where('attendance_date', $today)->where('status_in', 'sakit')->count();
            $stats['alpa_today'] = Attendance::whereIn('student_id', $studentIds)->where('attendance_date', $today)->where('status_in', 'alpa')->count();
        }

        return view('guru.dashboard', compact('stats', 'myClass', 'teacher'));
    }
}
