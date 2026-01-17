<?php
$page_title = 'Detail Pendaftar';
$current_page = 'data_pendaftar';
require_once BASE_PATH . '/admin/templates/header.php';
require_once BASE_PATH . '/core/koneksi.php';

if (!isset($_GET['event_id'])) {
    header("Location: kelola_pendaftar.php");
    exit();
}

$event_id = (int) $_GET['event_id'];
$penyelenggara_id = $_SESSION['penyelenggara_id_bersama'];

// Setup variabel dari GET
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_jk = isset($_GET['filter_jk']) ? $_GET['filter_jk'] : '';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';

try {
    // 1. Cek Validasi Event
    $stmt_event = $pdo->prepare("SELECT judul, tanggal_waktu, lokasi, harga, tipe_event FROM workshops WHERE id = ? AND penyelenggara_id = ?");
    $stmt_event->execute([$event_id, $penyelenggara_id]);
    $event = $stmt_event->fetch(PDO::FETCH_ASSOC);
    if (!$event) {
        die("Event tidak ditemukan atau Anda tidak memiliki akses.");
    }

    // 2. Ambil Header Kolom Dinamis (Form Custom)
    $stmt_headers = $pdo->prepare("SELECT id, label FROM form_fields WHERE workshop_id = ? ORDER BY urutan ASC, id ASC");
    $stmt_headers->execute([$event_id]);
    $table_headers = $stmt_headers->fetchAll(PDO::FETCH_ASSOC);

    // 3. Logic Filter SQL (FIXED)
    $query_conditions = ["workshop_id = ?"];
    $query_params = [$event_id];

    if (!empty($search)) {
        $query_conditions[] = "(nama_peserta LIKE ? OR email_peserta LIKE ? OR telepon_peserta LIKE ? OR kode_unik LIKE ?)";
        $term = "%$search%";
        array_push($query_params, $term, $term, $term, $term);
    }

    if (!empty($filter_jk)) {
        $query_conditions[] = "jenis_kelamin = ?";
        $query_params[] = $filter_jk;
    }

    if (!empty($filter_status)) {
        if ($filter_status == 'hadir') {
            $query_conditions[] = "status_kehadiran = 'hadir'";
        } elseif ($filter_status == 'absen') {
            $query_conditions[] = "(status_kehadiran IS NULL OR status_kehadiran != 'hadir')";
        }
    }

    $where_sql = implode(" AND ", $query_conditions);

    // 4. Hitung Total Data (Untuk Pagination)
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM pendaftaran WHERE $where_sql");
    $stmt_count->execute($query_params);
    $total_records = $stmt_count->fetchColumn();
    $total_pages = ceil($total_records / $limit);

    // 5. Ambil Data Pendaftar (Paginated)
    $sql_data = "SELECT * FROM pendaftaran WHERE $where_sql ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    $stmt_pendaftar = $pdo->prepare($sql_data);
    $stmt_pendaftar->execute($query_params);
    $pendaftar_list = $stmt_pendaftar->fetchAll(PDO::FETCH_ASSOC);

    // 6. Ambil Jawaban Dinamis (Hanya untuk peserta yang tampil di halaman ini)
    if (!empty($pendaftar_list)) {
        // Ambil ID peserta yang sedang tampil
        $pendaftar_ids = array_column($pendaftar_list, 'id');

        // Buat placeholder (?,?,?)
        $placeholders = str_repeat('?,', count($pendaftar_ids) - 1) . '?';

        $sql_answers = "SELECT pendaftaran_id, field_id, value 
                        FROM pendaftaran_data 
                        WHERE pendaftaran_id IN ($placeholders)";
        $stmt_answers = $pdo->prepare($sql_answers);
        $stmt_answers->execute($pendaftar_ids);
        $all_answers = $stmt_answers->fetchAll(PDO::FETCH_ASSOC);

        // Mapping jawaban ke array: [pendaftaran_id][field_id] = value
        $answers_map = [];
        foreach ($all_answers as $ans) {
            $answers_map[$ans['pendaftaran_id']][$ans['field_id']] = $ans['value'];
        }

        // Gabungkan ke data utama
        foreach ($pendaftar_list as &$p) {
            $p['answers'] = $answers_map[$p['id']] ?? [];
        }
        unset($p);
    }

    // 7. Ambil Statistik Global Event (Tidak terpengaruh filter pencarian)
    $stmt_stats = $pdo->prepare("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status_kehadiran = 'hadir' THEN 1 ELSE 0 END) as hadir
        FROM pendaftaran WHERE workshop_id = ?");
    $stmt_stats->execute([$event_id]);
    $stats_data = $stmt_stats->fetch(PDO::FETCH_ASSOC);
    $stats = [
        'total' => $stats_data['total'] ?? 0,
        'hadir' => $stats_data['hadir'] ?? 0,
        'absen' => ($stats_data['total'] ?? 0) - ($stats_data['hadir'] ?? 0)
    ];

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<div class="min-h-screen bg-gray-50 font-sans pb-32">

    <!-- Hero Header Section -->
    <div class="bg-emerald-900 pb-20 pt-10 px-4 rounded-b-[3rem] shadow-xl relative overflow-hidden">
        <!-- Elemen Dekoratif -->
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-emerald-800 rounded-full opacity-50 blur-3xl">
        </div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-amber-500 rounded-full opacity-20 blur-2xl">
        </div>

        <div
            class="max-w-7xl mx-auto relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <a href="kelola_pendaftar.php"
                        class="text-emerald-200 hover:text-white transition-all bg-white/10 hover:bg-white/20 p-2 rounded-full backdrop-blur-sm group">
                        <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                    </a>
                    <span
                        class="text-emerald-200 text-xs font-bold uppercase tracking-widest border border-emerald-700/50 px-2 py-1 rounded-md">Detail
                        Peserta</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight leading-tight">
                    <?= htmlspecialchars($event['judul']) ?>
                </h1>
                <div class="flex flex-wrap gap-4 mt-3 text-sm text-emerald-100/90">
                    <span class="flex items-center"><i class="fas fa-calendar-alt mr-2 text-amber-400"></i>
                        <?= date('d M Y, H:i', strtotime($event['tanggal_waktu'])) ?></span>
                    <span class="flex items-center"><i class="fas fa-map-marker-alt mr-2 text-emerald-400"></i>
                        <?= htmlspecialchars($event['lokasi']) ?></span>
                </div>
            </div>

            <!-- Right Side: Actions & Stats -->
            <div class="flex flex-col md:items-end gap-4">

                <!-- Action Buttons (SCAN & EXPORT) -->
                <div class="flex flex-wrap gap-3">
                    <a href="scan_checkin.php?event_id=<?= $event_id ?>"
                        class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-lg shadow-amber-500/30 flex items-center transform hover:-translate-y-0.5">
                        <i class="fas fa-qrcode mr-2"></i> Scan Check-in
                    </a>

                    <!-- Download Dropdown -->
                    <div class="relative" id="downloadDropdownContainer">
                        <button onclick="toggleDownloadDropdown()"
                            class="bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center">
                            <i class="fas fa-download mr-2"></i> Export
                            <i class="fas fa-chevron-down ml-2 text-xs transition-transform duration-200"
                                id="dropdownArrow"></i>
                        </button>
                        <!-- Dropdown Menu -->
                        <div id="downloadDropdown"
                            class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden z-50 animate-fade-in-up">
                            <a href="?event_id=<?= $event_id ?>&download=excel<?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_jk) ? '&filter_jk=' . urlencode($filter_jk) : '' ?><?= !empty($filter_status) ? '&filter_status=' . urlencode($filter_status) : '' ?>"
                                class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors border-b border-gray-50">
                                <i class="fas fa-file-excel mr-3 text-green-600"></i> Excel
                            </a>
                            <a href="?event_id=<?= $event_id ?>&download=pdf<?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_jk) ? '&filter_jk=' . urlencode($filter_jk) : '' ?><?= !empty($filter_status) ? '&filter_status=' . urlencode($filter_status) : '' ?>"
                                class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 transition-colors">
                                <i class="fas fa-file-pdf mr-3 text-red-600"></i> PDF
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="flex gap-3">
                    <div
                        class="bg-white/10 backdrop-blur-md border border-white/10 rounded-xl p-3 flex flex-col items-center min-w-[90px]">
                        <span class="text-2xl font-bold text-white"><?= $stats['total'] ?></span>
                        <span class="text-[10px] text-emerald-200 uppercase font-bold">Total</span>
                    </div>
                    <div
                        class="bg-emerald-600/30 backdrop-blur-md border border-emerald-500/30 rounded-xl p-3 flex flex-col items-center min-w-[90px]">
                        <span class="text-2xl font-bold text-emerald-300"><?= $stats['hadir'] ?></span>
                        <span class="text-[10px] text-emerald-100 uppercase font-bold">Hadir</span>
                    </div>
                    <div
                        class="bg-red-500/20 backdrop-blur-md border border-red-500/20 rounded-xl p-3 flex flex-col items-center min-w-[90px]">
                        <span class="text-2xl font-bold text-red-300"><?= $stats['absen'] ?></span>
                        <span class="text-[10px] text-red-100 uppercase font-bold">Absen</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto px-4 -mt-12 relative z-20">

        <!-- Search & Filter Card -->
        <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-6 mb-8">
            <form method="GET" action="">
                <input type="hidden" name="event_id" value="<?= $event_id ?>">

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <!-- Search Input -->
                    <div class="md:col-span-5">
                        <label class="block text-xs font-bold text-gray-500 mb-1 ml-1 uppercase">Pencarian</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all outline-none"
                                placeholder="Nama, Email, atau Kode Unik...">
                        </div>
                    </div>

                    <!-- Filter Status -->
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-500 mb-1 ml-1 uppercase">Status
                            Kehadiran</label>
                        <select name="filter_status"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all outline-none cursor-pointer">
                            <option value="">Semua Status</option>
                            <option value="hadir" <?= $filter_status == 'hadir' ? 'selected' : '' ?>>Hadir ✅</option>
                            <option value="absen" <?= $filter_status == 'absen' ? 'selected' : '' ?>>Belum Hadir ❌</option>
                        </select>
                    </div>

                    <!-- Filter Gender -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 mb-1 ml-1 uppercase">Jenis Kelamin</label>
                        <select name="filter_jk"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all outline-none cursor-pointer">
                            <option value="">Semua</option>
                            <option value="Laki-laki" <?= $filter_jk == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="Perempuan" <?= $filter_jk == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="md:col-span-2 flex gap-2">
                        <button type="submit"
                            class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg shadow-emerald-200">
                            Filter
                        </button>
                        <a href="?event_id=<?= $event_id ?>"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-3 px-4 rounded-xl transition-all border border-gray-200"
                            title="Reset Filter">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Data List -->
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden min-h-[400px]">

            <!-- Desktop Table View -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-gray-50/80 text-gray-600 border-b border-gray-200 text-xs uppercase tracking-wider">
                            <th class="px-6 py-5 font-bold">Peserta</th>
                            <th class="px-6 py-5 font-bold">Kontak</th>
                            <th class="px-6 py-5 font-bold text-center">Kehadiran</th>
                            <th class="px-6 py-5 font-bold text-center">Status Bayar</th>

                            <!-- Header Dinamis -->
                            <?php foreach ($table_headers as $header): ?>
                                <th class="px-6 py-5 font-bold border-l border-gray-100">
                                    <?= htmlspecialchars($header['label']) ?>
                                </th>
                            <?php endforeach; ?>

                            <th class="px-6 py-5 font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (count($pendaftar_list) > 0): ?>
                            <?php foreach ($pendaftar_list as $p): ?>
                                <tr class="hover:bg-emerald-50/30 transition-colors group">

                                    <!-- Kolom Peserta -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center text-gray-600 font-bold text-sm shadow-sm">
                                                <?= strtoupper(substr($p['nama_peserta'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-800 text-base mb-0.5">
                                                    <?= htmlspecialchars($p['nama_peserta']) ?></div>
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded border border-gray-200"><?= $p['kode_unik'] ?></span>
                                                    <span class="text-xs text-gray-500"><?= $p['jenis_kelamin'] ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Kolom Kontak -->
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-600 space-y-1">
                                            <div class="flex items-center gap-2"><i
                                                    class="fas fa-envelope text-xs text-gray-400"></i>
                                                <?= htmlspecialchars($p['email_peserta']) ?></div>
                                            <div class="flex items-center gap-2"><i
                                                    class="fas fa-phone text-xs text-gray-400"></i>
                                                <?= htmlspecialchars($p['telepon_peserta']) ?></div>
                                        </div>
                                    </td>

                                    <!-- Kolom Kehadiran -->
                                    <td class="px-6 py-4 text-center">
                                        <?php if ($p['status_kehadiran'] == 'hadir'): ?>
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 shadow-sm">
                                                <i class="fas fa-check mr-1.5"></i> Hadir
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-500 border border-gray-200">
                                                -
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Kolom Pembayaran -->
                                    <td class="px-6 py-4 text-center">
                                        <?php
                                        $status = $p['status_pembayaran'] ?? 'pending';
                                        $badges = [
                                            'paid' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'icon' => 'fa-check-circle', 'label' => 'Lunas'],
                                            'free' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'icon' => 'fa-gift', 'label' => 'Gratis'],
                                            'failed' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'icon' => 'fa-times-circle', 'label' => 'Gagal'],
                                            'pending' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-800', 'icon' => 'fa-clock', 'label' => 'Pending'],
                                        ];
                                        $b = $badges[$status] ?? $badges['pending'];
                                        ?>
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold <?= $b['bg'] ?> <?= $b['text'] ?> border border-opacity-20 border-current">
                                            <i class="fas <?= $b['icon'] ?> mr-1.5"></i> <?= $b['label'] ?>
                                        </span>
                                    </td>

                                    <!-- Kolom Dinamis -->
                                    <?php foreach ($table_headers as $header): ?>
                                        <td class="px-6 py-4 text-sm text-gray-600 border-l border-gray-50">
                                            <?= htmlspecialchars($p['answers'][$header['id']] ?? '-') ?>
                                        </td>
                                    <?php endforeach; ?>

                                    <!-- Kolom Aksi -->
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center items-center gap-2">
                                            <?php if ($p['status_kehadiran'] == 'hadir'): ?>
                                                <?php if (($p['sertifikat_status'] ?? '') == 'terkirim'): ?>
                                                    <button
                                                        class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center cursor-default"
                                                        title="Sertifikat Terkirim">
                                                        <i class="fas fa-check-double"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button onclick="kirimSertifikat(this)" data-id="<?= $p['id'] ?>"
                                                        data-nama="<?= htmlspecialchars($p['nama_peserta']) ?>"
                                                        class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 flex items-center justify-center transition-colors"
                                                        title="Kirim Sertifikat">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            <button onclick="hapusPeserta(this)" data-id="<?= $p['id'] ?>"
                                                data-nama="<?= htmlspecialchars($p['nama_peserta']) ?>"
                                                class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition-colors"
                                                title="Hapus">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="100%" class="px-6 py-16 text-center text-gray-500">
                                    <div
                                        class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-search text-3xl text-gray-300"></i>
                                    </div>
                                    <p class="font-medium">Tidak ada data ditemukan</p>
                                    <p class="text-sm mt-1">Coba ubah filter pencarian Anda</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile View (Card Layout) -->
            <div class="lg:hidden p-4 space-y-4">
                <?php if (count($pendaftar_list) > 0): ?>
                    <?php foreach ($pendaftar_list as $p): ?>
                        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm relative overflow-hidden">
                            <!-- Status Bar -->
                            <div class="absolute top-0 left-0 w-1 h-full 
                                <?= $p['status_kehadiran'] == 'hadir' ? 'bg-emerald-500' : 'bg-gray-200' ?>">
                            </div>

                            <div class="flex justify-between items-start mb-4 pl-3">
                                <div>
                                    <h3 class="font-bold text-gray-800 text-lg"><?= htmlspecialchars($p['nama_peserta']) ?></h3>
                                    <p class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                        <i class="fas fa-hashtag text-amber-500"></i> <?= $p['kode_unik'] ?>
                                        <span class="mx-1">•</span>
                                        <?= $p['jenis_kelamin'] ?>
                                    </p>
                                </div>
                                <div class="flex flex-col items-end gap-1">
                                    <?php if ($p['status_kehadiran'] == 'hadir'): ?>
                                        <span
                                            class="text-[10px] font-bold uppercase text-emerald-600 bg-emerald-50 px-2 py-1 rounded border border-emerald-100">Hadir</span>
                                    <?php endif; ?>
                                    <?php
                                    $status = $p['status_pembayaran'] ?? 'pending';
                                    $color = $status == 'paid' ? 'text-emerald-600 bg-emerald-50' : ($status == 'free' ? 'text-blue-600 bg-blue-50' : 'text-amber-600 bg-amber-50');
                                    $label = $status == 'paid' ? 'Lunas' : ($status == 'free' ? 'Gratis' : 'Pending');
                                    ?>
                                    <span
                                        class="text-[10px] font-bold uppercase <?= $color ?> px-2 py-1 rounded border border-current border-opacity-10"><?= $label ?></span>
                                </div>
                            </div>

                            <div class="pl-3 space-y-2 mb-4 text-sm text-gray-600">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 text-center"><i class="fas fa-envelope text-gray-400"></i></div>
                                    <div class="truncate"><?= htmlspecialchars($p['email_peserta']) ?></div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-6 text-center"><i class="fas fa-phone text-gray-400"></i></div>
                                    <div><?= htmlspecialchars($p['telepon_peserta']) ?></div>
                                </div>

                                <!-- Jawaban Dinamis di Mobile -->
                                <?php if (!empty($table_headers)): ?>
                                    <div class="border-t border-gray-100 mt-3 pt-3">
                                        <?php foreach ($table_headers as $header): ?>
                                            <div class="flex justify-between text-xs py-1">
                                                <span class="text-gray-500"><?= htmlspecialchars($header['label']) ?>:</span>
                                                <span
                                                    class="font-medium text-gray-800 text-right"><?= htmlspecialchars($p['answers'][$header['id']] ?? '-') ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="pl-3 flex gap-2 pt-3 border-t border-gray-100">
                                <?php if ($p['status_kehadiran'] == 'hadir'): ?>
                                    <button onclick="kirimSertifikat(this)" data-id="<?= $p['id'] ?>"
                                        data-nama="<?= htmlspecialchars($p['nama_peserta']) ?>"
                                        class="flex-1 bg-blue-600 text-white text-xs font-bold py-2.5 rounded-lg active:scale-95 transition-transform flex items-center justify-center gap-2">
                                        <i class="fas fa-certificate"></i> Kirim Sertifikat
                                    </button>
                                <?php endif; ?>
                                <button onclick="hapusPeserta(this)" data-id="<?= $p['id'] ?>"
                                    data-nama="<?= htmlspecialchars($p['nama_peserta']) ?>"
                                    class="w-10 bg-red-50 text-red-600 rounded-lg flex items-center justify-center active:bg-red-100 transition-colors">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center p-8 bg-white rounded-2xl border border-gray-100 shadow-sm">
                        <p class="text-gray-500">Tidak ada data.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="bg-white px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                    <div class="hidden sm:block text-sm text-gray-500">
                        Hal <?= $page ?> dari <?= $total_pages ?>
                    </div>
                    <div class="flex gap-2 w-full sm:w-auto justify-center">
                        <?php if ($page > 1): ?>
                            <a href="?event_id=<?= $event_id ?>&page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&filter_jk=<?= urlencode($filter_jk) ?>&filter_status=<?= urlencode($filter_status) ?>"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Prev
                            </a>
                        <?php endif; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?event_id=<?= $event_id ?>&page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&filter_jk=<?= urlencode($filter_jk) ?>&filter_status=<?= urlencode($filter_status) ?>"
                                class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 shadow-lg shadow-emerald-200">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Logic Toggle Dropdown Download
    function toggleDownloadDropdown() {
        const dropdown = document.getElementById('downloadDropdown');
        const arrow = document.getElementById('dropdownArrow');

        if (dropdown.classList.contains('hidden')) {
            dropdown.classList.remove('hidden');
            arrow.classList.add('rotate-180');
        } else {
            dropdown.classList.add('hidden');
            arrow.classList.remove('rotate-180');
        }
    }

    // Klik di luar menutup dropdown
    document.addEventListener('click', function (event) {
        const container = document.getElementById('downloadDropdownContainer');
        if (container && !container.contains(event.target)) {
            document.getElementById('downloadDropdown').classList.add('hidden');
            document.getElementById('dropdownArrow').classList.remove('rotate-180');
        }
    });

    // Logic Hapus Peserta
    function hapusPeserta(btn) {
        const id = btn.getAttribute('data-id');
        const nama = btn.getAttribute('data-nama');

        Swal.fire({
            title: 'Hapus Peserta?',
            text: `Anda yakin ingin menghapus data ${nama}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-2xl' }
        }).then((result) => {
            if (result.isConfirmed) {
                // Proses hapus via fetch (sesuaikan endpoint Anda)
                fetch(`hapus_peserta.php?id=${id}`, { method: 'POST' })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Gagal!', 'Terjadi kesalahan.', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error!', 'Gagal menghubungi server.', 'error'));
            }
        });
    }

    // Logic Kirim Sertifikat
    function kirimSertifikat(btn) {
        const id = btn.getAttribute('data-id');
        const nama = btn.getAttribute('data-nama');

        Swal.fire({
            title: 'Kirim Sertifikat?',
            text: `Kirim email sertifikat ke ${nama}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            confirmButtonText: 'Kirim Sekarang',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const formData = new FormData();
                formData.append('id', id);
                return fetch('proses_kirim_sertifikat.php', { method: 'POST', body: formData })
                    .then(response => {
                        if (!response.ok) throw new Error(response.statusText);
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Request failed: ${error}`);
                    });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value.status === 'success') {
                Swal.fire('Berhasil!', result.value.message, 'success').then(() => location.reload());
            } else if (result.isConfirmed) {
                Swal.fire('Gagal!', result.value.message || 'Terjadi kesalahan.', 'error');
            }
        });
    }
</script>

<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.2s ease-out forwards;
    }
</style>

<?php require_once BASE_PATH . '/admin/templates/footer.php'; ?>