<?php
// user/cetak_tiket.php
if (session_status() === PHP_SESSION_NONE)
    session_start();

$page_title = "E-Ticket";
$current_page = "dashboard";

require_once 'templates/header.php';
require_once '../core/koneksi.php';

// Validasi Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Validasi ID
if (!isset($_GET['id'])) {
    echo "<script>window.location='riwayat_transaksi.php';</script>";
    exit;
}

$id_pendaftaran = $_GET['id'];
$email_peserta = $_SESSION['email'];

// Ambil Data
$stmt = $pdo->prepare("SELECT p.*, w.judul, w.tanggal_waktu, w.lokasi, w.tipe_event, w.poster 
                       FROM pendaftaran p 
                       JOIN workshops w ON p.workshop_id = w.id 
                       WHERE p.id = ? AND p.email_peserta = ?");
$stmt->execute([$id_pendaftaran, $email_peserta]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    echo "
    <div class='min-h-screen flex items-center justify-center bg-gray-50'>
        <div class='text-center'>
            <i class='fas fa-ticket-alt text-4xl text-gray-300 mb-4'></i>
            <p class='text-gray-500 font-bold'>Tiket tidak ditemukan.</p>
            <a href='riwayat_transaksi.php' class='text-emerald-600 hover:underline text-sm mt-2 block'>Kembali</a>
        </div>
    </div>";
    require_once 'templates/footer.php';
    exit;
}

// Cek Status Pembayaran (Security Check)
$isValid = ($data['status_pembayaran'] == 'paid' || $data['status_pembayaran'] == 'free');

// Generate QR URL
$qr_content = $data['kode_unik'];
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qr_content);
?>

