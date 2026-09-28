<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Terminal Kiosk Presensi Mandiri - SMK Digital Indonesia</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- High-Performance QR Scanner (Html5Qrcode) & Face-API -->
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display: none !important; }
        
        #qrReader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-radius: 1.5rem !important;
        }
        #qrReader__scan_region {
            background: transparent !important;
        }
        #qrReader__dashboard {
            display: none !important;
        }

        /* Animated Scanner Laser */
        @keyframes scanLaser {
            0% { top: 10%; opacity: 0.8; }
            50% { top: 90%; opacity: 1; }
            100% { top: 10%; opacity: 0.8; }
        }
        .laser-line {
            animation: scanLaser 2s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-slate-950 text-white min-h-screen flex flex-col justify-between overflow-x-hidden select-none"
      x-data="kioskApp()" x-init="init()">

    <!-- Top Header -->
    <header class="px-6 sm:px-8 py-4 flex items-center justify-between border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-md sticky top-0 z-30">
        <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-400 to-indigo-600 flex items-center justify-center font-black text-2xl text-white shadow-lg shadow-indigo-500/20">
                P
            </div>
            <div>
                <h1 class="text-base sm:text-lg font-black tracking-wide text-white leading-tight">TERMINAL PRESENSI MANDIRI</h1>
                <p class="text-xs text-sky-400 font-medium">SMK Digital Indonesia &bull; Gerbang Utama</p>
            </div>
        </div>

        <!-- Clock & Navigation -->
        <div class="flex items-center space-x-6">
            <div class="text-right hidden sm:block">
                <div class="text-2xl sm:text-3xl font-black font-mono tracking-tight text-white" x-text="currentTime">00:00:00</div>
                <div class="text-xs text-slate-400 font-semibold" x-text="currentDate">Memuat tanggal...</div>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300 transition">
                Portal Admin &rarr;
            </a>
        </div>
    </header>

    <!-- Main Viewport Section -->
    <main class="flex-1 flex flex-col lg:flex-row items-center justify-center p-4 sm:p-6 gap-8 max-w-7xl mx-auto w-full">

        <!-- Left: Interactive Scanner Viewport with Mode Selector -->
        <div class="w-full lg:w-1/2 flex flex-col items-center">
            
            <!-- Scan Mode Switcher Tabs -->
            <div class="flex items-center space-x-2 p-1.5 bg-slate-900 rounded-2xl border border-slate-800 mb-4 shadow-lg">
                <button type="button" @click="switchMode('qr')"
                        :class="scanMode === 'qr' ? 'bg-indigo-600 text-white shadow-md font-bold' : 'text-slate-400 hover:text-white font-medium'"
                        class="px-5 py-2.5 rounded-xl text-xs transition flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Pemindai QR Code (Cepat & Responsif)</span>
                </button>
                <button type="button" @click="switchMode('face')"
                        :class="scanMode === 'face' ? 'bg-purple-600 text-white shadow-md font-bold' : 'text-slate-400 hover:text-white font-medium'"
                        class="px-5 py-2.5 rounded-xl text-xs transition flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Biometrik Wajah (Face AI)</span>
                </button>
            </div>

            <!-- Viewport Container -->
            <div class="relative w-full max-w-lg aspect-[4/3] bg-black rounded-3xl overflow-hidden border-2 border-indigo-500/40 shadow-2xl flex items-center justify-center">
                
                <!-- A. QR Mode Container -->
                <div x-show="scanMode === 'qr'" class="w-full h-full relative flex items-center justify-center">
                    <div id="qrReader" class="w-full h-full"></div>
                    
                    <!-- Scanner Laser Overlay -->
                    <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                        <div class="w-64 h-64 border-2 border-indigo-400/50 rounded-2xl relative overflow-hidden">
                            <!-- Corner Crosshairs -->
                            <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-sky-400"></div>
                            <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-sky-400"></div>
                            <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-sky-400"></div>
                            <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-sky-400"></div>
                            <!-- Glowing Scan Line -->
                            <div class="absolute inset-x-0 h-0.5 bg-gradient-to-r from-transparent via-sky-400 to-transparent shadow-[0_0_8px_#38bdf8] laser-line"></div>
                        </div>
                    </div>
                </div>

                <!-- B. Face Recognition Mode Container -->
                <div x-show="scanMode === 'face'" class="w-full h-full relative flex items-center justify-center">
                    <video id="faceCamera" autoplay muted playsinline class="w-full h-full object-cover transform -scale-x-100"></video>
                    
                    <!-- Facial Target Ring Guide -->
                    <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                        <div :class="{
                                'border-emerald-400 scale-105 border-solid shadow-[0_0_20px_rgba(52,211,153,0.5)]': faceMatchState === 'matched',
                                'border-amber-400 border-solid shadow-[0_0_15px_rgba(251,191,36,0.4)]': faceMatchState === 'unknown',
                                'border-purple-400 border-dashed': faceMatchState === 'searching'
                             }"
                             class="w-52 h-64 border-2 rounded-[50%] transition-all duration-300"></div>
                    </div>

                    <!-- Face Scanning Status HUD Banner -->
                    <div class="absolute bottom-4 inset-x-4 pointer-events-none flex justify-center z-10">
                        <div class="px-4 py-2 rounded-2xl backdrop-blur-md text-xs font-bold shadow-lg transition-all"
                             :class="{
                                'bg-emerald-600/90 text-white': faceMatchState === 'matched',
                                'bg-amber-600/90 text-white': faceMatchState === 'unknown',
                                'bg-slate-900/80 text-slate-200 border border-slate-700': faceMatchState === 'searching'
                             }">
                            <span x-text="faceStatusText">Mempersiapkan AI Wajah...</span>
                        </div>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="absolute top-4 left-4 bg-slate-950/85 backdrop-blur-md px-3.5 py-1.5 rounded-full text-xs font-semibold flex items-center space-x-2 border border-slate-700 z-10">
                    <span class="w-2.5 h-2.5 rounded-full animate-pulse"
                          :class="cameraStatus.includes('Offline') ? 'bg-rose-400' : 'bg-emerald-400'"></span>
                    <span x-text="cameraStatus">Kamera Aktif</span>
                </div>
            </div>

            <p class="text-xs text-slate-400 mt-3 text-center">
                <span x-show="scanMode === 'qr'">Arahkan kartu <strong>QR Code</strong> ke kotak scanner &bull; Respon instan tanpa jeda.</span>
                <span x-show="scanMode === 'face'">Posisikan wajah Anda tepat di dalam lingkaran panduan.</span>
            </p>
        </div>

        <!-- Right: Active Rule & Manual/USB Scanner Box -->
        <div class="w-full lg:w-1/2 max-w-md space-y-6">

            <!-- Active Rule Banner -->
            <div class="bg-slate-900/80 p-5 rounded-3xl border border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-sky-400 uppercase tracking-wider">Jadwal Presensi Hari Ini</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        {{ $activeRule->name ?? 'Reguler' }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                    <div class="p-2.5 rounded-2xl bg-slate-800/80 border border-slate-700">
                        <span class="text-slate-400 block text-[10px]">Batas Tepat Waktu</span>
                        <strong class="font-mono text-emerald-400 text-sm">{{ substr($activeRule->time_in_deadline ?? '07:15', 0, 5) }} WIB</strong>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-slate-800/80 border border-slate-700">
                        <span class="text-slate-400 block text-[10px]">Mulai Jam Pulang</span>
                        <strong class="font-mono text-sky-400 text-sm">{{ substr($activeRule->time_out_start ?? '15:00', 0, 5) }} WIB</strong>
                    </div>
                </div>
            </div>

            <!-- Hardware USB Scanner & Manual Input Box -->
            <div class="bg-gradient-to-br from-indigo-950/40 to-slate-900 p-6 rounded-3xl border border-indigo-500/30 shadow-xl space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-sky-500/20 text-sky-400 flex items-center justify-center font-bold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-sm">Scanner Fisik USB / Input Manual</h3>
                        <p class="text-xs text-slate-400">Mendukung otomatis alat scanner tembak USB barcode/QR</p>
                    </div>
                </div>

                <form @submit.prevent="submitManualCode" class="relative">
                    <input type="text" x-ref="barcodeInput" x-model="manualCode"
                           placeholder="Scan alat USB atau ketik token..."
                           autofocus
                           class="w-full px-4 py-3.5 rounded-2xl bg-slate-950 border border-indigo-500/50 text-white font-mono text-sm tracking-wider focus:outline-none focus:ring-2 focus:ring-sky-400/50 pr-24">
                    <button type="submit"
                            class="absolute right-2 top-2 px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-bold text-white transition">
                        Kirim
                    </button>
                </form>

                <div class="flex items-center justify-between text-xs text-slate-400 pt-2 border-t border-slate-800">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" x-model="voiceEnabled" class="rounded text-indigo-500 bg-slate-900 border-slate-700">
                        <span>Suara Audio & Sapaan (TTS)</span>
                    </label>

                    <button @click="showTestDrawer = !showTestDrawer" class="text-sky-400 hover:text-sky-300 font-semibold underline text-[11px]">
                        Panel Pengujian Cepat &darr;
                    </button>
                </div>
            </div>

            <!-- Quick Testing Drawer (Allows instant test of any student) -->
            <div x-cloak x-show="showTestDrawer" class="bg-slate-900/90 rounded-3xl p-4 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-wider block">Uji Coba Presensi Siswa:</span>
                    <span class="text-[10px] text-slate-400" x-text="registeredStudents.length + ' Siswa'"></span>
                </div>
                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                    <template x-for="st in registeredStudents" :key="st.id">
                        <div class="p-2.5 rounded-xl bg-slate-800/80 flex items-center justify-between text-xs">
                            <div>
                                <strong class="text-white" x-text="st.name"></strong>
                                <span class="text-[10px] text-slate-400 block" x-text="'Token: ' + st.barcode_token"></span>
                            </div>
                            <div class="flex space-x-1.5">
                                <button @click="testScanFace(st.id)" class="px-2.5 py-1 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-bold text-[10px]">
                                    Tes Wajah
                                </button>
                                <button @click="testScanQR(st.barcode_token)" class="px-2.5 py-1 rounded-lg bg-sky-600 hover:bg-sky-500 text-white font-bold text-[10px]">
                                    Tes QR
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>

    </main>

    <!-- Bottom Footer -->
    <footer class="px-8 py-3 text-center text-xs text-slate-500 border-t border-slate-900 bg-slate-950">
        Sistem Presensi Digital &bull; SMK Digital Indonesia &bull; Pemindai Cepat AI Wajah & QR Code Hardware-Accelerated
    </footer>

    <!-- Result Feedback Modal Overlay (Fast Auto-Dismiss) -->
    <div x-cloak x-show="showResultModal"
         @click="showResultModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md cursor-pointer">
        <div class="bg-slate-900 border border-slate-700 text-white rounded-3xl p-8 w-full max-w-md shadow-2xl text-center space-y-5 cursor-default"
             @click.stop>
            
            <div class="w-20 h-20 rounded-full mx-auto flex items-center justify-center"
                 :class="resultData.success ? (resultData.status === 'terlambat' ? 'bg-amber-500/20 text-amber-400 border-2 border-amber-500' : 'bg-emerald-500/20 text-emerald-400 border-2 border-emerald-500') : 'bg-rose-500/20 text-rose-400 border-2 border-rose-500'">
                <template x-if="resultData.success">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </template>
                <template x-if="!resultData.success">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </template>
            </div>

            <div class="space-y-1">
                <template x-if="resultData.student">
                    <h2 class="text-2xl font-black text-white" x-text="resultData.student.name"></h2>
                </template>
                <template x-if="resultData.student && resultData.student.school_class">
                    <p class="text-xs font-semibold text-slate-400" x-text="'Kelas: ' + resultData.student.school_class.name"></p>
                </template>
                <p class="text-sm font-semibold mt-2"
                   :class="resultData.success ? (resultData.status === 'terlambat' ? 'text-amber-400' : 'text-emerald-400') : 'text-rose-400'"
                   x-text="resultData.message"></p>
            </div>

            <template x-if="resultData.success">
                <div class="inline-flex items-center space-x-2 px-4 py-2 rounded-2xl bg-slate-800 border border-slate-700 text-xs font-bold text-slate-200">
                    <span x-text="'Waktu: ' + resultData.time + ' WIB'"></span>
                    <span>&bull;</span>
                    <span class="uppercase tracking-wider" x-text="resultData.action"></span>
                </div>
            </template>

            <div class="pt-2">
                <button @click="showResultModal = false" class="px-5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300">
                    Tutup (Siap Scan Berikutnya)
                </button>
            </div>
        </div>
    </div>

    <!-- Core Interactive Logic -->
    <script>
        function kioskApp() {
            return {
                currentTime: '',
                currentDate: '',
                cameraStatus: 'Menyiapkan Pemindai Kamera...',
                scanMode: 'qr', // 'qr' or 'face'
                manualCode: '',
                voiceEnabled: true,
                showResultModal: false,
                showTestDrawer: false,
                resultData: {},
                modalTimer: null,
                registeredStudents: [],
                html5QrScanner: null,
                faceMatcher: null,
                faceDetected: false,
                faceMatchState: 'searching', // 'searching', 'unknown', 'matched'
                faceStatusText: 'Posisikan wajah Anda di depan kamera',
                isScanningFaceLoop: false,
                isThrottled: false,
                isFaceModelLoaded: false,

                async init() {
                    this.updateClock();
                    setInterval(() => this.updateClock(), 1000);

                    // 1. Ambil daftar siswa terdaftar & descriptor biometrik
                    await this.loadStudentDescriptors();

                    // 2. Preload Face-API model secara asynchronous di background
                    this.preloadFaceModels();

                    // 3. Jalankan pemindai QR Code default
                    this.startQRScanner();

                    // 4. Autofokus listener USB scanner
                    setInterval(() => {
                        if (!this.showResultModal && this.$refs.barcodeInput) {
                            this.$refs.barcodeInput.focus();
                        }
                    }, 2500);
                },

                updateClock() {
                    const now = new Date();
                    this.currentTime = now.toLocaleTimeString('id-ID', { hour12: false });
                    this.currentDate = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                },

                async preloadFaceModels() {
                    if (this.isFaceModelLoaded || typeof faceapi === 'undefined') return;
                    try {
                        const LOCAL_URL = '/models/face-api';
                        await Promise.all([
                            faceapi.nets.tinyFaceDetector.loadFromUri(LOCAL_URL),
                            faceapi.nets.faceLandmark68TinyNet.loadFromUri(LOCAL_URL),
                            faceapi.nets.faceRecognitionNet.loadFromUri(LOCAL_URL)
                        ]);
                        this.isFaceModelLoaded = true;
                    } catch (e) {
                        try {
                            const CDN_URL = 'https://cdn.jsdelivr.net/gh/cgarciagl/face-api.js@0.22.2/weights';
                            await Promise.all([
                                faceapi.nets.tinyFaceDetector.loadFromUri(CDN_URL),
                                faceapi.nets.faceLandmark68TinyNet.loadFromUri(CDN_URL),
                                faceapi.nets.faceRecognitionNet.loadFromUri(CDN_URL)
                            ]);
                            this.isFaceModelLoaded = true;
                        } catch (err2) {
                            console.warn("Model face-api belum dapat dimuat:", err2);
                        }
                    }
                },

                async loadStudentDescriptors() {
                    try {
                        const res = await fetch("{{ route('kiosk.api.descriptors') }}");
                        this.registeredStudents = await res.json();

                        if (this.registeredStudents.length > 0 && typeof faceapi !== 'undefined') {
                            const labeled = [];
                            this.registeredStudents.forEach(s => {
                                if (s.face_descriptor) {
                                    try {
                                        const desc = new Float32Array(JSON.parse(s.face_descriptor));
                                        if (desc.length === 128) {
                                            labeled.push(new faceapi.LabeledFaceDescriptors(String(s.id), [desc]));
                                        }
                                    } catch (e) {}
                                }
                            });
                            if (labeled.length > 0) {
                                // Threshold 0.62 for reliable recognition across varied webcam lighting
                                this.faceMatcher = new faceapi.FaceMatcher(labeled, 0.62);
                            }
                        }
                    } catch (e) {
                        console.warn("Gagal memuat descriptor siswa:", e);
                    }
                },

                switchMode(mode) {
                    this.scanMode = mode;
                    if (mode === 'qr') {
                        this.stopFaceCamera();
                        this.startQRScanner();
                    } else {
                        this.stopQRScanner();
                        this.startFaceCamera();
                    }
                },

                // 1. ULTRA-FAST HARDWARE-ACCELERATED QR SCANNER
                startQRScanner() {
                    this.cameraStatus = 'Pemindai QR Code Aktif';
                    if (this.html5QrScanner) {
                        try { this.html5QrScanner.stop(); } catch(e) {}
                    }

                    setTimeout(() => {
                        // Restrict scanning specifically to QR Code for 10x faster frame parsing
                        const formatsToSupport = [
                            Html5QrcodeSupportedFormats.QR_CODE
                        ];

                        this.html5QrScanner = new Html5Qrcode("qrReader", {
                            formatsToSupport: formatsToSupport,
                            experimentalFeatures: {
                                useBarCodeDetectorIfSupported: true
                            },
                            verbose: false
                        });

                        const qrboxFunction = function(viewfinderWidth, viewfinderHeight) {
                            const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                            const qrboxEdgeSize = Math.floor(minEdge * 0.75);
                            return {
                                width: qrboxEdgeSize,
                                height: qrboxEdgeSize
                            };
                        };

                        this.html5QrScanner.start(
                            { facingMode: "user" },
                            {
                                fps: 30, // 30 FPS for instant frame detection
                                qrbox: qrboxFunction,
                                aspectRatio: 1.333334
                            },
                            (decodedText, decodedResult) => {
                                if (this.isThrottled || this.showResultModal) return;
                                this.isThrottled = true;
                                this.playBeepSound();
                                
                                this.processAttendance("{{ route('kiosk.api.scan-barcode') }}", {
                                    barcode_token: decodedText.trim()
                                }).finally(() => {
                                    // Rapid cooldown (1.5 seconds) for quick line scanning
                                    setTimeout(() => { this.isThrottled = false; }, 1500);
                                });
                            },
                            (errorMessage) => {
                                // Scanning frames in progress
                            }
                        ).catch(err => {
                            this.cameraStatus = 'Gunakan Scanner USB / Ketik Token';
                        });
                    }, 250);
                },

                stopQRScanner() {
                    if (this.html5QrScanner) {
                        try {
                            this.html5QrScanner.stop().then(() => {
                                this.html5QrScanner.clear();
                            }).catch(() => {});
                        } catch (e) {}
                    }
                },

                // 2. REAL-TIME AI FACE SCANNER
                async startFaceCamera() {
                    this.cameraStatus = 'Mengaktifkan AI Pengenal Wajah...';
                    this.faceStatusText = 'Memuat AI Wajah...';
                    const video = document.getElementById('faceCamera');

                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({
                            video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' }
                        });
                        video.srcObject = stream;
                        await new Promise(r => video.onloadedmetadata = r);
                        video.play();

                        // Ensure models are loaded
                        if (!this.isFaceModelLoaded) {
                            await this.preloadFaceModels();
                        }

                        // Re-verify descriptors
                        if (!this.faceMatcher) {
                            await this.loadStudentDescriptors();
                        }

                        this.cameraStatus = 'Kamera Wajah Aktif & Siap';
                        this.faceStatusText = 'Posisikan wajah Anda tepat di dalam lingkaran panduan';
                        this.faceMatchState = 'searching';
                        this.runFaceDetectionLoop(video);
                    } catch (err) {
                        this.cameraStatus = 'Kamera Wajah Offline';
                        this.faceStatusText = 'Kamera tidak terdeteksi. Gunakan Mode QR Code.';
                    }
                },

                runFaceDetectionLoop(video) {
                    this.isScanningFaceLoop = true;
                    const loop = async () => {
                        if (!this.isScanningFaceLoop || this.scanMode !== 'face') return;

                        if (!this.isThrottled && !this.showResultModal && !video.paused && !video.ended) {
                            try {
                                const detection = await faceapi
                                    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.45 }))
                                    .withFaceLandmarks(true)
                                    .withFaceDescriptor();

                                if (detection) {
                                    this.faceDetected = true;
                                    
                                    if (this.faceMatcher) {
                                        const bestMatch = this.faceMatcher.findBestMatch(detection.descriptor);
                                        
                                        if (bestMatch.label !== 'unknown') {
                                            const studentId = Number(bestMatch.label);
                                            const matchedStudent = this.registeredStudents.find(s => s.id === studentId);
                                            const studentName = matchedStudent ? matchedStudent.name : 'Siswa';
                                            
                                            this.faceMatchState = 'matched';
                                            this.faceStatusText = `✓ Dikenali: ${studentName}`;
                                            
                                            this.isThrottled = true;
                                            this.playBeepSound();
                                            await this.processAttendance("{{ route('kiosk.api.scan-face') }}", {
                                                student_id: studentId
                                            });
                                            
                                            setTimeout(() => { 
                                                this.isThrottled = false; 
                                                this.faceMatchState = 'searching';
                                                this.faceStatusText = 'Posisikan wajah Anda tepat di dalam lingkaran panduan';
                                            }, 2000);
                                        } else {
                                            // Face detected but unindexed
                                            this.faceMatchState = 'unknown';
                                            this.faceStatusText = 'Wajah terdeteksi, namun belum terdaftar di sistem';
                                        }
                                    } else {
                                        this.faceMatchState = 'unknown';
                                        this.faceStatusText = 'Wajah terdeteksi (Belum ada data biometrik siswa)';
                                    }
                                } else {
                                    this.faceDetected = false;
                                    this.faceMatchState = 'searching';
                                    this.faceStatusText = 'Posisikan wajah Anda tepat di dalam lingkaran panduan';
                                }
                            } catch (e) {
                                // Frame skip
                            }
                        }

                        if (this.isScanningFaceLoop) {
                            requestAnimationFrame(loop);
                        }
                    };
                    loop();
                },

                stopFaceCamera() {
                    this.isScanningFaceLoop = false;
                    const video = document.getElementById('faceCamera');
                    if (video && video.srcObject) {
                        video.srcObject.getTracks().forEach(track => track.stop());
                        video.srcObject = null;
                    }
                },

                playBeepSound() {
                    try {
                        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = audioCtx.createOscillator();
                        const gain = audioCtx.createGain();
                        osc.connect(gain);
                        gain.connect(audioCtx.destination);
                        osc.frequency.value = 1046.5; // C6 high clear chime
                        gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.12);
                        osc.start();
                        osc.stop(audioCtx.currentTime + 0.12);
                    } catch (e) {}
                },

                async submitManualCode() {
                    const token = this.manualCode.trim();
                    if (!token) return;

                    this.manualCode = '';
                    this.playBeepSound();
                    await this.processAttendance("{{ route('kiosk.api.scan-barcode') }}", { barcode_token: token });
                },

                async testScanFace(studentId) {
                    this.playBeepSound();
                    await this.processAttendance("{{ route('kiosk.api.scan-face') }}", { student_id: studentId });
                },

                async testScanQR(token) {
                    this.playBeepSound();
                    await this.processAttendance("{{ route('kiosk.api.scan-barcode') }}", { barcode_token: token });
                },

                async processAttendance(url, payload) {
                    try {
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await res.json();
                        this.resultData = data;
                        this.showResultModal = true;

                        if (this.voiceEnabled && 'speechSynthesis' in window) {
                            window.speechSynthesis.cancel();
                            const utterance = new SpeechSynthesisUtterance(data.message);
                            utterance.lang = 'id-ID';
                            utterance.rate = 1.05;
                            window.speechSynthesis.speak(utterance);
                        }

                        clearTimeout(this.modalTimer);
                        this.modalTimer = setTimeout(() => {
                            this.showResultModal = false;
                        }, 2200); // 2.2 seconds fast dismiss for smooth flow

                    } catch (err) {
                        this.resultData = {
                            success: false,
                            message: 'Gagal menghubungi server presensi.'
                        };
                        this.showResultModal = true;
                    }
                }
            }
        }
    </script>
</body>
</html>
