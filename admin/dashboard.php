<?php
// 1. Mulai sesi


// 2. Panggil koneksi database (PENTING: Path harus benar)
// Menggunakan __DIR__ agar path relatif dari file ini (admin/) mundur satu folder (../) ke core/
require_once __DIR__ . '/../core/koneksi.php';
require_once __DIR__ . '/templates/header.php';

// 3. Cek sesi login & role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penyelenggara') {
    header("Location: ../login.php");
    exit();
}


// --- PERBAIKAN UTAMA: DEFINISI VARIABEL AGAR TIDAK UNDEFINED ---

// A. Tentukan Penyelenggara ID untuk Query Database
// Prioritas: Session ID Bersama -> Owner ID -> User ID Login
$penyelenggara_id = $_SESSION['penyelenggara_id_bersama'] ?? $_SESSION['owner_id'] ?? $_SESSION['user_id'];

// B. Tentukan Nama User untuk Tampilan
// Cek 'nama_lengkap' dulu, kalau tidak ada cek 'nama_user', kalau tidak ada pakai 'Admin'
$nama_user_display = $_SESSION['nama_lengkap'] ?? $_SESSION['nama_user'] ?? 'Admin';

// C. Tentukan Foto Profil
$foto_profil = !empty($_SESSION['foto_profil']) ? $_SESSION['foto_profil'] : '../assets/img/admin.jpg';

// D. Cek Status Owner
$is_owner = !isset($_SESSION['owner_id']) || $_SESSION['owner_id'] === null;

// E. Variabel Halaman
$page_title = 'Dashboard';
$current_page = 'dashboard';

