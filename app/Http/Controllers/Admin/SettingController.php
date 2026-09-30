<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRule;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $rules = AttendanceRule::orderBy('id')->get();
        $schoolName = config('app.school_name', 'SMK Negeri 77 Jakarta');
        $schoolAddress = config('app.school_address', 'Jl. Cempaka Putih Tengah XVII No. 77, Jakarta Pusat');
        $schoolSubnets = config('attendance.school_subnets', ['192.168.1.0/24', '10.0.0.0/24', '127.0.0.1']);

        return view('admin.settings.index', compact('rules', 'schoolName', 'schoolAddress', 'schoolSubnets'));
    }
}
