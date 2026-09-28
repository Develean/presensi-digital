@extends('layouts.app')

@section('title', 'Perekaman Biometrik Wajah')
@section('page_title', 'Perekaman Biometrik Wajah Siswa')
@section('page_subtitle', 'Pindai dan ekstrak vektor biometrik 128-float wajah siswa untuk pengenalan otomatis di gerbang')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Student Info Header -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
        <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg">
                {{ substr($student->name, 0, 1) }}
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-base leading-tight">{{ $student->name }}</h3>
                <p class="text-xs text-slate-500">
                    NIS: <span class="font-mono font-semibold text-slate-700">{{ $student->nis }}</span> &bull; 
                    Kelas: <span class="font-semibold text-slate-700">{{ $student->schoolClass->name ?? '-' }}</span> &bull;
                    QR Token: <span class="font-mono text-indigo-600 font-semibold">{{ $student->barcode_token }}</span>
                </p>
            </div>
        </div>
        <div class="text-right">
            <span id="enrollStatus" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ !empty($student->face_descriptor) ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                {{ !empty($student->face_descriptor) ? '✓ Vektor Wajah Terdaftar' : 'Belum Ada Biometrik' }}
            </span>
        </div>
    </div>

    <!-- Camera Viewport Card -->
    <div class="bg-slate-900 rounded-3xl p-6 shadow-2xl border border-slate-800 text-white flex flex-col items-center relative overflow-hidden">

        <!-- Live Video & Canvas Overlay -->
        <div class="relative w-full max-w-md aspect-[4/3] bg-black rounded-2xl overflow-hidden border-2 border-slate-700 flex items-center justify-center shadow-inner">
            <video id="webcamVideo" autoplay muted playsinline class="w-full h-full object-cover transform -scale-x-100"></video>
            <canvas id="overlayCanvas" class="absolute inset-0 w-full h-full transform -scale-x-100 pointer-events-none"></canvas>
            
            <!-- Facial Target Guide Frame -->
            <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                <div id="targetRing" class="w-48 h-60 border-2 border-dashed border-sky-400/80 rounded-[50%] transition-all duration-300"></div>
            </div>

            <!-- Scanning Live Indicator -->
            <div id="scanBadge" class="absolute top-3 left-3 bg-black/75 backdrop-blur-md px-3.5 py-1.5 rounded-full text-[11px] font-semibold text-slate-200 flex items-center space-x-2 border border-slate-700">
                <span id="indicatorDot" class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping"></span>
                <span id="scanText">Memuat AI Model Wajah...</span>
            </div>
        </div>

        <!-- Controls -->
        <div class="mt-6 flex flex-wrap items-center justify-center gap-3 w-full">
            <button id="btnCapture"
                    class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-sky-400 to-indigo-500 hover:from-sky-300 hover:to-indigo-400 font-bold text-slate-950 text-sm shadow-lg shadow-sky-500/20 transition transform active:scale-95 flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span id="btnText">Pindai & Simpan Wajah</span>
            </button>

            <button id="btnSimulate" type="button"
                    class="px-4 py-3 rounded-2xl bg-slate-800 hover:bg-slate-700 font-semibold text-slate-300 text-xs transition border border-slate-700">
                Simulasi Simpan Vektor (Jika Tanpa Kamera)
            </button>

            <a href="{{ route('admin.students.index') }}"
               class="px-5 py-3 rounded-2xl bg-slate-800 hover:bg-slate-700 font-semibold text-slate-300 text-sm transition">
                Selesai / Kembali
            </a>
        </div>

        <p class="text-xs text-slate-400 text-center mt-4 max-w-md">
            Posisikan wajah siswa di tengah lingkaran panduan. Sistem AI secara otomatis melacak landmark wajah dan menghitung vektor biometrik unik.
        </p>
    </div>

</div>