// --- QUERY DATA STATISTIK ---
try {
    // 1. Jumlah workshop
    $stmt_workshop = $pdo->prepare("SELECT COUNT(*) FROM workshops WHERE penyelenggara_id = ?");
    $stmt_workshop->execute([$penyelenggara_id]);
    $total_workshop = $stmt_workshop->fetchColumn();

    // 2. Jumlah total pendaftar
    $stmt_pendaftar = $pdo->prepare("SELECT COUNT(*) FROM pendaftaran p 
                                     JOIN workshops w ON p.workshop_id = w.id 
                                     WHERE w.penyelenggara_id = ?");
    $stmt_pendaftar->execute([$penyelenggara_id]);
    $total_pendaftar = $stmt_pendaftar->fetchColumn();

    // 3. Jumlah peserta hadir
    $stmt_hadir = $pdo->prepare("SELECT COUNT(*) FROM pendaftaran p 
                                 JOIN workshops w ON p.workshop_id = w.id 
                                 WHERE w.penyelenggara_id = ? AND p.status_kehadiran = 'hadir'");
    $stmt_hadir->execute([$penyelenggara_id]);
    $total_hadir = $stmt_hadir->fetchColumn();

    // 4. Jumlah sertifikat terkirim
    $stmt_sertifikat = $pdo->prepare("SELECT COUNT(*) FROM pendaftaran p 
                                      JOIN workshops w ON p.workshop_id = w.id 
                                      WHERE w.penyelenggara_id = ? AND p.sertifikat_status = 'terkirim'");
    $stmt_sertifikat->execute([$penyelenggara_id]);
    $total_sertifikat = $stmt_sertifikat->fetchColumn();

    // 5. Workshop terbaru
    $stmt_terbaru = $pdo->prepare("SELECT * FROM workshops 
                                   WHERE penyelenggara_id = ? 
                                   ORDER BY created_at DESC LIMIT 5");
    $stmt_terbaru->execute([$penyelenggara_id]);
    $workshop_terbaru = $stmt_terbaru->fetchAll(PDO::FETCH_ASSOC);

    // 6. Pendaftar terbaru
    $stmt_pendaftar_terbaru = $pdo->prepare("SELECT p.*, w.judul as workshop_judul 
                                             FROM pendaftaran p 
                                             JOIN workshops w ON p.workshop_id = w.id 
                                             WHERE w.penyelenggara_id = ? 
                                             ORDER BY p.created_at DESC LIMIT 5");
    $stmt_pendaftar_terbaru->execute([$penyelenggara_id]);
    $pendaftar_terbaru = $stmt_pendaftar_terbaru->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}
?>

<!-- Hero Welcome Section -->
<div
    class="relative overflow-hidden rounded-2xl mb-8 bg-gradient-to-br from-primary-900 via-primary-800 to-primary-950 text-white p-8 shadow-2xl border border-primary-700/50">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-5">
        <div class="absolute top-0 left-0 w-72 h-72 bg-gold-500 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-primary-600 rounded-full blur-3xl"></div>
    </div>

    <div class="relative z-10">
        <div class="flex flex-col md:flex-row items-center justify-between">
            <div class="flex-1 mb-8 md:mb-0 md:mr-8">
                <div
                    class="inline-flex items-center px-4 py-2 rounded-full bg-primary-800/50 border border-primary-700 mb-4">
                    <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse mr-2"></div>
                    <span class="text-sm font-medium text-primary-200">Panel Admin Aktif</span>
                </div>

                <h1 class="text-3xl md:text-4xl font-bold mb-3 leading-tight">
                    <span class="bg-gradient-to-r from-white via-gold-200 to-white bg-clip-text text-transparent">
                        Selamat Datang, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>!
                    </span>
                </h1>
                <p class="text-primary-200/80 text-lg max-w-2xl mb-6">
                    Kelola semua aktivitas workshop dan pantau performa sistem dengan mudah melalui dashboard modern
                    ini.
                </p>

                <div class="flex flex-wrap gap-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full bg-emerald-400 shadow-glow animate-pulse"></div>
                        <span class="text-sm font-medium">Sistem: <span class="text-gold-300">Online</span></span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-calendar-alt text-gold-400"></i>
                        <span class="text-sm"><?= date('d F Y') ?></span>
                    </div>
                </div>
            </div>

            <div class="relative">
                <div
                    class="w-24 h-24 rounded-2xl bg-gradient-to-br from-gold-500 to-primary-600 flex items-center justify-center shadow-2xl shadow-gold-500/30">
                    <i class="fas fa-chart-line text-white text-3xl"></i>
                </div>
                <div
                    class="absolute -top-2 -right-2 w-10 h-10 bg-gradient-to-br from-primary-400 to-gold-400 rounded-full flex items-center justify-center border-4 border-primary-900">
                    <i class="fas fa-star text-primary-900 text-sm"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Workshop Card -->
    <div class="group relative">
        <div
            class="absolute inset-0 bg-gradient-to-r from-primary-600 to-gold-500 rounded-2xl blur opacity-20 group-hover:opacity-30 transition-opacity duration-300">
        </div>
        <div
            class="relative bg-white rounded-2xl shadow-lg border border-primary-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
            <div
                class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-primary-50 to-gold-50 rounded-full -translate-y-12 translate-x-6">
            </div>
            <div class="p-6 relative">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium mb-1">Total Workshop</p>
                        <h3 class="text-3xl font-bold text-slate-800"><?= $total_workshop ?></h3>
                        <div class="flex items-center mt-2">
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="bg-gradient-to-r from-primary-500 to-gold-400 h-2 rounded-full"
                                    style="width: <?= min(100, ($total_workshop / 10) * 100) ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="w-14 h-14 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary-200">
                        <i class="fas fa-chalkboard-teacher text-white text-2xl"></i>
                    </div>
                </div>
            </div>
            <div
                class="px-6 py-3 border-t border-slate-100 bg-gradient-to-r from-primary-50/30 to-transparent rounded-b-2xl">
                <a href="kelola_event.php"
                    class="text-primary-700 hover:text-primary-800 text-sm font-medium transition-colors flex items-center group/link">
                    <span>Kelola Workshop</span>
                    <i class="fas fa-arrow-right ml-2 text-xs group-hover/link:translate-x-1 transition-transform"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Total Pendaftar Card -->
    <div class="group relative">
        <div
            class="absolute inset-0 bg-gradient-to-r from-primary-500 to-emerald-400 rounded-2xl blur opacity-20 group-hover:opacity-30 transition-opacity duration-300">
        </div>
        <div
            class="relative bg-white rounded-2xl shadow-lg border border-primary-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
            <div
                class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-emerald-50 to-primary-50 rounded-full -translate-y-12 translate-x-6">
            </div>
            <div class="p-6 relative">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium mb-1">Total Pendaftar</p>
                        <h3 class="text-3xl font-bold text-slate-800"><?= $total_pendaftar ?></h3>
                        <div class="flex items-center mt-2">
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="bg-gradient-to-r from-emerald-500 to-primary-400 h-2 rounded-full"
                                    style="width: <?= min(100, ($total_pendaftar / 50) * 100) ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-200">
                        <i class="fas fa-users text-white text-2xl"></i>
                    </div>
                </div>
            </div>
            <div
                class="px-6 py-3 border-t border-slate-100 bg-gradient-to-r from-emerald-50/30 to-transparent rounded-b-2xl">
                <a href="kelola_pendaftar.php"
                    class="text-emerald-700 hover:text-emerald-800 text-sm font-medium transition-colors flex items-center group/link">
                    <span>Lihat Pendaftar</span>
                    <i class="fas fa-arrow-right ml-2 text-xs group-hover/link:translate-x-1 transition-transform"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Peserta Hadir Card -->
    <div class="group relative">
        <div
            class="absolute inset-0 bg-gradient-to-r from-gold-400 to-amber-500 rounded-2xl blur opacity-20 group-hover:opacity-30 transition-opacity duration-300">
        </div>
        <div
            class="relative bg-white rounded-2xl shadow-lg border border-primary-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
            <div
                class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-gold-50 to-amber-50 rounded-full -translate-y-12 translate-x-6">
            </div>
            <div class="p-6 relative">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium mb-1">Peserta Hadir</p>
                        <h3 class="text-3xl font-bold text-slate-800"><?= $total_hadir ?></h3>
                        <p class="text-gold-600 text-sm font-medium mt-1 flex items-center">
                            <i class="fas fa-trending-up mr-1"></i>
                            <?php if ($total_pendaftar > 0): ?>
                                <?= round(($total_hadir / $total_pendaftar) * 100) ?>% Attendance
                            <?php else: ?>
                                0% Attendance
                            <?php endif; ?>
                        </p>
                    </div>
                    <div
                        class="w-14 h-14 bg-gradient-to-br from-gold-500 to-gold-600 rounded-xl flex items-center justify-center shadow-lg shadow-gold-200">
                        <i class="fas fa-user-check text-white text-2xl"></i>
                    </div>
                </div>
            </div>
            <div
                class="px-6 py-3 border-t border-slate-100 bg-gradient-to-r from-gold-50/30 to-transparent rounded-b-2xl">
                <a href="kelola_pendaftar.php?filter=hadir"
                    class="text-gold-700 hover:text-gold-800 text-sm font-medium transition-colors flex items-center group/link">
                    <span>Detail Kehadiran</span>
                    <i class="fas fa-arrow-right ml-2 text-xs group-hover/link:translate-x-1 transition-transform"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Sertifikat Terkirim Card -->
    <div class="group relative">
        <div
            class="absolute inset-0 bg-gradient-to-r from-violet-500 to-primary-400 rounded-2xl blur opacity-20 group-hover:opacity-30 transition-opacity duration-300">
        </div>
        <div
            class="relative bg-white rounded-2xl shadow-lg border border-primary-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
            <div
                class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-violet-50 to-primary-50 rounded-full -translate-y-12 translate-x-6">
            </div>
            <div class="p-6 relative">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium mb-1">Sertifikat Terkirim</p>
                        <h3 class="text-3xl font-bold text-slate-800"><?= $total_sertifikat ?></h3>
                        <p class="text-violet-600 text-sm font-medium mt-1 flex items-center">
                            <i class="fas fa-paper-plane mr-1"></i>
                            <?php if ($total_pendaftar > 0): ?>
                                <?= round(($total_sertifikat / $total_pendaftar) * 100) ?>% Delivered
                            <?php else: ?>
                                0% Delivered
                            <?php endif; ?>
                        </p>
                    </div>
                    <div
                        class="w-14 h-14 bg-gradient-to-br from-violet-500 to-violet-600 rounded-xl flex items-center justify-center shadow-lg shadow-violet-200">
                        <i class="fas fa-certificate text-white text-2xl"></i>
                    </div>
                </div>
            </div>
            <div
                class="px-6 py-3 border-t border-slate-100 bg-gradient-to-r from-violet-50/30 to-transparent rounded-b-2xl">
                <a href="kelola_pendaftar.php?filter=sertifikat"
                    class="text-violet-700 hover:text-violet-800 text-sm font-medium transition-colors flex items-center group/link">
                    <span>Kelola Sertifikat</span>
                    <i class="fas fa-arrow-right ml-2 text-xs group-hover/link:translate-x-1 transition-transform"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-8">
    <!-- Workshop Terbaru & Pendaftar Terbaru -->
    <div class="xl:col-span-2 space-y-8">
        <!-- Workshop Terbaru -->
        <div class="bg-white rounded-2xl shadow-lg border border-primary-100 overflow-hidden">
            <div
                class="px-6 py-5 bg-gradient-to-r from-primary-50 to-primary-100/50 border-b border-primary-200 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-primary-500 to-gold-400 rounded-lg flex items-center justify-center shadow-sm">
                        <i class="fas fa-calendar-alt text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Workshop Terbaru</h3>
                        <p class="text-sm text-slate-600">5 workshop terakhir yang dibuat</p>
                    </div>
                </div>
                <span
                    class="bg-gradient-to-r from-primary-500 to-primary-600 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-sm">
                    <?= count($workshop_terbaru) ?> Active
                </span>
            </div>

            <div class="divide-y divide-slate-100">
                <?php if (count($workshop_terbaru) > 0): ?>
                    <?php foreach ($workshop_terbaru as $workshop): ?>
                        <div class="px-6 py-4 hover:bg-primary-50/30 transition-all duration-200 group">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h4
                                            class="font-bold text-slate-800 group-hover:text-primary-700 transition-colors truncate">
                                            <?= htmlspecialchars($workshop['judul']) ?>
                                        </h4>
                                        <?php if ($workshop['tipe_event'] == 'berbayar'): ?>
                                            <span
                                                class="bg-gradient-to-r from-gold-500 to-gold-600 text-white text-xs font-bold px-2 py-0.5 rounded-full whitespace-nowrap shadow-sm">
                                                <i class="fas fa-coins mr-1"></i>Berbayar
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white text-xs font-bold px-2 py-0.5 rounded-full whitespace-nowrap shadow-sm">
                                                <i class="fas fa-gift mr-1"></i>Gratis
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                                        <div class="flex items-center text-sm text-slate-600">
                                            <i class="fas fa-map-marker-alt mr-3 text-primary-500 w-4"></i>
                                            <span class="truncate"><?= htmlspecialchars($workshop['lokasi']) ?></span>
                                        </div>
                                        <div class="flex items-center text-sm text-slate-600">
                                            <i class="fas fa-clock mr-3 text-primary-500 w-4"></i>
                                            <span><?= date('d M Y', strtotime($workshop['tanggal_waktu'])) ?></span>
                                        </div>
                                        <div class="flex items-center text-sm text-slate-600">
                                            <i class="fas fa-stopwatch mr-3 text-primary-500 w-4"></i>
                                            <span><?= date('H:i', strtotime($workshop['tanggal_waktu'])) ?> WIB</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <span
                                        class="bg-primary-100 text-primary-700 text-xs font-medium px-3 py-1 rounded-full border border-primary-200">
                                        <?= ucfirst($workshop['visibilitas']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="px-6 py-10 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-50 mb-4">
                            <i class="fas fa-calendar-times text-primary-400 text-2xl"></i>
                        </div>
                        <p class="text-slate-600 font-medium mb-2">Belum ada workshop yang dibuat</p>
                        <p class="text-slate-500 text-sm mb-4">Mulai buat workshop pertama Anda</p>
                        <a href="kelola_event.php"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 text-white font-medium rounded-lg hover:shadow-lg transition-all duration-200">
                            <i class="fas fa-plus mr-2"></i>
                            Buat Workshop
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 bg-gradient-to-r from-primary-50/50 to-transparent">
                <a href="kelola_event.php"
                    class="text-primary-700 hover:text-primary-800 font-medium transition-colors flex items-center justify-center group/link">
                    <span>Lihat Semua Workshop</span>
                    <i class="fas fa-arrow-right ml-2 text-sm group-hover/link:translate-x-1 transition-transform"></i>
                </a>
            </div>
        </div>

        <!-- Pendaftar Terbaru -->
        <div class="bg-white rounded-2xl shadow-lg border border-primary-100 overflow-hidden">
            <div
                class="px-6 py-5 bg-gradient-to-r from-emerald-50 to-emerald-100/50 border-b border-emerald-200 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-primary-400 rounded-lg flex items-center justify-center shadow-sm">
                        <i class="fas fa-users text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Pendaftar Terbaru</h3>
                        <p class="text-sm text-slate-600">5 pendaftar terbaru workshop</p>
                    </div>
                </div>
                <span
                    class="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-sm">
                    <?= count($pendaftar_terbaru) ?> New
                </span>
            </div>

            <div class="divide-y divide-slate-100">
                <?php if (count($pendaftar_terbaru) > 0): ?>
                    <?php foreach ($pendaftar_terbaru as $pendaftar): ?>
                        <div class="px-6 py-4 hover:bg-emerald-50/30 transition-all duration-200 group">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="relative">
                                        <div
                                            class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                                            <?= strtoupper(substr($pendaftar['nama_peserta'], 0, 1)) ?>
                                        </div>
                                        <?php if ($pendaftar['status_kehadiran'] == 'hadir'): ?>
                                            <div
                                                class="absolute -bottom-1 -right-1 w-5 h-5 bg-gradient-to-br from-emerald-400 to-emerald-500 rounded-full border-2 border-white flex items-center justify-center">
                                                <i class="fas fa-check text-white text-[8px]"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <h4
                                            class="font-bold text-slate-800 group-hover:text-emerald-700 transition-colors truncate">
                                            <?= htmlspecialchars($pendaftar['nama_peserta']) ?>
                                        </h4>
                                        <p class="text-sm text-slate-600 truncate mb-1">
                                            <?= htmlspecialchars($pendaftar['email_peserta']) ?>
                                        </p>
                                        <p class="text-xs text-slate-500 flex items-center">
                                            <i class="fas fa-chalkboard-teacher mr-2 text-primary-500"></i>
                                            <span class="truncate"><?= htmlspecialchars($pendaftar['workshop_judul']) ?></span>
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-col items-end space-y-2">
                                    <?php if ($pendaftar['status_kehadiran'] == 'hadir'): ?>
                                        <span
                                            class="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white text-xs font-bold px-3 py-1 rounded-full">
                                            <i class="fas fa-check mr-1"></i>Hadir
                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="bg-gradient-to-r from-gold-500 to-gold-600 text-white text-xs font-bold px-3 py-1 rounded-full">
                                            <i class="fas fa-clock mr-1"></i>Menunggu
                                        </span>
                                    <?php endif; ?>
                                    <span class="text-xs text-slate-500">
                                        <?= date('d M, H:i', strtotime($pendaftar['created_at'])) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="px-6 py-10 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-50 mb-4">
                            <i class="fas fa-user-slash text-emerald-400 text-2xl"></i>
                        </div>
                        <p class="text-slate-600 font-medium mb-2">Belum ada pendaftar workshop</p>
                        <p class="text-slate-500 text-sm">Pendaftar akan muncul di sini</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 bg-gradient-to-r from-emerald-50/50 to-transparent">
                <a href="kelola_pendaftar.php"
                    class="text-emerald-700 hover:text-emerald-800 font-medium transition-colors flex items-center justify-center group/link">
                    <span>Lihat Semua Pendaftar</span>
                    <i class="fas fa-arrow-right ml-2 text-sm group-hover/link:translate-x-1 transition-transform"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Stats -->
    <div class="space-y-8">
        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl shadow-lg border border-primary-100 overflow-hidden">
            <div class="px-6 py-5 bg-gradient-to-r from-gold-50 to-gold-100/50 border-b border-gold-200">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-gold-500 to-amber-500 rounded-lg flex items-center justify-center shadow-sm">
                        <i class="fas fa-bolt text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Aksi Cepat</h3>
                        <p class="text-sm text-slate-600">Akses fitur utama dengan cepat</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <!-- Manage Events -->
                <a href="kelola_event.php"
                    class="flex items-center p-4 rounded-xl transition-all duration-200 group bg-gradient-to-r from-primary-50 to-primary-100/30 hover:from-primary-100 hover:to-primary-200 border border-primary-200 hover:border-primary-300 hover:shadow-md">
                    <div
                        class="w-12 h-12 rounded-lg bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center mr-4 shadow-sm group-hover:scale-110 transition-transform duration-200">
                        <i class="fas fa-calendar-plus text-white text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-slate-800 group-hover:text-primary-700">Kelola Event</h4>
                        <p class="text-sm text-slate-600">Buat dan kelola workshop</p>
                    </div>
                    <div
                        class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center group-hover:bg-primary-200 transition-colors">
                        <i class="fas fa-chevron-right text-primary-600 text-xs"></i>
                    </div>
                </a>

                <!-- View Registrants -->
                <a href="kelola_pendaftar.php"
                    class="flex items-center p-4 rounded-xl transition-all duration-200 group bg-gradient-to-r from-emerald-50 to-emerald-100/30 hover:from-emerald-100 hover:to-emerald-200 border border-emerald-200 hover:border-emerald-300 hover:shadow-md">
                    <div
                        class="w-12 h-12 rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center mr-4 shadow-sm group-hover:scale-110 transition-transform duration-200">
                        <i class="fas fa-users text-white text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-slate-800 group-hover:text-emerald-700">Lihat Pendaftar</h4>
                        <p class="text-sm text-slate-600">Kelola peserta workshop</p>
                    </div>
                    <div
                        class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                        <i class="fas fa-chevron-right text-emerald-600 text-xs"></i>
                    </div>
                </a>

                <!-- Scan Check-in -->
                <a href="scan_checkin.php"
                    class="flex items-center p-4 rounded-xl transition-all duration-200 group bg-gradient-to-r from-blue-50 to-blue-100/30 hover:from-blue-100 hover:to-blue-200 border border-blue-200 hover:border-blue-300 hover:shadow-md">
                    <div
                        class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center mr-4 shadow-sm group-hover:scale-110 transition-transform duration-200">
                        <i class="fas fa-qrcode text-white text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-slate-800 group-hover:text-blue-700">Scan Check-in</h4>
                        <p class="text-sm text-slate-600">Scan QR code peserta</p>
                    </div>
                    <div
                        class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                        <i class="fas fa-chevron-right text-blue-600 text-xs"></i>
                    </div>
                </a>

                <?php if ($is_owner): ?>
                    <!-- Manage Team -->
                    <a href="kelola_tim.php"
                        class="flex items-center p-4 rounded-xl transition-all duration-200 group bg-gradient-to-r from-violet-50 to-violet-100/30 hover:from-violet-100 hover:to-violet-200 border border-violet-200 hover:border-violet-300 hover:shadow-md">
                        <div
                            class="w-12 h-12 rounded-lg bg-gradient-to-br from-violet-500 to-violet-600 flex items-center justify-center mr-4 shadow-sm group-hover:scale-110 transition-transform duration-200">
                            <i class="fas fa-users-cog text-white text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-slate-800 group-hover:text-violet-700">Kelola Tim</h4>
                            <p class="text-sm text-slate-600">Atur anggota tim</p>
                        </div>
                        <div
                            class="w-8 h-8 rounded-full bg-violet-100 flex items-center justify-center group-hover:bg-violet-200 transition-colors">
                            <i class="fas fa-chevron-right text-violet-600 text-xs"></i>
                        </div>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- System Stats -->
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary-900 via-primary-800 to-primary-950 text-white p-6 border border-primary-700 shadow-xl">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-48 h-48 bg-gold-500 rounded-full blur-3xl"></div>
            </div>

            <div class="relative z-10">
                <div class="flex items-center space-x-3 mb-6">
                    <div
                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-gold-500 to-gold-600 flex items-center justify-center">
                        <i class="fas fa-chart-bar text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Statistik Sistem</h3>
                        <p class="text-sm text-primary-200/80">Overview performa</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- Workshop Stats -->
                    <div class="flex items-center justify-between py-3 border-b border-primary-800">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-primary-800 flex items-center justify-center">
                                <i class="fas fa-chalkboard-teacher text-gold-400 text-sm"></i>
                            </div>
                            <span class="text-primary-200">Workshop Aktif</span>
                        </div>
                        <span class="font-bold text-xl text-white"><?= $total_workshop ?></span>
                    </div>

                    <!-- Registrants Stats -->
                    <div class="flex items-center justify-between py-3 border-b border-primary-800">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-primary-800 flex items-center justify-center">
                                <i class="fas fa-users text-gold-400 text-sm"></i>
                            </div>
                            <span class="text-primary-200">Total Pendaftar</span>
                        </div>
                        <span class="font-bold text-xl text-white"><?= $total_pendaftar ?></span>
                    </div>

                    <!-- Attendance Stats -->
                    <div class="flex items-center justify-between py-3 border-b border-primary-800">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-primary-800 flex items-center justify-center">
                                <i class="fas fa-user-check text-gold-400 text-sm"></i>
                            </div>
                            <span class="text-primary-200">Rate Kehadiran</span>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-xl text-white">
                                <?php if ($total_pendaftar > 0): ?>
                                    <?= round(($total_hadir / $total_pendaftar) * 100) ?>%
                                <?php else: ?>
                                    0%
                                <?php endif; ?>
                            </span>
                            <div class="text-xs text-primary-300">
                                <?= $total_hadir ?> dari <?= $total_pendaftar ?>
                            </div>
                        </div>
                    </div>

                    <!-- Certificate Stats -->
                    <div class="flex items-center justify-between py-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-primary-800 flex items-center justify-center">
                                <i class="fas fa-certificate text-gold-400 text-sm"></i>
                            </div>
                            <span class="text-primary-200">Sertifikat</span>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-xl text-white"><?= $total_sertifikat ?></span>
                            <div class="text-xs text-primary-300">Terkirim</div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-6 pt-4 border-t border-primary-800">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                            <span class="text-xs text-primary-300">Sistem Aktif</span>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-primary-300">Terakhir Update</div>
                            <div class="text-sm font-medium text-gold-300"><?= date('d M Y H:i') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once BASE_PATH . '/admin/templates/footer.php';
?>