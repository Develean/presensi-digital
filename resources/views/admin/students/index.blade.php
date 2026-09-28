@extends('layouts.app')

@section('title', 'Data Siswa & Biometrik')
@section('page_title', 'Master Data Siswa & Biometrik')
@section('page_subtitle', 'Kelola informasi siswa, token barcode unik, dan status biometrik wajah')

@section('content')
<div class="space-y-6">

    <!-- Filters & Action Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <form action="{{ route('admin.students.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <!-- Search Input -->
            <div class="relative flex-1 md:w-64">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIS, Nama, Barcode..."
                       class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/20 outline-none">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <!-- Filter Class -->
            <select name="class_id" class="px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/20 outline-none">
                <option value="">Semua Kelas</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold transition">
                Filter
            </button>
            @if(request()->hasAny(['search', 'class_id']))
                <a href="{{ route('admin.students.index') }}" class="text-xs text-rose-600 hover:underline">Reset</a>
            @endif
        </form>

        <a href="{{ route('admin.students.create') }}"
           class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-md shadow-indigo-600/20 transition flex items-center space-x-1.5 flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Siswa Baru</span>
        </a>
    </div>

    <!-- Students Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-100">
                        <th class="px-5 py-3.5">NIS / Token Barcode</th>
                        <th class="px-5 py-3.5">Nama Siswa</th>
                        <th class="px-5 py-3.5">Kelas</th>
                        <th class="px-5 py-3.5">Kontak WhatsApp Ortu</th>
                        <th class="px-5 py-3.5 text-center">Status Wajah</th>
                        <th class="px-5 py-3.5 text-right">Aksi & Kartu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $s)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-800">{{ $s->nis }}</div>
                                <div class="font-mono text-[11px] text-indigo-600 font-semibold">{{ $s->barcode_token }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-slate-800">{{ $s->name }}</div>
                                <div class="text-xs text-slate-400">{{ $s->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ $s->schoolClass->name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-600">
                                <div class="font-medium text-slate-700">{{ $s->parent_name }}</div>
                                <div class="font-mono text-emerald-600 flex items-center space-x-1 mt-0.5">
                                    <span>WA: {{ $s->parent_phone }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if(!empty($s->face_descriptor))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        Terdaftar
                                    </span>
                                @else
                                    <a href="{{ route('admin.students.enroll-face', $s) }}"
                                       class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 hover:bg-amber-200 transition">
                                        Scan Kamera
                                    </a>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('admin.students.card', $s) }}" target="_blank"
                                   title="Cetak Kartu Barcode Pelajar"
                                   class="px-2.5 py-1.5 rounded-lg border border-indigo-200 hover:bg-indigo-50 text-xs font-semibold text-indigo-700 transition inline-flex items-center space-x-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    <span>Cetak Kartu</span>
                                </a>
                                <a href="{{ route('admin.students.enroll-face', $s) }}"
                                   title="Perekaman Biometrik Wajah"
                                   class="px-2.5 py-1.5 rounded-lg border border-purple-200 hover:bg-purple-50 text-xs font-semibold text-purple-700 transition inline-flex items-center space-x-1">
                                    <span>Wajah</span>
                                </a>
                                <a href="{{ route('admin.students.edit', $s) }}"
                                   class="px-2.5 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-xs font-semibold text-slate-700 transition">
                                    Edit
                                </a>
                                <form action="{{ route('admin.students.destroy', $s) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Hapus data siswa {{ $s->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-rose-200 hover:bg-rose-50 text-xs font-semibold text-rose-600 transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                Tidak ada data siswa yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $students->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
