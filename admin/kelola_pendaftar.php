<?php
$page_title = 'Data Pendaftar';
$current_page = 'data_pendaftar';
require_once BASE_PATH . '/admin/templates/header.php';
require_once 'core/koneksi.php';

$penyelenggara_id = $_SESSION['penyelenggara_id_bersama'];

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';

if (!empty($search)) {
    $search_condition = " AND (w.judul LIKE ? OR w.deskripsi LIKE ?)";
}

// Get total number of records for pagination
try {
    $count_sql = "SELECT COUNT(*) as total 
                  FROM workshops w 
                  WHERE w.penyelenggara_id = ? $search_condition";
    $count_stmt = $pdo->prepare($count_sql);

    $count_stmt->bindValue(1, $penyelenggara_id, PDO::PARAM_INT);
    $i = 2;
    if (!empty($search)) {
        $count_stmt->bindValue($i++, "%$search%", PDO::PARAM_STR);
        $count_stmt->bindValue($i++, "%$search%", PDO::PARAM_STR);
    }

    $count_stmt->execute();
    $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_records / $limit);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Get events with pagination
try {
    $sql = "SELECT w.*, 
                   (SELECT COUNT(*) FROM pendaftaran p WHERE p.workshop_id = w.id) as jumlah_pendaftar,
                   (SELECT COUNT(*) FROM pendaftaran p WHERE p.workshop_id = w.id AND p.status_kehadiran = 'hadir') as jumlah_hadir
            FROM workshops w 
            WHERE w.penyelenggara_id = ? $search_condition
            ORDER BY w.tanggal_waktu DESC 
            LIMIT ? OFFSET ?";

    $stmt = $pdo->prepare($sql);

    $index = 1;
    $stmt->bindValue($index++, $penyelenggara_id, PDO::PARAM_INT);
    if (!empty($search)) {
        $stmt->bindValue($index++, "%$search%", PDO::PARAM_STR);
        $stmt->bindValue($index++, "%$search%", PDO::PARAM_STR);
    }

    $stmt->bindValue($index++, (int) $limit, PDO::PARAM_INT);
    $stmt->bindValue($index++, (int) $offset, PDO::PARAM_INT);

    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                            <h1 class="text-2xl font-bold text-slate-800">Data Pendaftar</h1>
                            <p class="text-slate-600">Kelola dan pantau peserta dari semua event Anda</p>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-slate-800"><?= $total_records ?></div>
                            <div class="text-sm text-slate-600">Total Event</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-slate-800">
                                <?= array_sum(array_column($events, 'jumlah_pendaftar')) ?>
                            </div>
                            <div class="text-sm text-slate-600">Total Pendaftar</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-slate-800">
                                <?= array_sum(array_column($events, 'jumlah_hadir')) ?>
                            </div>
                            <div class="text-sm text-slate-600">Peserta Hadir</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-slate-800">
                                <?= count(array_filter($events, function ($event) {
                                    return strtotime($event['tanggal_waktu']) > time();
                                })) ?>
                            </div>
                            <div class="text-sm text-slate-600">Event Aktif</div>
                        </div>
                    </div>
                </div>

                <!-- Search Form -->
                <form method="GET" class="flex flex-col sm:flex-row gap-3">
                    <div class="relative">
                        <div class="relative rounded-xl shadow-sm">
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                                class="block w-full md:w-80 pl-10 pr-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition-all duration-300 bg-white"
                                placeholder="Cari event...">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-slate-400"></i>
                            </div>
                            <?php if (!empty($search)): ?>
                                <a href="?page=1"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button type="submit"
                        class="bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg flex items-center justify-center">
                        <i class="fas fa-search mr-2"></i> Cari
                    </button>
                </form>
            </div>
        </div>

        <!-- Events Cards for Mobile -->
        <div class="lg:hidden space-y-6">
            <?php if (count($events) > 0): ?>
                <?php foreach ($events as $event): ?>
                    <div
                        class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6 hover:shadow-xl transition-all duration-300">
                        <div class="flex items-start space-x-4 mb-4">
                            <?php if ($event['poster']): ?>
                                <img src="../assets/img/posters/<?= htmlspecialchars($event['poster']) ?>" alt="Poster"
                                    class="w-20 h-16 object-cover rounded-lg border border-slate-200 flex-shrink-0">
                            <?php else: ?>
                                <div
                                    class="w-20 h-16 bg-slate-100 rounded-lg flex items-center justify-center border border-slate-200 flex-shrink-0">
                                    <i class="fas fa-calendar text-slate-400 text-xl"></i>
                                </div>
                            <?php endif; ?>

                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-semibold text-slate-800 mb-2">
                                    <?= htmlspecialchars($event['judul']) ?>
                                </h3>
                                <div class="space-y-2">
                                    <div class="flex items-center text-sm text-slate-600">
                                        <i class="fas fa-calendar-alt mr-2 text-amber-500"></i>
                                        <?= date('d M Y, H:i', strtotime($event['tanggal_waktu'])) ?>
                                    </div>
                                    <div class="flex items-center space-x-4 text-sm">
                                        <span class="flex items-center text-slate-600">
                                            <i class="fas fa-users mr-1 text-blue-500"></i>
                                            <?= $event['jumlah_pendaftar'] ?> Pendaftar
                                        </span>
                                        <span class="flex items-center text-emerald-600">
                                            <i class="fas fa-user-check mr-1"></i>
                                            <?= $event['jumlah_hadir'] ?> Hadir
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="flex items-center justify-between mb-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                <?= strtotime($event['tanggal_waktu']) > time()
                                    ? 'bg-emerald-100 text-emerald-800 border border-emerald-200'
                                    : 'bg-slate-100 text-slate-800 border border-slate-200' ?>">
                                <i
                                    class="fas fa-<?= strtotime($event['tanggal_waktu']) > time() ? 'clock' : 'check-circle' ?> mr-2 text-xs"></i>
                                <?= strtotime($event['tanggal_waktu']) > time() ? 'Akan Datang' : 'Selesai' ?>
                            </span>

                            <?php if ($event['jumlah_pendaftar'] > 0): ?>
                                <span class="text-sm text-slate-500">
                                    <?= round(($event['jumlah_hadir'] / $event['jumlah_pendaftar']) * 100) ?>% Kehadiran
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-200">
                            <a href="lihat_detail_pendaftar?event_id=<?= $event['id'] ?>"
                                class="bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-semibold py-2 px-6 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1 flex items-center">
                                <i class="fas fa-eye mr-2"></i> Lihat Detail
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-8 text-center">
                    <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-calendar-times text-slate-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-600 mb-2">Tidak Ada Event Ditemukan</h3>
                    <p class="text-slate-500 mb-4">
                        <?php if (!empty($search)): ?>
                            Tidak ada event yang cocok dengan pencarian "<?= htmlspecialchars($search) ?>"
                        <?php else: ?>
                            Anda belum memiliki event yang dibuat
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($search)): ?>
                        <a href="?page=1" class="text-amber-600 hover:text-amber-700 font-medium">
                            Tampilkan semua event
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Events Table for Desktop -->
        <div class="hidden lg:block bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
            <!-- Table Header -->
            <div class="bg-gradient-to-r from-slate-800 to-slate-900 text-white px-6 py-4">
                <div class="grid grid-cols-12 gap-4 items-center">
                    <div class="col-span-4">
                        <span class="font-semibold">Event</span>
                    </div>
                    <div class="col-span-2">
                        <span class="font-semibold">Tanggal</span>
                    </div>
                    <div class="col-span-2 text-center">
                        <span class="font-semibold">Pendaftar</span>
                    </div>
                    <div class="col-span-2 text-center">
                        <span class="font-semibold">Kehadiran</span>
                    </div>
                    <div class="col-span-2 text-center">
                        <span class="font-semibold">Aksi</span>
                    </div>
                </div>
            </div>

            <!-- Table Body -->
            <div class="divide-y divide-slate-200">
                <?php if (count($events) > 0): ?>
                    <?php foreach ($events as $event): ?>
                        <div class="px-6 py-4 hover:bg-slate-50/50 transition-colors group">
                            <div class="grid grid-cols-12 gap-4 items-center">
                                <!-- Event Info -->
                                <div class="col-span-4">
                                    <div class="flex items-center space-x-4">
                                        <?php if ($event['poster']): ?>
                                            <img src="../assets/img/posters/<?= htmlspecialchars($event['poster']) ?>" alt="Poster"
                                                class="w-16 h-12 object-cover rounded-lg border border-slate-200">
                                        <?php else: ?>
                                            <div
                                                class="w-16 h-12 bg-slate-100 rounded-lg flex items-center justify-center border border-slate-200">
                                                <i class="fas fa-calendar text-slate-400"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="min-w-0 flex-1">
                                            <h4
                                                class="font-semibold text-slate-800 group-hover:text-amber-600 transition-colors truncate">
                                                <?= htmlspecialchars($event['judul']) ?>
                                            </h4>
                                            <p class="text-sm text-slate-500 truncate">
                                                <?= htmlspecialchars($event['lokasi']) ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Date -->
                                <div class="col-span-2">
                                    <div class="text-sm text-slate-700">
                                        <div class="font-medium"><?= date('d M Y', strtotime($event['tanggal_waktu'])) ?></div>
                                        <div class="text-slate-500"><?= date('H:i', strtotime($event['tanggal_waktu'])) ?> WIB
                                        </div>
                                    </div>
                                </div>

                                <!-- Pendaftar -->
                                <div class="col-span-2 text-center">
                                    <div class="flex flex-col items-center">
                                        <span
                                            class="text-lg font-semibold text-slate-800"><?= $event['jumlah_pendaftar'] ?></span>
                                        <span class="text-xs text-slate-500">Pendaftar</span>
                                    </div>
                                </div>

                                <!-- Kehadiran -->
                                <div class="col-span-2 text-center">
                                    <div class="flex flex-col items-center">
                                        <?php if ($event['jumlah_pendaftar'] > 0): ?>
                                            <span
                                                class="text-lg font-semibold text-emerald-600"><?= $event['jumlah_hadir'] ?></span>
                                            <span class="text-xs text-slate-500">
                                                (<?= round(($event['jumlah_hadir'] / $event['jumlah_pendaftar']) * 100) ?>%)
                                            </span>
                                        <?php else: ?>
                                            <span class="text-lg font-semibold text-slate-400">0</span>
                                            <span class="text-xs text-slate-500">(0%)</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="col-span-2">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="lihat_detail_pendaftar?event_id=<?= $event['id'] ?>"
                                            class="w-12 h-12 bg-amber-500 hover:bg-amber-600 text-white rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 shadow-md hover:shadow-lg"
                                            title="Lihat Detail Pendaftar">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <?php if ($event['jumlah_pendaftar'] > 0): ?>
                                            <a href="export_pendaftar.php?event_id=<?= $event['id'] ?>"
                                                class="w-12 h-12 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 shadow-md hover:shadow-lg"
                                                title="Export Data">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        <?php endif; ?>
                                        <button onclick="openModalPeserta()"
                                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition flex items-center gap-2">
                                            <i class="fas fa-user-plus"></i> Tambah Santri Internal
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="px-6 py-12 text-center">
                        <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-calendar-times text-slate-400 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-600 mb-2">Tidak Ada Event Ditemukan</h3>
                        <p class="text-slate-500">
                            <?php if (!empty($search)): ?>
                                Coba gunakan kata kunci pencarian yang berbeda
                            <?php else: ?>
                                Anda belum memiliki event yang dibuat
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="mt-8">
                <!-- Mobile Pagination -->
                <div class="lg:hidden bg-white rounded-2xl shadow-lg border border-slate-200 p-4">
                    <div class="flex items-center justify-between">
                        <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                            class="flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors font-medium <?= $page <= 1 ? 'opacity-50 pointer-events-none' : '' ?>">
                            <i class="fas fa-chevron-left mr-2"></i> Sebelumnya
                        </a>

                        <span class="text-sm text-slate-700 font-medium">
                            <?= $page ?> / <?= $total_pages ?>
                        </span>

                        <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                            class="flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors font-medium <?= $page >= $total_pages ? 'opacity-50 pointer-events-none' : '' ?>">
                            Selanjutnya <i class="fas fa-chevron-right ml-2"></i>
                        </a>
                    </div>
                </div>

                <!-- Desktop Pagination -->
                <div class="hidden lg:block bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-slate-600">
                            Menampilkan <span class="font-semibold text-slate-800"><?= ($offset + 1) ?></span> -
                            <span class="font-semibold text-slate-800"><?= min($offset + $limit, $total_records) ?></span>
                            dari
                            <span class="font-semibold text-slate-800"><?= $total_records ?></span> event
                        </div>

                        <div class="flex items-center space-x-2">
                            <!-- Previous Button -->
                            <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
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
                                <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="w-10 h-10 rounded-xl border flex items-center justify-center text-sm font-medium transition-all duration-300 
                                        <?= $i == $page
                                            ? 'bg-amber-500 text-white border-amber-500 shadow-lg'
                                            : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>

                            <!-- Next Button -->
                            <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
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

