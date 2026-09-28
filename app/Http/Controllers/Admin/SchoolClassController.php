<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::with(['teacher'])
            ->withCount('students')
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $teachers = User::where('role', 'guru')->orderBy('name')->get();

        return view('admin.classes.index', compact('classes', 'teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'level' => ['required', 'string', 'max:10'],
            'teacher_id' => ['nullable', 'exists:users,id'],
        ], [
            'name.required' => 'Nama kelas wajib diisi.',
            'level.required' => 'Tingkat kelas wajib diisi.',
            'teacher_id.exists' => 'Wali kelas yang dipilih tidak valid.',
        ]);

        SchoolClass::create($validated);

        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function update(Request $request, SchoolClass $class)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'level' => ['required', 'string', 'max:10'],
            'teacher_id' => ['nullable', 'exists:users,id'],
        ]);

        $class->update($validated);

        return redirect()->route('admin.classes.index')->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $class)
    {
        if ($class->students()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus kelas yang masih memiliki siswa.');
        }

        $class->delete();

        return redirect()->route('admin.classes.index')->with('success', 'Kelas berhasil dihapus.');
    }
}
