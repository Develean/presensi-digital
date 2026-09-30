<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with('schoolClass')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('barcode_token', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        $students = $query->paginate(15)->withQueryString();
        $classes = SchoolClass::orderBy('name')->get();

        return view('admin.students.index', compact('students', 'classes'));
    }

    public function create()
    {
        $classes = SchoolClass::orderBy('name')->get();
        return view('admin.students.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'max:30', 'unique:students,nis'],
            'nisn' => ['nullable', 'string', 'max:30', 'unique:students,nisn'],
            'name' => ['required', 'string', 'max:150'],
            'gender' => ['required', 'in:L,P'],
            'class_id' => ['required', 'exists:classes,id'],
            'parent_name' => ['required', 'string', 'max:150'],
            'parent_phone' => ['required', 'string', 'max:25'],
        ], [
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah digunakan oleh siswa lain.',
            'name.required' => 'Nama lengkap siswa wajib diisi.',
            'class_id.required' => 'Kelas wajib dipilih.',
            'parent_phone.required' => 'Nomor WhatsApp orang tua wajib diisi.',
        ]);

        // Format nomor HP agar standar internasional 62...
        $phone = preg_replace('/[^0-9]/', '', $validated['parent_phone']);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        $validated['parent_phone'] = $phone;

        // Otomatis buat barcode token unik jika belum ada
        do {
            $token = 'STU-' . date('ym') . '-' . strtoupper(Str::random(5));
        } while (Student::where('barcode_token', $token)->exists());

        $validated['barcode_token'] = $token;
        $validated['is_active'] = true;

        Student::create($validated);

        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function edit(Student $student)
    {
        $classes = SchoolClass::orderBy('name')->get();
        return view('admin.students.edit', compact('student', 'classes'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'max:30', 'unique:students,nis,' . $student->id],
            'nisn' => ['nullable', 'string', 'max:30', 'unique:students,nisn,' . $student->id],
            'name' => ['required', 'string', 'max:150'],
            'gender' => ['required', 'in:L,P'],
            'class_id' => ['required', 'exists:classes,id'],
            'parent_name' => ['required', 'string', 'max:150'],
            'parent_phone' => ['required', 'string', 'max:25'],
            'is_active' => ['required', 'boolean'],
        ]);

        $phone = preg_replace('/[^0-9]/', '', $validated['parent_phone']);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        $validated['parent_phone'] = $phone;

        $student->update($validated);

        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil dihapus.');
    }

    public function card(Student $student)
    {
        $student->load('schoolClass');
        return view('admin.students.card', compact('student'));
    }

    public function enrollFace(Student $student)
    {
        $student->load('schoolClass');
        return view('admin.students.enroll_face', compact('student'));
    }

    public function saveFace(Request $request, Student $student)
    {
        $validated = $request->validate([
            'face_descriptor' => ['required', 'string'],
        ]);

        $student->update([
            'face_descriptor' => $validated['face_descriptor'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data biometrik wajah siswa berhasil disimpan!',
        ]);
    }

    public function resetDevice(Student $student)
    {
        $student->resetDevice();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Kaitan perangkat untuk siswa {$student->name} berhasil di-reset.",
            ]);
        }

        return back()->with('success', "Kaitan perangkat untuk siswa {$student->name} berhasil di-reset.");
    }
}