<div id="modalPilihPeserta"
    class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh]">

        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Pilih Santri</h3>
                <p class="text-sm text-gray-500">Centang santri yang akan diikutkan ke event ini.</p>
            </div>
            <button onclick="closeModalPeserta()" class="text-gray-400 hover:text-red-500 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="p-0 overflow-y-auto flex-1">
            <form action="proses_tambah_peserta_internal.php" method="POST" id="formInternal">
                <input type="hidden" name="workshop_id" value="<?= $workshop_id ?>">

                <div class="p-4 border-b border-gray-100 sticky top-0 bg-white z-10">
                    <input type="text" id="searchSantri" onkeyup="filterSantri()" placeholder="Cari nama santri..."
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase sticky top-[57px] z-10 shadow-sm">
                        <tr>
                            <th class="p-4 w-10 text-center">
                                <input type="checkbox" id="checkAll"
                                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            </th>
                            <th class="p-4">Nama Santri</th>
                            <th class="p-4">Email / Kontak</th>
                            <th class="p-4">JK</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="listSantriBody">
                        <?php
                        // QUERY CERDAS: 
                        // Ambil user role 'peserta' YANG BELUM ada di tabel pendaftaran event ini
                        $sql_calon = "SELECT * FROM users 
                                      WHERE role = 'peserta' 
                                      AND id NOT IN (
                                          SELECT user_id FROM pendaftaran WHERE workshop_id = ?
                                      )
                                      ORDER BY nama_lengkap ASC";

                        $stmt_c = $pdo->prepare($sql_calon);
                        $stmt_c->execute([$workshop_id]);
                        $calon_peserta = $stmt_c->fetchAll();

                        if (count($calon_peserta) > 0):
                            foreach ($calon_peserta as $usr):
                                ?>
                                <tr class="hover:bg-blue-50 transition cursor-pointer" onclick="toggleRow(this)">
                                    <td class="p-4 text-center">
                                        <input type="checkbox" name="user_ids[]" value="<?= $usr['id'] ?>"
                                            class="santri-checkbox w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 pointer-events-none">
                                    </td>
                                    <td class="p-4 font-medium text-gray-800 search-target">
                                        <?= htmlspecialchars($usr['nama_lengkap']) ?>
                                    </td>
                                    <td class="p-4 text-sm text-gray-500">
                                        <?= htmlspecialchars($usr['email']) ?>
                                    </td>
                                    <td class="p-4 text-sm text-gray-500">
                                        <?= $usr['jenis_kelamin'] == 'Laki-laki' ? '<i class="fas fa-mars text-blue-500"></i>' : '<i class="fas fa-venus text-pink-500"></i>' ?>
                                    </td>
                                </tr>
                            <?php
                            endforeach;
                        else:
                            ?>
                            <tr>
                                <td colspan="4" class="p-8 text-center text-gray-500">
                                    Semua santri sudah terdaftar di event ini.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </form>
        </div>

        <div class="p-5 border-t border-gray-100 bg-gray-50 flex justify-between items-center">
            <span class="text-sm text-gray-500" id="selectedCount">0 terpilih</span>
            <div class="flex gap-3">
                <button type="button" onclick="closeModalPeserta()"
                    class="px-4 py-2 text-gray-600 hover:bg-gray-200 rounded-lg transition">Batal</button>
                <button type="submit" form="formInternal"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-lg transition">
                    Simpan Peserta
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('modalPilihPeserta');
    const checkboxes = document.querySelectorAll('.santri-checkbox');
    const selectedCountLabel = document.getElementById('selectedCount');

    function openModalPeserta() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModalPeserta() {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Klik Baris untuk Centang (UX Friendly)
    function toggleRow(row) {
        const checkbox = row.querySelector('.santri-checkbox');
        checkbox.checked = !checkbox.checked;
        updateCount();
    }

    // Fitur Check All
    document.getElementById('checkAll').addEventListener('change', function () {
        const isChecked = this.checked;
        // Hanya centang yang terlihat (hasil search)
        const visibleRows = document.querySelectorAll('#listSantriBody tr:not([style*="display: none"]) .santri-checkbox');
        visibleRows.forEach(box => box.checked = isChecked);
        updateCount();
    });

    // Update Counter Jumlah Terpilih
    function updateCount() {
        const count = document.querySelectorAll('.santri-checkbox:checked').length;
        selectedCountLabel.innerText = count + " terpilih";
    }

    // Fitur Search / Filter Nama
    function filterSantri() {
        const input = document.getElementById('searchSantri').value.toLowerCase();
        const rows = document.querySelectorAll('#listSantriBody tr');

        rows.forEach(row => {
            const name = row.querySelector('.search-target').innerText.toLowerCase();
            if (name.includes(input)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }
</script>

<?php
require_once BASE_PATH . '/admin/templates/footer.php';
?>

<!-- SweetAlert2 untuk konfirmasi hapus -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
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
                                confirmButtonColor: '#10b981'
                            }).then(() => {
                                // Refresh halaman atau hapus elemen dari DOM
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
        // Untuk halaman detail pendaftar (lihat_detail_pendaftar.php)
        const hapusButtons = document.querySelectorAll('.btn-hapus-peserta');
        hapusButtons.forEach(button => {
            button.addEventListener('click', function () {
                const pendaftaranId = this.getAttribute('data-id');
                const namaPeserta = this.getAttribute('data-nama');
                hapusPeserta(pendaftaranId, namaPeserta);
            });
        });
    });
</script>