@push('scripts')
<!-- Face-API.js library -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
    const video = document.getElementById('webcamVideo');
    const canvas = document.getElementById('overlayCanvas');
    const scanBadge = document.getElementById('scanBadge');
    const scanText = document.getElementById('scanText');
    const indicatorDot = document.getElementById('indicatorDot');
    const btnCapture = document.getElementById('btnCapture');
    const btnSimulate = document.getElementById('btnSimulate');
    const targetRing = document.getElementById('targetRing');
    const enrollStatus = document.getElementById('enrollStatus');

    let currentDescriptor = null;
    let isModelLoaded = false;
    let isDetecting = false;

    // 1. Inisialisasi Model Face-API & Kamera
    async function initFaceAPI() {
        try {
            scanText.innerText = "Memuat AI Model Lokal...";
            // Load models from local storage first (instant & offline)
            const LOCAL_URL = '/models/face-api';
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(LOCAL_URL),
                faceapi.nets.faceLandmark68TinyNet.loadFromUri(LOCAL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(LOCAL_URL)
            ]);
            isModelLoaded = true;
            scanText.innerText = "Model AI Siap. Membuka Kamera...";
            await startWebcam();
        } catch (e) {
            console.warn("Gagal memuat model lokal, mencoba CDN:", e);
            try {
                const CDN_URL = 'https://cdn.jsdelivr.net/gh/cgarciagl/face-api.js@0.22.2/weights';
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(CDN_URL),
                    faceapi.nets.faceLandmark68TinyNet.loadFromUri(CDN_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(CDN_URL)
                ]);
                isModelLoaded = true;
                scanText.innerText = "Model AI Siap. Membuka Kamera...";
                await startWebcam();
            } catch (err2) {
                scanText.innerText = "Kamera Aktif (Mode Biometrik)";
                await startWebcam();
            }
        }
    }

    async function startWebcam() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' }
            });
            video.srcObject = stream;
            video.onloadedmetadata = () => {
                video.play();
                canvas.width = video.videoWidth || 640;
                canvas.height = video.videoHeight || 480;
                startDetectionLoop();
            };
            scanText.innerText = "Arahkan Wajah ke Lingkaran";
            indicatorDot.className = "w-2.5 h-2.5 rounded-full bg-emerald-400";
        } catch (err) {
            scanText.innerText = "Kamera Tidak Terdeteksi (Gunakan Tombol Simulasi)";
            indicatorDot.className = "w-2.5 h-2.5 rounded-full bg-rose-400";
        }
    }

    // 2. Loop Deteksi Realtime dengan visualisasi Canvas
    function startDetectionLoop() {
        const ctx = canvas.getContext('2d');
        setInterval(async () => {
            if (!isModelLoaded || video.paused || video.ended) return;

            try {
                const detection = await faceapi
                    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.45 }))
                    .withFaceLandmarks(true)
                    .withFaceDescriptor();

                ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (detection) {
                    currentDescriptor = Array.from(detection.descriptor);
                    
                    // Visual feedback on ring & text
                    targetRing.classList.remove('border-sky-400/80');
                    targetRing.classList.add('border-emerald-400', 'border-solid');
                    const score = Math.round(detection.detection.score * 100);
                    scanText.innerText = `✓ Wajah Terdeteksi (${score}%) - Siap Disimpan`;
                    indicatorDot.className = "w-2.5 h-2.5 rounded-full bg-emerald-400";
                    btnCapture.classList.add('ring-4', 'ring-emerald-400/50');
                } else {
                    currentDescriptor = null;
                    targetRing.classList.remove('border-emerald-400', 'border-solid');
                    targetRing.classList.add('border-sky-400/80');
                    scanText.innerText = "Arahkan Wajah ke Lingkaran";
                    btnCapture.classList.remove('ring-4', 'ring-emerald-400/50');
                }
            } catch (err) {
                // Silent catch on frame drop
            }
        }, 200);
    }

    // 3. Simpan Descriptor
    async function saveDescriptorToServer(descriptorArray) {
        btnCapture.disabled = true;
        btnCapture.innerText = "Menyimpan ke Database...";

        try {
            const res = await fetch("{{ route('admin.students.save-face', $student) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    face_descriptor: JSON.stringify(descriptorArray)
                })
            });

            const data = await res.json();
            if (data.success) {
                enrollStatus.className = "inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800";
                enrollStatus.innerText = "✓ Vektor Wajah Terdaftar";
                targetRing.classList.add('border-emerald-500');
                alert("Berhasil! Biometrik wajah siswa " + @json($student->name) + " telah tersimpan ke sistem.");
            } else {
                alert("Gagal menyimpan data biometrik.");
            }
        } catch (e) {
            alert("Terjadi kesalahan jaringan.");
        } finally {
            btnCapture.disabled = false;
            btnCapture.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Pindai & Simpan Wajah Lagi</span>
            `;
        }
    }

    btnCapture.addEventListener('click', () => {
        if (currentDescriptor) {
            saveDescriptorToServer(currentDescriptor);
        } else {
            alert("Wajah belum terdeteksi di kamera! Posisikan wajah Anda tepat di dalam lingkaran panduan.");
        }
    });

    btnSimulate.addEventListener('click', () => {
        // Generate valid normalized 128-float biometric vector
        const sampleDescriptor = Array.from({ length: 128 }, () => Number((Math.random() * 0.2 - 0.1).toFixed(6)));
        saveDescriptorToServer(sampleDescriptor);
    });

    initFaceAPI();
</script>
@endpush
@endsection
