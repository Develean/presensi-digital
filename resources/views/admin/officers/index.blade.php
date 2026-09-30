@extends('layouts.app')

@section('title', 'Data Petugas')
@section('page_title', 'Data Petugas & Administrator')
@section('page_subtitle', 'Kelola akun administrator, petugas piket gerbang, dan operator sistem presensi')

@section('content')
<div x-data="{ 
    addModal: false, 
    editModal: false, 
    editData: { id: null, name: '', email: '' } 
}" class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.officers.index') }}" class="flex items-center space-x-2 w-full sm:w-80">
            <div class="relative w-full">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama atau email petugas..."
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            @if(request('search'))
                <a href="{{ route('admin.officers.index') }}" class="px-3 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition">Reset</a>
            @endif
        </form>

        <!-- Add Button -->
        <button @click="addModal = true"
                class="px-4 py-2.5 rounded-xl bg-[#26a69a] hover:bg-[#20968b] text-white font-semibold text-sm shadow-md shadow-teal-600/20 transition flex items-center justify-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Petugas Baru</span>
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-100">
                        <th class="px-5 py-3.5">Nama Petugas</th>
                        <th class="px-5 py-3.5">Email Akun</th>
                        <th class="px-5 py-3.5 text-center">Hak Akses</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($officers as $off)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl bg-teal-50 text-[#26a69a] font-bold flex items-center justify-center text-sm border border-teal-100">
                                        {{ substr($off->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $off->name }}</p>
                                        <p class="text-xs text-slate-400">ID Petugas #{{ $off->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600 font-mono text-xs">
                                {{ $off->email }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-800 border border-teal-200">
                                    Administrator
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if(auth()->id() === $off->id)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Akun Anda (Aktif)
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">Tersedia</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="inline-flex items-center space-x-2">
                                    <button @click="
                                        editData = {
                                            id: {{ $off->id }},
                                            name: '{{ addslashes($off->name) }}',
                                            email: '{{ addslashes($off->email) }}'
                                        };
                                        editModal = true;
                                    " class="p-1.5 rounded-lg text-slate-500 hover:text-teal-600 hover:bg-slate-100 transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    @if(auth()->id() !== $off->id)
                                        <form action="{{ route('admin.officers.destroy', $off) }}" method="POST"
                                              onsubmit="return confirm('Hapus petugas {{ $off->name }}?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-slate-100 transition" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400 text-sm">
                                Belum ada data petugas. Klik tombol Tambah Petugas Baru untuk mendaftarkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($officers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $officers->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Petugas -->
    <div x-show="addModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="background-color: rgba(15, 23, 42, 0.5);">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div @click.away="addModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-800 text-base">Tambah Petugas Baru</h3>
                    <button @click="addModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>
                <form action="{{ route('admin.officers.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Lengkap</label>
                        <input type="text" name="name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none" placeholder="Contoh: Budi Santoso, S.Kom">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Email / Username Login</label>
                        <input type="email" name="email" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none" placeholder="petugas@sekolah.sch.id">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Password</label>
                        <input type="password" name="password" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none" placeholder="Minimal 6 karakter">
                    </div>
                    <div class="flex items-center justify-end space-x-2 pt-2">
                        <button type="button" @click="addModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 text-sm font-medium">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-[#26a69a] hover:bg-[#20968b] text-white text-sm font-semibold shadow-md shadow-teal-600/20">Simpan Petugas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Petugas -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="background-color: rgba(15, 23, 42, 0.5);">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div @click.away="editModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-800 text-base">Edit Data Petugas</h3>
                    <button @click="editModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>
                <form :action="'{{ url('admin/officers') }}/' + editData.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Lengkap</label>
                        <input type="text" name="name" x-model="editData.name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Email / Username Login</label>
                        <input type="email" name="email" x-model="editData.email" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Password Baru (Kosongkan jika tidak diganti)</label>
                        <input type="password" name="password" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none" placeholder="Minimal 6 karakter">
                    </div>
                    <div class="flex items-center justify-end space-x-2 pt-2">
                        <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 text-sm font-medium">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-[#26a69a] hover:bg-[#20968b] text-white text-sm font-semibold shadow-md shadow-teal-600/20">Perbarui Petugas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
