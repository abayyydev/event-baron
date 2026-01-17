<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once '../core/koneksi.php';

$page_title = "Riwayat Transaksi";
$current_page = "transaksi";

require_once 'templates/header.php';

$email_peserta = $_SESSION['email'];

// Query Transaksi Lengkap
$sql = "SELECT p.*, w.judul, w.tipe_event, w.harga 
        FROM pendaftaran p 
        JOIN workshops w ON p.workshop_id = w.id 
        WHERE p.email_peserta = :email 
        ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email_peserta]);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung Statistik Sederhana
$total_trx = count($transaksi);
$pending_trx = 0;
foreach ($transaksi as $t) {
    if ($t['status_pembayaran'] == 'pending')
        $pending_trx++;
}
?>

<div class="min-h-screen bg-gray-50 font-sans pb-20">

    <!-- Hero Section -->
    <div class="bg-emerald-900 relative overflow-hidden pb-24 pt-10 rounded-b-[3rem] shadow-xl">
        <!-- Dekorasi Background -->
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-emerald-800 rounded-full opacity-50 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-amber-500 rounded-full opacity-20 blur-2xl"></div>

        <div class="max-w-6xl mx-auto px-6 relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <span class="text-emerald-200 text-xs font-bold uppercase tracking-widest border border-emerald-700/50 px-2 py-1 rounded-md">Billing & History</span>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-white mt-2 leading-tight">
                        Riwayat Transaksi
                    </h1>
                    <p class="text-emerald-100/90 mt-2 text-sm md:text-base max-w-lg">
                        Pantau status pembayaran dan akses invoice pendaftaran event Anda di sini.
                    </p>
                </div>
                
                <div class="flex gap-3">
                    <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-xl p-3 px-5 flex flex-col items-center min-w-[100px]">
                        <span class="text-2xl font-bold text-white"><?= $total_trx ?></span>
                        <span class="text-[10px] text-emerald-200 uppercase font-bold">Total</span>
                    </div>
                    <?php if ($pending_trx > 0): ?>
                        <div class="bg-amber-500/20 backdrop-blur-md border border-amber-500/30 rounded-xl p-3 px-5 flex flex-col items-center min-w-[100px]">
                            <span class="text-2xl font-bold text-amber-400 animate-pulse"><?= $pending_trx ?></span>
                            <span class="text-[10px] text-amber-200 uppercase font-bold">Menunggu</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 -mt-16 relative z-20 space-y-8">

        <!-- Transaction List -->
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden min-h-[400px]">
            
            <!-- Desktop View -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 text-gray-600 border-b border-gray-200 text-xs uppercase tracking-wider">
                            <th class="px-6 py-5 font-bold">Event & ID Transaksi</th>
                            <th class="px-6 py-5 font-bold">Tanggal</th>
                            <th class="px-6 py-5 font-bold">Tagihan</th>
                            <th class="px-6 py-5 font-bold text-center">Status</th>
                            <th class="px-6 py-5 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if ($total_trx > 0): ?>
                                <?php foreach ($transaksi as $row):
                                    $tgl = date('d M Y, H:i', strtotime($row['created_at']));

                                    // Status Logic
                                    $status = $row['status_pembayaran'];
                                    $badgeClass = match ($status) {
                                        'paid' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                        'pending' => 'bg-amber-100 text-amber-700 border-amber-200 animate-pulse',
                                        'failed' => 'bg-red-100 text-red-700 border-red-200',
                                        'free' => 'bg-blue-100 text-blue-700 border-blue-200',
                                        default => 'bg-gray-100 text-gray-600'
                                    };
                                    $label = match ($status) {
                                        'paid' => 'Lunas',
                                        'pending' => 'Menunggu Pembayaran',
                                        'failed' => 'Gagal / Expired',
                                        'free' => 'Gratis',
                                        default => ucfirst($status)
                                    };
                                    $icon = match ($status) {
                                        'paid' => 'fa-check-circle',
                                        'pending' => 'fa-clock',
                                        'failed' => 'fa-times-circle',
                                        'free' => 'fa-gift',
                                        default => 'fa-info-circle'
                                    };
                                    ?>
                                    <tr class="hover:bg-emerald-50/30 transition-colors group">
                                        <td class="px-6 py-5">
                                            <div class="font-bold text-gray-800 text-base mb-1 group-hover:text-emerald-700 transition-colors">
                                                <?= htmlspecialchars($row['judul']) ?>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded border border-gray-200 font-mono tracking-wide">
                                                    #<?= $row['kode_unik'] ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-sm text-gray-500">
                                            <?= $tgl ?> WIB
                                        </td>
                                        <td class="px-6 py-5 font-bold text-gray-700 font-mono">
                                            <?php if ($row['tipe_event'] == 'gratis' || $row['harga'] <= 0): ?>
                                                    <span class="text-blue-600">Free</span>
                                            <?php else: ?>
                                                    Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border <?= $badgeClass ?>">
                                                <i class="fas <?= $icon ?> mr-1.5"></i> <?= $label ?>
                                            </span>
                                            <?php if ($status == 'pending' && !empty($row['payment_expiry'])): ?>
                                                    <div class="text-[10px] text-amber-600 mt-1 font-medium">
                                                        Exp: <?= date('d M H:i', strtotime($row['payment_expiry'])) ?>
                                                    </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-5 text-right">
                                            <div class="flex justify-end gap-2">
                                                <?php if ($status == 'pending' && !empty($row['payment_url'])): ?>
                                                        <a href="<?= $row['payment_url'] ?>" target="_blank"
                                                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-xs font-bold rounded-xl shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5">
                                                            <i class="fas fa-credit-card mr-2"></i> Bayar
                                                        </a>
                                                <?php elseif ($status == 'paid' || $status == 'free'): ?>
                                                        <a href="cetak_tiket.php?id=<?= $row['id'] ?>" target="_blank"
                                                            class="inline-flex items-center px-4 py-2 bg-white border border-emerald-500 text-emerald-600 hover:bg-emerald-50 text-xs font-bold rounded-xl transition-colors">
                                                            <i class="fas fa-ticket-alt mr-2"></i> E-Ticket
                                                        </a>
                                                <?php else: ?>
                                                        <span class="text-xs text-gray-400 italic px-2">Tidak Tersedia</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                        <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-20 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4 border-2 border-dashed border-gray-200">
                                                <i class="fas fa-receipt text-3xl text-gray-300"></i>
                                            </div>
                                            <h3 class="text-lg font-bold text-gray-700">Belum Ada Transaksi</h3>
                                            <p class="text-sm text-gray-500 mt-1 mb-6">Anda belum mendaftar event apapun.</p>
                                            <a href="dashboard.php" class="px-6 py-2.5 bg-emerald-600 text-white rounded-xl font-bold text-sm hover:bg-emerald-700 transition shadow-lg shadow-emerald-200">
                                                Cari Event Sekarang
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile View (Card Layout) -->
            <div class="lg:hidden p-4 space-y-4">
                <?php if ($total_trx > 0): ?>
                        <?php foreach ($transaksi as $row):
                            // Logic Status (Sama seperti desktop)
                            $status = $row['status_pembayaran'];
                            $badgeClass = match ($status) {
                                'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
                                'failed' => 'bg-red-50 text-red-700 border-red-100',
                                'free' => 'bg-blue-50 text-blue-700 border-blue-100',
                                default => 'bg-gray-50 text-gray-600'
                            };
                            $label = match ($status) {
                                'paid' => 'Lunas',
                                'pending' => 'Menunggu Pembayaran',
                                'failed' => 'Gagal',
                                'free' => 'Gratis',
                                default => ucfirst($status)
                            };
                            ?>
                            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm relative overflow-hidden">
                                <!-- Status Bar Indicator -->
                                <div class="absolute top-0 left-0 w-1.5 h-full 
                            <?= $status == 'paid' ? 'bg-emerald-500' : ($status == 'pending' ? 'bg-amber-500' : 'bg-gray-300') ?>">
                                </div>

                                <div class="flex justify-between items-start mb-3 pl-3">
                                    <div class="min-w-0 flex-1 mr-2">
                                        <h3 class="font-bold text-gray-800 text-base leading-snug line-clamp-2">
                                            <?= htmlspecialchars($row['judul']) ?>
                                        </h3>
                                        <p class="text-xs text-gray-500 mt-1 font-mono">#<?= $row['kode_unik'] ?></p>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-1 rounded border uppercase tracking-wide <?= $badgeClass ?>">
                                        <?= $label ?>
                                    </span>
                                </div>

                                <div class="pl-3 grid grid-cols-2 gap-4 text-sm text-gray-600 mb-4">
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase font-bold">Tanggal</p>
                                        <p class="font-medium"><?= date('d M Y', strtotime($row['created_at'])) ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-400 uppercase font-bold">Total Tagihan</p>
                                        <p class="font-bold text-gray-800 text-lg">
                                            <?php if ($row['tipe_event'] == 'gratis' || $row['harga'] <= 0): ?>
                                                    Free
                                            <?php else: ?>
                                                    Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="pl-3 pt-3 border-t border-gray-100">
                                    <?php if ($status == 'pending' && !empty($row['payment_url'])): ?>
                                            <a href="<?= $row['payment_url'] ?>" target="_blank"
                                                class="w-full flex items-center justify-center bg-amber-500 text-white font-bold py-3 rounded-xl shadow-lg shadow-amber-200 active:scale-95 transition-transform">
                                                Bayar Sekarang <i class="fas fa-arrow-right ml-2 text-xs"></i>
                                            </a>
                                            <?php if (!empty($row['payment_expiry'])): ?>
                                                    <p class="text-center text-[10px] text-red-500 mt-2 font-medium">
                                                        Bayar sebelum: <?= date('d M H:i', strtotime($row['payment_expiry'])) ?>
                                                    </p>
                                            <?php endif; ?>
                                    <?php elseif ($status == 'paid' || $status == 'free'): ?>
                                            <a href="cetak_tiket.php?id=<?= $row['id'] ?>" target="_blank"
                                                class="w-full flex items-center justify-center bg-emerald-50 text-emerald-700 font-bold py-3 rounded-xl border border-emerald-100 active:bg-emerald-100 transition-colors">
                                                <i class="fas fa-ticket-alt mr-2"></i> Lihat E-Ticket
                                            </a>
                                    <?php else: ?>
                                            <button disabled class="w-full bg-gray-100 text-gray-400 font-bold py-3 rounded-xl cursor-not-allowed">
                                                Tidak Tersedia
                                            </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                <?php else: ?>
                        <div class="text-center py-12 bg-white rounded-2xl border border-gray-100 shadow-sm">
                            <i class="fas fa-receipt text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500 font-medium">Belum ada transaksi.</p>
                        </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Info Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-8">
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl border border-amber-100 p-6 flex items-start gap-4">
                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-amber-500 shadow-sm flex-shrink-0">
                    <i class="fas fa-info-circle text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-amber-900 mb-1">Status Pending?</h3>
                    <p class="text-sm text-amber-800/80 leading-relaxed">
                        Jika status transaksi Anda <b>Menunggu Pembayaran</b>, segera selesaikan pembayaran sebelum batas waktu habis agar pendaftaran tidak dibatalkan otomatis.
                    </p>
                </div>
            </div>

            <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-2xl border border-emerald-100 p-6 flex items-start gap-4">
                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-emerald-500 shadow-sm flex-shrink-0">
                    <i class="fas fa-ticket-alt text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-emerald-900 mb-1">Tiket & Sertifikat</h3>
                    <p class="text-sm text-emerald-800/80 leading-relaxed">
                        E-Ticket hanya tersedia jika status pembayaran <b>Lunas</b> atau event tersebut <b>Gratis</b>. Tunjukkan tiket saat check-in di lokasi.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once 'templates/footer.php'; ?>