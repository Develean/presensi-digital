@extends('layouts.app')

@section('title', 'Dashboard Guru & Wali Kelas')
@section('page_title', 'Portal Informasi Guru')
@section('page_subtitle', 'Pemantauan kehadiran siswa perwalian dan pengelolaan absensi kelas')

@section('content')
<div class="space-y-6">

    @if($myClass)
        <!-- Homeroom Class Header Banner -->
        <div class="bg-gradient-to-r from-emerald-800 to-teal-900 rounded-3xl p-6 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="space-y-1">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/30 text-emerald-200 border border-emerald-400/20">
                    Kelas Perwalian Aktif
                </span>
                <h3 class="text-2xl font-black tracking-tight">{{ $myClass->name }}</h3>
                <p class="text-xs text-emerald-100">
                    Wali Kelas: <strong class="text-white">{{ $teacher->name }}</strong> &bull; Total Siswa Terdaftar: <strong>{{ $stats['total_students'] }} Siswa</strong>
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="#guru-presensi" onclick="alert('Fitur Pencatatan Kehadiran & Rekap Guru dibuka lengkap pada Fase 4')" class="px-5 py-3 rounded-2xl bg-white hover:bg-emerald-50 text-emerald-950 font-bold text-xs shadow-lg transition transform active:scale-95 flex items-center space-x-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span>Kelola Presensi Kelas</span>
                </a>
            </div>
        </div>

        <!-- Attendance Stats Today in this Class -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="font-bold text-slate-800 text-sm">Status Kehadiran Siswa Kelas {{ $myClass->name }} Hari Ini</h4>
                <span class="text-xs text-slate-400">{{ now()->translatedFormat('d F Y') }}</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-center">
                    <span class="text-[11px] font-bold text-emerald-700 uppercase">Hadir Tepat</span>
                    <p class="text-2xl font-black text-emerald-800 mt-1">{{ $stats['hadir_today'] }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-100 text-center">
                    <span class="text-[11px] font-bold text-amber-700 uppercase">Terlambat</span>
                    <p class="text-2xl font-black text-amber-800 mt-1">{{ $stats['terlambat_today'] }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-sky-50 border border-sky-100 text-center">
                    <span class="text-[11px] font-bold text-sky-700 uppercase">Izin</span>
                    <p class="text-2xl font-black text-sky-800 mt-1">{{ $stats['izin_today'] }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-100 text-center">
                    <span class="text-[11px] font-bold text-indigo-700 uppercase">Sakit</span>
                    <p class="text-2xl font-black text-indigo-800 mt-1">{{ $stats['sakit_today'] }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-100 text-center">
                    <span class="text-[11px] font-bold text-rose-700 uppercase">Alpa / Belum</span>
                    <p class="text-2xl font-black text-rose-800 mt-1">{{ $stats['alpa_today'] }}</p>
                </div>
            </div>
        </div>

    @else
        <!-- No class assigned -->
        <div class="bg-white rounded-3xl p-10 border border-slate-200/80 shadow-sm text-center space-y-3">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="font-bold text-slate-800 text-base">Belum Ditugaskan Sebagai Wali Kelas</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto">
                Akun Anda belum ditetapkan sebagai wali kelas untuk rombel tertentu oleh Administrator. Hubungi admin sekolah untuk penugasan kelas.
            </p>
        </div>
    @endif

</div>
@endsection
