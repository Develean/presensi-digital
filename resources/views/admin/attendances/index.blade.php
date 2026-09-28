@extends('layouts.app')

@section('title', 'Riwayat & Log Presensi')
@section('page_title', 'Riwayat & Log Presensi Siswa')
@section('page_subtitle', 'Pantau dan saring rekam jejak presensi berdasarkan tanggal, kelas, siswa, status, waktu, dan metode')

@section('content')
<div class="space-y-6">

    <!-- Multi-Dimensional Filter Card -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-bold text-slate-800 text-sm flex items-center space-x-2">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                <span>Filter Pencarian Presensi</span>
            </h3>
            @if(request()->hasAny(['date', 'student_search', 'class_id', 'status', 'time_filter', 'method']))
                <a href="{{ route('admin.attendances.index') }}" class="text-xs font-semibold text-rose-600 hover:underline">
                    Reset Semua Filter
                </a>
            @endif
        </div>

        <form action="{{ route('admin.attendances.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
            <!-- 1. Tanggal -->
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ request('date', $date) }}"
                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/20 outline-none">
            </div>

            <!-- 2. Siswa (Nama / NIS) -->
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Cari Siswa</label>
                <input type="text" name="student_search" value="{{ request('student_search') }}" placeholder="NIS atau Nama..."
                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/20 outline-none">
            </div>

            <!-- 3. Kelas -->
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Kelas</label>
                <select name="class_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/20 outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 4. Status -->
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Status Kehadiran</label>
                <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/20 outline-none">
                    <option value="">Semua Status</option>
                    <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Hadir Tepat</option>
                    <option value="terlambat" {{ request('status') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                    <option value="izin" {{ request('status') == 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="sakit" {{ request('status') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="alpa" {{ request('status') == 'alpa' ? 'selected' : '' }}>Alpa</option>
                </select>
            </div>

            <!-- 5. Waktu (Masuk / Pulang) -->
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Waktu Presensi</label>
                <select name="time_filter" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/20 outline-none">
                    <option value="">Semua Waktu</option>
                    <option value="has_in" {{ request('time_filter') == 'has_in' ? 'selected' : '' }}>Sudah Masuk</option>
                    <option value="has_out" {{ request('time_filter') == 'has_out' ? 'selected' : '' }}>Sudah Pulang</option>
                    <option value="no_out" {{ request('time_filter') == 'no_out' ? 'selected' : '' }}>Masuk (Belum Pulang)</option>
                </select>
            </div>

            <!-- 6. Metode Scan -->
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Metode Scan</label>
                <select name="method" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/20 outline-none">
                    <option value="">Semua Metode</option>
                    <option value="face" {{ request('method') == 'face' ? 'selected' : '' }}>Face Recognition</option>
                    <option value="barcode" {{ request('method') == 'barcode' ? 'selected' : '' }}>Barcode Kartu</option>
                    <option value="manual" {{ request('method') == 'manual' ? 'selected' : '' }}>Manual Guru</option>
                </select>
            </div>

            <div class="sm:col-span-2 md:col-span-3 lg:col-span-6 flex justify-end pt-1">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-md shadow-indigo-600/20 transition flex items-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Terapkan Filter</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-100">
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Siswa</th>
                        <th class="px-5 py-3.5">Kelas</th>
                        <th class="px-5 py-3.5">Jam Masuk</th>
                        <th class="px-5 py-3.5">Jam Pulang</th>
                        <th class="px-5 py-3.5 text-center">Metode</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $row)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-3.5 font-mono text-xs text-slate-600">
                                {{ \Carbon\Carbon::parse($row->attendance_date)->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-800">{{ $row->student->name }}</div>
                                <div class="font-mono text-[11px] text-slate-400">NIS: {{ $row->student->nis }}</div>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-600">
                                {{ $row->student->schoolClass->name ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-slate-700">
                                {{ $row->time_in ? substr($row->time_in, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-slate-700">
                                {{ $row->time_out ? substr($row->time_out, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <div class="space-y-0.5">
                                    @if($row->method_in)
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $row->method_in == 'face' ? 'bg-purple-100 text-purple-700' : ($row->method_in == 'barcode' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700') }}">
                                            In: {{ strtoupper($row->method_in) }}
                                        </span>
                                    @endif
                                    @if($row->method_out)
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $row->method_out == 'face' ? 'bg-purple-100 text-purple-700' : ($row->method_out == 'barcode' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700') }}">
                                            Out: {{ strtoupper($row->method_out) }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @php
                                    $statusColors = [
                                        'hadir' => 'bg-emerald-100 text-emerald-800',
                                        'terlambat' => 'bg-amber-100 text-amber-800',
                                        'izin' => 'bg-sky-100 text-sky-800',
                                        'sakit' => 'bg-indigo-100 text-indigo-800',
                                        'alpa' => 'bg-rose-100 text-rose-800',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $statusColors[$row->status_in] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ ucfirst($row->status_in) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-500">
                                {{ $row->notes ?? '-' }}
                                @if($row->updater)
                                    <span class="block text-[10px] text-slate-400">Diedit oleh: {{ $row->updater->name }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                Tidak ada catatan presensi yang sesuai dengan kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($attendances->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
