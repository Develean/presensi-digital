<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Presensi Digital') - Sistem Presensi Sekolah</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN & Alpine.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        navy: {
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617',
                        },
                        emerald: {
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }

        /* Clean animations */
        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.08);
        }

        .btn-bounce {
            transition: transform 0.15s ease, opacity 0.15s ease;
        }
        .btn-bounce:active {
            transform: scale(0.97);
        }

        .nav-transition {
            transition: all 0.18s ease-in-out;
        }
        .nav-transition:hover {
            transform: translateX(3px);
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-100 text-slate-800 antialiased min-h-screen flex flex-col md:flex-row overflow-x-hidden selection:bg-emerald-500 selection:text-white"
      x-data="{
          sidebarOpen: localStorage.getItem('presensi_sidebar_open') !== 'false',
          toggleSidebar() {
              this.sidebarOpen = !this.sidebarOpen;
              localStorage.setItem('presensi_sidebar_open', this.sidebarOpen);
          }
      }">

    <!-- Mobile Backdrop Overlay -->
    <div x-cloak x-show="sidebarOpen"
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 md:hidden transition-opacity"></div>

    <!-- Sidebar Navigation (Midnight Navy Solid - Bersih, Jelas, & Bisa Dibuka-Tutup) -->
    <aside class="fixed md:static inset-y-0 left-0 z-50 w-64 bg-[#0f172a] text-slate-100 flex-shrink-0 flex flex-col justify-between shadow-xl border-r border-slate-800 transition-all duration-300 ease-in-out"
           :class="sidebarOpen ? 'translate-x-0 md:ml-0' : '-translate-x-full md:-ml-64'">
        
        <div>
            <!-- School Brand / Header -->
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between bg-slate-950/60 h-16">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-500 flex items-center justify-center font-black text-lg text-white shadow-md shadow-emerald-500/30 flex-shrink-0">
                        P
                    </div>
                    <div class="truncate">
                        <h1 class="font-extrabold text-sm tracking-tight text-white leading-tight truncate">SMKN 77 JAKARTA</h1>
                        <p class="text-[11px] text-emerald-400 font-semibold truncate">Presensi Digital</p>
                    </div>
                </div>

                <!-- Close button for Mobile -->
                <button type="button" @click="sidebarOpen = false"
                        class="md:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Navigation Links (9 Menu Jelas & Rapi) -->
            <nav class="p-3.5 space-y-1 text-sm overflow-y-auto max-h-[calc(100vh-140px)]">
                @if(auth()->user()->isAdmin())
                    <!-- 1. Dashboard -->
                    @php $isDashboard = request()->routeIs('admin.dashboard'); @endphp
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition nav-transition {{ $isDashboard ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white font-semibold' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ $isDashboard ? 'text-white' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="currentColor">
                            <rect x="3" y="3" width="8" height="8" rx="1.5" />
                            <rect x="13" y="3" width="8" height="8" rx="1.5" />
                            <rect x="3" y="13" width="8" height="8" rx="1.5" />
                            <rect x="13" y="13" width="8" height="8" rx="1.5" />
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- 2. Presensi Siswa -->
                    @php $isPresensi = request()->routeIs('admin.attendances.*'); @endphp
                    <a href="{{ route('admin.attendances.index') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition nav-transition {{ $isPresensi ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white font-semibold' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ $isPresensi ? 'text-white' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 7l2.5 2.5L10 5" />
                            <path d="M14 7h7" />
                            <path d="M3 17l2.5 2.5L10 15" />
                            <path d="M14 17h7" />
                        </svg>
                        <span>Presensi Siswa</span>
                    </a>

                    <!-- 3. Data Siswa -->
                    @php $isStudents = request()->routeIs('admin.students.*'); @endphp
                    <a href="{{ route('admin.students.index') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition nav-transition {{ $isStudents ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white font-semibold' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ $isStudents ? 'text-white' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
                        </svg>
                        <span>Data Siswa</span>
                    </a>

                    <!-- 4. Data Guru -->
                    @php $isTeachers = request()->routeIs('admin.teachers.*'); @endphp
                    <a href="{{ route('admin.teachers.index') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition nav-transition {{ $isTeachers ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white font-semibold' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ $isTeachers ? 'text-white' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
                        </svg>
                        <span>Data Guru</span>
                    </a>

                    <!-- 5. Data Kelas & Jurusan -->
                    @php $isClasses = request()->routeIs('admin.classes.*'); @endphp
                    <a href="{{ route('admin.classes.index') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition nav-transition {{ $isClasses ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white font-semibold' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ $isClasses ? 'text-white' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zm0 8.55L5.45 8.5 12 5.04l6.55 3.46L12 11.55zM6 12.72v3.91c0 2.22 3.13 3.91 6 3.91s6-1.69 6-3.91v-3.91l-6 3.27-6-3.27z" />
                        </svg>
                        <span>Data Kelas & Jurusan</span>
                    </a>

                    <!-- 6. Generate QR Code -->
                    @php $isQr = request()->routeIs('admin.qrcode.*'); @endphp
                    <a href="{{ route('admin.qrcode.index') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition nav-transition {{ $isQr ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white font-semibold' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ $isQr ? 'text-white' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 3h7v7H3V3zm2 2v3h3V5H5zm9-2h7v7h-7V3zm2 2v3h3V5h-3zM3 14h7v7H3v-7zm2 2v3h3v-3H5zm12 1h2v4h-2v-4zm-3-3h2v2h-2v-2zm5 0h2v2h-2v-2zm-3 5h5v2h-5v-2zm-2-2h2v2h-2v-2z" />
                        </svg>
                        <span>Generate QR Code</span>
                    </a>

                    <!-- 7. Generate Laporan -->
                    @php $isReports = request()->routeIs('admin.reports.*'); @endphp
                    <a href="{{ route('admin.reports.index') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition nav-transition {{ $isReports ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white font-semibold' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ $isReports ? 'text-white' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 8h-1V3H6v5H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-11-3h8v3H8V5zm8 14H8v-4h8v4zm2-4v-2H6v2H4v-4c0-.55.45-1 1-1h14c.55 0 1 .45 1 1v4h-2z" />
                            <circle cx="18" cy="11.5" r="1" />
                        </svg>
                        <span>Generate Laporan</span>
                    </a>

                    <!-- 8. Data Petugas -->
                    @php $isOfficers = request()->routeIs('admin.officers.*'); @endphp
                    <a href="{{ route('admin.officers.index') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition nav-transition {{ $isOfficers ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white font-semibold' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ $isOfficers ? 'text-white' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20 18c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2H0v2h24v-2h-4zM4 6h16v10H4V6z" />
                        </svg>
                        <span>Data Petugas</span>
                    </a>

                    <!-- 9. Pengaturan -->
                    @php $isSettings = request()->routeIs('admin.settings.*') || request()->routeIs('admin.rules.*'); @endphp
                    <a href="{{ route('admin.settings.index') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition nav-transition {{ $isSettings ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white font-semibold' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ $isSettings ? 'text-white' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58a.49.49 0 00.12-.61l-1.92-3.32a.488.488 0 00-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54a.484.484 0 00-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58a.485.485 0 00-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z" />
                        </svg>
                        <span>Pengaturan</span>
                    </a>
                @else
                    <!-- Portal Guru -->
                    @php $isGuruDash = request()->routeIs('guru.dashboard'); @endphp
                    <a href="{{ route('guru.dashboard') }}" 
                       class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl transition nav-transition {{ $isGuruDash ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white font-semibold' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ $isGuruDash ? 'text-white' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="currentColor">
                            <rect x="3" y="3" width="8" height="8" rx="1.5" />
                            <rect x="13" y="3" width="8" height="8" rx="1.5" />
                            <rect x="3" y="13" width="8" height="8" rx="1.5" />
                            <rect x="13" y="13" width="8" height="8" rx="1.5" />
                        </svg>
                        <span>Dashboard Guru</span>
                    </a>
                @endif
            </nav>
        </div>

        <!-- User Profile & Logout Box -->
        <div class="p-3.5 border-t border-slate-800 bg-slate-950/60">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2.5 overflow-hidden">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-emerald-500 to-teal-500 flex items-center justify-center font-bold text-xs text-white uppercase shadow-sm flex-shrink-0">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="truncate max-w-[125px]">
                        <p class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</p>
                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            {{ strtoupper(auth()->user()->role) }}
                        </span>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Keluar" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition btn-bounce">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- Topbar Header (Clean, Sharp, & Professional) -->
        <header class="bg-white border-b border-slate-200 px-4 sm:px-6 py-3.5 flex items-center justify-between sticky top-0 z-20 shadow-sm">
            <div class="flex items-center space-x-3.5">
                <!-- Hamburger Toggle Button -->
                <button type="button" @click="toggleSidebar()"
                        class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-emerald-500/30 btn-bounce"
                        title="Buka / Tutup Sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                    </svg>
                </button>

                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-800 tracking-tight leading-tight">@yield('page_title', 'Halaman')</h2>
                    <p class="text-xs text-slate-500 hidden sm:block">@yield('page_subtitle', 'Sistem Presensi Digital')</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <span class="text-xs font-semibold px-3 py-1.5 bg-emerald-50 border border-emerald-200/80 rounded-full text-emerald-700 flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="hidden sm:inline">{{ now()->translatedFormat('l, d F Y') }}</span>
                    <span class="sm:hidden font-mono">{{ now()->format('d/m/Y') }}</span>
                </span>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="px-4 sm:px-6 pt-4">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3 rounded-2xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-800 btn-bounce">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3 rounded-2xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-rose-500 hover:text-rose-800 btn-bounce">&times;</button>
                </div>
            @endif
        </div>

        <!-- Page Content -->
        <div class="p-4 sm:p-6 flex-1 min-w-0">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>
