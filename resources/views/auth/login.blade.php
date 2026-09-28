<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Presensi Digital Sekolah</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md" x-data="{ email: '{{ old('email') }}', password: '' }">
        <!-- Logo / Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-500 to-sky-400 items-center justify-center font-black text-3xl text-white shadow-xl shadow-indigo-500/30 mb-4">
                P
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Presensi Digital Sekolah</h1>
            <p class="text-sm text-slate-400 mt-1">Masuk untuk mengelola presensi siswa & jadwal</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white/95 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-white/20">
            @if(session('error'))
                <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-700 text-xs px-4 py-3 rounded-xl">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs px-4 py-3 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Email</label>
                    <input type="email" name="email" x-model="email" required autofocus
                           class="w-full px-4 py-3 rounded-xl border @error('email') border-rose-500 bg-rose-50/50 @else border-slate-200 @enderror focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-sm transition outline-none"
                           placeholder="nama@sekolah.sch.id">
                    @error('email')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Password</label>
                    </div>
                    <input type="password" name="password" x-model="password" required
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-sm transition outline-none"
                           placeholder="••••••••">
                    @error('password')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 mr-2">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full py-3.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-lg shadow-indigo-600/30 transition transform active:scale-[0.98]">
                    Masuk ke Sistem
                </button>
            </form>

            <!-- Quick Demo Accounts -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider text-center mb-3">
                    Akses Cepat Pengujian:
                </p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" @click="email = 'admin@sekolah.sch.id'; password = 'password'"
                            class="p-2.5 rounded-xl border border-indigo-100 bg-indigo-50/60 hover:bg-indigo-100 text-indigo-700 font-semibold transition text-center">
                        <span class="block text-[11px] text-slate-500 font-normal">Login sebagai</span>
                        Admin
                    </button>
                    <button type="button" @click="email = 'guru@sekolah.sch.id'; password = 'password'"
                            class="p-2.5 rounded-xl border border-emerald-100 bg-emerald-50/60 hover:bg-emerald-100 text-emerald-700 font-semibold transition text-center">
                        <span class="block text-[11px] text-slate-500 font-normal">Login sebagai</span>
                        Guru
                    </button>
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-slate-500 mt-6">
            &copy; {{ date('Y') }} Sistem Presensi Digital &bull; Face Scan & Barcode
        </p>
    </div>

</body>
</html>
