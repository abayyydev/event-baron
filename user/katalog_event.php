<?php
// user/katalog_event.php

if (session_status() === PHP_SESSION_NONE)
    session_start();

// 1. CEK LOGIN SANTRI
if (!isset($_SESSION['santri_id']) || $_SESSION['role'] !== 'peserta') {
    header("Location: ../login.php");
    exit();
}

require_once '../core/koneksi.php';
$page_title = "Katalog Event";
$current_page = "katalog_event";
require_once 'templates/header.php';

$santri_id = $_SESSION['santri_id'];

// 2. LOGIKA SEARCH & FILTER
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter = isset($_GET['tipe']) ? $_GET['tipe'] : 'all';

// Base Query
// Kita join ke pendaftaran untuk cek apakah santri ini sudah daftar atau belum (is_registered)
$sql = "SELECT w.*, 
        (SELECT COUNT(*) FROM pendaftaran p WHERE p.workshop_id = w.id AND p.santri_id = :sid) as is_registered
        FROM workshops w 
        WHERE w.tanggal_waktu >= NOW()"; // Hanya tampilkan event yang belum lewat

$params = ['sid' => $santri_id];

// Tambah Filter Search
if (!empty($search)) {
    $sql .= " AND (w.judul LIKE :search OR w.deskripsi LIKE :search OR w.lokasi LIKE :search)";
    $params['search'] = "%$search%";
}

// Tambah Filter Tipe (Gratis/Berbayar)
if ($filter == 'gratis') {
    $sql .= " AND (w.tipe_event = 'gratis' OR w.harga <= 0)";
} elseif ($filter == 'berbayar') {
    $sql .= " AND (w.tipe_event = 'berbayar' AND w.harga > 0)";
}

// Urutkan event terdekat duluan
$sql .= " ORDER BY w.tanggal_waktu ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $events = [];
}
?>

