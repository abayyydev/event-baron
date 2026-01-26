<?php
session_start();

// Cek sesi login dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'penyelenggara') {
    header("Location: login.php");
    exit();
}

// Ambil info event
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
<html lang="id">

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

        /* Animasi Scanner */
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
            border: 2px solid rgba(59, 130, 246, 0.5);
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
            background: linear-gradient(180deg, rgba(59, 130, 246, 0) 0%, rgba(59, 130, 246, 0.2) 50%, rgba(59, 130, 246, 0) 100%);
            height: 10px;
            width: 100%;
            animation: scan-beam 2s infinite linear;
            z-index: 5;
            pointer-events: none;
        }

        @keyframes pulse-border {

            0%,
            100% {
                border-color: rgba(59, 130, 246, 0.3);
            }

            50% {
                border-color: rgba(59, 130, 246, 0.8);
            }
        }

        @keyframes scan-beam {
            0% {
                top: 0;
            }

            100% {
                top: 100%;
            }
        }

        /* Styling Result */
        .status-success {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .status-error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .status-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        #qr-reader video {
            object-fit: cover;
            border-radius: 12px;
        }
    </style>
</head>

<body class="bg-slate-100 flex flex-col items-center justify-center min-h-screen p-4">

    <div class="text-center mb-6">
        <div
            class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full shadow-lg mb-4 text-blue-600">
            <i class="fas fa-qrcode text-3xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-1">Event Check-in</h1>
        <p class="text-sm text-gray-500 max-w-xs mx-auto"><?= $event_judul ?></p>
    </div>

    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl overflow-hidden">

        <div class="p-4 bg-gray-900 relative">
            <div class="scanner-frame bg-black h-72 w-full flex items-center justify-center relative">
                <div id="qr-reader" class="w-full h-full"></div>

                <div id="camera-placeholder"
                    class="absolute inset-0 flex flex-col items-center justify-center text-white/50">
                    <i class="fas fa-camera text-4xl mb-2"></i>
                    <span class="text-xs">Memuat Kamera...</span>
                </div>
            </div>

            <div class="scanner-overlay absolute top-4 left-4 right-4 h-72 pointer-events-none"></div>
        </div>

        <div id="scan-result" class="p-6 text-center min-h-[180px] flex items-center justify-center">
            <div class="text-gray-400">
                <p class="mb-2"><i class="fas fa-arrow-up text-xl animate-bounce"></i></p>
                <p class="text-sm font-medium">Arahkan kamera ke QR Code Peserta</p>
            </div>
        </div>

        <div class="bg-gray-50 p-4 border-t border-gray-100 flex justify-between items-center">
            <a href="lihat_detail_pendaftar.php?event_id=<?= $event_id ?>"
                class="text-gray-500 hover:text-gray-800 text-sm font-bold flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>

            <button id="switch-camera"
                class="bg-white border border-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-gray-50 flex items-center gap-2">
                <i class="fas fa-camera-rotate"></i> Switch Cam
            </button>
        </div>
    </div>

    <div class="mt-6 w-full max-w-md grid grid-cols-3 gap-3">
        <div class="bg-white p-3 rounded-xl shadow-sm text-center">
            <span class="block text-2xl font-bold text-emerald-500" id="success-count">0</span>
            <span class="text-[10px] text-gray-400 uppercase font-bold">Berhasil</span>
        </div>
        <div class="bg-white p-3 rounded-xl shadow-sm text-center">
            <span class="block text-2xl font-bold text-red-500" id="error-count">0</span>
            <span class="text-[10px] text-gray-400 uppercase font-bold">Gagal</span>
        </div>
        <div class="bg-white p-3 rounded-xl shadow-sm text-center">
            <span class="block text-2xl font-bold text-blue-500" id="total-count">0</span>
            <span class="text-[10px] text-gray-400 uppercase font-bold">Total Scan</span>
        </div>
    </div>

    <script>
        let html5QrcodeScanner;
        let isProcessing = false;

        // Elemen Statistik
        const successEl = document.getElementById('success-count');
        const errorEl = document.getElementById('error-count');
        const totalEl = document.getElementById('total-count');

        // Update Stats Helper
        function updateStat(type) {
            if (type === 'success') successEl.innerText = parseInt(successEl.innerText) + 1;
            if (type === 'error') errorEl.innerText = parseInt(errorEl.innerText) + 1;
            totalEl.innerText = parseInt(totalEl.innerText) + 1;
        }

        // Fungsi Handler saat scan berhasil
        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return; // Cegah double scan
            isProcessing = true;

            // Pause kamera agar user lihat hasil
            html5QrcodeScanner.pause();

            const resultDiv = document.getElementById('scan-result');
            resultDiv.innerHTML = `
                <div class="text-blue-600 animate-pulse">
                    <i class="fas fa-spinner fa-spin text-3xl mb-2"></i>
                    <p class="font-bold">Memvalidasi...</p>
                </div>`;

            // Kirim Data via AJAX
            const formData = new FormData();
            formData.append('kode_unik', decodedText);

            // TAMBAHAN PENTING: Kirim ID Event agar bisa cek via Kartu Santri
            formData.append('event_id', '<?= $event_id ?>');

            fetch('proses_checkin.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Tampilan SUKSES
                        resultDiv.innerHTML = `
                        <div class="w-full status-success text-white p-4 rounded-xl shadow-inner">
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-2">
                                <i class="fas fa-check text-2xl"></i>
                            </div>
                            <h3 class="font-bold text-lg">${data.type === 'checkin' ? 'CHECK-IN' : 'CHECK-OUT'} BERHASIL</h3>
                            <p class="text-lg font-bold mt-1">${data.data.nama_peserta}</p>
                            <p class="text-xs opacity-90">${data.data.waktu} WIB</p>
                        </div>
                    `;
                        updateStat('success');
                    } else {
                        // Tampilan GAGAL / WARNING
                        let bgClass = data.type === 'denda' ? 'status-warning' : 'status-error';
                        let icon = data.type === 'denda' ? 'fa-exclamation-triangle' : 'fa-times';

                        resultDiv.innerHTML = `
                        <div class="w-full ${bgClass} text-white p-4 rounded-xl shadow-inner">
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-2">
                                <i class="fas ${icon} text-2xl"></i>
                            </div>
                            <h3 class="font-bold text-lg">${data.status === 'error' ? 'GAGAL' : 'PERINGATAN'}</h3>
                            <p class="font-medium text-sm mt-1">${data.message}</p>
                        </div>
                    `;
                        updateStat('error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    resultDiv.innerHTML = `<p class="text-red-500 font-bold">Koneksi Error!</p>`;
                    updateStat('error');
                })
                .finally(() => {
                    // Lanjutkan scan setelah 2.5 detik
                    setTimeout(() => {
                        resultDiv.innerHTML = `
                        <div class="text-gray-400">
                            <p class="mb-2"><i class="fas fa-arrow-up text-xl animate-bounce"></i></p>
                            <p class="text-sm font-medium">Arahkan kamera ke QR Code</p>
                        </div>
                    `;
                        html5QrcodeScanner.resume();
                        isProcessing = false;
                    }, 2500);
                });
        }

        // Config Scanner
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

        // Start Scanner
        html5QrcodeScanner = new Html5Qrcode("qr-reader");
        html5QrcodeScanner.start(
            { facingMode: "environment" }, // Pakai kamera belakang
            config,
            onScanSuccess
        ).catch(err => {
            document.getElementById('camera-placeholder').innerHTML = `
                <div class="text-red-400 text-center px-4">
                    <i class="fas fa-camera-slash text-2xl mb-2"></i>
                    <p class="text-xs">Kamera tidak dapat diakses.</p>
                    <p class="text-[10px] mt-1">Pastikan izin kamera aktif (HTTPS required).</p>
                </div>`;
        });

        // Switch Camera Button
        document.getElementById('switch-camera').addEventListener('click', () => {
            // Logic switch camera (Advance) - Untuk simpelnya, reload page biasanya cukup di mobile browser
            // Atau implementasikan stop() lalu start() dengan deviceId berbeda
            alert("Fitur ganti kamera akan me-refresh halaman.");
            location.reload();
        });

    </script>
</body>

</html>