<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'guru')->with('classes');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $teachers = $query->orderBy('name')->paginate(15)->withQueryString();
        $availableClasses = SchoolClass::orderBy('name')->get();

        return view('admin.teachers.index', compact('teachers', 'availableClasses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ], [
            'name.required' => 'Nama guru wajib diisi.',
            'email.required' => 'Email guru wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password awal wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        $teacher = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'guru',
        ]);

        if (!empty($validated['class_id'])) {
            SchoolClass::where('id', $validated['class_id'])->update(['teacher_id' => $teacher->id]);
        }

        return redirect()->route('admin.teachers.index')->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function update(Request $request, User $teacher)
    {
        if ($teacher->role !== 'guru') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($teacher->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $teacher->update($updateData);

        // Update wali kelas assignment
        if (isset($validated['class_id'])) {
            // Unassign current classes for this teacher
            SchoolClass::where('teacher_id', $teacher->id)->update(['teacher_id' => null]);

            if (!empty($validated['class_id'])) {
                SchoolClass::where('id', $validated['class_id'])->update(['teacher_id' => $teacher->id]);
            }
        }

        return redirect()->route('admin.teachers.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(User $teacher)
    {
        if ($teacher->role !== 'guru') {
            abort(404);
        }

        // Unlink classes first
        SchoolClass::where('teacher_id', $teacher->id)->update(['teacher_id' => null]);
        $teacher->delete();

        return redirect()->route('admin.teachers.index')->with('success', 'Data guru berhasil dihapus.');
    }
}
