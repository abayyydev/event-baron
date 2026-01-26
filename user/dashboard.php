<?php
// user/dashboard.php

// 1. Setup & Keamanan
session_start();
if (!isset($_SESSION['santri_id']) || $_SESSION['role'] !== 'peserta') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../core/koneksi.php';
require_once __DIR__ . '/templates/header.php';

// 2. Ambil Data Sesi
$santri_id = $_SESSION['santri_id'];
$nama_santri = $_SESSION['nama_lengkap'];
$nis = $_SESSION['nis'] ?? '00000';
$barcode_code = $_SESSION['barcode_code'] ?? 'STR-000';

// 3. Ambil Data Tiket Saya (Berdasarkan santri_id)
$tiket_saya = [];
try {
    $sql = "SELECT p.*, w.judul, w.tanggal_waktu, w.lokasi, w.poster, w.id as workshop_id 
            FROM pendaftaran p 
            JOIN workshops w ON p.workshop_id = w.id 
            WHERE p.santri_id = :sid 
            ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['sid' => $santri_id]);
    $tiket_saya = $stmt->fetchAll();
} catch (PDOException $e) {
    // Handle error silent
}

// 4. Ambil Agenda Terbaru (Event yang akan datang)
$agendas = [];
try {
    // Tampilkan event Public & Internal karena ini dashboard santri
    $sql_agenda = "SELECT * FROM workshops 
                   WHERE tanggal_waktu >= NOW() 
                   ORDER BY tanggal_waktu ASC LIMIT 6";
    $stmt_agenda = $pdo->query($sql_agenda);
    $agendas = $stmt_agenda->fetchAll();
} catch (PDOException $e) {
    // Handle error silent
}
?>

