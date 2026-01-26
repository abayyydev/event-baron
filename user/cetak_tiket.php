<?php
// user/cetak_tiket.php
if (session_status() === PHP_SESSION_NONE)
    session_start();

// 1. CEK LOGIN
if (!isset($_SESSION['santri_id']) || $_SESSION['role'] !== 'peserta') {
    header("Location: ../login.php");
    exit;
}

require_once '../core/koneksi.php';

// 2. VALIDASI ID
if (!isset($_GET['id'])) {
    echo "<script>window.location='tiket_saya.php';</script>";
    exit;
}

$id_pendaftaran = (int) $_GET['id'];
$santri_id = $_SESSION['santri_id'];

// 3. AMBIL DATA (Validasi kepemilikan berdasarkan santri_id)
$stmt = $pdo->prepare("SELECT p.*, w.judul, w.tanggal_waktu, w.lokasi, w.tipe_event, w.poster 
                        FROM pendaftaran p 
                        JOIN workshops w ON p.workshop_id = w.id 
                        WHERE p.id = ? AND p.santri_id = ?");
$stmt->execute([$id_pendaftaran, $santri_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika tiket tidak ditemukan / bukan milik user
if (!$data) {
    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <script src='https://cdn.tailwindcss.com'></script>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
    </head>
    <body class='bg-slate-50 flex items-center justify-center min-h-screen'>
        <div class='text-center'>
            <div class='w-20 h-20 bg-slate-200 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400'>
                <i class='fas fa-ticket-alt text-4xl'></i>
            </div>
            <h2 class='text-xl font-bold text-slate-700'>Tiket Tidak Ditemukan</h2>
            <p class='text-slate-500 mt-2 mb-6'>Tiket ini tidak tersedia atau Anda tidak memiliki akses.</p>
            <a href='tiket_saya.php' class='px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition'>Kembali</a>
        </div>
    </body>
    </html>";
    exit;
}

// 4. Validasi Status (Hanya Paid/Free yang boleh cetak)
$isValid = ($data['status_pembayaran'] == 'paid' || $data['status_pembayaran'] == 'free');

// 5. Generate QR Code
$qr_content = $data['kode_unik'];
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qr_content);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket - <?= htmlspecialchars($data['kode_unik']) ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 
                        sans: ['Inter', 'sans-serif'],
                        mono: ['Space Mono', 'monospace']
                    },
                    colors: {
                        primary: {
                            50: '#f0fdf6', 600: '#16a34a', 800: '#166534', 900: '#14532d',
                        },
                        gold: {
                            400: '#fbbf24', 500: '#f59e0b',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Efek Sobekan Kertas (Dashed Line & Circles) */
        .ticket-rip {
            position: relative;
            border: 2px dashed #cbd5e1; /* slate-300 */
        }
        /* Lingkaran atas/bawah atau kiri/kanan */
        .rip-circle {
            position: absolute;
            width: 30px;
            height: 30px;
            background-color: #f8fafc; /* slate-50 (Match body bg) */
            border-radius: 50%;
            z-index: 10;
        }

        /* Print Styles */
        @media print {
            @page { margin: 0; size: auto; }
            body { background: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .ticket-container { box-shadow: none; border: 1px solid #ddd; margin: 20px auto; }
            
            /* Paksa background muncul saat print */
            .bg-primary-900 { background-color: #14532d !important; color: white !important; }
            .bg-gold-500 { background-color: #f59e0b !important; }
            .rip-circle { background-color: white !important; }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col items-center py-10 px-4">

    <div class="w-full max-w-4xl flex justify-between items-center mb-8 no-print">
        <a href="tiket_saya.php" class="flex items-center text-slate-500 hover:text-primary-600 transition font-bold text-sm group">
            <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center mr-2 shadow-sm group-hover:border-primary-400">
                <i class="fas fa-arrow-left"></i>
            </div>
            Kembali
        </a>
        <button onclick="window.print()" class="bg-primary-600 hover:bg-primary-800 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-primary-200 transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
            <i class="fas fa-print"></i> Cetak PDF
        </button>
    </div>

    <div class="ticket-container w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row relative">

        <?php if (!$isValid): ?>
                <div class="absolute inset-0 z-50 flex items-center justify-center bg-white/80 backdrop-blur-sm no-print">
                    <div class="border-4 border-red-500 text-red-500 text-4xl font-black px-10 py-4 transform -rotate-12 rounded-xl opacity-80 uppercase tracking-widest">
                        BELUM LUNAS
                    </div>
                </div>
        <?php endif; ?>

        <div class="flex-grow p-8 md:p-10 relative">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-8 bg-gold-500 rounded-full"></span>
                    <span class="text-xs font-bold tracking-[0.2em] text-slate-400 uppercase">Official E-Ticket</span>
                </div>
                <div class="bg-primary-50 text-primary-800 px-3 py-1 rounded-lg text-xs font-bold uppercase border border-primary-100">
                    <?= $data['tipe_event'] == 'berbayar' ? 'Premium Pass' : 'Regular Pass' ?>
                </div>
            </div>

            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-800 mb-6 leading-tight">
                <?= htmlspecialchars($data['judul']) ?>
            </h1>

            <div class="grid grid-cols-2 gap-y-6 gap-x-8 mb-8">
                <div class="col-span-2 md:col-span-1">
                    <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Nama Peserta</p>
                    <p class="text-lg font-bold text-slate-800 truncate"><?= htmlspecialchars($data['nama_peserta']) ?></p>
                </div>
                <div class="col-span-2 md:col-span-1">
                    <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Lokasi</p>
                    <p class="text-sm font-bold text-slate-700 leading-snug"><?= htmlspecialchars($data['lokasi']) ?></p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Tanggal</p>
                    <p class="text-lg font-bold text-slate-800"><?= date('d M Y', strtotime($data['tanggal_waktu'])) ?></p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Waktu</p>
                    <p class="text-lg font-bold text-slate-800"><?= date('H:i', strtotime($data['tanggal_waktu'])) ?> WIB</p>
                </div>
            </div>

            <div class="border-t border-dashed border-slate-200 pt-6 flex items-center justify-between">
                <div class="text-[10px] text-slate-400 space-y-1">
                    <p><i class="fas fa-info-circle mr-1"></i> Harap tunjukkan QR Code ini kepada panitia.</p>
                    <p><i class="fas fa-check-circle mr-1"></i> Tiket ini valid dan resmi.</p>
                </div>
                <div class="text-primary-900 font-bold text-xl opacity-20">
                    <i class="fas fa-mosque"></i>
                </div>
            </div>
        </div>

        <div class="relative w-full md:w-auto flex flex-col justify-center items-center bg-primary-900 md:bg-white">
            <div class="hidden md:block w-0 h-[80%] border-l-2 border-dashed border-slate-300 relative mx-0"></div>
            <div class="hidden md:block rip-circle -top-4 left-1/2 transform -translate-x-1/2"></div>
            <div class="hidden md:block rip-circle -bottom-4 left-1/2 transform -translate-x-1/2"></div>

            <div class="md:hidden w-[85%] h-0 border-t-2 border-dashed border-primary-600/50 relative my-6"></div>
            <div class="md:hidden rip-circle -left-4 top-1/2 transform -translate-y-1/2"></div>
            <div class="md:hidden rip-circle -right-4 top-1/2 transform -translate-y-1/2"></div>
        </div>

        <div class="md:w-80 bg-primary-900 p-8 flex flex-col items-center justify-center text-center relative overflow-hidden text-white">
            
            <div class="absolute top-0 right-0 w-32 h-32 bg-primary-800 rounded-full opacity-50 blur-2xl -mr-10 -mt-10"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-gold-500 rounded-full opacity-20 blur-2xl -ml-10 -mb-10"></div>

            <div class="relative z-10 w-full flex flex-col items-center">
                <p class="text-primary-200 text-[10px] font-bold tracking-[0.3em] uppercase mb-6">Scan Here</p>

                <div class="bg-white p-3 rounded-2xl shadow-xl mb-6">
                    <img src="<?= $qr_url ?>" alt="QR Code" class="w-40 h-40 object-contain">
                </div>

                <div class="space-y-1">
                    <p class="text-[10px] text-primary-400 uppercase font-bold">Booking ID</p>
                    <p class="font-mono text-xl font-bold tracking-wider text-gold-400">
                        <?= $data['kode_unik'] ?>
                    </p>
                </div>

                <div class="mt-8 flex items-center justify-center gap-2 text-[10px] font-bold text-primary-300 border border-primary-700 px-4 py-1.5 rounded-full bg-primary-800/50">
                    <i class="fas fa-shield-alt"></i> VERIFIED TICKET
                </div>
            </div>
        </div>

    </div>

    <p class="text-slate-400 text-xs mt-6 text-center no-print max-w-md">
        Simpan tiket ini secara digital di HP Anda atau cetak di kertas A4.
    </p>

</body>
</html>