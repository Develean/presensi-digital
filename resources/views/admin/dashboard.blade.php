@extends('layouts.app')

@section('title', 'Dashboard Administrator')
@section('page_title', 'Dashboard Administrator')
@section('page_subtitle', 'Ringkasan statistik kehadiran siswa dan operasional sekolah hari ini')

@section('content')
<div class="space-y-6">

    <!-- Top Action Banner / Shortcut to Kiosk (Midnight Navy & Emerald Green with Shimmer Animation) -->
    <div class="relative overflow-hidden bg-gradient-to-r from-slate-900 via-[#0c1a2e] to-[#062c20] rounded-3xl p-6 text-white shadow-xl border border-emerald-500/20 flex flex-col md:flex-row items-center justify-between gap-4 card-interactive">
        <!-- Subtle Shimmer Sweep Overlay -->
        <div class="absolute inset-0 shimmer-card pointer-events-none opacity-40"></div>

        <div class="space-y-1.5 text-center md:text-left relative z-10">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping mr-1.5"></span>
                Mode Terminal Gerbang Sekolah
            </span>
            <h3 class="text-xl sm:text-2xl font-black tracking-tight text-white">Kiosk Presensi Mandiri Siswa</h3>
            <p class="text-xs sm:text-sm text-slate-300 max-w-xl leading-relaxed">
                Buka terminal layar penuh di gerbang sekolah untuk proses scan wajah biometrik (*face recognition*) dan scan QR Code kartu siswa tanpa jeda.
            </p>
        </div>
        <a href="{{ route('kiosk.index') }}" target="_blank" class="relative z-10 px-6 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 font-extrabold text-slate-950 text-sm shadow-lg shadow-emerald-500/25 transition transform active:scale-95 flex items-center space-x-2 flex-shrink-0 btn-bounce">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            <span>Buka Kiosk Scanner</span>
        </a>
    </div>

    <!-- Master Stat Cards with Micro-Interactions -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center space-x-4 card-interactive group">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3 shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Siswa Aktif</p>
                <h4 class="text-2xl font-black text-slate-800 tracking-tight">{{ $stats['total_students'] }}</h4>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center space-x-4 card-interactive group">
            <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center font-bold transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3 shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Guru & Wali Kelas</p>
                <h4 class="text-2xl font-black text-slate-800 tracking-tight">{{ $stats['total_teachers'] }}</h4>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center space-x-4 card-interactive group">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3 shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Rombel / Kelas</p>
                <h4 class="text-2xl font-black text-slate-800 tracking-tight">{{ $stats['total_classes'] }}</h4>
            </div>
        </div>
    </div>

    <!-- Attendance Today Status Grid -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4 card-interactive">
        <div class="flex items-center justify-between">
            <h3 class="font-extrabold text-slate-800 text-base">Status Kehadiran Hari Ini</h3>
            <span class="text-xs text-slate-500 font-medium">Berdasarkan data scan & koreksi guru</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-100 text-center transition-transform duration-200 hover:-translate-y-1 hover:shadow-sm">
                <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Hadir Tepat</span>
                <p class="text-2xl font-black text-emerald-800 mt-1">{{ $stats['attendance_today']['hadir'] }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-100 text-center transition-transform duration-200 hover:-translate-y-1 hover:shadow-sm">
                <span class="text-xs font-bold text-amber-700 uppercase tracking-wider">Terlambat</span>
                <p class="text-2xl font-black text-amber-800 mt-1">{{ $stats['attendance_today']['terlambat'] }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-sky-50/70 border border-sky-100 text-center transition-transform duration-200 hover:-translate-y-1 hover:shadow-sm">
                <span class="text-xs font-bold text-sky-700 uppercase tracking-wider">Izin</span>
                <p class="text-2xl font-black text-sky-800 mt-1">{{ $stats['attendance_today']['izin'] }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-100 text-center transition-transform duration-200 hover:-translate-y-1 hover:shadow-sm">
                <span class="text-xs font-bold text-indigo-700 uppercase tracking-wider">Sakit</span>
                <p class="text-2xl font-black text-indigo-800 mt-1">{{ $stats['attendance_today']['sakit'] }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-rose-50/70 border border-rose-100 text-center transition-transform duration-200 hover:-translate-y-1 hover:shadow-sm">
                <span class="text-xs font-bold text-rose-700 uppercase tracking-wider">Alpa / Belum</span>
                <p class="text-2xl font-black text-rose-800 mt-1">{{ $stats['attendance_today']['alpa'] }}</p>
            </div>
        </div>
    </div>

    <!-- Recent Attendance Log with Interactive Rows -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden card-interactive">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-extrabold text-slate-800 text-base">Riwayat Scan Presensi Terbaru</h3>
            <span class="text-xs font-bold px-3 py-1 bg-slate-100 text-slate-600 rounded-xl">10 Data Terakhir</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 text-xs uppercase font-bold border-b border-slate-100">
                        <th class="px-5 py-3.5">Siswa</th>
                        <th class="px-5 py-3.5">Kelas</th>
                        <th class="px-5 py-3.5">Waktu Masuk</th>
                        <th class="px-5 py-3.5">Metode</th>
                        <th class="px-5 py-3.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentAttendances as $item)
                        <tr class="hover:bg-emerald-50/40 transition-colors duration-150">
                            <td class="px-5 py-3.5 font-bold text-slate-800">{{ $item->student->name }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $item->student->schoolClass->name ?? '-' }}</td>
                            <td class="px-5 py-3.5 font-mono text-xs font-semibold text-slate-700">{{ $item->time_in ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-xs font-bold {{ $item->method_in == 'face' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ strtoupper($item->method_in ?? 'MANUAL') }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $item->status_in == 'hadir' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ ucfirst($item->status_in) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400 text-sm">
                                Belum ada catatan presensi hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
