<?php
// Mulai sesi
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Panggil koneksi database
require_once __DIR__ . '/../core/koneksi.php';
require_once __DIR__ . '/templates/header.php';

// 3. AMBIL DATA
$email_peserta = $_SESSION['email'] ?? '';
$user_id = $_SESSION['user_id'];
$nama_user = $_SESSION['nama_lengkap'] ?? 'Peserta';

if ($email_peserta) {
    // Query Tiket Saya
    $sql_tiket = "SELECT p.*, w.judul, w.tanggal_waktu, w.lokasi, w.poster, w.id as workshop_id 
                  FROM pendaftaran p 
                  JOIN workshops w ON p.workshop_id = w.id 
                  WHERE p.email_peserta = :email 
                  ORDER BY p.created_at DESC";

    if (isset($pdo)) {
        $stmt = $pdo->prepare($sql_tiket);
        $stmt->execute(['email' => $email_peserta]);
        $tiket_saya = $stmt->fetchAll();

        // Query Agenda Terbaru
        $sql_agenda = "SELECT * FROM workshops WHERE tanggal_waktu >= NOW() ORDER BY tanggal_waktu ASC LIMIT 6";
        $stmt_agenda = $pdo->query($sql_agenda);
        $agendas = $stmt_agenda->fetchAll();
    } else {
        die("Koneksi database gagal dimuat.");
    }
} else {
    $tiket_saya = [];
    $agendas = [];
}
?>

