@extends('layouts.app')

@section('title', 'Generate Laporan')
@section('page_title', 'Generate Laporan Presensi Siswa')
@section('page_subtitle', 'Rekapitulasi kehadiran, filter rentang tanggal, dan cetak laporan resmi')

@push('styles')
<style>
    @media print {
        aside, header, .no-print { display: none !important; }
        body, main { background: white !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }
        .print-only { display: block !important; }
        .page-break { page-break-after: always; }
        table { font-size: 11px !important; }
        th, td { padding: 4px 6px !important; }
    }
    .print-only { display: none; }
</style>
@endpush

@section('content')
<div class="space-y-6">

    <!-- Official Letterhead for Print (Hidden on Screen) -->
    <div class="print-only border-b-2 border-slate-900 pb-3 mb-6 text-center">
        <h2 class="text-sm font-bold tracking-wider uppercase text-slate-800">Pemerintah Provinsi DKI Jakarta &bull; Dinas Pendidikan</h2>
        <h1 class="text-xl font-black uppercase text-slate-900 tracking-tight">SMK NEGERI 77 JAKARTA</h1>
        <p class="text-xs text-slate-600">Jl. Cempaka Putih Tengah XVII No. 77, Jakarta Pusat &bull; Telp: (021) 4247777</p>
        <div class="mt-3 pt-2 border-t border-slate-300">
            <h3 class="text-sm font-bold uppercase underline">LAPORAN REKAPITULASI PRESENSI SISWA</h3>
            <p class="text-xs text-slate-600">
                Periode: {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}
                @if($selectedClass) &bull; Kelas: {{ $selectedClass->name }} @endif
            </p>
        </div>
    </div>

    <!-- Filter Card & Action Bar (no-print) -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm no-print">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Kelas & Jurusan</label>
                <select name="class_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Status Kehadiran</label>
                <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none">
                    <option value="">Semua Status</option>
                    <option value="hadir" {{ $status == 'hadir' ? 'selected' : '' }}>Hadir (Tepat Waktu)</option>
                    <option value="terlambat" {{ $status == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                    <option value="izin" {{ $status == 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="sakit" {{ $status == 'sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="alpa" {{ $status == 'alpa' ? 'selected' : '' }}>Alpa</option>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold transition">
                    Terapkan Filter
                </button>
                <button type="button" onclick="window.print()" class="px-4 py-2 rounded-xl bg-[#26a69a] hover:bg-[#20968b] text-white text-xs font-bold shadow-md shadow-teal-600/20 flex items-center space-x-1.5 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Log</span>
            <p class="text-2xl font-black text-slate-800 mt-1">{{ number_format($totalLogs) }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-emerald-100 bg-emerald-50/30 shadow-sm">
            <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider block">Tepat Waktu</span>
            <p class="text-2xl font-black text-emerald-700 mt-1">{{ number_format($totalHadir) }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-amber-100 bg-amber-50/30 shadow-sm">
            <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider block">Terlambat</span>
            <p class="text-2xl font-black text-amber-700 mt-1">{{ number_format($totalTerlambat) }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-blue-100 bg-blue-50/30 shadow-sm">
            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider block">Izin</span>
            <p class="text-2xl font-black text-blue-700 mt-1">{{ number_format($totalIzin) }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-indigo-100 bg-indigo-50/30 shadow-sm">
            <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider block">Sakit</span>
            <p class="text-2xl font-black text-indigo-700 mt-1">{{ number_format($totalSakit) }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-rose-100 bg-rose-50/30 shadow-sm">
            <span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider block">Alpa</span>
            <p class="text-2xl font-black text-rose-700 mt-1">{{ number_format($totalAlpa) }}</p>
        </div>
    </div>

    <!-- Rekapitulasi per Siswa Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-sm">Rekapitulasi Kehadiran Siswa</h3>
            <span class="text-xs text-slate-500 font-medium">Tingkat Kehadiran Rata-rata: <strong class="text-teal-700 font-bold">{{ $attendanceRate }}%</strong></span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                        <th class="px-4 py-3 text-center w-12">No</th>
                        <th class="px-4 py-3">NIS</th>
                        <th class="px-4 py-3">Nama Siswa</th>
                        <th class="px-4 py-3">Kelas</th>
                        <th class="px-3 py-3 text-center text-emerald-700 font-bold">H</th>
                        <th class="px-3 py-3 text-center text-amber-700 font-bold">T</th>
                        <th class="px-3 py-3 text-center text-blue-700 font-bold">I</th>
                        <th class="px-3 py-3 text-center text-indigo-700 font-bold">S</th>
                        <th class="px-3 py-3 text-center text-rose-700 font-bold">A</th>
                        <th class="px-4 py-3 text-center">Persentase</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($studentRecaps as $index => $row)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-2.5 text-center text-slate-400 font-medium">{{ $index + 1 }}</td>
                            <td class="px-4 py-2.5 font-mono text-slate-600">{{ $row['student']->nis }}</td>
                            <td class="px-4 py-2.5 font-semibold text-slate-800">{{ $row['student']->name }}</td>
                            <td class="px-4 py-2.5 text-slate-600">{{ $row['student']->schoolClass?->name ?? '-' }}</td>
                            <td class="px-3 py-2.5 text-center font-bold text-emerald-600 bg-emerald-50/30">{{ $row['hadir'] }}</td>
                            <td class="px-3 py-2.5 text-center font-bold text-amber-600 bg-amber-50/30">{{ $row['terlambat'] }}</td>
                            <td class="px-3 py-2.5 text-center font-bold text-blue-600 bg-blue-50/30">{{ $row['izin'] }}</td>
                            <td class="px-3 py-2.5 text-center font-bold text-indigo-600 bg-indigo-50/30">{{ $row['sakit'] }}</td>
                            <td class="px-3 py-2.5 text-center font-bold text-rose-600 bg-rose-50/30">{{ $row['alpa'] }}</td>
                            <td class="px-4 py-2.5 text-center font-bold">
                                <span class="px-2 py-0.5 rounded text-[11px] {{ $row['persentase'] >= 85 ? 'bg-emerald-100 text-emerald-800' : ($row['persentase'] >= 75 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ $row['persentase'] }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-slate-400">
                                Tidak ada data rekap presensi untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Official Signature Block for Print -->
    <div class="print-only mt-12 pt-6">
        <div class="flex justify-between text-xs text-slate-800">
            <div class="text-center w-64">
                <p>Mengetahui,</p>
                <p class="font-bold mb-16">Waka Kesiswaan</p>
                <p class="font-bold underline">_________________________</p>
                <p class="text-slate-500">NIP. .........................</p>
            </div>
            <div class="text-center w-64">
                <p>Jakarta, {{ \Carbon\Carbon::now('Asia/Jakarta')->isoFormat('D MMMM Y') }}</p>
                <p class="font-bold mb-16">Kepala Sekolah SMKN 77 Jakarta</p>
                <p class="font-bold underline">_________________________</p>
                <p class="text-slate-500">NIP. .........................</p>
            </div>
        </div>
    </div>

</div>
@endsection
