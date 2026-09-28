@extends('layouts.app')

@section('title', 'Aturan Jam Presensi')
@section('page_title', 'Master Aturan Jam Presensi')
@section('page_subtitle', 'Tentukan jam buka presensi, batas toleransi keterlambatan, jam pulang, dan hari aktif')

@section('content')
<div x-data="{ addModal: false }" class="space-y-6">

    <!-- Header Action -->
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500 font-medium">Jadwal presensi digunakan oleh Terminal Kiosk & Sistem Pengiriman WA Otomatis</p>
        <button @click="addModal = true"
                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-md shadow-indigo-600/20 transition flex items-center space-x-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Jadwal / Aturan Baru</span>
        </button>
    </div>

    <!-- Rules Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @forelse($rules as $r)
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h4 class="font-bold text-slate-800 text-base">{{ $r->name }}</h4>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $r->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $r->is_active ? 'Status: Aktif' : 'Status: Non-Aktif' }}
                        </span>
                    </div>
                    <form action="{{ route('admin.rules.destroy', $r) }}" method="POST"
                          onsubmit="return confirm('Hapus aturan jam ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 rounded-lg text-rose-500 hover:bg-rose-50 transition" title="Hapus Aturan">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>

                <!-- Timing Breakdown -->
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-2xl bg-indigo-50/60 border border-indigo-100 space-y-1">
                        <span class="text-[10px] font-bold text-indigo-700 uppercase tracking-wider block">Presensi Masuk</span>
                        <p class="text-slate-600">Buka: <strong class="text-slate-800 font-mono">{{ substr($r->time_in_start, 0, 5) }}</strong></p>
                        <p class="text-slate-600">Tepat Waktu s/d: <strong class="text-emerald-700 font-mono">{{ substr($r->time_in_deadline, 0, 5) }}</strong></p>
                        <p class="text-slate-600">Batas Terlambat: <strong class="text-rose-700 font-mono">{{ substr($r->time_in_late_limit, 0, 5) }}</strong></p>
                    </div>

                    <div class="p-3 rounded-2xl bg-emerald-50/60 border border-emerald-100 space-y-1">
                        <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">Presensi Pulang</span>
                        <p class="text-slate-600">Mulai Pulang: <strong class="text-slate-800 font-mono">{{ substr($r->time_out_start, 0, 5) }}</strong></p>
                        <p class="text-slate-600">Tutup Pulang: <strong class="text-slate-800 font-mono">{{ substr($r->time_out_end, 0, 5) }}</strong></p>
                        <p class="text-slate-400 text-[10px]">Di luar jam ini scan pulang ditolak</p>
                    </div>
                </div>

                <!-- Active Days Badges -->
                <div>
                    <span class="text-[11px] font-semibold text-slate-500 block mb-1.5">Hari Berlaku:</span>
                    <div class="flex flex-wrap gap-1">
                        @php
                            $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                        @endphp
                        @foreach($dayNames as $dayNum => $dayName)
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ in_array($dayNum, $r->active_days ?? []) ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-400' }}">
                                {{ $dayName }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-2 bg-white rounded-3xl p-10 border border-slate-200/80 text-center text-slate-400">
                Belum ada aturan jadwal jam presensi. Klik "Tambah Jadwal / Aturan Baru".
            </div>
        @endforelse
    </div>

    <!-- Modal Tambah Aturan -->
    <div x-cloak x-show="addModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="addModal = false" class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-lg">Tambah Aturan Jam Presensi</h3>
                <button @click="addModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>
            <form action="{{ route('admin.rules.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 uppercase mb-1">Nama Aturan / Jadwal</label>
                    <input type="text" name="name" required placeholder="Contoh: Reguler (Senin - Kamis)"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase mb-1">Buka Masuk</label>
                        <input type="time" name="time_in_start" value="06:00" required class="w-full px-2.5 py-2 rounded-xl border border-slate-200 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase mb-1">Batas Tepat</label>
                        <input type="time" name="time_in_deadline" value="07:15" required class="w-full px-2.5 py-2 rounded-xl border border-slate-200 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase mb-1">Batas Terlambat</label>
                        <input type="time" name="time_in_late_limit" value="07:30" required class="w-full px-2.5 py-2 rounded-xl border border-slate-200 text-sm outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase mb-1">Mulai Pulang</label>
                        <input type="time" name="time_out_start" value="15:00" required class="w-full px-2.5 py-2 rounded-xl border border-slate-200 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 uppercase mb-1">Selesai Pulang</label>
                        <input type="time" name="time_out_end" value="17:00" required class="w-full px-2.5 py-2 rounded-xl border border-slate-200 text-sm outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 uppercase mb-2">Pilih Hari Aktif Berlaku</label>
                    <div class="grid grid-cols-4 gap-2">
                        <label class="flex items-center space-x-1.5 p-2 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-50">
                            <input type="checkbox" name="active_days[]" value="1" checked class="rounded text-indigo-600">
                            <span>Senin</span>
                        </label>
                        <label class="flex items-center space-x-1.5 p-2 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-50">
                            <input type="checkbox" name="active_days[]" value="2" checked class="rounded text-indigo-600">
                            <span>Selasa</span>
                        </label>
                        <label class="flex items-center space-x-1.5 p-2 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-50">
                            <input type="checkbox" name="active_days[]" value="3" checked class="rounded text-indigo-600">
                            <span>Rabu</span>
                        </label>
                        <label class="flex items-center space-x-1.5 p-2 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-50">
                            <input type="checkbox" name="active_days[]" value="4" checked class="rounded text-indigo-600">
                            <span>Kamis</span>
                        </label>
                        <label class="flex items-center space-x-1.5 p-2 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-50">
                            <input type="checkbox" name="active_days[]" value="5" class="rounded text-indigo-600">
                            <span>Jumat</span>
                        </label>
                        <label class="flex items-center space-x-1.5 p-2 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-50">
                            <input type="checkbox" name="active_days[]" value="6" class="rounded text-indigo-600">
                            <span>Sabtu</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="addModal = false" class="px-4 py-2 rounded-xl border border-slate-200 font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700">Simpan Aturan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
