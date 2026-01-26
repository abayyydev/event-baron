<?php
// user/riwayat_transaksi.php

if (session_status() === PHP_SESSION_NONE)
    session_start();

// 1. CEK LOGIN SANTRI
if (!isset($_SESSION['santri_id']) || $_SESSION['role'] !== 'peserta') {
    header("Location: ../login.php");
    exit();
}

require_once '../core/koneksi.php';

$page_title = "Riwayat Transaksi";
$current_page = "transaksi";

require_once 'templates/header.php';

$santri_id = $_SESSION['santri_id'];

// 2. QUERY TRANSAKSI
// Pastikan p.* diambil semua agar kolom 'didaftarkan_oleh' terbawa
try {
    $sql = "SELECT p.*, w.judul, w.tipe_event, w.harga, w.poster, w.tanggal_waktu 
            FROM pendaftaran p 
            JOIN workshops w ON p.workshop_id = w.id 
            WHERE p.santri_id = :sid 
            ORDER BY p.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['sid' => $santri_id]);
    $transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $transaksi = [];
}

// 3. HITUNG STATISTIK
$total_trx = count($transaksi);
$pending_trx = 0;
$success_trx = 0;

foreach ($transaksi as $t) {
    if ($t['status_pembayaran'] == 'pending')
        $pending_trx++;
    if ($t['status_pembayaran'] == 'paid' || $t['status_pembayaran'] == 'free')
        $success_trx++;
}
?>

