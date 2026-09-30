<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Presensi Mandiri Siswa - Wi-Fi Sekolah</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        @keyframes pulse-subtle {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.95; transform: scale(1.015); }
        }
        .btn-pulse {
            animation: pulse-subtle 2.5s infinite ease-in-out;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between selection:bg-indigo-500 selection:text-white">

    <!-- Top Navigation / Header Bar -->
    <header class="bg-slate-900/90 border-b border-slate-800/80 backdrop-blur-md sticky top-0 z-20 px-4 py-3">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-sm font-bold text-white tracking-tight leading-tight">Presensi Mandiri</h1>
                    <p class="text-[11px] text-slate-400">Portal Siswa &bull; Wi-Fi Sekolah</p>
                </div>
            </div>

            <!-- Wi-Fi Status Badge -->
            <div class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>Wi-Fi Terhubung</span>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="max-w-md mx-auto w-full px-4 py-5 flex-1 flex flex-col space-y-4">

        <!-- Live Clock & Schedule Card -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-b from-slate-900 to-slate-900/90 border border-slate-800 p-5 shadow-xl">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="flex items-center justify-between mb-2">
                <span id="dateDisplay" class="text-xs text-slate-400 font-medium">
                    {{ $schedule['server_date_display'] ?? now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                </span>
                <span id="ruleBadge" class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-indigo-950 text-indigo-300 border border-indigo-800/50">
                    {{ $schedule['rule_name'] ?? 'Jadwal Reguler' }}
                </span>
            </div>

            <!-- Jam Real-Time -->
            <div class="flex items-baseline space-x-2 my-2">
                <div id="liveClock" class="text-4xl font-extrabold tracking-tight font-mono text-white">
                    {{ substr($schedule['server_time'] ?? now()->format('H:i:s'), 0, 5) }}
                </div>
                <div class="text-sm font-semibold text-slate-400">WIB</div>
            </div>

            <!-- Status Banner Jadwal Operasional -->
            <div id="scheduleBanner" class="mt-3 p-3 rounded-2xl bg-slate-950/80 border border-slate-800/80 flex items-center space-x-3">
                <div id="scheduleIcon" class="w-8 h-8 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="text-xs">
                    <div id="scheduleStatusText" class="font-bold text-slate-200">
                        {{ $schedule['message'] ?? 'Memeriksa jadwal...' }}
                    </div>
                    <div id="scheduleDetailText" class="text-slate-400 text-[11px] mt-0.5">
                        @if(isset($schedule['in_start']))
                            Masuk: {{ $schedule['in_start'] }} - {{ $schedule['in_late_limit'] }} | Pulang: {{ $schedule['out_start'] }} - {{ $schedule['out_end'] }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Identification Card -->
        <div class="bg-slate-900 rounded-3xl border border-slate-800 p-5 shadow-lg space-y-4">
            <div class="flex items-center justify-between">
                <label class="text-xs font-bold text-slate-300 uppercase tracking-wider">Identitas Siswa</label>
                <button type="button" id="btnChangeNis" class="hidden text-xs text-indigo-400 hover:text-indigo-300 font-semibold">
                    Ganti NIS
                </button>
            </div>

            <!-- NIS Form Input -->
            <div id="nisInputWrapper" class="space-y-3">
                <div class="relative">
                    <input type="text" id="nisInput" placeholder="Masukkan NIS Anda (contoh: 2026001)"
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-2xl px-4 py-3.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-mono"
                           autocomplete="off" inputmode="numeric">
                </div>
                <div class="flex items-center justify-between text-xs text-slate-400 px-1">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" id="rememberNis" checked class="rounded border-slate-700 bg-slate-800 text-indigo-600 focus:ring-0">
                        <span>Ingat saya di HP ini</span>
                    </label>
                    <button type="button" id="btnVerifyNis" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-indigo-300 font-semibold transition">
                        Verifikasi
                    </button>
                </div>
            </div>

            <!-- Profile Verified Card (Hidden until NIS entered) -->
            <div id="studentProfileCard" class="hidden bg-slate-950/70 border border-slate-800/80 rounded-2xl p-4 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <div id="studentName" class="font-bold text-white text-base leading-tight">Nama Siswa</div>
                        <div class="text-xs text-slate-400 mt-0.5">
                            NIS: <span id="studentNisBadge" class="font-mono text-slate-200"></span> &bull; 
                            Kelas: <span id="studentClassBadge" class="font-semibold text-indigo-400"></span>
                        </div>
                    </div>
                    <span id="deviceLockBadge" class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-sky-950 border border-sky-800 text-sky-300">
                        HP Terdaftar
                    </span>
                </div>

                <!-- Today's Attendance Summary -->
                <div id="attendanceSummaryCard" class="border-t border-slate-800/80 pt-3 grid grid-cols-2 gap-2 text-xs">
                    <div class="bg-slate-900/80 p-2.5 rounded-xl border border-slate-800/50">
                        <div class="text-[10px] text-slate-400 uppercase">Presensi Masuk</div>
                        <div id="summaryTimeIn" class="font-bold text-slate-200 mt-0.5 font-mono">--:--</div>
                        <div id="summaryStatusIn" class="text-[10px] text-slate-500 font-semibold">Belum Masuk</div>
                    </div>
                    <div class="bg-slate-900/80 p-2.5 rounded-xl border border-slate-800/50">
                        <div class="text-[10px] text-slate-400 uppercase">Presensi Pulang</div>
                        <div id="summaryTimeOut" class="font-bold text-slate-200 mt-0.5 font-mono">--:--</div>
                        <div id="summaryStatusOut" class="text-[10px] text-slate-500 font-semibold">Belum Pulang</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Big Attendance Action Button -->
        <div class="pt-2">
            <button id="btnSubmitAttendance" disabled
                    class="w-full py-4 px-6 rounded-2xl font-bold text-base shadow-xl flex items-center justify-center space-x-2 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed bg-slate-800 text-slate-400">
                <span id="btnIcon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </span>
                <span id="btnText">Menunggu Verifikasi NIS...</span>
            </button>
            <div id="actionHint" class="text-center text-xs text-slate-500 mt-2">
                Masukkan NIS Anda terlebih dahulu untuk mengaktifkan tombol.
            </div>
        </div>

        <!-- Alert / Feedback Notification Box -->
        <div id="alertBox" class="hidden rounded-2xl p-4 border text-sm transition-all duration-300"></div>

    </main>

    <!-- Footer Security Notice -->
    <footer class="max-w-md mx-auto w-full px-4 py-3 text-center text-[11px] text-slate-500">
        <p>Dilindungi verifikasi Subnet Wi-Fi Sekolah & Device Lock &bull; Anti-titip absen</p>
    </footer>

    <!-- Audio Element for Success Sound -->
    <audio id="beepAudio" preload="auto">
        <source src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" type="audio/mpeg">
    </audio>

    <script>
        // State variables
        let currentSchedule = @json($schedule);
        let verifiedStudent = null;
        let deviceId = getOrGenerateDeviceId();
        let deviceName = detectDeviceName();

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Elements
        const nisInput = document.getElementById('nisInput');
        const rememberNis = document.getElementById('rememberNis');
        const btnVerifyNis = document.getElementById('btnVerifyNis');
        const btnChangeNis = document.getElementById('btnChangeNis');
        const nisInputWrapper = document.getElementById('nisInputWrapper');
        const studentProfileCard = document.getElementById('studentProfileCard');
        const studentName = document.getElementById('studentName');
        const studentNisBadge = document.getElementById('studentNisBadge');
        const studentClassBadge = document.getElementById('studentClassBadge');
        const deviceLockBadge = document.getElementById('deviceLockBadge');
        const summaryTimeIn = document.getElementById('summaryTimeIn');
        const summaryStatusIn = document.getElementById('summaryStatusIn');
        const summaryTimeOut = document.getElementById('summaryTimeOut');
        const summaryStatusOut = document.getElementById('summaryStatusOut');
        const btnSubmit = document.getElementById('btnSubmitAttendance');
        const btnText = document.getElementById('btnText');
        const btnIcon = document.getElementById('btnIcon');
        const actionHint = document.getElementById('actionHint');
        const alertBox = document.getElementById('alertBox');
        const liveClock = document.getElementById('liveClock');
        const scheduleStatusText = document.getElementById('scheduleStatusText');
        const beepAudio = document.getElementById('beepAudio');

        // Initialize Local Device ID
        function getOrGenerateDeviceId() {
            let id = localStorage.getItem('presensi_device_uuid');
            if (!id) {
                id = 'DEV-' + ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
                    (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
                );
                localStorage.setItem('presensi_device_uuid', id);
            }
            return id;
        }

        function detectDeviceName() {
            const ua = navigator.userAgent;
            if (/android/i.test(ua)) return "Android Phone";
            if (/iPhone|iPad|iPod/i.test(ua)) return "Apple iPhone/iPad";
            if (/Windows/i.test(ua)) return "Windows PC/Laptop";
            return "Perangkat Siswa";
        }

        // Live Clock Tick
        setInterval(() => {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            liveClock.textContent = `${hours}:${minutes}`;
        }, 1000);

        // Auto Load Remembered NIS
        document.addEventListener('DOMContentLoaded', () => {
            const savedNis = localStorage.getItem('presensi_saved_nis');
            if (savedNis) {
                nisInput.value = savedNis;
                verifyNis(savedNis);
            }
        });

        // Event Listeners
        btnVerifyNis.addEventListener('click', () => {
            const nis = nisInput.value.trim();
            if (nis) verifyNis(nis);
        });

        nisInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                const nis = nisInput.value.trim();
                if (nis) verifyNis(nis);
            }
        });

        btnChangeNis.addEventListener('click', () => {
            studentProfileCard.classList.add('hidden');
            nisInputWrapper.classList.remove('hidden');
            btnChangeNis.classList.add('hidden');
            verifiedStudent = null;
            updateActionButton();
        });

        btnSubmit.addEventListener('click', submitAttendance);

        // Verify Student NIS via API
        async function verifyNis(nis) {
            btnVerifyNis.disabled = true;
            btnVerifyNis.textContent = 'Mengecek...';
            hideAlert();

            try {
                const res = await fetch('/api/presensi/status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ nis, device_id: deviceId })
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    showAlert(data.message || 'NIS tidak ditemukan.', 'error');
                    return;
                }

                verifiedStudent = data.student;
                currentSchedule = data.schedule;

                if (rememberNis.checked) {
                    localStorage.setItem('presensi_saved_nis', nis);
                } else {
                    localStorage.removeItem('presensi_saved_nis');
                }

                // Render Profile Card
                studentName.textContent = verifiedStudent.name;
                studentNisBadge.textContent = verifiedStudent.nis;
                studentClassBadge.textContent = verifiedStudent.class_name;

                // Device Match check
                if (!data.device_match) {
                    deviceLockBadge.className = 'text-[10px] font-semibold px-2 py-0.5 rounded-full bg-rose-950 border border-rose-800 text-rose-300';
                    deviceLockBadge.textContent = 'HP Berbeda!';
                    showAlert(`Akun ini sudah terikat ke HP lain (${verifiedStudent.locked_device_name}). Hubungi Guru Piket jika Anda mengganti perangkat.`, 'error');
                } else if (data.device_status === 'first_time') {
                    deviceLockBadge.className = 'text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-950 border border-amber-800 text-amber-300';
                    deviceLockBadge.textContent = 'HP Baru (Akan Dikunci)';
                } else {
                    deviceLockBadge.className = 'text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-950 border border-emerald-800 text-emerald-300';
                    deviceLockBadge.textContent = 'HP Cocok';
                }

                // Attendance Today
                if (data.attendance_today) {
                    summaryTimeIn.textContent = data.attendance_today.time_in ? data.attendance_today.time_in + ' WIB' : '--:--';
                    summaryStatusIn.textContent = data.attendance_today.status_in ? (data.attendance_today.status_in === 'hadir' ? 'Tepat Waktu' : 'Terlambat') : 'Belum';
                    summaryStatusIn.className = data.attendance_today.status_in === 'hadir' ? 'text-[10px] text-emerald-400 font-semibold' : 'text-[10px] text-amber-400 font-semibold';

                    summaryTimeOut.textContent = data.attendance_today.time_out ? data.attendance_today.time_out + ' WIB' : '--:--';
                    summaryStatusOut.textContent = data.attendance_today.status_out === 'pulang_normal' ? 'Sudah Pulang' : 'Belum';
                    summaryStatusOut.className = data.attendance_today.status_out === 'pulang_normal' ? 'text-[10px] text-indigo-400 font-semibold' : 'text-[10px] text-slate-500 font-semibold';
                } else {
                    summaryTimeIn.textContent = '--:--';
                    summaryStatusIn.textContent = 'Belum Masuk';
                    summaryStatusIn.className = 'text-[10px] text-slate-500 font-semibold';
                    summaryTimeOut.textContent = '--:--';
                    summaryStatusOut.textContent = 'Belum Pulang';
                    summaryStatusOut.className = 'text-[10px] text-slate-500 font-semibold';
                }

                // UI Flip
                nisInputWrapper.classList.add('hidden');
                studentProfileCard.classList.remove('hidden');
                btnChangeNis.classList.remove('hidden');

                updateActionButton(data);

            } catch (err) {
                showAlert('Gagal menghubungi server. Pastikan terhubung ke Wi-Fi sekolah.', 'error');
            } finally {
                btnVerifyNis.disabled = false;
                btnVerifyNis.textContent = 'Verifikasi';
            }
        }

        // Action Button State Management
        function updateActionButton(data = null) {
            if (!verifiedStudent) {
                btnSubmit.disabled = true;
                btnSubmit.className = 'w-full py-4 px-6 rounded-2xl font-bold text-base shadow-xl flex items-center justify-center space-x-2 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed bg-slate-800 text-slate-400';
                btnText.textContent = 'Menunggu Verifikasi NIS...';
                actionHint.textContent = 'Masukkan NIS Anda terlebih dahulu untuk mengaktifkan tombol.';
                return;
            }

            if (data && !data.device_match) {
                btnSubmit.disabled = true;
                btnSubmit.className = 'w-full py-4 px-6 rounded-2xl font-bold text-base shadow-xl flex items-center justify-center space-x-2 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed bg-rose-950 text-rose-300 border border-rose-800';
                btnText.textContent = 'Perangkat Tidak Cocok';
                actionHint.textContent = 'Akun terikat di HP lain. Lapor ke Guru Piket untuk reset kaitan HP.';
                return;
            }

            const att = data ? data.attendance_today : null;

            // Scenario 1: Presensi Masuk Buka
            if (currentSchedule.can_in) {
                if (att && att.time_in) {
                    btnSubmit.disabled = true;
                    btnSubmit.className = 'w-full py-4 px-6 rounded-2xl font-bold text-base shadow-xl flex items-center justify-center space-x-2 bg-slate-800 text-slate-400';
                    btnText.textContent = 'Sudah Presensi Masuk';
                    actionHint.textContent = `Anda telah tercatat masuk pukul ${att.time_in} WIB.`;
                } else {
                    btnSubmit.disabled = false;
                    btnSubmit.className = 'w-full py-4 px-6 rounded-2xl font-bold text-base shadow-xl flex items-center justify-center space-x-2 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white btn-pulse shadow-emerald-500/30';
                    btnText.textContent = 'PRESENSI MASUK SEKARANG';
                    actionHint.textContent = 'Tekan tombol di atas untuk mencatat kehadiran.';
                }
                return;
            }

            // Scenario 2: Presensi Pulang Buka
            if (currentSchedule.can_out) {
                if (att && att.time_out) {
                    btnSubmit.disabled = true;
                    btnSubmit.className = 'w-full py-4 px-6 rounded-2xl font-bold text-base shadow-xl flex items-center justify-center space-x-2 bg-slate-800 text-slate-400';
                    btnText.textContent = 'Sudah Presensi Pulang';
                    actionHint.textContent = 'Presensi hari ini telah lengkap. Hati-hati di jalan!';
                } else {
                    btnSubmit.disabled = false;
                    btnSubmit.className = 'w-full py-4 px-6 rounded-2xl font-bold text-base shadow-xl flex items-center justify-center space-x-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white btn-pulse shadow-indigo-500/30';
                    btnText.textContent = 'PRESENSI PULANG SEKARANG';
                    actionHint.textContent = 'Tekan tombol di atas untuk presensi kepulangan.';
                }
                return;
            }

            // Scenario 3: Di Luar Jam Operasional
            btnSubmit.disabled = true;
            btnSubmit.className = 'w-full py-4 px-6 rounded-2xl font-bold text-base shadow-xl flex items-center justify-center space-x-2 bg-slate-900 border border-slate-800 text-slate-400';
            btnText.textContent = 'Presensi Saat Ini Ditutup';
            actionHint.textContent = currentSchedule.message || 'Presensi hanya dibuka pada jam operasional sekolah.';
        }

        // Submit Attendance Action
        async function submitAttendance() {
            if (!verifiedStudent) return;

            btnSubmit.disabled = true;
            const originalText = btnText.textContent;
            btnText.textContent = 'Memproses Presensi...';
            hideAlert();

            try {
                const res = await fetch('/api/presensi/submit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        nis: verifiedStudent.nis,
                        device_id: deviceId,
                        device_name: deviceName,
                    })
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    showAlert(data.message || 'Gagal memproses presensi.', 'error');
                    btnSubmit.disabled = false;
                    btnText.textContent = originalText;
                    return;
                }

                // Play sound & vibration
                try {
                    beepAudio.play().catch(() => {});
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                } catch(e) {}

                showAlert(data.message, 'success');

                // Refresh Status
                setTimeout(() => {
                    verifyNis(verifiedStudent.nis);
                }, 1200);

            } catch (err) {
                showAlert('Gagal terhubung ke server. Periksa koneksi Wi-Fi Anda.', 'error');
                btnSubmit.disabled = false;
                btnText.textContent = originalText;
            }
        }

        function showAlert(msg, type) {
            alertBox.classList.remove('hidden', 'bg-rose-950/80', 'border-rose-800', 'text-rose-200', 'bg-emerald-950/80', 'border-emerald-800', 'text-emerald-200');
            if (type === 'error') {
                alertBox.classList.add('bg-rose-950/80', 'border-rose-800', 'text-rose-200');
            } else {
                alertBox.classList.add('bg-emerald-950/80', 'border-emerald-800', 'text-emerald-200');
            }
            alertBox.innerHTML = `
                <div class="flex items-start space-x-2">
                    <span class="font-bold text-xs uppercase">${type === 'error' ? 'Peringatan:' : 'Sukses:'}</span>
                    <span>${msg}</span>
                </div>
            `;
        }

        function hideAlert() {
            alertBox.classList.add('hidden');
        }
    </script>
</body>
</html>
