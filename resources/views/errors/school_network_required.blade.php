<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Terbatas - Wi-Fi Sekolah Diperlukan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-slate-800/90 border border-slate-700/80 rounded-3xl p-8 shadow-2xl backdrop-blur-xl text-center">
        <!-- Wi-Fi Alert Icon -->
        <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6" />
            </svg>
        </div>

        <h1 class="text-2xl font-extrabold text-white mb-2">Wi-Fi Sekolah Diperlukan</h1>
        <p class="text-slate-300 text-sm mb-6 leading-relaxed">
            Presensi mandiri hanya dapat diakses saat perangkat Anda terhubung ke 
            <span class="font-semibold text-amber-400">jaringan Wi-Fi resmi sekolah</span>.
        </p>

        <div class="bg-slate-900/80 border border-slate-700/50 rounded-2xl p-4 mb-6 text-left space-y-2">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span>IP Perangkat Terdeteksi:</span>
                <span class="font-mono text-slate-200">{{ $clientIp ?? 'Unknown' }}</span>
            </div>
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span>Status Jaringan:</span>
                <span class="text-rose-400 font-semibold flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> Bukan Wi-Fi Sekolah
                </span>
            </div>
        </div>

        <div class="space-y-3">
            <a href="javascript:location.reload()" class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold py-3 px-6 rounded-xl transition duration-200 shadow-lg shadow-blue-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Periksa Ulang Koneksi
            </a>
            <p class="text-xs text-slate-400">
                Jika sudah terhubung ke Wi-Fi sekolah namun masih terkendala, hubungi <span class="text-slate-200 font-medium">Guru Piket</span>.
            </p>
        </div>
    </div>
</body>
</html>
