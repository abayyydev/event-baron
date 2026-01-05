<?php
$page_title = 'Detail Pendaftar';
$current_page = 'data_pendaftar';
require_once BASE_PATH . '/admin/templates/header.php';
require_once 'core/koneksi.php';

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
    // Get event details
    $stmt_event = $pdo->prepare("SELECT judul, tanggal_waktu, lokasi, harga, tipe_event FROM workshops WHERE id = ? AND penyelenggara_id = ?");
    $stmt_event->execute([$event_id, $penyelenggara_id]);
    $event = $stmt_event->fetch(PDO::FETCH_ASSOC);
    if (!$event) {
        die("Event tidak ditemukan atau Anda tidak memiliki akses.");
    }

    // Ambil header kolom dinamis
    $stmt_headers = $pdo->prepare("SELECT id, label FROM form_fields WHERE workshop_id = ? ORDER BY urutan ASC, id ASC");
    $stmt_headers->execute([$event_id]);
    $table_headers = $stmt_headers->fetchAll(PDO::FETCH_ASSOC);

    // Ambil semua pendaftar dari tabel `pendaftaran` terlebih dahulu
    $stmt_pendaftar = $pdo->prepare("SELECT * FROM pendaftaran WHERE workshop_id = ? ORDER BY created_at ASC");
    $stmt_pendaftar->execute([$event_id]);

    // Ambil data dan jadikan array berkey id
    $rows = $stmt_pendaftar->fetchAll(PDO::FETCH_ASSOC);
    $pendaftar_list_raw = array_column($rows, null, 'id');

    // Inisialisasi array answers untuk setiap pendaftar
    foreach ($pendaftar_list_raw as $id => &$pendaftar) {
        $pendaftar['answers'] = [];
    }
    unset($pendaftar);

    // Ambil SEMUA jawaban dinamis untuk event ini dalam satu query
    $stmt_answers = $pdo->prepare("
        SELECT pd.pendaftaran_id, pd.field_id, pd.value 
        FROM pendaftaran_data pd
        JOIN pendaftaran p ON pd.pendaftaran_id = p.id
        WHERE p.workshop_id = ?
    ");
    $stmt_answers->execute([$event_id]);
    $all_answers = $stmt_answers->fetchAll(PDO::FETCH_ASSOC);

    // Masukkan setiap jawaban ke pendaftar yang sesuai
    foreach ($all_answers as $answer) {
        $pendaftaran_id = $answer['pendaftaran_id'];
        if (isset($pendaftar_list_raw[$pendaftaran_id])) {
            $pendaftar_list_raw[$pendaftaran_id]['answers'][$answer['field_id']] = $answer['value'];
        }
    }

    // Ambil statistik dari data yang sudah diolah
    $stats = ['total' => count($pendaftar_list_raw), 'hadir' => 0];
    foreach ($pendaftar_list_raw as $p) {
        if ($p['status_kehadiran'] == 'hadir')
            $stats['hadir']++;
    }
    $stats['absen'] = $stats['total'] - $stats['hadir'];

    // Pagination
    $total_records = count($pendaftar_list_raw);
    $total_pages = ceil($total_records / $limit);
    $page = max(1, min($page, $total_pages));
    $offset = ($page - 1) * $limit;

    $pendaftar_list = array_slice($pendaftar_list_raw, $offset, $limit);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6 mb-8">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-4">
                        <div
                            class="w-12 h-12 bg-gradient-to-r from-amber-500 to-orange-500 rounded-xl flex items-center justify-center">
                            <i class="fas fa-users text-white text-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-800">Detail Pendaftar</h1>
                            <p class="text-slate-600">Kelola peserta untuk event berikut</p>
                        </div>
                    </div>

                    <!-- Event Info -->
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                        <div class="flex items-center space-x-4">
                            <div class="flex-1">
                                <h3 class="font-semibold text-slate-800 text-lg">
                                    <?= htmlspecialchars($event['judul']) ?>
                                </h3>
                                <div class="flex flex-wrap gap-4 mt-2 text-sm text-slate-600">
                                    <span class="flex items-center">
                                        <i class="fas fa-calendar-alt mr-2 text-amber-500"></i>
                                        <?= date('d M Y, H:i', strtotime($event['tanggal_waktu'])) ?>
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                                        <?= htmlspecialchars($event['lokasi']) ?>
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fas fa-tag mr-2 text-purple-500"></i>
                                        <?= $event['tipe_event'] === 'berbayar' ? 'Berbayar' : 'Gratis' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="scan_checkin.php?event_id=<?= $event_id ?>"
                        class="bg-gradient-to-r from-emerald-500 to-green-500 hover:from-emerald-600 hover:to-green-600 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center">
                        <i class="fas fa-qrcode mr-3"></i> Scan Check-in
                    </a>

                    <!-- Download Dropdown -->
                    <div class="relative">
                        <button id="downloadDropdownButton"
                            class="bg-white border border-slate-300 text-slate-700 font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg hover:bg-slate-50 flex items-center">
                            <i class="fas fa-download mr-3"></i> Download
                            <i class="fas fa-chevron-down ml-2 text-xs"></i>
                        </button>
                        <div id="downloadDropdown"
                            class="hidden absolute right-0 mt-2 w-48 rounded-xl shadow-xl bg-white border border-slate-200 z-10">
                            <div class="py-2">
                                <a href="?event_id=<?= $event_id ?>&download=excel<?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_jk) ? '&filter_jk=' . urlencode($filter_jk) : '' ?><?= !empty($filter_status) ? '&filter_status=' . urlencode($filter_status) : '' ?>"
                                    class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                    <i class="fas fa-file-excel mr-3 text-green-600"></i> Excel
                                </a>
                                <a href="?event_id=<?= $event_id ?>&download=pdf<?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_jk) ? '&filter_jk=' . urlencode($filter_jk) : '' ?><?= !empty($filter_status) ? '&filter_status=' . urlencode($filter_status) : '' ?>"
                                    class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                    <i class="fas fa-file-pdf mr-3 text-red-600"></i> PDF
                                </a>
                            </div>
                        </div>
                    </div>

                    <a href="kelola_pendaftar.php"
                        class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg flex items-center">
                        <i class="fas fa-arrow-left mr-3"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4 border border-blue-200">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-slate-600">Total Peserta</h3>
                        <p class="text-2xl font-bold text-slate-800"><?= $stats['total'] ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mr-4 border border-emerald-200">
                        <i class="fas fa-check-circle text-emerald-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-slate-600">Hadir</h3>
                        <p class="text-2xl font-bold text-slate-800"><?= $stats['hadir'] ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
                <div class="flex items-center">
                    <div
                        class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center mr-4 border border-red-200">
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-slate-600">Tidak Hadir</h3>
                        <p class="text-2xl font-bold text-slate-800"><?= $stats['absen'] ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6 mb-8">
            <form method="GET" action="">
                <input type="hidden" name="event_id" value="<?= $event_id ?>">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="md:col-span-2">
                        <div class="relative">
                            <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>"
                                class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition-all duration-300 bg-white"
                                placeholder="Cari nama, email, atau telepon...">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-slate-400"></i>
                            </div>
                        </div>
                    </div>
                    <div>
                        <select name="filter_jk" id="filter_jk"
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition-all duration-300 bg-white">
                            <option value="">Semua Gender</option>
                            <option value="Laki-laki" <?= $filter_jk == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="Perempuan" <?= $filter_jk == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <select name="filter_status" id="filter_status"
                            class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition-all duration-300 bg-white">
                            <option value="">Semua Status</option>
                            <option value="hadir" <?= $filter_status == 'hadir' ? 'selected' : '' ?>>Hadir</option>
                            <option value="absen" <?= $filter_status == 'absen' ? 'selected' : '' ?>>Belum Hadir</option>
                        </select>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit"
                            class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg flex items-center justify-center">
                            <i class="fas fa-filter mr-2"></i> Filter
                        </button>
                        <a href="?event_id=<?= $event_id ?>"
                            class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-3 px-4 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg flex items-center justify-center">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
            <div class="mt-4 text-sm text-slate-600">
                Menampilkan <span class="font-semibold"><?= count($pendaftar_list) ?></span> dari
                <span class="font-semibold"><?= $total_records ?></span> peserta
            </div>
        </div>

        <!-- Participants Table -->
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
            <!-- Desktop View -->
            <div class="hidden lg:block">
                <div class="w-full overflow-x-auto border border-slate-200 rounded-xl">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-gradient-to-r from-slate-800 to-slate-900 text-white">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-sm font-semibold whitespace-nowrap">
                                        Info Peserta
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-sm font-semibold whitespace-nowrap">
                                        Kontak
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-4 text-center text-sm font-semibold whitespace-nowrap">
                                        Pembayaran
                                    </th>

                                    <?php foreach ($table_headers as $header): ?>
                                        <th scope="col" class="px-6 py-4 text-left text-sm font-semibold whitespace-nowrap">
                                            <?= htmlspecialchars($header['label']) ?>
                                        </th>
                                    <?php endforeach; ?>

                                    <th scope="col"
                                        class="px-6 py-4 text-center text-sm font-semibold whitespace-nowrap">
                                        Status
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-4 text-center text-sm font-semibold whitespace-nowrap">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-200 bg-white">
                                <?php if (!empty($pendaftar_list)): ?>
                                    <?php foreach ($pendaftar_list as $pendaftar): ?>
                                        <tr class="hover:bg-slate-50/50 transition-colors group">

                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center space-x-3">
                                                    <div
                                                        class="w-10 h-10 bg-gradient-to-r from-slate-700 to-slate-800 rounded-full flex items-center justify-center text-white font-semibold text-sm border border-slate-600">
                                                        <?= strtoupper(substr($pendaftar['nama_peserta'], 0, 1)) ?>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <h4
                                                            class="font-semibold text-slate-800 group-hover:text-amber-600 transition-colors truncate">
                                                            <?= htmlspecialchars($pendaftar['nama_peserta']) ?>
                                                        </h4>
                                                        <div class="flex items-center space-x-2 mt-1">
                                                            <span
                                                                class="text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded-full border border-slate-200">
                                                                <?= $pendaftar['kode_unik'] ?>
                                                            </span>
                                                            <span class="text-xs text-slate-500">
                                                                <?= htmlspecialchars($pendaftar['jenis_kelamin'] ?: '-') ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="space-y-1 text-sm text-slate-600">
                                                    <div class="flex items-center">
                                                        <i class="fas fa-envelope mr-2 text-slate-400 text-xs"></i>
                                                        <span class="truncate">
                                                            <?= htmlspecialchars($pendaftar['email_peserta']) ?>
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <i class="fas fa-phone mr-2 text-slate-400 text-xs"></i>
                                                        <span><?= htmlspecialchars($pendaftar['telepon_peserta']) ?></span>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <?php
                                                $status = $pendaftar['status_pembayaran'] ?? 'pending';
                                                switch ($status) {
                                                    case 'paid':
                                                        echo '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    <i class="fas fa-check-circle mr-1"></i> Lunas
                                                  </span>';
                                                        break;
                                                    case 'failed':
                                                        echo '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 border border-red-200">
                                                    <i class="fas fa-times-circle mr-1"></i> Gagal
                                                  </span>';
                                                        break;
                                                    case 'free':
                                                        echo '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-sky-100 text-sky-700 border border-sky-200">
                                                    <i class="fas fa-gift mr-1"></i> Gratis
                                                  </span>';
                                                        break;
                                                    default:
                                                        echo '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200">
                                                    <i class="fas fa-clock mr-1"></i> Pending
                                                  </span>';
                                                }
                                                ?>
                                            </td>

                                            <?php foreach ($table_headers as $header): ?>
                                                <td class="px-6 py-4 text-sm text-slate-800 whitespace-nowrap">
                                                    <?php
                                                    $field_id = $header['id'] ?? null;
                                                    echo htmlspecialchars($pendaftar['answers'][$field_id] ?? '-');
                                                    ?>
                                                </td>
                                            <?php endforeach; ?>

                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <?php if ($pendaftar['status_kehadiran'] == 'hadir'): ?>
                                                    <span
                                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                        <i class="fas fa-check mr-1"></i> Hadir
                                                    </span>
                                                <?php else: ?>
                                                    <span
                                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-800 border border-slate-200">
                                                        Belum Hadir
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center justify-center space-x-2">
                                                    <?php if ($pendaftar['status_kehadiran'] == 'hadir'): ?>
                                                        <?php if (!empty($pendaftar['sertifikat_status']) && $pendaftar['sertifikat_status'] == 'terkirim'): ?>
                                                            <span
                                                                class="inline-flex items-center px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-medium border border-emerald-200">
                                                                <i class="fas fa-check-circle mr-2"></i>Terkirim
                                                            </span>
                                                        <?php else: ?>
                                                            <button type="button"
                                                                class="kirim-sertifikat-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg text-sm font-medium flex items-center"
                                                                data-id="<?= $pendaftar['id'] ?>"
                                                                data-nama="<?= htmlspecialchars($pendaftar['nama_peserta'] ?? 'Peserta') ?>">
                                                                <i class="fas fa-certificate mr-2"></i> Kirim
                                                            </button>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-xs text-slate-400">-</span>
                                                    <?php endif; ?>

                                                    <button type="button"
                                                        class="hapus-peserta-btn w-10 h-10 bg-red-100 hover:bg-red-200 text-red-600 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 border border-red-200"
                                                        data-id="<?= $pendaftar['id'] ?>"
                                                        data-nama="<?= htmlspecialchars($pendaftar['nama_peserta']) ?>"
                                                        title="Hapus Peserta">
                                                        <i class="fas fa-trash-alt text-sm"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="<?= 5 + count($table_headers) ?>" class="px-6 py-12 text-center">
                                            <div
                                                class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                                <i class="fas fa-user-slash text-slate-400 text-3xl"></i>
                                            </div>
                                            <h3 class="text-lg font-semibold text-slate-600 mb-2">Belum Ada Pendaftar</h3>
                                            <p class="text-slate-500">Peserta akan muncul di sini setelah mendaftar</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <!-- Mobile View -->
            <div class="lg:hidden space-y-4 p-4">
                <?php if (!empty($pendaftar_list)): ?>
                    <?php foreach ($pendaftar_list as $pendaftar): ?>
                        <div
                            class="bg-white border border-slate-200 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-4">
                            <!-- Header -->
                            <div class="flex justify-between items-start mb-4 pb-4 border-b border-slate-200">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-12 h-12 bg-gradient-to-r from-slate-700 to-slate-800 rounded-full flex items-center justify-center text-white font-semibold border border-slate-600">
                                        <?= strtoupper(substr($pendaftar['nama_peserta'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-slate-800">
                                            <?= htmlspecialchars($pendaftar['nama_peserta']) ?>
                                        </h3>
                                        <div class="flex items-center space-x-2 mt-1">
                                            <span class="text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded-full">
                                                <?= $pendaftar['kode_unik'] ?>
                                            </span>
                                            <?php if ($pendaftar['status_kehadiran'] == 'hadir'): ?>
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                    <i class="fas fa-check mr-1"></i> Hadir
                                                </span>
                                            <?php else: ?>
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                                    Menunggu
                                                </span>
                                            <?php endif; ?>

                                            <?php
                                            $status = $pendaftar['status_pembayaran'] ?? 'pending';
                                            switch ($status) {
                                                case 'paid':
                                                    echo '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    <i class="fas fa-check-circle mr-1"></i> Lunas
                                                  </span>';
                                                    break;
                                                case 'failed':
                                                    echo '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 border border-red-200">
                                                    <i class="fas fa-times-circle mr-1"></i> Gagal
                                                  </span>';
                                                    break;
                                                case 'free':
                                                    echo '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-sky-100 text-sky-700 border border-sky-200">
                                                    <i class="fas fa-gift mr-1"></i> Gratis
                                                  </span>';
                                                    break;
                                                default:
                                                    echo '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200">
                                                    <i class="fas fa-clock mr-1"></i> Pending
                                                  </span>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Informasi -->
                            <div class="space-y-3 mb-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-sm font-medium text-slate-600">Email</span>
                                        <div class="text-sm text-slate-800 mt-1 flex items-center">
                                            <i class="fas fa-envelope mr-2 text-slate-400 text-xs"></i>
                                            <?= htmlspecialchars($pendaftar['email_peserta']) ?>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-slate-600">Telepon</span>
                                        <div class="text-sm text-slate-800 mt-1 flex items-center">
                                            <i class="fas fa-phone mr-2 text-slate-400 text-xs"></i>
                                            <?= htmlspecialchars($pendaftar['telepon_peserta']) ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dynamic Fields -->
                                <?php foreach ($table_headers as $header): ?>
                                    <div>
                                        <span
                                            class="text-sm font-medium text-slate-600"><?= htmlspecialchars($header['label']) ?></span>
                                        <div class="text-sm text-slate-800 mt-1">
                                            <?php
                                            $field_id = $header['id'] ?? null;
                                            echo htmlspecialchars($pendaftar['answers'][$field_id] ?? '-');
                                            ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Aksi -->
                            <div class="flex justify-between items-center pt-4 border-t border-slate-200">
                                <?php if ($pendaftar['status_kehadiran'] == 'hadir'): ?>
                                    <?php if (!empty($pendaftar['sertifikat_status']) && $pendaftar['sertifikat_status'] == 'terkirim'): ?>
                                        <span
                                            class="inline-flex items-center px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700 text-sm font-medium">
                                            <i class="fas fa-check-circle mr-2"></i>Sertifikat Terkirim
                                        </span>
                                    <?php else: ?>
                                        <button type="button"
                                            class="kirim-sertifikat-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-xl transition-colors text-sm font-medium flex items-center"
                                            data-id="<?= $pendaftar['id'] ?>"
                                            data-nama="<?= htmlspecialchars($pendaftar['nama_peserta'] ?? 'Peserta') ?>">
                                            <i class="fas fa-certificate mr-2"></i> Kirim Sertifikat
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-sm text-slate-400">Menunggu kehadiran</span>
                                <?php endif; ?>

                                <button type="button"
                                    class="hapus-peserta-btn bg-red-500 hover:bg-red-600 text-white w-10 h-10 rounded-xl flex items-center justify-center transition-colors"
                                    data-id="<?= $pendaftar['id'] ?>"
                                    data-nama="<?= htmlspecialchars($pendaftar['nama_peserta']) ?>" title="Hapus Peserta">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-12">
                        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user-slash text-slate-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-600 mb-2">Belum Ada Pendaftar</h3>
                        <p class="text-slate-500">Peserta akan muncul di sini setelah mendaftar</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="mt-8">
                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-slate-600">
                            Menampilkan <span class="font-semibold text-slate-800"><?= ($offset + 1) ?></span> -
                            <span class="font-semibold text-slate-800"><?= min($offset + $limit, $total_records) ?></span>
                            dari
                            <span class="font-semibold text-slate-800"><?= $total_records ?></span> peserta
                        </div>

                        <div class="flex items-center space-x-2">
                            <!-- Previous Button -->
                            <a href="?event_id=<?= $event_id ?>&page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_jk) ? '&filter_jk=' . urlencode($filter_jk) : '' ?><?= !empty($filter_status) ? '&filter_status=' . urlencode($filter_status) : '' ?>"
                                class="w-10 h-10 bg-white border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-colors flex items-center justify-center <?= $page <= 1 ? 'opacity-50 pointer-events-none' : '' ?>">
                                <i class="fas fa-chevron-left text-sm"></i>
                            </a>

                            <!-- Page Numbers -->
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $start_page + 4);
                            $start_page = max(1, $end_page - 4);

                            for ($i = $start_page; $i <= $end_page; $i++):
                                ?>
                                <a href="?event_id=<?= $event_id ?>&page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_jk) ? '&filter_jk=' . urlencode($filter_jk) : '' ?><?= !empty($filter_status) ? '&filter_status=' . urlencode($filter_status) : '' ?>"
                                    class="w-10 h-10 rounded-xl border flex items-center justify-center text-sm font-medium transition-all duration-300 
                                        <?= $i == $page
                                            ? 'bg-amber-500 text-white border-amber-500 shadow-lg'
                                            : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>

                            <!-- Next Button -->
                            <a href="?event_id=<?= $event_id ?>&page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($filter_jk) ? '&filter_jk=' . urlencode($filter_jk) : '' ?><?= !empty($filter_status) ? '&filter_status=' . urlencode($filter_status) : '' ?>"
                                class="w-10 h-10 bg-white border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-colors flex items-center justify-center <?= $page >= $total_pages ? 'opacity-50 pointer-events-none' : '' ?>">
                                <i class="fas fa-chevron-right text-sm"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Toggle download dropdown
    document.getElementById('downloadDropdownButton').addEventListener('click', function () {
        document.getElementById('downloadDropdown').classList.toggle('hidden');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function (event) {
        const dropdown = document.getElementById('downloadDropdown');
        const button = document.getElementById('downloadDropdownButton');

        if (!dropdown.contains(event.target) && !button.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Fungsi untuk menghapus peserta
    function hapusPeserta(pendaftaranId, namaPeserta) {
        Swal.fire({
            title: 'Hapus Peserta?',
            html: `Apakah Anda yakin ingin menghapus <strong>${namaPeserta}</strong> dari event ini?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            backdrop: true,
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Sedang menghapus peserta',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Kirim request hapus ke server
                fetch(`hapus_peserta.php?id=${pendaftaranId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#10b981',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                // Refresh halaman
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: data.message,
                                icon: 'error',
                                confirmButtonColor: '#ef4444'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat menghapus peserta',
                            icon: 'error',
                            confirmButtonColor: '#ef4444'
                        });
                    });
            }
        });
    }

    // Event listener untuk tombol hapus
    document.addEventListener('DOMContentLoaded', function () {
        const hapusButtons = document.querySelectorAll('.hapus-peserta-btn');
        hapusButtons.forEach(button => {
            button.addEventListener('click', function () {
                const pendaftaranId = this.getAttribute('data-id');
                const namaPeserta = this.getAttribute('data-nama');
                hapusPeserta(pendaftaranId, namaPeserta);
            });
        });

        // Event listener untuk kirim sertifikat (jika ada)
        // Event listener untuk kirim sertifikat
        const kirimSertifikatButtons = document.querySelectorAll('.kirim-sertifikat-btn');
        kirimSertifikatButtons.forEach(button => {
            button.addEventListener('click', function () {
                const pendaftaranId = this.getAttribute('data-id');
                const namaPeserta = this.getAttribute('data-nama');

                Swal.fire({
                    title: 'Kirim Sertifikat?',
                    text: `Kirim sertifikat digital kepada ${namaPeserta}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3b82f6',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Kirim!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Sedang menyiapkan sertifikat',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });

                        // Kirim data ke backend
                        const formData = new FormData();
                        formData.append('id', pendaftaranId);

                        fetch('proses_kirim_sertifikat.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: data.message,
                                        icon: 'success',
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload(); // Refresh halaman
                                    });
                                } else {
                                    Swal.fire('Gagal!', data.message, 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire('Error!', 'Terjadi kesalahan koneksi.', 'error');
                            });
                    }
                });
            });
        });
    });
</script>

<?php
require_once 'templates/footer.php';
?>