<div class="min-h-screen bg-gray-50 font-sans pb-20">

    <!-- Hero Section -->
    <div class="bg-emerald-900 relative overflow-hidden pb-24 pt-10 rounded-b-[3rem] shadow-xl">
        <!-- Dekorasi Background -->
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-emerald-800 rounded-full opacity-50 blur-3xl">
        </div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-amber-500 rounded-full opacity-20 blur-2xl">
        </div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <span
                        class="text-emerald-200 text-xs font-bold uppercase tracking-widest border border-emerald-700/50 px-2 py-1 rounded-md">Dashboard
                        Peserta</span>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-white mt-2 leading-tight">
                        Halo, <span class="text-amber-400"><?= htmlspecialchars(explode(' ', $nama_user)[0]) ?>!</span>
                        👋
                    </h1>
                    <p class="text-emerald-100/90 mt-2 text-sm md:text-base max-w-lg">
                        Selamat datang kembali. Cek tiket eventmu dan temukan kegiatan seru lainnya di sini.
                    </p>
                </div>
                <div class="hidden md:block">
                    <div
                        class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-4 flex items-center gap-4">
                        <div class="text-right">
                            <p class="text-xs text-emerald-200 uppercase font-bold">Event Diikuti</p>
                            <p class="text-2xl font-bold text-white"><?= count($tiket_saya) ?></p>
                        </div>
                        <div
                            class="w-12 h-12 rounded-full bg-emerald-500 flex items-center justify-center text-white shadow-lg shadow-emerald-500/30">
                            <i class="fas fa-ticket-alt text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Container (Overlap) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 -mt-16 relative z-20 space-y-12">

        <!-- SECTION 1: TIKET SAYA -->
        <div>
            <div class="flex items-center justify-between mb-6 px-2">
                <h2 class="text-xl font-bold text-amber-400 flex items-center gap-2">
                    <i class="fas fa-ticket-alt text-amber-400"></i> Tiket Saya
                </h2>
                <?php if (count($tiket_saya) > 3): ?>
                    <a href="#" class="text-sm font-semibold text-amber-400 hover:text-emerald-700">Lihat Semua</a>
                <?php endif; ?>
            </div>

            <?php if (count($tiket_saya) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($tiket_saya as $row): ?>
                        <?php
                        $is_lunas = ($row['status_pembayaran'] == 'paid' || $row['status_pembayaran'] == 'free');
                        $poster_src = !empty($row['poster']) ? BASE_URL . 'assets/img/posters/' . $row['poster'] : 'https://via.placeholder.com/400x200?text=No+Poster';
                        ?>

                        <!-- Ticket Card -->
                        <div
                            class="group bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1 flex flex-col h-full">
                            <!-- Image Header -->
                            <div class="h-40 bg-gray-200 relative overflow-hidden">
                                <img src="<?= $poster_src ?>"
                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                                    alt="Poster">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>

                                <!-- Status Badge -->
                                <div class="absolute top-3 right-3">
                                    <?php if ($is_lunas): ?>
                                        <span
                                            class="bg-emerald-500/90 backdrop-blur text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-sm flex items-center gap-1">
                                            <i class="fas fa-check-circle"></i> Terdaftar
                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="bg-amber-500/90 backdrop-blur text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-sm flex items-center gap-1">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="absolute bottom-3 left-4 right-4 text-white">
                                    <p class="text-xs font-medium opacity-90 flex items-center gap-1">
                                        <i class="fas fa-calendar-day text-amber-400"></i>
                                        <?= date('d M Y', strtotime($row['tanggal_waktu'])) ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Content Body -->
                            <div class="p-5 flex flex-col flex-grow">
                                <h3 class="font-bold text-gray-800 text-lg leading-snug mb-2 line-clamp-2"
                                    title="<?= htmlspecialchars($row['judul']) ?>">
                                    <?= htmlspecialchars($row['judul']) ?>
                                </h3>

                                <div class="text-sm text-gray-500 mb-4 flex items-start gap-2">
                                    <i class="fas fa-map-marker-alt text-emerald-500 mt-1"></i>
                                    <span class="line-clamp-1"><?= htmlspecialchars($row['lokasi']) ?></span>
                                </div>

                                <!-- Action Area (Push to bottom) -->
                                <div class="mt-auto space-y-2 pt-4 border-t border-gray-100">
                                    <?php if ($is_lunas): ?>
                                        <!-- Tombol E-Ticket -->
                                        <a href="cetak_tiket.php?id=<?= $row['id'] ?>" target="_blank"
                                            class="w-full flex items-center justify-center gap-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold py-2.5 rounded-xl transition-colors text-xs border border-emerald-100">
                                            <i class="fas fa-qrcode text-sm"></i> Lihat E-Ticket
                                        </a>

                                        <?php
                                        // LOGIC: Kuesioner vs Sertifikat
                                        if ($row['status_kehadiran'] == 'hadir') {
                                            $stmt_cek_k = $pdo->prepare("SELECT is_kuesioner_active FROM workshops WHERE id = ?");
                                            $stmt_cek_k->execute([$row['workshop_id']]);
                                            $kuesioner_active = $stmt_cek_k->fetchColumn();

                                            $stmt_cek_ans = $pdo->prepare("SELECT COUNT(*) FROM workshop_answers WHERE workshop_id = ? AND user_id = ?");
                                            $stmt_cek_ans->execute([$row['workshop_id'], $user_id]);
                                            $sudah_isi = $stmt_cek_ans->fetchColumn() > 0;

                                            if ($kuesioner_active == 1 && !$sudah_isi) {
                                                // WAJIB ISI KUESIONER
                                                echo '
                                                <a href="isi_kuesioner.php?id=' . $row['workshop_id'] . '"
                                                    class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold py-2.5 rounded-xl transition-all shadow-md hover:shadow-lg text-xs animate-pulse">
                                                    <i class="fas fa-poll-h"></i> Isi Kuesioner (Wajib)
                                                </a>';
                                            } else {
                                                // DOWNLOAD SERTIFIKAT
                                                echo '
                                                <a href="download_sertifikat.php?id=' . $row['id'] . '"
                                                    class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-amber-400 to-orange-500 text-white font-bold py-2.5 rounded-xl transition-all shadow-md hover:shadow-lg text-xs">
                                                    <i class="fas fa-certificate"></i> Download Sertifikat
                                                </a>';
                                            }
                                        } else {
                                            // BELUM HADIR
                                            echo '
                                            <div class="w-full bg-gray-100 text-gray-400 font-medium py-2.5 rounded-xl text-center text-xs flex items-center justify-center gap-1 cursor-default">
                                                <i class="fas fa-clock"></i> Menunggu Kehadiran
                                            </div>';
                                        }
                                        ?>

                                    <?php elseif ($row['payment_url']): ?>
                                        <!-- Tombol Bayar -->
                                        <a href="<?= $row['payment_url'] ?>" target="_blank"
                                            class="w-full flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 rounded-xl transition-colors shadow-md hover:shadow-lg text-xs">
                                            <i class="fas fa-wallet"></i> Bayar Sekarang
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-3xl p-8 text-center border-2 border-dashed border-gray-200">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-ticket-alt text-4xl text-gray-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-700">Belum ada tiket</h3>
                    <p class="text-gray-500 text-sm mt-1">Kamu belum mendaftar di event apapun.</p>
                    <a href="#agenda-section"
                        class="mt-4 inline-block text-emerald-600 font-bold text-sm hover:underline">Cari Event</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- SECTION 2: AGENDA TERBARU -->
        <div id="agenda-section">
            <div class="flex items-center justify-between mb-6 px-2">
                <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-calendar-star text-amber-500"></i> Agenda Terbaru
                </h2>
            </div>

            <?php if (!empty($agendas)): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($agendas as $agenda): ?>
                        <article
                            class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 group flex flex-col h-full hover:-translate-y-1">
                            <div class="h-48 bg-gray-200 relative overflow-hidden">
                                <?php if (!empty($agenda['poster'])): ?>
                                    <img src="<?= BASE_URL ?>assets/img/posters/<?= htmlspecialchars($agenda['poster']) ?>"
                                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center bg-emerald-800 text-white">
                                        <i class="fas fa-image text-4xl opacity-50"></i>
                                    </div>
                                <?php endif; ?>

                                <!-- Price Tag -->
                                <div class="absolute top-4 right-4">
                                    <span
                                        class="bg-white/90 backdrop-blur text-gray-800 text-xs font-bold py-1.5 px-3 rounded-xl shadow-lg border border-white/50">
                                        <?= ($agenda['tipe_event'] == 'berbayar') ? 'Rp ' . number_format($agenda['harga'], 0, ',', '.') : 'Gratis' ?>
                                    </span>
                                </div>
                            </div>

                            <div class="p-6 flex flex-col flex-grow">
                                <div class="mb-2">
                                    <span
                                        class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md">
                                        Event
                                    </span>
                                </div>
                                <h3
                                    class="text-lg font-bold text-gray-800 mb-2 line-clamp-2 group-hover:text-emerald-600 transition-colors">
                                    <?= htmlspecialchars($agenda['judul']) ?>
                                </h3>

                                <div class="flex items-center text-gray-500 text-xs mb-4 gap-4">
                                    <span class="flex items-center gap-1.5">
                                        <i class="far fa-clock text-amber-500"></i>
                                        <?= date('d M, H:i', strtotime($agenda['tanggal_waktu'])) ?>
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <i class="fas fa-map-marker-alt text-emerald-500"></i>
                                        <span class="truncate max-w-[100px]"><?= htmlspecialchars($agenda['lokasi']) ?></span>
                                    </span>
                                </div>

                                <p class="text-gray-500 text-sm mb-6 line-clamp-2 leading-relaxed flex-grow">
                                    <?= htmlspecialchars(substr($agenda['deskripsi'], 0, 100)) ?>...
                                </p>

                                <a href="<?= BASE_URL ?>user/daftar_event.php?id=<?= $agenda['id'] ?>"
                                    class="w-full bg-slate-900 hover:bg-emerald-600 text-white font-bold py-3 rounded-xl transition-all duration-300 text-sm flex items-center justify-center gap-2 group-hover:shadow-lg group-hover:shadow-emerald-500/20">
                                    Daftar Sekarang <i
                                        class="fas fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-16 bg-white rounded-3xl border border-gray-100">
                    <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 font-medium">Belum ada agenda terdekat.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once 'templates/footer.php'; ?>