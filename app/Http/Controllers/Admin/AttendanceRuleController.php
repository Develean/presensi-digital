<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRule;
use Illuminate\Http\Request;

class AttendanceRuleController extends Controller
{
    public function index()
    {
        $rules = AttendanceRule::orderBy('id')->get();
        return view('admin.rules.index', compact('rules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'time_in_start' => ['required', 'date_format:H:i'],
            'time_in_deadline' => ['required', 'date_format:H:i', 'after:time_in_start'],
            'time_in_late_limit' => ['required', 'date_format:H:i', 'after_or_equal:time_in_deadline'],
            'time_out_start' => ['required', 'date_format:H:i', 'after:time_in_late_limit'],
            'time_out_end' => ['required', 'date_format:H:i', 'after:time_out_start'],
            'active_days' => ['required', 'array', 'min:1'],
            'active_days.*' => ['integer', 'between:1,7'],
        ], [
            'name.required' => 'Nama jadwal aturan presensi wajib diisi.',
            'time_in_deadline.after' => 'Batas jam tepat waktu harus setelah jam buka presensi.',
            'time_in_late_limit.after_or_equal' => 'Batas terlambat harus sama atau setelah batas tepat waktu.',
            'time_out_start.after' => 'Jam mulai pulang harus setelah batas jam masuk.',
            'time_out_end.after' => 'Jam selesai pulang harus setelah jam mulai pulang.',
            'active_days.required' => 'Pilih minimal satu hari aktif.',
        ]);

        AttendanceRule::create($validated);

        return redirect()->route('admin.rules.index')->with('success', 'Aturan jam presensi berhasil ditambahkan.');
    }

    public function update(Request $request, AttendanceRule $rule)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'time_in_start' => ['required'],
            'time_in_deadline' => ['required'],
            'time_in_late_limit' => ['required'],
            'time_out_start' => ['required'],
            'time_out_end' => ['required'],
            'active_days' => ['required', 'array', 'min:1'],
            'active_days.*' => ['integer', 'between:1,7'],
            'is_active' => ['required', 'boolean'],
        ]);

        $rule->update($validated);

        return redirect()->route('admin.rules.index')->with('success', 'Aturan jam presensi berhasil diperbarui.');
    }

    public function destroy(AttendanceRule $rule)
    {
        $rule->delete();
        return redirect()->route('admin.rules.index')->with('success', 'Aturan jam presensi berhasil dihapus.');
    }
}