<div class="min-h-screen bg-slate-50 font-sans pb-24">

    <div class="bg-primary-900 relative overflow-hidden pb-32 pt-10 px-6 rounded-b-[3rem] shadow-xl">

        <div
            class="absolute top-0 right-0 -mt-10 -mr-10 w-72 h-72 bg-primary-800 rounded-full opacity-50 blur-3xl animate-pulse-subtle">
        </div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-48 h-48 bg-gold-500 rounded-full opacity-20 blur-3xl">
        </div>

        <div class="max-w-7xl mx-auto relative z-10 grid grid-cols-1 lg:grid-cols-3 gap-8 items-center">

            <div class="lg:col-span-2 text-white">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 backdrop-blur-md mb-4">
                    <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                    <span class="text-xs font-semibold tracking-wide uppercase text-green-100">Dashboard Santri</span>
                </div>
                <h1 class="text-3xl md:text-5xl font-extrabold leading-tight mb-3">
                    Ahlan wa Sahlan, <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold-300 to-gold-500">
                        <?= htmlspecialchars($nama_santri) ?>
                    </span>
                </h1>
                <p class="text-primary-100 text-sm md:text-base max-w-xl leading-relaxed opacity-90">
                    Pantau kegiatan pondok, cek jadwal kajian, dan kelola pendaftaran event antum dengan mudah dalam
                    satu aplikasi.
                </p>
            </div>

            <div class="lg:col-span-1 flex justify-center lg:justify-end">
                <div
                    class="w-full max-w-sm bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl border border-white/20 rounded-2xl p-1 shadow-2xl transform transition hover:scale-105 duration-300">
                    <div
                        class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-xl p-5 relative overflow-hidden h-full text-white border border-slate-700">
                        <div
                            class="absolute top-0 right-0 w-32 h-32 bg-gold-500/10 rounded-full blur-2xl -mr-10 -mt-10">
                        </div>

                        <div class="flex justify-between items-start mb-6">
                            <div class="flex items-center gap-3">
                                <img src="../assets/img/images/logo-pondok.png"
                                    class="w-10 h-10 object-contain drop-shadow-md">
                                <div>
                                    <h3 class="text-xs font-bold text-gold-400 tracking-wider">KARTU SANTRI</h3>
                                    <p class="text-[10px] text-slate-400">Ponpes Al Ihsan Baron</p>
                                </div>
                            </div>
                            <div class="bg-white p-1 rounded-lg">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=<?= $barcode_code ?>"
                                    class="w-12 h-12" alt="QR Code">
                            </div>
                        </div>

                        <div class="mb-6">
                            <p class="text-xs text-slate-400 mb-1">Nomor Induk Santri (NIS)</p>
                            <p class="text-2xl font-mono tracking-widest text-white font-bold"
                                style="text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                                <?= htmlspecialchars($nis) ?>
                            </p>
                        </div>

                        <div class="flex justify-between items-end border-t border-white/10 pt-3">
                            <div>
                                <p class="text-[10px] text-slate-400">Nama Santri</p>
                                <p class="text-sm font-semibold truncate max-w-[150px]">
                                    <?= htmlspecialchars($nama_santri) ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-slate-400">ID Barcode</p>
                                <p class="text-xs font-mono text-gold-300"><?= $barcode_code ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 -mt-20 relative z-20 space-y-10">

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div
                class="bg-white p-4 rounded-2xl shadow-soft border border-slate-100 flex flex-col items-center justify-center text-center hover:-translate-y-1 transition-transform">
                <span class="text-3xl font-bold text-primary-600 mb-1"><?= count($tiket_saya) ?></span>
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Event Diikuti</span>
            </div>
            <div
                class="bg-white p-4 rounded-2xl shadow-soft border border-slate-100 flex flex-col items-center justify-center text-center hover:-translate-y-1 transition-transform">
                <span class="text-3xl font-bold text-gold-500 mb-1"><?= count($agendas) ?></span>
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Agenda Baru</span>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-6 px-1">
                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-ticket-alt text-gold-500"></i> Event Saya
                </h2>
                <?php if (count($tiket_saya) > 0): ?>
                    <a href="tiket_saya.php"
                        class="text-xs font-bold text-primary-600 hover:text-primary-800 transition-colors">Lihat Semua
                        →</a>
                <?php endif; ?>
            </div>

            <?php if (count($tiket_saya) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach (array_slice($tiket_saya, 0, 3) as $row): ?>
                        <?php
                        $is_lunas = ($row['status_pembayaran'] == 'paid' || $row['status_pembayaran'] == 'free');
                        $is_internal = ($row['didaftarkan_oleh'] == 'admin'); // Cek Pendaftar
                        $poster = !empty($row['poster']) ? '../assets/img/posters/' . $row['poster'] : '../assets/img/default-event.jpg';
                        ?>
                        <div
                            class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden group hover:shadow-lg transition-all">
                            <div class="h-32 bg-gray-200 relative overflow-hidden">
                                <img src="<?= $poster ?>"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <span
                                    class="absolute top-3 right-3 px-2 py-1 rounded-md text-[10px] font-bold text-white backdrop-blur-md <?= $is_lunas ? 'bg-green-500/80' : 'bg-amber-500/80' ?>">
                                    <?= $is_lunas ? 'TERDAFTAR' : 'MENUNGGU' ?>
                                </span>
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold text-slate-800 text-sm mb-1 truncate">
                                    <?= htmlspecialchars($row['judul']) ?></h3>
                                <p class="text-xs text-slate-500 mb-3 flex items-center gap-1">
                                    <i class="far fa-calendar"></i> <?= date('d M Y', strtotime($row['tanggal_waktu'])) ?>
                                </p>

                                <?php if ($is_internal): ?>
                                    <div
                                        class="w-full py-2 bg-slate-100 text-slate-500 text-xs font-bold text-center rounded-lg border border-slate-200 flex items-center justify-center gap-1">
                                        <i class="fas fa-id-card"></i> Gunakan Kartu Santri
                                    </div>
                                <?php elseif ($is_lunas): ?>
                                    <a href="cetak_tiket.php?id=<?= $row['id'] ?>" target="_blank"
                                        class="block w-full py-2 bg-primary-50 text-primary-700 text-xs font-bold text-center rounded-lg hover:bg-primary-100 transition-colors">
                                        <i class="fas fa-qrcode"></i> Buka E-Ticket
                                    </a>
                                <?php else: ?>
                                    <a href="<?= $row['payment_url'] ?>" target="_blank"
                                        class="block w-full py-2 bg-gold-50 text-gold-700 text-xs font-bold text-center rounded-lg hover:bg-gold-100 transition-colors">
                                        Bayar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-2xl p-8 text-center border-2 border-dashed border-slate-200">
                    <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-ticket-alt text-slate-400"></i>
                    </div>
                    <p class="text-slate-500 text-sm">Belum ada event yang diikuti.</p>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <div class="flex items-center justify-between mb-6 px-1">
                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-bullhorn text-gold-500"></i> Informasi & Agenda
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($agendas as $agenda): ?>
                    <div
                        class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden hover:shadow-lg transition-all duration-300 group flex flex-col h-full">
                        <div class="h-40 relative overflow-hidden">
                            <?php if ($agenda['poster']): ?>
                                <img src="../assets/img/posters/<?= htmlspecialchars($agenda['poster']) ?>"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <?php else: ?>
                                <div class="w-full h-full bg-primary-800 flex items-center justify-center">
                                    <i class="fas fa-image text-3xl text-white/30"></i>
                                </div>
                            <?php endif; ?>
                            <div class="absolute top-3 left-3">
                                <span
                                    class="bg-white/90 backdrop-blur text-primary-800 text-[10px] font-bold px-2 py-1 rounded shadow-sm">
                                    <?= strtoupper($agenda['visibilitas']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="p-5 flex-1 flex flex-col">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-bold text-slate-800 leading-snug line-clamp-2">
                                    <?= htmlspecialchars($agenda['judul']) ?>
                                </h3>
                            </div>
                            <div class="flex items-center gap-3 text-xs text-slate-500 mb-4">
                                <span class="flex items-center gap-1"><i class="far fa-clock text-gold-500"></i>
                                    <?= date('d M, H:i', strtotime($agenda['tanggal_waktu'])) ?></span>
                                <span class="flex items-center gap-1 truncate max-w-[100px]"><i
                                        class="fas fa-map-marker-alt text-primary-500"></i>
                                    <?= htmlspecialchars($agenda['lokasi']) ?></span>
                            </div>
                            <div class="mt-auto pt-4 border-t border-slate-50 flex items-center justify-between">
                                <span class="text-sm font-bold text-slate-700">
                                    <?= ($agenda['tipe_event'] == 'gratis') ? 'Gratis' : 'Rp ' . number_format($agenda['harga'], 0, ',', '.') ?>
                                </span>
                                <a href="detail_event.php?id=<?= $agenda['id'] ?>"
                                    class="text-xs font-bold text-white bg-slate-900 hover:bg-primary-600 px-4 py-2 rounded-lg transition-colors">
                                    Detail
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</div>

<?php require_once 'templates/footer.php'; ?>