<?php
// user/cetak_tiket.php
$page_title = "E-Ticket";
$current_page = "transaksi";

require_once __DIR__ . '/templates/header.php';

// Validasi
if (!isset($_GET['id'])) {
    echo "<script>window.location='dashboard.php';</script>";
    exit;
}

$id_pendaftaran = $_GET['id'];
$email_peserta = $_SESSION['email'];

// Ambil Data
$stmt = $pdo->prepare("SELECT p.*, w.judul, w.tanggal_waktu, w.lokasi, w.tipe_event 
                       FROM pendaftaran p 
                       JOIN workshops w ON p.workshop_id = w.id 
                       WHERE p.id = ? AND p.email_peserta = ?");
$stmt->execute([$id_pendaftaran, $email_peserta]);
$data = $stmt->fetch();

if (!$data) {
    echo "<div class='p-6 text-center text-red-500'>Tiket tidak ditemukan.</div>";
    require_once 'templates/footer.php';
    exit;
}

// Generate QR URL
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($data['kode_unik']);
?>

<style>
    /* CSS Tiket */
    .ticket-wrapper {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    .dashed-line {
        border-left: 2px dashed #cbd5e1;
        position: relative;
    }

    .dashed-line::before,
    .dashed-line::after {
        content: '';
        position: absolute;
        width: 30px;
        height: 30px;
        background: #f8fafc;
        border-radius: 50%;
        left: -16px;
    }

    .dashed-line::before {
        top: -45px;
    }

    .dashed-line::after {
        bottom: -45px;
    }

    /* CSS PRINTING (Sembunyikan Sidebar & Header saat Print) */
    @media print {
        @page {
            margin: 0;
            size: auto;
        }

        body * {
            visibility: hidden;
        }

        aside,
        header,
        nav,
        footer,
        .no-print {
            display: none !important;
        }

        #ticket-area,
        #ticket-area * {
            visibility: visible;
        }

        #ticket-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            box-shadow: none;
            border: 2px solid #000;
        }

        .bg-gray-50 {
            background: white !important;
        }

        /* Reset background body */
    }
</style>

<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6 no-print">
        <a href="riwayat_transaksi.php" class="text-gray-600 hover:text-primary font-medium flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
        <button onclick="window.print()"
            class="bg-primary hover:bg-green-800 text-white px-6 py-2.5 rounded-xl font-bold shadow-md transition flex items-center">
            <i class="fas fa-print mr-2"></i> Cetak Tiket
        </button>
    </div>

    <div id="ticket-area" class="ticket-wrapper flex flex-col md:flex-row min-h-[300px]">

        <div class="flex-grow p-8 flex flex-col justify-between bg-white relative">
            <div>
                <div class="flex items-center space-x-2 mb-2 opacity-70">
                    <i class="fas fa-mosque text-secondary"></i>
                    <span class="text-xs font-bold tracking-widest text-primary uppercase">Ponpes Al Ihsan Baron</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2 leading-tight">
                    <?= htmlspecialchars($data['judul']) ?>
                </h1>
                <div
                    class="inline-block bg-primary/10 text-primary px-3 py-1 rounded-md text-xs font-bold uppercase mb-6">
                    <?= $data['tipe_event'] == 'berbayar' ? 'Tiket Berbayar' : 'Tiket Gratis' ?>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-400 uppercase font-bold mb-1">Nama Peserta</p>
                        <p class="font-bold text-gray-800 text-lg"><?= htmlspecialchars($data['nama_peserta']) ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase font-bold mb-1">Jadwal</p>
                        <p class="font-bold text-gray-800">
                            <?= date('d M Y', strtotime($data['tanggal_waktu'])) ?>
                            <span
                                class="text-sm font-normal text-gray-500 block"><?= date('H:i', strtotime($data['tanggal_waktu'])) ?>
                                WIB</span>
                        </p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs text-gray-400 uppercase font-bold mb-1">Lokasi</p>
                        <p class="font-medium text-gray-700 flex items-center">
                            <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                            <?= htmlspecialchars($data['lokasi']) ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-4 border-t border-gray-100 text-xs text-gray-400 flex justify-between items-center">
                <span>ORDER ID: <?= $data['kode_unik'] ?></span>
                <span class="flex items-center text-green-600 font-bold bg-green-50 px-2 py-1 rounded">
                    <i class="fas fa-check-circle mr-1"></i> VALID
                </span>
            </div>
        </div>

        <div class="dashed-line hidden md:block"></div>

        <div
            class="md:w-80 bg-gray-50 p-8 flex flex-col items-center justify-center text-center border-t md:border-t-0 border-gray-200">
            <p class="text-xs text-gray-400 uppercase font-bold tracking-widest mb-4">SCAN CHECK-IN</p>

            <div class="bg-white p-2 rounded-xl shadow-sm border border-gray-200 mb-4">
                <img src="<?= $qr_url ?>" alt="QR Code" class="w-40 h-40 object-contain">
            </div>

            <p
                class="font-mono text-lg font-bold text-gray-700 tracking-wider bg-white px-4 py-1 rounded border border-gray-200">
                <?= $data['kode_unik'] ?>
            </p>
            <p class="text-[10px] text-gray-400 mt-2">Tunjukkan QR ini kepada petugas.</p>
        </div>

    </div>
</div>

<?php require_once 'templates/footer.php'; ?>