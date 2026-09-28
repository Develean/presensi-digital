<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu QR Code - {{ $student->name }}</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- QRCode.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        
        #qrcode img, #qrcode canvas {
            display: block;
            margin: 0 auto;
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
            image-rendering: pixelated;
        }

        @media print {
            .no-print { display: none !important; }
            body { 
                background: white !important; 
                padding: 0 !important; 
                margin: 0 !important; 
                color: #0f172a !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .print-wrapper { 
                margin: 0 !important; 
                padding: 10mm !important; 
                width: 100% !important;
                display: flex !important;
                justify-content: center !important;
            }
            .card-jumbo {
                width: 130mm !important;
                border: 2px solid #0f172a !important;
                border-radius: 12mm !important;
                padding: 8mm !important;
                page-break-inside: avoid !important;
                box-shadow: none !important;
            }
            .card-compact {
                width: 95mm !important;
                border: 2px solid #0f172a !important;
                border-radius: 8mm !important;
                padding: 6mm !important;
                page-break-inside: avoid !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-4 sm:p-8 flex flex-col items-center justify-start text-slate-800">

    <!-- Top Action Toolbar (Interactive Controls) -->
    <div class="no-print w-full max-w-2xl mb-6 bg-white p-4 rounded-2xl shadow-md border border-slate-200 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.students.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700 transition flex items-center space-x-1.5">
                <span>&larr;</span>
                <span>Kembali</span>
            </a>
            
            <!-- Format Preset Selector -->
            <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs font-bold">
                <button type="button" id="btnModeJumbo" onclick="setCardMode('jumbo')" class="px-3.5 py-1.5 rounded-lg bg-indigo-600 text-white shadow-sm transition">
                    Jumbo (Scan Cepat Jarak Jauh)
                </button>
                <button type="button" id="btnModeCompact" onclick="setCardMode('compact')" class="px-3.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition">
                    Kartu Saku (ID Card)
                </button>
            </div>
        </div>

        <button onclick="window.print()" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white transition shadow-lg shadow-indigo-600/30 flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak Kartu (Print)</span>
        </button>
    </div>

    <!-- Info Banner Before Print -->
    <div class="no-print max-w-2xl w-full mb-4 px-4 py-3 bg-sky-50 border border-sky-200 rounded-xl text-xs text-sky-800 flex items-center space-x-2">
        <svg class="w-5 h-5 text-sky-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>Ukuran QR Code telah diperbesar (hingga <strong>240px &times; 240px</strong>) dengan kontras maksimal agar terbaca instan oleh webcam Kiosk dalam hitungan milidetik.</span>
    </div>

    <!-- Main Printable Card Wrapper -->
    <div class="print-wrapper flex flex-col items-center">
        
        <!-- CARD CONTAINER -->
        <div id="cardContainer" class="card-jumbo w-[480px] bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 text-white rounded-3xl shadow-2xl p-7 relative overflow-hidden border-2 border-indigo-500/40 transition-all duration-300">
            
            <!-- Glow background decorative elements -->
            <div class="absolute -top-16 -right-16 w-52 h-52 bg-sky-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-16 -left-16 w-52 h-52 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Header: School Identity & Grade Badge -->
            <div class="flex items-center justify-between border-b border-slate-700/80 pb-4 relative z-10">
                <div class="flex items-center space-x-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-sky-400 to-indigo-500 flex items-center justify-center font-black text-xl text-slate-950 shadow-md">
                        P
                    </div>
                    <div>
                        <h2 class="text-sm font-black tracking-wider uppercase text-white leading-tight">SMK DIGITAL INDONESIA</h2>
                        <p class="text-[10px] text-sky-400 font-extrabold tracking-widest uppercase">KARTU PRESENSI RESMI SISWA</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-block text-[11px] font-black px-3.5 py-1 rounded-full bg-indigo-500/30 text-indigo-300 border border-indigo-500/40 uppercase">
                        {{ $student->schoolClass->name ?? 'Siswa' }}
                    </span>
                </div>
            </div>

            <!-- Central Content: Prominent High-Density QR Code -->
            <div class="py-6 flex flex-col items-center justify-center relative z-10 space-y-4">
                
                <!-- White QR Box with generous padding for high contrast scanning -->
                <div id="qrBox" class="bg-white p-4 sm:p-5 rounded-2xl flex flex-col items-center justify-center shadow-2xl border-4 border-white">
                    <div id="qrcode" class="flex items-center justify-center"></div>
                    <div class="mt-2.5 pt-2 border-t border-slate-200 w-full text-center">
                        <span class="text-xs font-mono font-black text-slate-900 tracking-widest block">{{ $student->barcode_token }}</span>
                    </div>
                </div>

                <!-- Student Identity Details Card -->
                <div class="w-full bg-slate-900/80 border border-slate-800 rounded-2xl p-4 space-y-2">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-wider text-sky-400 block">Nama Lengkap Siswa</span>
                            <h3 class="text-base sm:text-lg font-black text-white leading-tight">{{ $student->name }}</h3>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center space-x-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Aktif</span>
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-800 text-xs">
                        <div>
                            <span class="text-[10px] uppercase font-medium text-slate-400 block">Nomor Induk Siswa (NIS)</span>
                            <span class="font-mono font-bold text-white text-sm">{{ $student->nis }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-medium text-slate-400 block">Kelas / Jurusan</span>
                            <span class="font-semibold text-slate-200 text-sm">{{ $student->schoolClass->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer: Scanning Guide & Authentication -->
            <div class="flex items-center justify-between text-[10px] text-slate-400 border-t border-slate-800/80 pt-3 relative z-10">
                <span class="flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Arahkan ke Terminal Kiosk untuk Presensi</span>
                </span>
                <span class="font-bold text-sky-400 font-mono tracking-wider">SMK-DIGITAL</span>
            </div>

        </div>

    </div>

    <script>
        const qrContainer = document.getElementById("qrcode");
        let currentSize = 240; // Default Jumbo Size for instant camera detection
        let qrInstance = null;

        function renderQR(size) {
            qrContainer.innerHTML = '';
            qrInstance = new QRCode(qrContainer, {
                text: "{{ $student->barcode_token }}",
                width: size,
                height: size,
                colorDark : "#020617",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H // Highest error correction for dirty/curved cards
            });
        }

        function setCardMode(mode) {
            const card = document.getElementById('cardContainer');
            const btnJumbo = document.getElementById('btnModeJumbo');
            const btnCompact = document.getElementById('btnModeCompact');

            if (mode === 'jumbo') {
                card.className = "card-jumbo w-[480px] bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 text-white rounded-3xl shadow-2xl p-7 relative overflow-hidden border-2 border-indigo-500/40 transition-all duration-300";
                btnJumbo.className = "px-3.5 py-1.5 rounded-lg bg-indigo-600 text-white shadow-sm transition";
                btnCompact.className = "px-3.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition";
                currentSize = 240;
                renderQR(240);
            } else {
                card.className = "card-compact w-[360px] bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 text-white rounded-2xl shadow-xl p-5 relative overflow-hidden border border-indigo-500/40 transition-all duration-300";
                btnCompact.className = "px-3.5 py-1.5 rounded-lg bg-indigo-600 text-white shadow-sm transition";
                btnJumbo.className = "px-3.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition";
                currentSize = 175;
                renderQR(175);
            }
        }

        // Initial render with large size (240px)
        renderQR(240);
    </script>
</body>
</html>
