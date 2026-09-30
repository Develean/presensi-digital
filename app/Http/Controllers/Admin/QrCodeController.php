<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with('schoolClass')->orderBy('name');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(24)->withQueryString();
        $classes = SchoolClass::orderBy('name')->get();

        return view('admin.qrcode.index', compact('students', 'classes'));
    }

    public function batchPrint(Request $request)
    {
        $studentIds = $request->input('student_ids', []);
        
        if (empty($studentIds) && $request->filled('class_id')) {
            $students = Student::where('class_id', $request->input('class_id'))
                ->with('schoolClass')
                ->orderBy('name')
                ->get();
        } elseif (!empty($studentIds)) {
            $students = Student::whereIn('id', $studentIds)
                ->with('schoolClass')
                ->orderBy('name')
                ->get();
        } else {
            return back()->with('error', 'Silakan pilih siswa atau kelas yang ingin dicetak kartu QR Code.');
        }

        return view('admin.qrcode.batch_print', compact('students'));
    }
}
