<?php

if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once '../core/koneksi.php';
// user/riwayat_transaksi.php
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
?>

<div class="max-w-5xl mx-auto">

    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Riwayat Transaksi</h1>
            <p class="text-gray-500 text-sm">Pantau status pendaftaran event Anda di sini.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <span
                class="bg-white border border-gray-200 px-4 py-2 rounded-full text-xs font-bold text-gray-600 shadow-sm">
                Total Transaksi: <?= count($transaksi) ?>
            </span>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="p-5 font-bold">Event & ID</th>
                        <th class="p-5 font-bold">Tanggal</th>
                        <th class="p-5 font-bold">Tagihan</th>
                        <th class="p-5 font-bold text-center">Status</th>
                        <th class="p-5 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (count($transaksi) > 0): ?>
                        <?php foreach ($transaksi as $row): ?>
                            <?php
                            $tgl = date('d M Y, H:i', strtotime($row['created_at']));

                            // Logika Status
                            $status_class = '';
                            $status_label = '';
                            $status_icon = '';

                            switch ($row['status_pembayaran']) {
                                case 'paid':
                                    $status_class = 'bg-green-100 text-green-700 border-green-200';
                                    $status_label = 'Lunas';
                                    $status_icon = 'fa-check-circle';
                                    break;
                                case 'pending':
                                    $status_class = 'bg-orange-100 text-orange-700 border-orange-200 animate-pulse';
                                    $status_label = 'Menunggu Pembayaran';
                                    $status_icon = 'fa-clock';
                                    break;
                                case 'failed':
                                    $status_class = 'bg-red-100 text-red-700 border-red-200';
                                    $status_label = 'Gagal / Expired';
                                    $status_icon = 'fa-times-circle';
                                    break;
                                case 'free':
                                    $status_class = 'bg-blue-100 text-blue-700 border-blue-200';
                                    $status_label = 'Gratis (Terdaftar)';
                                    $status_icon = 'fa-ticket-alt';
                                    break;
                                default:
                                    $status_class = 'bg-gray-100 text-gray-600';
                                    $status_label = $row['status_pembayaran'];
                            }
                            ?>
                            <tr class="hover:bg-gray-50/50 transition duration-150">
                                <td class="p-5">
                                    <div class="font-bold text-gray-800 text-base mb-1"><?= htmlspecialchars($row['judul']) ?>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span
                                            class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded border border-gray-200 font-mono">
                                            <?= $row['kode_unik'] ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="p-5 text-sm text-gray-500 whitespace-nowrap">
                                    <?= $tgl ?> WIB
                                </td>
                                <td class="p-5 font-mono font-bold text-gray-700">
                                    <?php if ($row['tipe_event'] == 'gratis' || $row['harga'] <= 0): ?>
                                        <span class="text-green-600">Free</span>
                                    <?php else: ?>
                                        Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                                    <?php endif; ?>
                                </td>
                                <td class="p-5 text-center">
                                    <span
                                        class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold border <?= $status_class ?>">
                                        <i class="fas <?= $status_icon ?> mr-1.5"></i> <?= $status_label ?>
                                    </span>

                                    <?php if ($row['status_pembayaran'] == 'pending' && !empty($row['payment_expiry'])): ?>
                                        <div class="text-[10px] text-orange-600 mt-1 font-medium">
                                            Expired: <?= date('d M H:i', strtotime($row['payment_expiry'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="p-5 text-right">
                                    <div class="flex justify-end gap-2">
                                        <?php if ($row['status_pembayaran'] == 'pending' && !empty($row['payment_url'])): ?>
                                            <a href="<?= $row['payment_url'] ?>" target="_blank"
                                                class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white text-xs font-bold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5 flex items-center">
                                                <i class="fas fa-credit-card mr-2"></i> Bayar Sekarang
                                            </a>
                                        <?php elseif ($row['status_pembayaran'] == 'paid' || $row['status_pembayaran'] == 'free'): ?>
                                            <a href="cetak_tiket.php?id=<?= $row['id'] ?>"
                                                class="bg-white border border-primary text-primary hover:bg-primary hover:text-white text-xs font-bold py-2 px-4 rounded-lg transition flex items-center">
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
                            <td colspan="5" class="p-12 text-center text-gray-500 bg-white">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-receipt text-2xl text-gray-400"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-700">Belum Ada Transaksi</h3>
                                    <p class="text-sm mt-1">Daftar event menarik sekarang juga!</p>
                                    <a href="dashboard.php" class="mt-4 text-primary font-bold hover:underline text-sm">Cari
                                        Event</a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl flex items-start">
            <div class="bg-blue-100 p-2 rounded-lg text-blue-600 mr-3">
                <i class="fas fa-info-circle"></i>
            </div>
            <div>
                <h4 class="font-bold text-blue-800 text-sm">Status Pembayaran</h4>
                <p class="text-xs text-blue-600 mt-1">Jika status <b>Menunggu Pembayaran</b>, segera klik tombol "Bayar
                    Sekarang" sebelum batas waktu habis.</p>
            </div>
        </div>
        <div class="bg-green-50 border border-green-100 p-4 rounded-xl flex items-start">
            <div class="bg-green-100 p-2 rounded-lg text-green-600 mr-3">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div>
                <h4 class="font-bold text-green-800 text-sm">E-Ticket Event</h4>
                <p class="text-xs text-green-600 mt-1">Tiket hanya muncul jika status pembayaran <b>Lunas</b> atau event
                    tersebut <b>Gratis</b>.</p>
            </div>
        </div>
    </div>

</div>

<?php require_once 'templates/footer.php'; ?>