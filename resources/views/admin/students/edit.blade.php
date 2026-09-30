@extends('layouts.app')

@section('title', 'Edit Siswa')
@section('page_title', 'Perbarui Data Siswa')
@section('page_subtitle', 'Edit informasi siswa, status aktif, dan nomor kontak orang tua')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-sm space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-slate-800 text-lg">Edit: {{ $student->name }}</h3>
                <p class="font-mono text-xs text-indigo-600 font-semibold">Token Barcode: {{ $student->barcode_token }}</p>
            </div>
            <a href="{{ route('admin.students.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">&larr; Kembali</a>
        </div>

        <form action="{{ route('admin.students.update', $student) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nomor Induk Siswa (NIS)</label>
                    <input type="text" name="nis" value="{{ old('nis', $student->nis) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">NISN</label>
                    <input type="text" name="nisn" value="{{ old('nisn', $student->nisn) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Lengkap Siswa</label>
                <input type="text" name="name" value="{{ old('name', $student->name) }}" required
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Jenis Kelamin</label>
                    <select name="gender" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                        <option value="L" {{ old('gender', $student->gender) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('gender', $student->gender) == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Kelas</label>
                    <select name="class_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ old('class_id', $student->class_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Status Keaktifan</label>
                    <select name="is_active" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                        <option value="1" {{ old('is_active', $student->is_active) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ !old('is_active', $student->is_active) ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 space-y-4">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kontak Orang Tua / Wali</h4>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Orang Tua / Wali</label>
                    <input type="text" name="parent_name" value="{{ old('parent_name', $student->parent_name) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nomor WhatsApp Orang Tua</label>
                    <input type="text" name="parent_phone" value="{{ old('parent_phone', $student->parent_phone) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-5 border-t border-slate-100">
                <a href="{{ route('admin.students.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-lg shadow-indigo-600/30 transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <!-- Panel Kunci Perangkat (Device Lock) -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm mt-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-sky-50 border border-sky-100 flex items-center justify-center text-sky-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">Kunci Perangkat Presensi Mandiri (Device Lock)</h4>
                    <p class="text-xs text-slate-500">Mencegah siswa titip absen dari HP siswa lain.</p>
                </div>
            </div>

            @if($student->isDeviceLocked())
                <form action="{{ route('admin.students.reset-device', $student) }}" method="POST"
                      onsubmit="return confirm('Lepas kaitan HP untuk siswa {{ $student->name }}?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold border border-rose-200 transition">
                        Reset Kaitan HP
                    </button>
                </form>
            @endif
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div class="bg-slate-50 p-3 rounded-xl">
                <span class="text-slate-400 block mb-0.5">Status HP:</span>
                @if($student->isDeviceLocked())
                    <span class="font-bold text-sky-700">Terkunci (Terdaftar)</span>
                @else
                    <span class="font-semibold text-slate-600">Belum Ada HP Terikat</span>
                @endif
            </div>
            <div class="bg-slate-50 p-3 rounded-xl">
                <span class="text-slate-400 block mb-0.5">Nama Perangkat:</span>
                <span class="font-mono text-slate-700">{{ $student->device_name ?: '-' }}</span>
            </div>
            <div class="bg-slate-50 p-3 rounded-xl">
                <span class="text-slate-400 block mb-0.5">Waktu Kunci:</span>
                <span class="text-slate-700">{{ $student->device_locked_at ? $student->device_locked_at->format('d M Y H:i') : '-' }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
