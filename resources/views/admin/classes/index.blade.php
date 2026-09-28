@extends('layouts.app')

@section('title', 'Manajemen Kelas')
@section('page_title', 'Manajemen Kelas & Wali Kelas')
@section('page_subtitle', 'Kelola daftar rombongan belajar (rombel) dan penugasan wali kelas')

@section('content')
<div x-data="{ addModal: false, editModal: false, editData: { id: null, name: '', level: 'X', teacher_id: '' } }" class="space-y-6">

    <!-- Header Action -->
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500 font-medium">Total: <span class="font-bold text-slate-800">{{ $classes->count() }} Kelas</span></p>
        <button @click="addModal = true"
                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-md shadow-indigo-600/20 transition flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Kelas Baru</span>
        </button>
    </div>

    <!-- Classes Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-100">
                        <th class="px-5 py-3.5">Tingkat</th>
                        <th class="px-5 py-3.5">Nama Rombel / Kelas</th>
                        <th class="px-5 py-3.5">Wali Kelas</th>
                        <th class="px-5 py-3.5 text-center">Jumlah Siswa</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($classes as $c)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    Kelas {{ $c->level }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 font-bold text-slate-800">{{ $c->name }}</td>
                            <td class="px-5 py-3.5 text-slate-600">
                                {{ $c->teacher->name ?? 'Belum Ditugaskan' }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ $c->students_count }} Siswa
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-2">
                                <button @click="editData = { id: {{ $c->id }}, name: '{{ $c->name }}', level: '{{ $c->level }}', teacher_id: '{{ $c->teacher_id ?? '' }}' }; editModal = true"
                                        class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-xs font-semibold text-slate-700 transition">
                                    Edit
                                </button>
                                <form action="{{ route('admin.classes.destroy', $c) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg border border-rose-200 hover:bg-rose-50 text-xs font-semibold text-rose-600 transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-slate-400">
                                Belum ada kelas yang terdaftar. Klik "Tambah Kelas Baru" untuk memulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Kelas -->
    <div x-cloak x-show="addModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="addModal = false" class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-lg">Tambah Kelas Baru</h3>
                <button @click="addModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>
            <form action="{{ route('admin.classes.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Tingkat</label>
                    <select name="level" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                        <option value="X">Kelas X (Sepuluh)</option>
                        <option value="XI">Kelas XI (Sebelas)</option>
                        <option value="XII">Kelas XII (Dua Belas)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Rombel / Kelas</label>
                    <input type="text" name="name" required placeholder="Contoh: X-RPL 1"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Wali Kelas (Opsional)</label>
                    <select name="teacher_id" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                        <option value="">-- Pilih Guru / Wali Kelas --</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center justify-end space-x-2 pt-3">
                    <button type="button" @click="addModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">Simpan Kelas</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Kelas -->
    <div x-cloak x-show="editModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="editModal = false" class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-lg">Edit Data Kelas</h3>
                <button @click="editModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>
            <form :action="'{{ url('admin/classes') }}/' + editData.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Tingkat</label>
                    <select name="level" x-model="editData.level" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                        <option value="X">Kelas X</option>
                        <option value="XI">Kelas XI</option>
                        <option value="XII">Kelas XII</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Rombel / Kelas</label>
                    <input type="text" name="name" x-model="editData.name" required
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Wali Kelas</label>
                    <select name="teacher_id" x-model="editData.teacher_id" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none">
                        <option value="">-- Tanpa Wali Kelas --</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center justify-end space-x-2 pt-3">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
