@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page_title', 'Pengaturan Sistem Presensi')
@section('page_subtitle', 'Konfigurasi aturan jam presensi, identitas sekolah, jaringan Wi-Fi, dan kiosk scanner')

@section('content')
<div x-data="{ activeTab: 'rules', addRuleModal: false }" class="space-y-6">

    <!-- Settings Navigation Tabs -->
    <div class="flex items-center space-x-2 border-b border-slate-200 pb-3">
        <button @click="activeTab = 'rules'"
                :class="activeTab === 'rules' ? 'bg-[#26a69a] text-white shadow-md shadow-teal-600/20' : 'bg-white text-slate-600 hover:bg-slate-100'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Aturan Jam Presensi</span>
        </button>

        <button @click="activeTab = 'school'"
                :class="activeTab === 'school' ? 'bg-[#26a69a] text-white shadow-md shadow-teal-600/20' : 'bg-white text-slate-600 hover:bg-slate-100'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span>Profil Sekolah</span>
        </button>

        <button @click="activeTab = 'network'"
                :class="activeTab === 'network' ? 'bg-[#26a69a] text-white shadow-md shadow-teal-600/20' : 'bg-white text-slate-600 hover:bg-slate-100'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
            <span>Keamanan Jaringan Wi-Fi</span>
        </button>

        <button @click="activeTab = 'kiosk'"
                :class="activeTab === 'kiosk' ? 'bg-[#26a69a] text-white shadow-md shadow-teal-600/20' : 'bg-white text-slate-600 hover:bg-slate-100'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <span>Terminal Kiosk Scanner</span>
        </button>
    </div>

    <!-- TAB 1: ATURAN JAM PRESENSI -->
    <div x-show="activeTab === 'rules'" class="space-y-6">
        <div class="flex items-center justify-between">
            <p class="text-sm text-slate-500 font-medium">Jadwal operasional presensi harian, batas tepat waktu, dan dispensasi terlambat</p>
            <button @click="addRuleModal = true"
                    class="px-4 py-2 rounded-xl bg-[#26a69a] hover:bg-[#20968b] text-white font-semibold text-xs shadow-md shadow-teal-600/20 transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Jadwal / Aturan Baru</span>
            </button>
        </div>

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
                        <div class="p-3 rounded-2xl bg-teal-50/60 border border-teal-100 space-y-1">
                            <span class="text-[10px] font-bold text-teal-700 uppercase tracking-wider block">Presensi Masuk</span>
                            <p class="text-slate-600">Buka: <strong class="text-slate-800 font-mono">{{ substr($r->time_in_start, 0, 5) }}</strong></p>
                            <p class="text-slate-600">Tepat Waktu: <strong class="text-emerald-700 font-mono">{{ substr($r->time_in_deadline, 0, 5) }}</strong></p>
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
                                $dayNames = [
                                    1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
                                    4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
                                ];
                                $activeDays = is_array($r->days) ? $r->days : json_decode($r->days, true) ?? [];
                            @endphp
                            @foreach($dayNames as $num => $name)
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold {{ in_array($num, $activeDays) ? 'bg-teal-100 text-teal-800 border border-teal-200' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-2 py-12 text-center text-slate-400 bg-white rounded-3xl border border-slate-200">
                    Belum ada aturan jadwal presensi. Silakan tambah aturan baru.
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 2: PROFIL SEKOLAH -->
    <div x-show="activeTab === 'school'" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm max-w-2xl space-y-4">
        <h3 class="font-bold text-slate-800 text-base border-b border-slate-100 pb-3">Identitas & Profil Sekolah</h3>
        <div class="space-y-4 text-sm">
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Nama Resmi Sekolah</label>
                <input type="text" readonly value="{{ $schoolName }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-bold outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Alamat Sekolah</label>
                <textarea readonly rows="2" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 outline-none">{{ $schoolAddress }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Tahun Ajaran Aktif</label>
                    <input type="text" readonly value="{{ date('Y') }}/{{ date('Y')+1 }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-semibold outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Zona Waktu Sistem</label>
                    <input type="text" readonly value="Asia/Jakarta (WIB)" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-semibold outline-none">
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 3: KEAMANAN JARINGAN WI-FI -->
    <div x-show="activeTab === 'network'" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm max-w-2xl space-y-4">
        <h3 class="font-bold text-slate-800 text-base border-b border-slate-100 pb-3">Konfigurasi Validasi Subnet Wi-Fi Sekolah</h3>
        <p class="text-xs text-slate-500">Presensi mandiri siswa via smartphone hanya diizinkan apabila terhubung ke Wi-Fi sekolah dengan IP Subnet yang terdaftar di bawah ini (Mencegah titip absen dari rumah):</p>
        <div class="space-y-2">
            @foreach($schoolSubnets as $subnet)
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="font-mono text-sm font-bold text-slate-800">{{ $subnet }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif & Terverifikasi</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- TAB 4: TERMINAL KIOSK -->
    <div x-show="activeTab === 'kiosk'" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm max-w-2xl space-y-4">
        <h3 class="font-bold text-slate-800 text-base border-b border-slate-100 pb-3">Terminal Kiosk Layar Penuh Gerbang</h3>
        <p class="text-xs text-slate-500">Terminal Kiosk dipasang di gerbang masuk sekolah pada tablet / PC monitor touchscreen untuk scan Barcode / QR Code dan Face Recognition otomatis.</p>
        
        <div class="p-4 rounded-2xl bg-teal-50 border border-teal-200 flex items-center justify-between">
            <div>
                <h4 class="font-bold text-teal-900 text-sm">Buka Terminal Kiosk Presensi</h4>
                <p class="text-xs text-teal-700">Tampilan Fullscreen tanpa header dan navigasi admin</p>
            </div>
            <a href="{{ route('kiosk.index') }}" target="_blank"
               class="px-4 py-2.5 rounded-xl bg-[#26a69a] hover:bg-[#20968b] text-white font-bold text-xs shadow-md shadow-teal-600/30 flex items-center space-x-1.5 transition">
                <span>Buka Kiosk Scanner</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </div>

    <!-- Modal Tambah Aturan Jam Presensi -->
    <div x-show="addRuleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="background-color: rgba(15, 23, 42, 0.5);">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div @click.away="addRuleModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-800 text-base">Tambah Aturan Jam Presensi Baru</h3>
                    <button @click="addRuleModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>
                <form action="{{ route('admin.rules.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Aturan</label>
                        <input type="text" name="name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none" placeholder="Contoh: Jadwal Reguler Senin - Jumat">
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Mulai Masuk</label>
                            <input type="time" name="time_in_start" required value="06:00" class="w-full px-2 py-2 rounded-xl border border-slate-200 text-xs font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Batas Tepat Waktu</label>
                            <input type="time" name="time_in_deadline" required value="07:00" class="w-full px-2 py-2 rounded-xl border border-slate-200 text-xs font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Batas Terlambat</label>
                            <input type="time" name="time_in_late_limit" required value="08:00" class="w-full px-2 py-2 rounded-xl border border-slate-200 text-xs font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Buka Scan Pulang</label>
                            <input type="time" name="time_out_start" required value="15:00" class="w-full px-2 py-2 rounded-xl border border-slate-200 text-xs font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Tutup Scan Pulang</label>
                            <input type="time" name="time_out_end" required value="17:00" class="w-full px-2 py-2 rounded-xl border border-slate-200 text-xs font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-2">Hari Aktif</label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'] as $num => $day)
                                <label class="flex items-center space-x-2 text-xs font-medium text-slate-700 p-2 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                                    <input type="checkbox" name="days[]" value="{{ $num }}" {{ $num <= 5 ? 'checked' : '' }} class="rounded text-[#26a69a] focus:ring-teal-500">
                                    <span>{{ $day }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-2">
                        <button type="button" @click="addRuleModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 text-sm font-medium">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-[#26a69a] hover:bg-[#20968b] text-white text-sm font-semibold shadow-md shadow-teal-600/20">Simpan Aturan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
