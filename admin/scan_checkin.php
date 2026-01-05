<?php
session_start();

// Cek sesi login dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'penyelenggara') {
    header("Location: login.php");
    exit();
}

// Ambil info event (opsional, untuk judul halaman)
require_once '../core/koneksi.php';
$event_id = $_GET['event_id'] ?? 0;
$event_judul = 'Scan Check-in';
if ($event_id) {
    $stmt = $pdo->prepare("SELECT judul FROM workshops WHERE id = ?");
    $stmt->execute([$event_id]);
    $judul = $stmt->fetchColumn();
    if ($judul) {
        $event_judul = "Check-in: " . htmlspecialchars($judul);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $event_judul ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .scanner-frame {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
        }

        .scanner-frame::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 2px solid rgba(59, 130, 246, 0.3);
            border-radius: 12px;
            animation: pulse-border 2s infinite;
            z-index: 10;
            pointer-events: none;
        }

        .scanner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.2), transparent);
            animation: scan-beam 2s infinite;
            z-index: 5;
            pointer-events: none;
        }

        @keyframes pulse-border {

            0%,
            100% {
                border-color: rgba(59, 130, 246, 0.3);
            }

            50% {
                border-color: rgba(59, 130, 246, 0.7);
            }
        }

        @keyframes scan-beam {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .status-success {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .status-error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
        }

        .floating-icon {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        #qr-reader {
            width: 100% !important;
        }

        #qr-reader__dashboard {
            display: none;
        }

        .camera-permission {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>

<body
    class="bg-gradient-to-br from-blue-50 via-indigo-50 to-cyan-100 flex flex-col items-center justify-center min-h-screen p-4">

    <!-- Header dengan logo dan judul -->
    <div class="text-center mb-8">
        <div
            class="floating-icon inline-flex items-center justify-center w-16 h-16 bg-white rounded-full shadow-lg mb-4">
            <i class="fas fa-qrcode text-2xl text-blue-600"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Event Check-in System</h1>
        <p class="text-gray-600 max-w-md">Scan QR Code tiket peserta untuk proses check-in yang cepat dan efisien</p>
    </div>

    <div class="w-full max-w-md mx-auto card-hover">
        <!-- Card utama dengan gradient biru -->
        <div class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 rounded-3xl shadow-2xl overflow-hidden">
            <!-- Header card -->
            <div class="p-6 text-white text-center relative overflow-hidden">
                <div class="absolute inset-0 bg-blue-500 opacity-10"></div>
                <div class="relative z-10">
                    <h2 class="text-xl font-bold mb-2 flex items-center justify-center">
                        <i class="fas fa-ticket-alt mr-3"></i> <?= $event_judul ?>
                    </h2>
                    <p class="text-blue-100 text-sm opacity-90">Arahkan kamera ke QR Code untuk memindai</p>
                </div>
            </div>

            <!-- Area scanner dengan efek khusus -->
            <div class="p-6 bg-white/90 backdrop-blur-sm">
                <div class="scanner-frame relative">
                    <div id="qr-reader"
                        class="w-full h-64 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg overflow-hidden flex items-center justify-center">
                        <!-- Placeholder saat kamera loading -->
                        <div id="camera-placeholder" class="text-center p-4">
                            <div
                                class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-camera text-blue-500 text-xl"></i>
                            </div>
                            <p class="text-gray-600 font-medium">Menginisialisasi kamera...</p>
                            <p class="text-gray-500 text-sm mt-1">Pastikan Anda memberikan izin akses kamera</p>
                        </div>
                    </div>
                    <div class="scanner-overlay"></div>
                </div>

                <!-- Pesan error kamera -->
                <div id="camera-error" class="hidden mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-center">
                    <i class="fas fa-exclamation-triangle text-red-500 text-xl mb-2"></i>
                    <p class="text-red-700 font-medium">Kamera tidak dapat diakses</p>
                    <p class="text-red-600 text-sm mt-1" id="error-message"></p>
                    <button id="retry-camera"
                        class="mt-3 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                        <i class="fas fa-redo mr-2"></i>Coba Lagi
                    </button>
                </div>

                <!-- Tips scanner -->
                <div class="mt-6 flex items-center justify-center text-blue-600 bg-blue-50 p-3 rounded-lg">
                    <i class="fas fa-info-circle mr-2"></i>
                    <p class="text-center text-sm font-medium">Pastikan QR Code terlihat jelas dan dalam pencahayaan
                        yang baik</p>
                </div>
            </div>

            <!-- Status area dengan animasi -->
            <div id="scan-result" class="px-6 pb-6 bg-white/80 backdrop-blur-sm">
                <div
                    class="status-placeholder text-center text-gray-500 text-sm py-6 rounded-lg border-2 border-dashed border-blue-200 bg-blue-50/50">
                    <i class="fas fa-camera text-3xl mb-3 text-blue-400"></i>
                    <p class="font-medium">Scanner Siap</p>
                    <p class="text-xs mt-1">Tunggu hingga QR Code terdeteksi...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel kontrol dengan tombol aksi -->
    <div class="mt-8 flex flex-col sm:flex-row gap-4 w-full max-w-md">
        <a href="lihat_detail_pendaftar.php?event_id=<?= $event_id ?>"
            class="flex-1 px-6 py-3 bg-white text-blue-600 border border-blue-300 rounded-xl shadow-sm hover:bg-blue-50 hover:shadow-md transition-all duration-300 flex items-center justify-center font-medium group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
            <span>Kembali ke Daftar</span>
        </a>

        <div class="flex gap-2">
            <button id="switch-camera"
                class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl shadow hover:from-blue-600 hover:to-blue-700 hover:shadow-md transition-all duration-300 flex items-center font-medium group">
                <i class="fas fa-camera-rotate mr-2 group-hover:rotate-90 transition-transform"></i>
                <span>Switch Kamera</span>
            </button>

            <button id="toggle-sound"
                class="px-4 py-3 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-xl shadow hover:from-indigo-600 hover:to-indigo-700 hover:shadow-md transition-all duration-300 flex items-center font-medium group">
                <i class="fas fa-volume-up group-hover:scale-110 transition-transform"></i>
            </button>
        </div>
    </div>

    <!-- Statistik check-in -->
    <div class="mt-8 bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg w-full max-w-md">
        <h3 class="text-lg font-bold text-gray-800 mb-4 text-center">Statistik Check-in</h3>
        <div class="grid grid-cols-3 gap-4 text-center">
            <div class="bg-blue-50 p-4 rounded-xl">
                <div class="text-2xl font-bold text-blue-600" id="success-count">0</div>
                <div class="text-xs text-gray-600 mt-1">Berhasil</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl">
                <div class="text-2xl font-bold text-gray-600" id="total-count">0</div>
                <div class="text-xs text-gray-600 mt-1">Total Scan</div>
            </div>
            <div class="bg-red-50 p-4 rounded-xl">
                <div class="text-2xl font-bold text-red-600" id="error-count">0</div>
                <div class="text-xs text-gray-600 mt-1">Gagal</div>
            </div>
        </div>
    </div>

    <script>
        // Variabel global
        let html5QrcodeScanner;
        let currentCameraId = null;
        let soundEnabled = true;
        let cameras = [];
        let currentCameraIndex = 0;

        // Elemen UI
        const cameraPlaceholder = document.getElementById('camera-placeholder');
        const cameraError = document.getElementById('camera-error');
        const errorMessage = document.getElementById('error-message');
        const retryButton = document.getElementById('retry-camera');
        const switchCameraButton = document.getElementById('switch-camera');

        // Statistik
        const successCount = document.getElementById('success-count');
        const totalCount = document.getElementById('total-count');
        const errorCount = document.getElementById('error-count');

        // Suara untuk notifikasi
        const successSound = new Audio('../assets/sound/sound.mp3');
        const errorSound = new Audio('../assets/sound/sound.mp3');

        // Fungsi untuk mendapatkan daftar kamera
        async function getCameras() {
            try {
                const devices = await Html5Qrcode.getCameras();
                cameras = devices;
                console.log('Cameras found:', cameras);
                return cameras;
            } catch (error) {
                console.error('Error getting cameras:', error);
                showCameraError('Tidak dapat mengakses daftar kamera: ' + error.message);
                return [];
            }
        }

        // Fungsi untuk memulai scanner
        async function startScanner(cameraId = null) {
            try {
                if (html5QrcodeScanner) {
                    await html5QrcodeScanner.stop().catch(() => { });
                    await html5QrcodeScanner.clear().catch(() => { });
                }

                cameraPlaceholder.style.display = 'none';
                cameraError.classList.add('hidden');

                // Gunakan kamera belakang sebagai default
                const config = {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                };

                html5QrcodeScanner = new Html5Qrcode("qr-reader");

                const constraints = cameraId ? { deviceId: { exact: cameraId } } : { facingMode: "environment" };

                await html5QrcodeScanner.start(
                    constraints,
                    config,
                    onScanSuccess,
                    onScanFailure
                );

                console.log('Scanner started successfully');
            } catch (error) {
                console.error('Error starting scanner:', error);
                showCameraError('Gagal memulai kamera: ' + error.message);
            }
        }

        // Fungsi untuk menampilkan error kamera
        function showCameraError(message) {
            cameraPlaceholder.style.display = 'none';
            errorMessage.textContent = message;
            cameraError.classList.remove('hidden');
        }

        // Fungsi untuk mengganti kamera
        async function switchCamera() {
            if (cameras.length <= 1) {
                alert('Hanya ada 1 kamera yang terdeteksi');
                return;
            }

            currentCameraIndex = (currentCameraIndex + 1) % cameras.length;
            const nextCamera = cameras[currentCameraIndex];

            console.log('Switching to camera:', nextCamera.label);

            try {
                await startScanner(nextCamera.id);
            } catch (error) {
                console.error('Error switching camera:', error);
                showCameraError('Gagal mengganti kamera: ' + error.message);
            }
        }

        // Callback saat scan berhasil
        function onScanSuccess(decodedText, decodedResult) {
            console.log(`Scan berhasil: ${decodedText}`);
            updateStats('total');

            // Hentikan scanner sementara
            html5QrcodeScanner.pause();

            const resultContainer = document.getElementById('scan-result');
            resultContainer.innerHTML = `
                <div class="flex flex-col items-center justify-center p-6 bg-blue-50 text-blue-700 rounded-xl border-2 border-blue-200">
                    <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3">
                        <i class="fas fa-spinner fa-spin text-white"></i>
                    </div>
                    <span class="font-medium">Memvalidasi tiket...</span>
                    <p class="text-xs mt-1 text-blue-600">Harap tunggu sebentar</p>
                </div>
            `;

            // Kirim data ke server
            const formData = new FormData();
            formData.append('kode_unik', decodedText);

            fetch('proses_checkin.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (soundEnabled) {
                            successSound.play().catch(e => console.log('Audio play failed'));
                        }
                        updateStats('success');
                        resultContainer.innerHTML = `
                            <div class="status-success text-white p-6 rounded-xl flex flex-col items-center text-center shadow-lg">
                                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-check text-2xl"></i>
                                </div>
                                <h3 class="font-bold text-xl mb-2">CHECK-IN BERHASIL</h3>
                                <p class="font-semibold text-lg">${data.data.nama_peserta}</p>
                                <p class="text-white/90 mt-2 text-sm">Tiket telah berhasil divalidasi</p>
                                <div class="mt-4 bg-white/20 px-3 py-1 rounded-full text-xs">
                                    <i class="fas fa-clock mr-1"></i> ${new Date().toLocaleTimeString()}
                                </div>
                            </div>
                        `;
                    } else {
                        if (soundEnabled) {
                            errorSound.play().catch(e => console.log('Audio play failed'));
                        }
                        updateStats('error');
                        resultContainer.innerHTML = `
                            <div class="status-error text-white p-6 rounded-xl flex flex-col items-center text-center shadow-lg">
                                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-times text-2xl"></i>
                                </div>
                                <h3 class="font-bold text-xl mb-2">CHECK-IN GAGAL</h3>
                                <p class="font-medium">${data.message}</p>
                                <p class="text-white/90 mt-2 text-sm">Silakan coba scan ulang atau periksa manual</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (soundEnabled) {
                        errorSound.play().catch(e => console.log('Audio play failed'));
                    }
                    updateStats('error');
                    resultContainer.innerHTML = `
                        <div class="bg-red-100 text-red-800 p-6 rounded-xl border border-red-200 flex flex-col items-center text-center">
                            <div class="w-16 h-16 bg-red-500 text-white rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-wifi-slash text-xl"></i>
                            </div>
                            <h3 class="font-bold text-lg mb-2">KONEKSI GAGAL</h3>
                            <p class="text-center text-sm">Tidak dapat terhubung ke server. Periksa koneksi internet Anda.</p>
                        </div>
                    `;
                })
                .finally(() => {
                    // Lanjutkan scanning setelah 3 detik
                    setTimeout(() => {
                        if (html5QrcodeScanner) {
                            html5QrcodeScanner.resume();
                        }
                        resultContainer.innerHTML = `
                            <div class="status-placeholder text-center text-gray-500 text-sm py-6 rounded-lg border-2 border-dashed border-blue-200 bg-blue-50/50">
                                <i class="fas fa-camera text-3xl mb-3 text-blue-400"></i>
                                <p class="font-medium">Scanner Siap</p>
                                <p class="text-xs mt-1">Tunggu hingga QR Code terdeteksi...</p>
                            </div>
                        `;
                    }, 3000);
                });
        }

        function onScanFailure(error) {
            // Biarkan kosong untuk mengurangi console noise
        }

        // Update statistik
        function updateStats(type) {
            switch (type) {
                case 'success':
                    successCount.textContent = parseInt(successCount.textContent) + 1;
                    break;
                case 'error':
                    errorCount.textContent = parseInt(errorCount.textContent) + 1;
                    break;
                case 'total':
                    totalCount.textContent = parseInt(totalCount.textContent) + 1;
                    break;
            }
        }

        // Inisialisasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', async function () {
            console.log('Initializing scanner...');

            // Dapatkan daftar kamera terlebih dahulu
            await getCameras();

            // Mulai scanner
            if (cameras.length > 0) {
                await startScanner();
            } else {
                showCameraError('Tidak ada kamera yang terdeteksi. Pastikan kamera tersedia dan izin telah diberikan.');
            }

            // Event listeners
            retryButton.addEventListener('click', async function () {
                console.log('Retrying camera...');
                await getCameras();
                if (cameras.length > 0) {
                    await startScanner();
                }
            });

            switchCameraButton.addEventListener('click', switchCamera);

            document.getElementById('toggle-sound').addEventListener('click', function () {
                soundEnabled = !soundEnabled;
                const icon = this.querySelector('i');
                if (soundEnabled) {
                    icon.className = 'fas fa-volume-up group-hover:scale-110 transition-transform';
                    this.classList.remove('from-gray-500', 'to-gray-600');
                    this.classList.add('from-indigo-500', 'to-indigo-600');
                } else {
                    icon.className = 'fas fa-volume-mute group-hover:scale-110 transition-transform';
                    this.classList.remove('from-indigo-500', 'to-indigo-600');
                    this.classList.add('from-gray-500', 'to-gray-600');
                }
            });
        });

        // Cleanup saat halaman ditutup
        window.addEventListener('beforeunload', function () {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().catch(error => {
                    console.error('Error clearing scanner:', error);
                });
            }
        });
    </script>
</body>

</html>