<div class="min-h-screen bg-slate-50 font-sans pb-20">

    <div class="bg-primary-900 pb-28 pt-10 px-4 rounded-b-[3rem] shadow-xl relative overflow-hidden">
        <div
            class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-primary-800 rounded-full opacity-50 blur-3xl animate-pulse-subtle">
        </div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-gold-500 rounded-full opacity-20 blur-2xl">
        </div>

        <div class="max-w-6xl mx-auto px-4 relative z-10 text-center">
            <span
                class="text-primary-200 text-xs font-bold uppercase tracking-widest border border-primary-700/50 px-3 py-1 rounded-full bg-primary-800/50 backdrop-blur-md">
                Agenda Pondok
            </span>
            <h1 class="text-3xl md:text-5xl font-extrabold text-white mt-4 leading-tight mb-4">
                Jelajahi Event Seru
            </h1>
            <p class="text-primary-100/90 text-sm md:text-base max-w-xl mx-auto">
                Temukan berbagai kegiatan bermanfaat, workshop, dan kajian untuk meningkatkan kapasitas diri.
            </p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 -mt-20 relative z-20 space-y-8">

        <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-4">
            <form action="" method="GET" class="flex flex-col md:flex-row gap-4">

                <div class="flex-grow relative">
                    <i class="fas fa-search absolute left-4 top-3.5 text-slate-400"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                        class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all text-sm font-medium"
                        placeholder="Cari judul event, topik, atau lokasi...">
                </div>

                <div class="relative min-w-[180px]">
                    <i class="fas fa-filter absolute left-4 top-3.5 text-slate-400"></i>
                    <select name="tipe" onchange="this.form.submit()"
                        class="w-full pl-11 pr-10 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-primary-500 outline-none text-sm font-medium appearance-none cursor-pointer hover:bg-slate-100 transition-colors">
                        <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>Semua Tipe</option>
                        <option value="gratis" <?= $filter == 'gratis' ? 'selected' : '' ?>>Gratis (Free)</option>
                        <option value="berbayar" <?= $filter == 'berbayar' ? 'selected' : '' ?>>Berbayar (Paid)</option>
                    </select>
                    <i
                        class="fas fa-chevron-down absolute right-4 top-4 text-slate-400 text-xs pointer-events-none"></i>
                </div>

                <button type="submit" class="md:hidden bg-primary-600 text-white font-bold py-3 rounded-xl shadow-md">
                    Terapkan
                </button>
            </form>
        </div>

        <?php if (count($events) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($events as $row):
                    $poster = !empty($row['poster']) ? '../assets/img/posters/' . $row['poster'] : '../assets/img/default-event.jpg';
                    $is_registered = $row['is_registered'] > 0; // Cek apakah sudah terdaftar
                    $is_free = ($row['tipe_event'] == 'gratis' || $row['harga'] <= 0);
                    ?>
                    <div
                        class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl transition-all duration-300 group flex flex-col h-full hover:-translate-y-1 relative">

                        <div class="h-48 bg-slate-200 relative overflow-hidden">
                            <img src="<?= $poster ?>"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">

                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-60 group-hover:opacity-80 transition-opacity">
                            </div>

                            <div class="absolute top-4 left-4 flex gap-2">
                                <span
                                    class="bg-white/90 backdrop-blur text-primary-900 text-[10px] font-bold px-3 py-1 rounded-full shadow-sm uppercase tracking-wide">
                                    <?= strtoupper($row['visibilitas']) ?>
                                </span>
                                <?php if ($is_registered): ?>
                                    <span
                                        class="bg-emerald-500/90 backdrop-blur text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-sm uppercase tracking-wide flex items-center gap-1">
                                        <i class="fas fa-check-circle"></i> Terdaftar
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="absolute top-4 right-4">
                                <?php if ($is_free): ?>
                                    <span
                                        class="bg-blue-500 text-white text-xs font-bold px-3 py-1.5 rounded-xl shadow-lg border border-white/20">
                                        FREE
                                    </span>
                                <?php else: ?>
                                    <span
                                        class="bg-amber-400 text-amber-900 text-xs font-bold px-3 py-1.5 rounded-xl shadow-lg border border-white/20">
                                        Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex items-center gap-4 text-xs text-slate-500 mb-3 font-medium">
                                <div class="flex items-center gap-1.5 bg-slate-50 px-2 py-1 rounded-md">
                                    <i class="far fa-calendar-alt text-gold-500"></i>
                                    <?= date('d M Y', strtotime($row['tanggal_waktu'])) ?>
                                </div>
                                <div class="flex items-center gap-1.5 bg-slate-50 px-2 py-1 rounded-md truncate max-w-[120px]">
                                    <i class="fas fa-map-marker-alt text-primary-500"></i>
                                    <span class="truncate"><?= htmlspecialchars($row['lokasi']) ?></span>
                                </div>
                            </div>

                            <h3
                                class="font-bold text-slate-800 text-lg leading-snug mb-2 line-clamp-2 group-hover:text-primary-700 transition-colors">
                                <?= htmlspecialchars($row['judul']) ?>
                            </h3>

                            <p class="text-slate-500 text-sm mb-6 line-clamp-2 leading-relaxed">
                                <?= htmlspecialchars(substr($row['deskripsi'], 0, 100)) ?>...
                            </p>

                            <div class="mt-auto pt-4 border-t border-slate-50">
                                <a href="daftar_event.php?id=<?= $row['id'] ?>"
                                    class="w-full flex items-center justify-center gap-2 bg-slate-900 hover:bg-primary-600 text-white font-bold py-3 rounded-xl transition-all shadow-md hover:shadow-lg group-hover:shadow-primary-500/20 text-sm">
                                    <?php if ($is_registered): ?>
                                        <i class="fas fa-user-plus"></i> Daftar Lagi
                                    <?php else: ?>
                                        Daftar Sekarang <i
                                            class="fas fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
                                    <?php endif; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-3xl p-12 text-center shadow-sm border border-slate-200">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 animate-pulse">
                    <i class="fas fa-search text-4xl text-slate-300"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-700">Event Tidak Ditemukan</h3>
                <p class="text-slate-500 mt-2 max-w-md mx-auto">
                    Maaf, kami tidak menemukan event dengan kata kunci "<b><?= htmlspecialchars($search) ?></b>" atau filter
                    yang Anda pilih.
                </p>
                <a href="katalog_event.php"
                    class="mt-6 inline-block px-6 py-2.5 bg-primary-600 text-white font-bold rounded-xl shadow hover:bg-primary-700 transition text-sm">
                    Reset Pencarian
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'templates/footer.php'; ?>