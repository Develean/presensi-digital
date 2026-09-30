@extends('layouts.app')

@section('title', 'Generate QR Code')
@section('page_title', 'Generate QR Code Kartu Presensi')
@section('page_subtitle', 'Cetak dan generate barcode / QR Code presensi siswa satuan atau massal per kelas')

@section('content')
<div x-data="{ 
    selectAll: false, 
    selected: [],
    toggleAll() {
        if (this.selectAll) {
            this.selected = {{ json_encode($students->pluck('id')->toArray()) }};
        } else {
            this.selected = [];
        }
    }
}" class="space-y-6">

    <!-- Filters & Actions Header -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Class & Search Filter -->
        <form method="GET" action="{{ route('admin.qrcode.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <!-- Filter Kelas -->
            <select name="class_id" onchange="this.form.submit()" class="px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none">
                <option value="">Semua Kelas & Jurusan</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }} (Kelas {{ $c->level }})
                    </option>
                @endforeach
            </select>

            <!-- Search input -->
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / NIS..."
                       class="pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none w-56">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 text-white text-xs font-semibold hover:bg-slate-900 transition">
                Filter
            </button>
            @if(request('class_id') || request('search'))
                <a href="{{ route('admin.qrcode.index') }}" class="px-3 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition">
                    Reset
                </a>
            @endif
        </form>

        <!-- Batch Print Form Trigger -->
        <form method="POST" action="{{ route('admin.qrcode.batch-print') }}" target="_blank" class="flex items-center space-x-3 w-full md:w-auto justify-end">
            @csrf
            <!-- Pass selected IDs -->
            <template x-for="id in selected" :key="id">
                <input type="hidden" name="student_ids[]" :value="id">
            </template>
            @if(request('class_id'))
                <input type="hidden" name="class_id" value="{{ request('class_id') }}">
            @endif

            <div class="flex items-center space-x-2">
                <label class="flex items-center space-x-2 text-xs font-semibold text-slate-600 cursor-pointer bg-slate-50 px-3 py-2.5 rounded-xl border border-slate-200">
                    <input type="checkbox" x-model="selectAll" @change="toggleAll()" class="rounded text-[#26a69a] focus:ring-teal-500">
                    <span>Pilih Semua di Halaman Ini</span>
                </label>

                <button type="submit" 
                        class="px-4 py-2.5 rounded-xl bg-[#26a69a] hover:bg-[#20968b] text-white font-semibold text-xs shadow-md shadow-teal-600/20 transition flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak Kartu QR Terpilih (<span x-text="selected.length">0</span>)</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Student QR Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        @forelse($students as $st)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition relative group">
                <!-- Checkbox -->
                <div class="absolute top-4 left-4 z-10">
                    <input type="checkbox" value="{{ $st->id }}" x-model="selected" class="w-4 h-4 rounded text-[#26a69a] focus:ring-teal-500 border-slate-300">
                </div>

                <div class="text-center pt-2">
                    <!-- Photo / Initials -->
                    @if($st->photo)
                        <img src="{{ asset('storage/' . $st->photo) }}" class="w-16 h-16 rounded-full mx-auto object-cover border-2 border-teal-500 shadow-sm">
                    @else
                        <div class="w-16 h-16 rounded-full mx-auto bg-slate-100 text-slate-600 font-bold flex items-center justify-center text-xl border-2 border-slate-200">
                            {{ substr($st->name, 0, 1) }}
                        </div>
                    @endif

                    <h4 class="font-bold text-slate-800 text-sm mt-3 truncate" title="{{ $st->name }}">{{ $st->name }}</h4>
                    <p class="text-xs font-mono text-slate-500">NIS: {{ $st->nis }}</p>
                    <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                        {{ $st->schoolClass?->name ?? 'Tanpa Kelas' }}
                    </span>

                    <!-- QR Code Canvas Container -->
                    <div class="my-4 p-2 bg-slate-50 rounded-xl inline-block border border-slate-100">
                        <div id="qrcode-{{ $st->id }}" class="flex justify-center"></div>
                    </div>
                </div>

                <!-- Card Actions -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <a href="{{ route('admin.students.card', $st) }}" target="_blank"
                       class="text-teal-700 hover:text-teal-900 font-semibold flex items-center space-x-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Cetak Satuan</span>
                    </a>
                    <a href="{{ route('admin.students.edit', $st) }}" class="text-slate-400 hover:text-slate-600">
                        Detail &rarr;
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-slate-400">
                Tidak ada data siswa ditemukan untuk kriteria pencarian ini.
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($students->hasPages())
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80">
            {{ $students->links() }}
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @foreach($students as $st)
            new QRCode(document.getElementById("qrcode-{{ $st->id }}"), {
                text: "{{ $st->nis }}",
                width: 96,
                height: 96,
                colorDark : "#0f172a",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        @endforeach
    });
</script>
@endpush
