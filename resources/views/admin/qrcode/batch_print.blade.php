<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Massal Kartu QR Presensi Siswa</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        
        @media print {
            .no-print { display: none !important; }
            body { 
                background: white !important; 
                margin: 0 !important; 
                padding: 5mm !important; 
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important; 
            }
            .page-break { page-break-after: always; }
            .print-card { 
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-6">

    <!-- Top Action Bar -->
    <div class="max-w-5xl mx-auto mb-6 flex items-center justify-between no-print bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div>
            <h1 class="font-bold text-slate-800 text-lg">Cetak Kartu Presensi QR Code</h1>
            <p class="text-xs text-slate-500">Total: {{ $students->count() }} Kartu Siswa Siap Cetak (A4 Layout)</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="window.close()" class="px-4 py-2 rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 text-xs font-semibold">
                Tutup
            </button>
            <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-[#26a69a] hover:bg-[#20968b] text-white text-xs font-bold shadow-md shadow-teal-600/30 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Semua Sekarang</span>
            </button>
        </div>
    </div>

    <!-- Cards Grid (Print Optimized) -->
    <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($students as $st)
            <div class="print-card bg-white border-2 border-slate-800 rounded-2xl p-4 shadow-sm relative overflow-hidden flex flex-col justify-between" style="width: 85mm; height: 54mm; margin: 0 auto;">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                    <div class="flex items-center space-x-2">
                        <div class="w-6 h-6 rounded-lg bg-[#26a69a] text-white font-black text-xs flex items-center justify-center">
                            P
                        </div>
                        <div>
                            <h3 class="font-extrabold text-[11px] text-slate-900 tracking-tight leading-none">SMK NEGERI 77 JAKARTA</h3>
                            <p class="text-[8px] text-slate-500 font-medium leading-tight">KARTU PRESENSI DIGITAL</p>
                        </div>
                    </div>
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-700">
                        {{ $st->schoolClass?->name ?? '-' }}
                    </span>
                </div>

                <!-- Body (QR Code + Student Info) -->
                <div class="flex items-center space-x-3 my-auto py-1">
                    <div class="p-1 bg-white border border-slate-300 rounded-lg flex-shrink-0">
                        <div id="print-qr-{{ $st->id }}" class="w-[28mm] h-[28mm] flex items-center justify-center"></div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">Nama Siswa</p>
                        <h4 class="font-bold text-slate-900 text-xs truncate leading-snug">{{ $st->name }}</h4>
                        
                        <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider mt-1.5">Nomor Induk (NIS)</p>
                        <p class="font-mono font-bold text-teal-700 text-xs">{{ $st->nis }}</p>

                        <p class="text-[8px] text-slate-400 mt-1 italic">Scan di Kiosk Gerbang</p>
                    </div>
                </div>

                <!-- Footer Strip -->
                <div class="border-t border-slate-100 pt-1 flex items-center justify-between text-[7px] text-slate-400">
                    <span>Tahun Ajaran {{ date('Y') }}/{{ date('Y')+1 }}</span>
                    <span>Validasi Resmi Sekolah</span>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @foreach($students as $st)
                new QRCode(document.getElementById("print-qr-{{ $st->id }}"), {
                    text: "{{ $st->nis }}",
                    width: 90,
                    height: 90,
                    colorDark : "#0f172a",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.H
                });
            @endforeach
        });
    </script>
</body>
</html>
