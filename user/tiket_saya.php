<?php
// user/tiket_saya.php

if (session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION['santri_id']) || $_SESSION['role'] !== 'peserta') {
    header("Location: ../login.php");
    exit();
}

require_once '../core/koneksi.php';
$page_title = "Tiket Saya";
$current_page = "tiket_saya";
require_once 'templates/header.php';

$santri_id = $_SESSION['santri_id'];

// QUERY: Ambil tiket Valid (Paid atau Free)
try {
    // Kita ambil semua kolom pendaftaran (p.*) agar kolom 'didaftarkan_oleh' terbawa
    $sql = "SELECT p.*, w.judul, w.tanggal_waktu, w.lokasi, w.poster, w.jam_selesai
            FROM pendaftaran p 
            JOIN workshops w ON p.workshop_id = w.id 
            WHERE p.santri_id = :sid 
            AND (p.status_pembayaran = 'paid' OR p.status_pembayaran = 'free')
            ORDER BY w.tanggal_waktu DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['sid' => $santri_id]);
    $tikets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tikets = [];
}
?>

<div class="min-h-screen bg-slate-50 font-sans pb-20">
    
    <div class="bg-primary-900 pt-10 pb-20 px-6 rounded-b-[2.5rem] shadow-lg relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-primary-800 rounded-full opacity-50 blur-3xl -mr-16 -mt-16"></div>
        <div class="max-w-6xl mx-auto relative z-10 text-center">
            <h1 class="text-3xl font-extrabold text-white mb-2">Akses Event Saya</h1>
            <p class="text-primary-200 text-sm">Daftar kegiatan yang dapat Anda ikuti.</p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 -mt-10 relative z-20">
        
        <?php if (count($tikets) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($tikets as $row):
                        $poster = !empty($row['poster']) ? '../assets/img/posters/' . $row['poster'] : '../assets/img/default-event.jpg';
                        $is_upcoming = strtotime($row['tanggal_waktu']) > time();

                        // LOGIKA PENENTU: Siapa yang mendaftarkan?
                        // Jika 'admin' -> Berarti pendaftaran internal (Wajib/Pondok)
                        // Jika 'mandiri' -> Berarti santri daftar sendiri
                        $is_internal = ($row['didaftarkan_oleh'] == 'admin');
                        ?>
                
                        <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden hover:shadow-xl transition-all duration-300 group flex flex-col h-full">
                    
                            <div class="h-40 bg-gray-200 relative overflow-hidden">
                                <img src="<?= $poster ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                        
                                <div class="absolute top-3 left-3">
                                    <?php if ($is_upcoming): ?>
                                            <span class="bg-blue-500 text-white text-[10px] font-bold px-2 py-1 rounded shadow-sm">AKAN DATANG</span>
                                    <?php else: ?>
                                            <span class="bg-gray-500 text-white text-[10px] font-bold px-2 py-1 rounded shadow-sm">SELESAI</span>
                                    <?php endif; ?>
                                </div>

                                <div class="absolute top-3 right-3">
                                    <?php if ($is_internal): ?>
                                            <span class="bg-purple-600 text-white text-[10px] font-bold px-2 py-1 rounded shadow-sm flex items-center gap-1 border border-purple-400">
                                                <i class="fas fa-star text-yellow-300"></i> INTERNAL
                                            </span>
                                    <?php else: ?>
                                            <span class="bg-emerald-500 text-white text-[10px] font-bold px-2 py-1 rounded shadow-sm border border-emerald-400">
                                                MANDIRI
                                            </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="p-5 flex-1 flex flex-col">
                                <h3 class="font-bold text-slate-800 text-lg leading-snug mb-2 line-clamp-2">
                                    <?= htmlspecialchars($row['judul']) ?>
                                </h3>
                        
                                <div class="space-y-2 mb-6">
                                    <div class="flex items-center text-sm text-slate-600">
                                        <i class="far fa-calendar-alt w-5 text-gold-500"></i>
                                        <span><?= date('d M Y, H:i', strtotime($row['tanggal_waktu'])) ?> WIB</span>
                                    </div>
                                    <div class="flex items-center text-sm text-slate-600">
                                        <i class="fas fa-map-marker-alt w-5 text-primary-500"></i>
                                        <span class="truncate"><?= htmlspecialchars($row['lokasi']) ?></span>
                                    </div>
                                </div>

                                <div class="mt-auto border-t-2 border-dashed border-slate-200 pt-4 relative">
                                    <div class="absolute -left-7 -top-3 w-6 h-6 bg-slate-50 rounded-full"></div>
                                    <div class="absolute -right-7 -top-3 w-6 h-6 bg-slate-50 rounded-full"></div>

                                    <div class="flex justify-between items-center w-full">
                                
                                        <?php if ($is_internal): ?>
                                                <div class="w-full bg-purple-50 border border-purple-100 rounded-xl p-3 flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 shrink-0">
                                                        <i class="fas fa-id-card text-lg"></i>
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-[10px] font-bold text-purple-400 uppercase tracking-wider">Metode Masuk</p>
                                                        <p class="text-xs font-bold text-slate-700">Scan Kartu Santri</p>
                                                    </div>
                                                    <div class="text-purple-300">
                                                        <i class="fas fa-check-circle"></i>
                                                    </div>
                                                </div>

                                        <?php else: ?>
                                                <div class="flex justify-between items-center w-full">
                                                    <div>
                                                        <p class="text-[10px] text-slate-400 uppercase font-bold">Kode Tiket</p>
                                                        <p class="text-sm font-mono font-bold text-slate-800 tracking-wide">
                                                            <?= $row['kode_unik'] ?>
                                                        </p>
                                                    </div>
                                                    <a href="cetak_tiket.php?id=<?= $row['id'] ?>" target="_blank" 
                                                       class="px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-200 hover:shadow-emerald-300 transition-all flex items-center gap-2 transform hover:-translate-y-0.5">
                                                        <i class="fas fa-qrcode"></i> Buka E-Ticket
                                                    </a>
                                                </div>
                                        <?php endif; ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
        <?php else: ?>
                <div class="bg-white rounded-3xl p-10 text-center shadow-sm border border-slate-200">
                    <div class="w-20 h-20 bg-primary-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-calendar-times text-4xl text-primary-300"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-700">Belum Ada Jadwal</h3>
                    <p class="text-slate-500 mt-2 mb-6">Anda belum terdaftar di event apapun.</p>
                    <a href="katalog_event.php" class="inline-block px-6 py-3 bg-primary-600 text-white font-bold rounded-xl shadow-lg hover:bg-primary-700 transition-all">
                        Lihat Katalog Event
                    </a>
                </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'templates/footer.php'; ?>