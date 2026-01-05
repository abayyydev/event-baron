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

<!-- Welcome Banner -->
<div
    class="bg-gradient-to-r from-slate-800 to-slate-900 text-white py-8 px-6 rounded-2xl shadow-lg mb-8 border border-slate-700">
    <div class="flex flex-col md:flex-row items-center justify-between">
        <div class="flex-1 mb-6 md:mb-0">
            <h1 class="text-3xl md:text-4xl font-bold mb-2">Selamat Datang,
                <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>! 👋
            </h1>
            <p class="text-slate-300 text-lg">Berikut adalah ringkasan aktivitas workshop dan performa Anda</p>
        </div>
        <div class="flex items-center space-x-4">
            <div class="text-right">
                <p class="text-slate-400 text-sm">Status Sistem</p>
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                    <span class="font-semibold text-white">Online</span>
                </div>
            </div>
            <div
                class="w-12 h-12 bg-amber-500/20 rounded-full flex items-center justify-center border border-amber-500/30">
                <i class="fas fa-chart-line text-amber-400 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Workshop Card -->
    <div
        class="bg-white rounded-2xl shadow-lg border border-slate-200 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium mb-1">Total Workshop</p>
                    <h3 class="text-3xl font-bold text-slate-800"><?= $total_workshop ?></h3>
                    <p class="text-emerald-600 text-sm font-medium mt-1 flex items-center">
                        <i class="fas fa-trending-up mr-1"></i>
                        Active
                    </p>
                </div>
                <div class="w-14 h-14 bg-amber-100 rounded-xl flex items-center justify-center border border-amber-200">
                    <i class="fas fa-chalkboard-teacher text-amber-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="border-t border-slate-100 px-6 py-3 bg-slate-50 rounded-b-2xl">
            <a href="kelola_event.php"
                class="text-slate-700 hover:text-slate-900 text-sm font-medium transition-colors flex items-center">
                Lihat semua
                <i class="fas fa-arrow-right ml-2 text-xs"></i>
            </a>
        </div>
    </div>

    <!-- Total Pendaftar Card -->
    <div
        class="bg-white rounded-2xl shadow-lg border border-slate-200 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium mb-1">Total Pendaftar</p>
                    <h3 class="text-3xl font-bold text-slate-800"><?= $total_pendaftar ?></h3>
                    <p class="text-blue-600 text-sm font-medium mt-1 flex items-center">
                        <i class="fas fa-users mr-1"></i>
                        Participants
                    </p>
                </div>
                <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center border border-blue-200">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="border-t border-slate-100 px-6 py-3 bg-slate-50 rounded-b-2xl">
            <a href="kelola_pendaftar.php"
                class="text-slate-700 hover:text-slate-900 text-sm font-medium transition-colors flex items-center">
                Lihat semua
                <i class="fas fa-arrow-right ml-2 text-xs"></i>
            </a>
        </div>
    </div>

    <!-- Peserta Hadir Card -->
    <div
        class="bg-white rounded-2xl shadow-lg border border-slate-200 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium mb-1">Peserta Hadir</p>
                    <h3 class="text-3xl font-bold text-slate-800"><?= $total_hadir ?></h3>
                    <p class="text-emerald-600 text-sm font-medium mt-1">
                        <?php if ($total_pendaftar > 0): ?>
                            <?= round(($total_hadir / $total_pendaftar) * 100) ?>% Attendance
                        <?php else: ?>
                            0% Attendance
                        <?php endif; ?>
                    </p>
                </div>
                <div
                    class="w-14 h-14 bg-emerald-100 rounded-xl flex items-center justify-center border border-emerald-200">
                    <i class="fas fa-user-check text-emerald-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="border-t border-slate-100 px-6 py-3 bg-slate-50 rounded-b-2xl">
            <a href="kelola_pendaftar.php?filter=hadir"
                class="text-slate-700 hover:text-slate-900 text-sm font-medium transition-colors flex items-center">
                Lihat detail
                <i class="fas fa-arrow-right ml-2 text-xs"></i>
            </a>
        </div>
    </div>

    <!-- Sertifikat Terkirim Card -->
    <div
        class="bg-white rounded-2xl shadow-lg border border-slate-200 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium mb-1">Sertifikat Terkirim</p>
                    <h3 class="text-3xl font-bold text-slate-800"><?= $total_sertifikat ?></h3>
                    <p class="text-violet-600 text-sm font-medium mt-1 flex items-center">
                        <i class="fas fa-paper-plane mr-1"></i>
                        Delivered
                    </p>
                </div>
                <div
                    class="w-14 h-14 bg-violet-100 rounded-xl flex items-center justify-center border border-violet-200">
                    <i class="fas fa-certificate text-violet-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="border-t border-slate-100 px-6 py-3 bg-slate-50 rounded-b-2xl">
            <a href="kelola_pendaftar.php?filter=sertifikat"
                class="text-slate-700 hover:text-slate-900 text-sm font-medium transition-colors flex items-center">
                Lihat detail
                <i class="fas fa-arrow-right ml-2 text-xs"></i>
            </a>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-8">
    <!-- Workshop Terbaru & Pendaftar Terbaru -->
    <div class="xl:col-span-2 space-y-8">
        <!-- Workshop Terbaru -->
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200">
            <div
                class="px-6 py-5 border-b border-slate-200 flex items-center justify-between bg-slate-50 rounded-t-2xl">
                <h3 class="text-lg font-semibold text-slate-800 flex items-center">
                    <i class="fas fa-calendar-alt text-amber-500 mr-3"></i>
                    Workshop Terbaru
                </h3>
                <span
                    class="bg-amber-100 text-amber-800 text-xs font-medium px-3 py-1 rounded-full border border-amber-200">
                    <?= count($workshop_terbaru) ?> Active
                </span>
            </div>
            <div class="divide-y divide-slate-100">
                <?php if (count($workshop_terbaru) > 0): ?>
                    <?php foreach ($workshop_terbaru as $workshop): ?>
                        <div class="px-6 py-4 hover:bg-slate-50 transition-colors group">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-slate-800 group-hover:text-amber-600 transition-colors truncate">
                                    <?= htmlspecialchars($workshop['judul']) ?>
                                </h4>
                                <span
                                    class="bg-emerald-100 text-emerald-800 text-xs font-medium px-2 py-1 rounded-full whitespace-nowrap ml-2 border border-emerald-200">
                                    <?= date('d M Y', strtotime($workshop['tanggal_waktu'])) ?>
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm text-slate-600">
                                <div class="flex items-center space-x-4">
                                    <span class="flex items-center">
                                        <i class="fas fa-map-marker-alt mr-2 text-slate-400"></i>
                                        <?= htmlspecialchars($workshop['lokasi']) ?>
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fas fa-clock mr-2 text-slate-400"></i>
                                        <?= date('H:i', strtotime($workshop['tanggal_waktu'])) ?>
                                    </span>
                                </div>
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded border border-blue-200">
                                    <?= $workshop['tipe_event'] == 'berbayar' ? 'Berbayar' : 'Gratis' ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="px-6 py-8 text-center">
                        <i class="fas fa-calendar-times text-slate-300 text-4xl mb-3"></i>
                        <p class="text-slate-500">Belum ada workshop yang dibuat</p>
                        <a href="kelola_event.php"
                            class="text-amber-600 hover:text-amber-700 text-sm font-medium mt-2 inline-block">
                            Buat workshop pertama Anda
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <a href="kelola_event.php"
                    class="text-slate-700 hover:text-slate-900 text-sm font-medium transition-colors flex items-center justify-center">
                    Lihat Semua Workshop
                    <i class="fas fa-arrow-right ml-2 text-xs"></i>
                </a>
            </div>
        </div>

        <!-- Pendaftar Terbaru -->
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200">
            <div
                class="px-6 py-5 border-b border-slate-200 flex items-center justify-between bg-slate-50 rounded-t-2xl">
                <h3 class="text-lg font-semibold text-slate-800 flex items-center">
                    <i class="fas fa-users text-blue-500 mr-3"></i>
                    Pendaftar Terbaru
                </h3>
                <span
                    class="bg-blue-100 text-blue-800 text-xs font-medium px-3 py-1 rounded-full border border-blue-200">
                    <?= count($pendaftar_terbaru) ?> New
                </span>
            </div>
            <div class="divide-y divide-slate-100">
                <?php if (count($pendaftar_terbaru) > 0): ?>
                    <?php foreach ($pendaftar_terbaru as $pendaftar): ?>
                        <div class="px-6 py-4 hover:bg-slate-50 transition-colors group">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-10 h-10 bg-gradient-to-r from-slate-700 to-slate-800 rounded-full flex items-center justify-center text-white font-semibold text-sm border border-slate-600">
                                        <?= strtoupper(substr($pendaftar['nama_peserta'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-slate-800 group-hover:text-blue-600 transition-colors">
                                            <?= htmlspecialchars($pendaftar['nama_peserta']) ?>
                                        </h4>
                                        <p class="text-sm text-slate-500"><?= htmlspecialchars($pendaftar['email_peserta']) ?>
                                        </p>
                                    </div>
                                </div>
                                <?php if ($pendaftar['status_kehadiran'] == 'hadir'): ?>
                                    <span
                                        class="bg-emerald-100 text-emerald-800 text-xs font-medium px-2 py-1 rounded-full border border-emerald-200">
                                        <i class="fas fa-check mr-1"></i>Hadir
                                    </span>
                                <?php else: ?>
                                    <span
                                        class="bg-slate-100 text-slate-800 text-xs font-medium px-2 py-1 rounded-full border border-slate-200">
                                        Menunggu
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="text-sm text-slate-600 flex items-center justify-between">
                                <span class="flex items-center">
                                    <i class="fas fa-chalkboard mr-2 text-slate-400"></i>
                                    <?= htmlspecialchars($pendaftar['workshop_judul']) ?>
                                </span>
                                <span class="text-xs text-slate-500">
                                    <?= date('d M H:i', strtotime($pendaftar['created_at'])) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="px-6 py-8 text-center">
                        <i class="fas fa-user-slash text-slate-300 text-4xl mb-3"></i>
                        <p class="text-slate-500">Belum ada pendaftar workshop</p>
                        <p class="text-slate-400 text-sm mt-1">Pendaftar akan muncul di sini</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <a href="kelola_pendaftar.php"
                    class="text-slate-700 hover:text-slate-900 text-sm font-medium transition-colors flex items-center justify-center">
                    Lihat Semua Pendaftar
                    <i class="fas fa-arrow-right ml-2 text-xs"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Stats -->
    <div class="space-y-8">
        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50 rounded-t-2xl">
                <h3 class="text-lg font-semibold text-slate-800 flex items-center">
                    <i class="fas fa-bolt text-amber-500 mr-3"></i>
                    Aksi Cepat
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <a href="kelola_event.php"
                    class="flex items-center p-4 bg-amber-50 rounded-xl hover:bg-amber-100 transition-all duration-300 group border border-amber-200 hover:border-amber-300">
                    <div
                        class="w-12 h-12 bg-amber-500 rounded-lg flex items-center justify-center mr-4 group-hover:scale-110 transition-transform shadow-sm">
                        <i class="fas fa-calendar-plus text-white text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-slate-800 group-hover:text-amber-700">Kelola Event</h4>
                        <p class="text-sm text-slate-600">Buat dan kelola workshop</p>
                    </div>
                    <i class="fas fa-chevron-right text-slate-400 group-hover:text-amber-600 transition-colors"></i>
                </a>

                <a href="kelola_pendaftar.php"
                    class="flex items-center p-4 bg-blue-50 rounded-xl hover:bg-blue-100 transition-all duration-300 group border border-blue-200 hover:border-blue-300">
                    <div
                        class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mr-4 group-hover:scale-110 transition-transform shadow-sm">
                        <i class="fas fa-users text-white text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-slate-800 group-hover:text-blue-700">Lihat Pendaftar</h4>
                        <p class="text-sm text-slate-600">Kelola peserta workshop</p>
                    </div>
                    <i class="fas fa-chevron-right text-slate-400 group-hover:text-blue-600 transition-colors"></i>
                </a>

                <a href="scan_checkin.php"
                    class="flex items-center p-4 bg-emerald-50 rounded-xl hover:bg-emerald-100 transition-all duration-300 group border border-emerald-200 hover:border-emerald-300">
                    <div
                        class="w-12 h-12 bg-emerald-500 rounded-lg flex items-center justify-center mr-4 group-hover:scale-110 transition-transform shadow-sm">
                        <i class="fas fa-qrcode text-white text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-slate-800 group-hover:text-emerald-700">Scan Check-in</h4>
                        <p class="text-sm text-slate-600">Scan QR code peserta</p>
                    </div>
                    <i class="fas fa-chevron-right text-slate-400 group-hover:text-emerald-600 transition-colors"></i>
                </a>

                <?php if ($is_owner): ?>
                    <a href="kelola_tim.php"
                        class="flex items-center p-4 bg-violet-50 rounded-xl hover:bg-violet-100 transition-all duration-300 group border border-violet-200 hover:border-violet-300">
                        <div
                            class="w-12 h-12 bg-violet-500 rounded-lg flex items-center justify-center mr-4 group-hover:scale-110 transition-transform shadow-sm">
                            <i class="fas fa-users-cog text-white text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-800 group-hover:text-violet-700">Kelola Tim</h4>
                            <p class="text-sm text-slate-600">Atur anggota tim</p>
                        </div>
                        <i class="fas fa-chevron-right text-slate-400 group-hover:text-violet-600 transition-colors"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- System Stats -->
        <div
            class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl shadow-lg text-white p-6 border border-slate-700">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <i class="fas fa-chart-bar text-amber-400 mr-3"></i>
                Statistik Sistem
            </h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between py-2 border-b border-slate-700">
                    <span class="text-slate-300">Workshop Aktif</span>
                    <span class="font-semibold text-white"><?= $total_workshop ?></span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-700">
                    <span class="text-slate-300">Total Pendaftar</span>
                    <span class="font-semibold text-white"><?= $total_pendaftar ?></span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-700">
                    <span class="text-slate-300">Rate Kehadiran</span>
                    <span class="font-semibold text-white">
                        <?php if ($total_pendaftar > 0): ?>
                            <?= round(($total_hadir / $total_pendaftar) * 100) ?>%
                        <?php else: ?>
                            0%
                        <?php endif; ?>
                    </span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-slate-300">Sertifikat</span>
                    <span class="font-semibold text-white"><?= $total_sertifikat ?> Terkirim</span>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-700">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-400">Last Updated</span>
                    <span class="text-slate-300"><?= date('d M Y H:i') ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

require_once BASE_PATH . '/admin/templates/footer.php';
?>