<style>
    /* Custom Font for Ticket (Optional) */
    @import url('https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap');

    .font-mono-ticket {
        font-family: 'Space Mono', monospace;
    }

    /* Ticket Tear-off Effect */
    .ticket-rip {
        position: relative;
        height: 100%;
        border-left: 2px dashed #d1fae5;
        /* Emerald-100 */
    }

    .ticket-rip::before,
    .ticket-rip::after {
        content: '';
        position: absolute;
        width: 30px;
        height: 30px;
        background-color: #f9fafb;
        /* Gray-50 matches page bg */
        border-radius: 50%;
        left: -15px;
        z-index: 10;
    }

    .ticket-rip::before {
        top: -15px;
    }

    .ticket-rip::after {
        bottom: -15px;
    }

    /* Print Styles */
    @media print {
        @page {
            margin: 0;
            size: auto;
        }

        body {
            background: white;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .no-print,
        header,
        aside,
        footer {
            display: none !important;
        }

        .min-h-screen {
            min-height: 0;
        }

        .ticket-container {
            box-shadow: none;
            border: 1px solid #ddd;
            margin: 20px auto;
            page-break-inside: avoid;
        }

        /* Fix background clipping in print */
        .bg-emerald-900 {
            background-color: #064e3b !important;
            color: white !important;
        }

        .ticket-rip::before,
        .ticket-rip::after {
            background-color: white !important;
        }
    }
</style>

<div class="min-h-screen bg-gray-50 font-sans py-10 px-4 flex flex-col items-center">

    <!-- Navbar / Actions (No Print) -->
    <div class="w-full max-w-4xl flex justify-between items-center mb-8 no-print">
        <a href="riwayat_transaksi.php"
            class="flex items-center text-gray-500 hover:text-emerald-600 transition font-bold text-sm">
            <div
                class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center mr-2 shadow-sm">
                <i class="fas fa-arrow-left"></i>
            </div>
            Kembali
        </a>
        <button onclick="window.print()"
            class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-emerald-200 transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
            <i class="fas fa-print"></i> Cetak Tiket
        </button>
    </div>

    <!-- Ticket Container -->
    <div
        class="ticket-container w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row relative">

        <!-- Watermark jika belum valid -->
        <?php if (!$isValid): ?>
            <div class="absolute inset-0 z-50 flex items-center justify-center bg-white/80 backdrop-blur-sm">
                <div
                    class="border-4 border-red-500 text-red-500 text-4xl font-black px-10 py-4 transform -rotate-12 rounded-xl opacity-80 uppercase tracking-widest">
                    BELUM LUNAS
                </div>
            </div>
        <?php endif; ?>

        <!-- LEFT SIDE: Event Info -->
        <div class="flex-grow p-8 md:p-10 relative">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-8 bg-amber-400 rounded-full"></span>
                    <span class="text-xs font-bold tracking-[0.2em] text-gray-400 uppercase">E-Ticket Masuk</span>
                </div>
                <div
                    class="bg-emerald-50 text-emerald-700 px-3 py-1 rounded-lg text-xs font-bold uppercase border border-emerald-100">
                    <?= $data['tipe_event'] == 'berbayar' ? 'VIP Access' : 'Regular' ?>
                </div>
            </div>

            <!-- Title -->
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-800 mb-6 leading-tight">
                <?= htmlspecialchars($data['judul']) ?>
            </h1>

            <!-- Details Grid -->
            <div class="grid grid-cols-2 gap-y-6 gap-x-4 mb-8">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">Nama Peserta</p>
                    <p class="text-lg font-bold text-gray-800 truncate"><?= htmlspecialchars($data['nama_peserta']) ?>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">Tanggal</p>
                    <p class="text-lg font-bold text-gray-800">
                        <?= date('d M Y', strtotime($data['tanggal_waktu'])) ?>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">Waktu</p>
                    <p class="text-lg font-bold text-gray-800">
                        <?= date('H:i', strtotime($data['tanggal_waktu'])) ?> WIB
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold mb-1">Lokasi</p>
                    <p class="text-sm font-bold text-gray-700 leading-snug">
                        <?= htmlspecialchars($data['lokasi']) ?>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-dashed border-gray-200 pt-6 flex items-center justify-between">
                <div class="text-xs text-gray-400">
                    <p>Harap tunjukkan QR Code saat check-in.</p>
                    <p>Satu tiket berlaku untuk satu orang.</p>
                </div>
                <!-- Logo Kecil (Opsional) -->
                <div class="text-emerald-800 font-bold text-xl opacity-20">
                    <i class="fas fa-mosque"></i>
                </div>
            </div>
        </div>

        <!-- DIVIDER (Tear-off Line) -->
        <div class="relative w-full md:w-auto flex flex-col items-center justify-center bg-emerald-900 md:bg-white">
            <!-- Dashed Line Logic -->
            <div class="hidden md:block ticket-rip w-0 h-full border-l-2 border-dashed border-gray-300 relative mx-4">
            </div>
            <!-- Mobile Divider -->
            <div class="md:hidden w-full h-0 border-t-2 border-dashed border-emerald-700 relative my-4">
                <div class="absolute left-0 -top-[15px] w-[30px] h-[30px] bg-gray-50 rounded-full -ml-[15px]"></div>
                <div class="absolute right-0 -top-[15px] w-[30px] h-[30px] bg-gray-50 rounded-full -mr-[15px]"></div>
            </div>
        </div>

        <!-- RIGHT SIDE: QR Code -->
        <div
            class="md:w-80 bg-emerald-900 p-8 flex flex-col items-center justify-center text-center relative overflow-hidden">
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-800 rounded-full opacity-50 blur-2xl -mr-10 -mt-10">
            </div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-amber-500 rounded-full opacity-20 blur-2xl -ml-10 -mb-10">
            </div>

            <div class="relative z-10 w-full flex flex-col items-center">
                <p class="text-emerald-200 text-xs font-bold tracking-[0.3em] uppercase mb-6">Scan This</p>

                <div class="bg-white p-3 rounded-2xl shadow-xl mb-6">
                    <img src="<?= $qr_url ?>" alt="QR Code" class="w-40 h-40 object-contain">
                </div>

                <div class="space-y-1">
                    <p class="text-xs text-emerald-400 uppercase font-bold">Booking ID</p>
                    <p class="text-white font-mono-ticket text-lg font-bold tracking-wider">
                        <?= $data['kode_unik'] ?>
                    </p>
                </div>

                <div
                    class="mt-6 flex items-center justify-center gap-2 text-emerald-300/50 text-xs font-bold border border-emerald-700/50 px-3 py-1 rounded-full">
                    <i class="fas fa-check-circle"></i> VERIFIED
                </div>
            </div>
        </div>

    </div>

    <p class="text-gray-400 text-xs mt-6 text-center no-print max-w-md">
        Simpan tiket ini secara digital atau cetak di kertas A4. Pastikan QR Code terlihat jelas.
    </p>

</div>

<?php require_once 'templates/footer.php'; ?>