<div class="min-h-screen bg-slate-50 font-sans pb-20">

    <div class="bg-primary-900 pb-24 pt-10 px-4 rounded-b-[3rem] shadow-xl relative overflow-hidden">
        <div
            class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-primary-800 rounded-full opacity-50 blur-3xl animate-pulse-subtle">
        </div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-gold-500 rounded-full opacity-20 blur-2xl">
        </div>

        <div class="max-w-6xl mx-auto px-4 relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <span
                            class="text-primary-200 text-xs font-bold uppercase tracking-widest border border-primary-700/50 px-2 py-1 rounded-md">History</span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-white leading-tight">
                        Riwayat Transaksi
                    </h1>
                    <p class="text-primary-100/90 mt-2 text-sm md:text-base max-w-lg">
                        Pantau status pendaftaran event, lakukan pembayaran, dan unduh tiket Anda di sini.
                    </p>
                </div>

                <div class="flex gap-3">
                    <div
                        class="bg-white/10 backdrop-blur-md border border-white/10 rounded-xl p-3 px-5 flex flex-col items-center min-w-[90px]">
                        <span class="text-2xl font-bold text-white"><?= $total_trx ?></span>
                        <span class="text-[10px] text-primary-200 uppercase font-bold">Total</span>
                    </div>
                    <div
                        class="bg-emerald-500/20 backdrop-blur-md border border-emerald-500/30 rounded-xl p-3 px-5 flex flex-col items-center min-w-[90px]">
                        <span class="text-2xl font-bold text-emerald-400"><?= $success_trx ?></span>
                        <span class="text-[10px] text-emerald-200 uppercase font-bold">Berhasil</span>
                    </div>
                    <?php if ($pending_trx > 0): ?>
                        <div
                            class="bg-amber-500/20 backdrop-blur-md border border-amber-500/30 rounded-xl p-3 px-5 flex flex-col items-center min-w-[90px]">
                            <span class="text-2xl font-bold text-amber-400 animate-pulse"><?= $pending_trx ?></span>
                            <span class="text-[10px] text-amber-200 uppercase font-bold">Menunggu</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 -mt-16 relative z-20 space-y-8">

        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden min-h-[400px]">

            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-slate-50 text-slate-600 border-b border-slate-200 text-xs uppercase tracking-wider">
                            <th class="px-6 py-5 font-bold">Event Info</th>
                            <th class="px-6 py-5 font-bold">Waktu Transaksi</th>
                            <th class="px-6 py-5 font-bold">Tagihan</th>
                            <th class="px-6 py-5 font-bold text-center">Status</th>
                            <th class="px-6 py-5 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if ($total_trx > 0): ?>
                            <?php foreach ($transaksi as $row):
                                $tgl_daftar = date('d M Y, H:i', strtotime($row['created_at']));
                                $is_internal = ($row['didaftarkan_oleh'] == 'admin');

                                // Logic Status Badge
                                $status = $row['status_pembayaran'];
                                $badgeClass = match ($status) {
                                    'paid' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
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
                                <tr class="hover:bg-primary-50/30 transition-colors group">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-slate-200 flex-shrink-0 overflow-hidden">
                                                <?php if ($row['poster']): ?>
                                                    <img src="<?= BASE_URL ?>assets/img/posters/<?= htmlspecialchars($row['poster']) ?>"
                                                        class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <div
                                                        class="w-full h-full flex items-center justify-center bg-slate-300 text-slate-500">
                                                        <i class="fas fa-image"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div
                                                    class="font-bold text-slate-800 text-sm mb-0.5 group-hover:text-primary-700 transition-colors line-clamp-1">
                                                    <?= htmlspecialchars($row['judul']) ?>
                                                </div>
                                                <div
                                                    class="text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded border border-slate-200 font-mono inline-block">
                                                    #<?= $row['kode_unik'] ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-5 text-sm text-slate-500">
                                        <?= $tgl_daftar ?> WIB
                                    </td>

                                    <td class="px-6 py-5 font-bold text-slate-700 font-mono text-sm">
                                        <?php if ($row['tipe_event'] == 'gratis' || $row['harga'] <= 0): ?>
                                            <span class="text-blue-600">Free</span>
                                        <?php else: ?>
                                            Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-6 py-5 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider <?= $badgeClass ?>">
                                            <i class="fas <?= $icon ?> mr-1.5"></i> <?= $label ?>
                                        </span>
                                        <?php if ($status == 'pending' && !empty($row['payment_expiry'])): ?>
                                            <div class="text-[9px] text-red-500 mt-1 font-medium animate-pulse">
                                                Exp: <?= date('d M H:i', strtotime($row['payment_expiry'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-6 py-5 text-right">
                                        <div class="flex justify-end gap-2">
                                            <?php if ($status == 'pending' && !empty($row['payment_url'])): ?>
                                                <a href="<?= $row['payment_url'] ?>" target="_blank"
                                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-xs font-bold rounded-lg shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5">
                                                    <i class="fas fa-credit-card mr-2"></i> Bayar
                                                </a>
                                            <?php elseif ($status == 'paid' || $status == 'free'): ?>
                                                <?php if ($is_internal): ?>
                                                    <span
                                                        class="inline-flex items-center px-3 py-2 bg-slate-100 text-slate-500 border border-slate-200 rounded-lg text-xs font-bold cursor-default"
                                                        title="Gunakan Kartu Santri untuk Check-in">
                                                        <i class="fas fa-id-card mr-2"></i> Kartu Santri
                                                    </span>
                                                <?php else: ?>
                                                    <a href="cetak_tiket.php?id=<?= $row['id'] ?>" target="_blank"
                                                        class="inline-flex items-center px-4 py-2 bg-white border border-primary-500 text-primary-600 hover:bg-primary-50 text-xs font-bold rounded-lg transition-colors">
                                                        <i class="fas fa-ticket-alt mr-2"></i> E-Ticket
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-xs text-slate-400 italic">Closed</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div
                                            class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4 border-2 border-dashed border-slate-200">
                                            <i class="fas fa-receipt text-3xl text-slate-300"></i>
                                        </div>
                                        <h3 class="text-lg font-bold text-slate-700">Belum Ada Transaksi</h3>
                                        <p class="text-sm text-slate-500 mt-1 mb-6">Anda belum mendaftar event apapun.</p>
                                        <a href="dashboard.php"
                                            class="px-6 py-2.5 bg-primary-600 text-white rounded-xl font-bold text-sm hover:bg-primary-700 transition shadow-lg shadow-primary-200">
                                            Cari Event Sekarang
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="lg:hidden p-4 space-y-4">
                <?php if ($total_trx > 0): ?>
                    <?php foreach ($transaksi as $row):
                        // Logic Status
                        $status = $row['status_pembayaran'];
                        $is_internal = ($row['didaftarkan_oleh'] == 'admin');

                        $badgeClass = match ($status) {
                            'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                            'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
                            'failed' => 'bg-red-50 text-red-700 border-red-100',
                            'free' => 'bg-blue-50 text-blue-700 border-blue-100',
                            default => 'bg-gray-50 text-gray-600'
                        };
                        $label = match ($status) {
                            'paid' => 'Lunas',
                            'pending' => 'Menunggu',
                            'failed' => 'Gagal',
                            'free' => 'Gratis',
                            default => ucfirst($status)
                        };
                        ?>
                        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm relative overflow-hidden">
                            <div
                                class="absolute top-0 left-0 w-1.5 h-full 
                                <?= $status == 'paid' ? 'bg-emerald-500' : ($status == 'pending' ? 'bg-amber-500' : 'bg-slate-300') ?>">
                            </div>

                            <div class="pl-3">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-bold text-slate-800 text-sm leading-snug line-clamp-2 pr-2">
                                        <?= htmlspecialchars($row['judul']) ?>
                                    </h3>
                                    <span
                                        class="text-[10px] font-bold px-2 py-1 rounded border uppercase tracking-wide flex-shrink-0 <?= $badgeClass ?>">
                                        <?= $label ?>
                                    </span>
                                </div>
                                <p class="text-[10px] text-slate-400 font-mono mb-3">ID: #<?= $row['kode_unik'] ?></p>

                                <div class="grid grid-cols-2 gap-4 text-xs text-slate-600 mb-4 bg-slate-50 p-3 rounded-lg">
                                    <div>
                                        <p class="text-[10px] text-slate-400 uppercase font-bold">Waktu</p>
                                        <p class="font-medium"><?= date('d M, H:i', strtotime($row['created_at'])) ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] text-slate-400 uppercase font-bold">Total</p>
                                        <p class="font-bold text-slate-800">
                                            <?= ($row['tipe_event'] == 'gratis' || $row['harga'] <= 0) ? 'Free' : 'Rp ' . number_format($row['harga'], 0, ',', '.') ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <?php if ($status == 'pending' && !empty($row['payment_url'])): ?>
                                        <a href="<?= $row['payment_url'] ?>" target="_blank"
                                            class="flex-1 flex items-center justify-center bg-amber-500 text-white font-bold py-2.5 rounded-xl text-xs shadow-md">
                                            Bayar Sekarang
                                        </a>
                                    <?php elseif ($status == 'paid' || $status == 'free'): ?>
                                        <?php if ($is_internal): ?>
                                            <div
                                                class="flex-1 flex items-center justify-center bg-slate-100 text-slate-500 border border-slate-200 font-bold py-2.5 rounded-xl text-xs">
                                                <i class="fas fa-id-card mr-2"></i> Pakai Kartu Santri
                                            </div>
                                        <?php else: ?>
                                            <a href="cetak_tiket.php?id=<?= $row['id'] ?>" target="_blank"
                                                class="flex-1 flex items-center justify-center bg-white border border-primary-500 text-primary-700 font-bold py-2.5 rounded-xl text-xs hover:bg-primary-50">
                                                Lihat E-Ticket
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button disabled
                                            class="flex-1 bg-slate-100 text-slate-400 font-bold py-2.5 rounded-xl text-xs cursor-not-allowed">
                                            Tidak Tersedia
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-12">
                        <p class="text-slate-500 font-medium">Belum ada transaksi.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-8">
            <div
                class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl border border-amber-100 p-5 flex items-start gap-4">
                <div
                    class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-amber-500 shadow-sm flex-shrink-0">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <h3 class="font-bold text-amber-900 text-sm mb-1">Status Pending?</h3>
                    <p class="text-xs text-amber-800/80 leading-relaxed">
                        Segera selesaikan pembayaran Anda. Transaksi yang kadaluwarsa akan dibatalkan otomatis oleh
                        sistem.
                    </p>
                </div>
            </div>

            <div
                class="bg-gradient-to-br from-primary-50 to-green-50 rounded-2xl border border-primary-100 p-5 flex items-start gap-4">
                <div
                    class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-primary-500 shadow-sm flex-shrink-0">
                    <i class="fas fa-id-badge"></i>
                </div>
                <div>
                    <h3 class="font-bold text-primary-900 text-sm mb-1">Metode Check-in</h3>
                    <p class="text-xs text-primary-800/80 leading-relaxed">
                        Gunakan <b>Kartu Santri</b> untuk event internal pondok, atau <b>E-Ticket</b> untuk event
                        mandiri.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once 'templates/footer.php'; ?>