@extends('layouts.app')

@section('title', 'Tambah Siswa Baru')
@section('page_title', 'Pendaftaran Siswa Baru')
@section('page_subtitle', 'Masukkan data identitas siswa, kelas, dan kontak WhatsApp orang tua')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-sm space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-800 text-lg">Formulir Siswa</h3>
            <a href="{{ route('admin.students.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">&larr; Kembali ke Daftar</a>
        </div>

        <form action="{{ route('admin.students.store') }}" method="POST" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nomor Induk Siswa (NIS) *</label>
                    <input type="text" name="nis" value="{{ old('nis') }}" required placeholder="Contoh: 2026001"
                           class="w-full px-3.5 py-2.5 rounded-xl border @error('nis') border-rose-500 bg-rose-50/50 @else border-slate-200 @enderror text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                    @error('nis') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">NISN (Opsional)</label>
                    <input type="text" name="nisn" value="{{ old('nisn') }}" placeholder="Contoh: 0081234567"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Lengkap Siswa *</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Muhammad Fauzan"
                       class="w-full px-3.5 py-2.5 rounded-xl border @error('name') border-rose-500 @else border-slate-200 @enderror text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                @error('name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Jenis Kelamin *</label>
                    <select name="gender" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                        <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Kelas / Rombel *</label>
                    <select name="class_id" required class="w-full px-3.5 py-2.5 rounded-xl border @error('class_id') border-rose-500 @else border-slate-200 @enderror text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('class_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 space-y-4">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kontak Orang Tua / Wali (Tujuan Notifikasi WA)</h4>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Orang Tua / Wali *</label>
                    <input type="text" name="parent_name" value="{{ old('parent_name') }}" required placeholder="Contoh: Ahmad Subagyo"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nomor WhatsApp Orang Tua *</label>
                    <input type="text" name="parent_phone" value="{{ old('parent_phone') }}" required placeholder="Contoh: 081234567890 atau 6281234567890"
                           class="w-full px-3.5 py-2.5 rounded-xl border @error('parent_phone') border-rose-500 @else border-slate-200 @enderror text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                    <p class="text-[11px] text-slate-400 mt-1">Sistem otomatis mengubah format 08... menjadi format internasional 628...</p>
                    @error('parent_phone') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-5 border-t border-slate-100">
                <a href="{{ route('admin.students.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-lg shadow-indigo-600/30 transition">
                    Simpan Data Siswa
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
