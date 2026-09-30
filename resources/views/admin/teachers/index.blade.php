@extends('layouts.app')

@section('title', 'Data Guru')
@section('page_title', 'Data Guru & Wali Kelas')
@section('page_subtitle', 'Kelola akun guru pengajar dan penugasan sebagai wali kelas')

@section('content')
<div x-data="{ 
    addModal: false, 
    editModal: false, 
    editData: { id: null, name: '', email: '', class_id: '' } 
}" class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.teachers.index') }}" class="flex items-center space-x-2 w-full sm:w-80">
            <div class="relative w-full">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama atau email guru..."
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            @if(request('search'))
                <a href="{{ route('admin.teachers.index') }}" class="px-3 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition">Reset</a>
            @endif
        </form>

        <!-- Add Button -->
        <button @click="addModal = true"
                class="px-4 py-2.5 rounded-xl bg-[#26a69a] hover:bg-[#20968b] text-white font-semibold text-sm shadow-md shadow-teal-600/20 transition flex items-center justify-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Data Guru</span>
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-100">
                        <th class="px-5 py-3.5">Guru</th>
                        <th class="px-5 py-3.5">Email</th>
                        <th class="px-5 py-3.5">Wali Kelas</th>
                        <th class="px-5 py-3.5 text-center">Role Akses</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($teachers as $t)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl bg-teal-50 text-[#26a69a] font-bold flex items-center justify-center text-sm border border-teal-100">
                                        {{ substr($t->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $t->name }}</p>
                                        <p class="text-xs text-slate-400">ID #{{ $t->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600 font-mono text-xs">
                                {{ $t->email }}
                            </td>
                            <td class="px-5 py-3.5">
                                @if($t->classes->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($t->classes as $cls)
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                                {{ $cls->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum ada kelas</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Guru / Wali Kelas
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="inline-flex items-center space-x-2">
                                    <button @click="
                                        editData = {
                                            id: {{ $t->id }},
                                            name: '{{ addslashes($t->name) }}',
                                            email: '{{ addslashes($t->email) }}',
                                            class_id: '{{ $t->classes->first()?->id ?? '' }}'
                                        };
                                        editModal = true;
                                    " class="p-1.5 rounded-lg text-slate-500 hover:text-teal-600 hover:bg-slate-100 transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <form action="{{ route('admin.teachers.destroy', $t) }}" method="POST"
                                          onsubmit="return confirm('Hapus data guru {{ $t->name }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-slate-100 transition" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400 text-sm">
                                Belum ada data guru. Klik tombol Tambah Data Guru untuk mendaftarkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($teachers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $teachers->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Guru -->
    <div x-show="addModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="background-color: rgba(15, 23, 42, 0.5);">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div @click.away="addModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-800 text-base">Tambah Data Guru Baru</h3>
                    <button @click="addModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>
                <form action="{{ route('admin.teachers.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Lengkap & Gelar</label>
                        <input type="text" name="name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none" placeholder="Contoh: Dra. Hj. Siti Nurhaliza, M.Pd">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Email / Akun Login</label>
                        <input type="email" name="email" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none" placeholder="nama.guru@sekolah.sch.id">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Password</label>
                        <input type="password" name="password" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none" placeholder="Minimal 6 karakter">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Tugaskan Sebagai Wali Kelas (Opsional)</label>
                        <select name="class_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none">
                            <option value="">-- Bukan Wali Kelas / Pilih Nanti --</option>
                            @foreach($availableClasses as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} (Kelas {{ $c->level }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center justify-end space-x-2 pt-2">
                        <button type="button" @click="addModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 text-sm font-medium">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-[#26a69a] hover:bg-[#20968b] text-white text-sm font-semibold shadow-md shadow-teal-600/20">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Guru -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="background-color: rgba(15, 23, 42, 0.5);">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div @click.away="editModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-800 text-base">Edit Data Guru</h3>
                    <button @click="editModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>
                <form :action="'{{ url('admin/teachers') }}/' + editData.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Lengkap & Gelar</label>
                        <input type="text" name="name" x-model="editData.name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Email / Akun Login</label>
                        <input type="email" name="email" x-model="editData.email" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Password Baru (Kosongkan jika tidak diganti)</label>
                        <input type="password" name="password" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none" placeholder="Minimal 6 karakter">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Tugaskan Sebagai Wali Kelas</label>
                        <select name="class_id" x-model="editData.class_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none">
                            <option value="">-- Bukan Wali Kelas / Bebas Tugas --</option>
                            @foreach($availableClasses as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} (Kelas {{ $c->level }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center justify-end space-x-2 pt-2">
                        <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 text-sm font-medium">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-[#26a69a] hover:bg-[#20968b] text-white text-sm font-semibold shadow-md shadow-teal-600/20">Perbarui Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
