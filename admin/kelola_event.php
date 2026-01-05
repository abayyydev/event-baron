<?php
if (!defined('BASE_PATH')) {
    // dirname(__DIR__) mengambil folder root (naik 1 level dari folder admin)
    define('BASE_PATH', dirname(__DIR__));
}
$page_title = 'Kelola Event';
$current_page = 'kelola_event';
require_once BASE_PATH . '/admin/templates/header.php';
// 2. LOAD KONEKSI DENGAN BASE_PATH (SOLUSI ERROR $pdo)
require_once BASE_PATH . '/core/koneksi.php';
$penyelenggara_id = $_SESSION['penyelenggara_id_bersama'];

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

try {
    $countSql = "SELECT COUNT(*) FROM workshops WHERE penyelenggara_id = ? AND judul LIKE ?";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([$penyelenggara_id, "%$search%"]);
    $total_records = $countStmt->fetchColumn();
    $total_pages = ceil($total_records / $limit);

    $sql = "SELECT * FROM workshops WHERE penyelenggara_id = ? AND judul LIKE ? ORDER BY tanggal_waktu DESC LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, $penyelenggara_id, PDO::PARAM_INT);
    $stmt->bindValue(2, "%$search%", PDO::PARAM_STR);
    $stmt->bindValue(3, $limit, PDO::PARAM_INT);
    $stmt->bindValue(4, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8">
            <div class="mb-6 lg:mb-0">
                <h1 class="text-3xl font-bold text-slate-800 mb-2">Kelola Event</h1>
                <p class="text-slate-600">Kelola dan pantau semua workshop dan event Anda di satu tempat</p>
            </div>
            <a href="form_event.php"
                class="bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center">
                <i class="fas fa-plus mr-3"></i>
                Tambah Event Baru
            </a>
        </div>

        <!-- Search and Stats Section -->
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6 mb-8">
            <div class="flex flex-col lg:flex-row gap-4 items-center justify-between">
                <!-- Search Box -->
                <div class="flex-grow relative">
                    <div class="relative">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                            placeholder="Cari event berdasarkan judul..."
                            class="w-full pl-12 pr-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition-all duration-300 bg-slate-50/50">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-slate-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button type="submit"
                        class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-3 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg flex items-center">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="kelola_event"
                        class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-3 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg flex items-center">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-200">
                <div class="text-center">
                    <div class="text-2xl font-bold text-slate-800"><?= $total_records ?></div>
                    <div class="text-sm text-slate-600">Total Event</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-slate-800">
                        <?= count(array_filter($events, function ($event) {
                            return strtotime($event['tanggal_waktu']) > time();
                        })) ?>
                    </div>
                    <div class="text-sm text-slate-600">Event Mendatang</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-slate-800">
                        <?= count(array_filter($events, function ($event) {
                            return strtotime($event['tanggal_waktu']) <= time();
                        })) ?>
                    </div>
                    <div class="text-sm text-slate-600">Event Selesai</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-slate-800">
                        <?= count(array_filter($events, function ($event) {
                            return $event['tipe_event'] === 'berbayar';
                        })) ?>
                    </div>
                    <div class="text-sm text-slate-600">Event Berbayar</div>
                </div>
            </div>
        </div>

        <!-- Events Table/Cards -->
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
            <!-- Desktop Table -->
            <div class="hidden lg:block">
                <div class="bg-gradient-to-r from-slate-800 to-slate-900 text-white px-6 py-4">
                    <div class="grid grid-cols-12 gap-4 items-center">
                        <div class="col-span-2">
                            <span class="font-semibold">Poster</span>
                        </div>
                        <div class="col-span-3">
                            <span class="font-semibold">Judul Event</span>
                        </div>
                        <div class="col-span-2">
                            <span class="font-semibold">Tanggal & Waktu</span>
                        </div>
                        <div class="col-span-2">
                            <span class="font-semibold">Lokasi</span>
                        </div>
                        <div class="col-span-1">
                            <span class="font-semibold">Tipe</span>
                        </div>
                        <div class="col-span-2 text-center">
                            <span class="font-semibold">Aksi</span>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-slate-200">
                    <?php if (count($events) > 0): ?>
                        <?php foreach ($events as $event): ?>
                            <div class="px-6 py-4 hover:bg-slate-50/50 transition-colors group">
                                <div class="grid grid-cols-12 gap-4 items-center">
                                    <!-- Poster -->
                                    <div class="col-span-2">
                                        <?php if ($event['poster']): ?>
                                            <img src="../assets/img/posters/<?= htmlspecialchars($event['poster']) ?>" alt="Poster"
                                                class="h-16 w-24 object-cover rounded-lg shadow-sm border border-slate-200">
                                        <?php else: ?>
                                            <div
                                                class="h-16 w-24 bg-slate-100 rounded-lg flex items-center justify-center border border-slate-200">
                                                <i class="fas fa-image text-slate-400 text-xl"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Judul -->
                                    <div class="col-span-3">
                                        <h3 class="font-semibold text-slate-800 group-hover:text-amber-600 transition-colors">
                                            <?= htmlspecialchars($event['judul']) ?>
                                        </h3>
                                        <p class="text-sm text-slate-500 mt-1 line-clamp-2">
                                            <?= htmlspecialchars(substr($event['deskripsi'] ?? '', 0, 100)) ?>...
                                        </p>
                                    </div>

                                    <!-- Tanggal -->
                                    <div class="col-span-2">
                                        <div class="flex items-center text-slate-700">
                                            <i class="fas fa-calendar-alt mr-2 text-amber-500"></i>
                                            <div>
                                                <div class="text-sm font-medium">
                                                    <?= date('d M Y', strtotime($event['tanggal_waktu'])) ?>
                                                </div>
                                                <div class="text-xs text-slate-500">
                                                    <?= date('H:i', strtotime($event['tanggal_waktu'])) ?> WIB
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Lokasi -->
                                    <div class="col-span-2">
                                        <div class="flex items-center text-slate-700">
                                            <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                                            <span class="text-sm"><?= htmlspecialchars($event['lokasi']) ?></span>
                                        </div>
                                    </div>

                                    <!-- Tipe -->
                                    <div class="col-span-1">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            <?= $event['tipe_event'] === 'berbayar' ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' ?>">
                                            <?= $event['tipe_event'] === 'berbayar' ? 'Berbayar' : 'Gratis' ?>
                                        </span>
                                    </div>

                                    <!-- Actions -->
                                    <div class="col-span-2">
                                        <div class="flex items-center justify-center space-x-2">
                                            <!-- Form Management -->
                                            <a href="kelola_form.php?id=<?= $event['id'] ?>"
                                                class="w-10 h-10 bg-emerald-100 hover:bg-emerald-200 text-emerald-600 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 border border-emerald-200"
                                                title="Atur Form Pendaftaran">
                                                <i class="fas fa-list-alt text-sm"></i>
                                            </a>

                                            <!-- Edit Button -->
                                            <a href="form_event.php?id=<?= $event['id'] ?>"
                                                class="w-10 h-10 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 border border-blue-200"
                                                title="Edit Event">
                                                <i class="fas fa-pencil-alt text-sm"></i>
                                            </a>

                                            <!-- Delete Button -->
                                            <button
                                                class="hapus-btn w-10 h-10 bg-red-100 hover:bg-red-200 text-red-600 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 border border-red-200"
                                                title="Hapus Event" data-id="<?= $event['id'] ?>"
                                                data-judul="<?= htmlspecialchars($event['judul']) ?>">
                                                <i class="fas fa-trash-alt text-sm"></i>
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
                            <h3 class="text-lg font-semibold text-slate-600 mb-2">Belum Ada Event</h3>
                            <p class="text-slate-500 mb-4">
                                <?php if ($search): ?>
                                    Tidak ada event yang cocok dengan pencarian "<?= htmlspecialchars($search) ?>"
                                <?php else: ?>
                                    Anda belum membuat event apapun. Mulai dengan membuat event pertama Anda!
                                <?php endif; ?>
                            </p>
                            <button data-modal-target="#addEventModal"
                                class="bg-amber-500 hover:bg-amber-600 text-white font-medium py-2 px-6 rounded-xl transition-colors inline-flex items-center">
                                <i class="fas fa-plus mr-2"></i> Buat Event Pertama
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mobile Cards -->
            <div class="lg:hidden">
                <?php if (count($events) > 0): ?>
                    <?php foreach ($events as $event): ?>
                        <div class="border-b border-slate-200 p-6 hover:bg-slate-50/50 transition-colors">
                            <div class="flex items-start space-x-4 mb-4">
                                <?php if ($event['poster']): ?>
                                    <img src="../assets/img/posters/<?= htmlspecialchars($event['poster']) ?>" alt="Poster"
                                        class="h-20 w-16 object-cover rounded-lg shadow-sm border border-slate-200 flex-shrink-0">
                                <?php else: ?>
                                    <div
                                        class="h-20 w-16 bg-slate-100 rounded-lg flex items-center justify-center border border-slate-200 flex-shrink-0">
                                        <i class="fas fa-image text-slate-400"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-slate-800 text-lg mb-1">
                                        <?= htmlspecialchars($event['judul']) ?>
                                    </h3>
                                    <div class="flex items-center text-sm text-slate-600 mb-1">
                                        <i class="fas fa-calendar-alt mr-2 text-amber-500"></i>
                                        <?= date('d M Y, H:i', strtotime($event['tanggal_waktu'])) ?>
                                    </div>
                                    <div class="flex items-center text-sm text-slate-600 mb-2">
                                        <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                                        <?= htmlspecialchars($event['lokasi']) ?>
                                    </div>
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                        <?= $event['tipe_event'] === 'berbayar' ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' ?>">
                                        <?= $event['tipe_event'] === 'berbayar' ? 'Berbayar' : 'Gratis' ?>
                                    </span>
                                </div>
                            </div>

                            <div class="flex justify-between items-center">
                                <a href="kelola_form.php?id=<?= $event['id'] ?>"
                                    class="text-emerald-600 hover:text-emerald-700 text-sm font-medium flex items-center">
                                    <i class="fas fa-list-alt mr-2"></i> Kelola Form
                                </a>

                                <div class="flex space-x-2">
                                    <button
                                        class="edit-btn w-10 h-10 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-xl flex items-center justify-center transition-all duration-300 border border-blue-200"
                                        title="Edit Event" data-id="<?= $event['id'] ?>"
                                        data-judul="<?= htmlspecialchars($event['judul']) ?>"
                                        data-deskripsi="<?= htmlspecialchars($event['deskripsi'] ?? '') ?>"
                                        data-tanggal="<?= date('Y-m-d\TH:i', strtotime($event['tanggal_waktu'])) ?>"
                                        data-lokasi="<?= htmlspecialchars($event['lokasi']) ?>"
                                        data-poster="<?= htmlspecialchars($event['poster'] ?? '') ?>"
                                        data-sertifikat-template="<?= htmlspecialchars($event['sertifikat_template'] ?? '') ?>"
                                        data-sertifikat-prefix="<?= htmlspecialchars($event['sertifikat_prefix'] ?? '') ?>"
                                        data-sertifikat-nomor-awal="<?= $event['sertifikat_nomor_awal'] ?>"
                                        data-sertifikat-font="<?= htmlspecialchars($event['sertifikat_font'] ?? 'Poppins-SemiBold.ttf') ?>"
                                        data-sertifikat-orientasi="<?= htmlspecialchars($event['sertifikat_orientasi'] ?? 'portrait') ?>"
                                        data-sertifikat-nama-fs="<?= $event['sertifikat_nama_fs'] ?>"
                                        data-sertifikat-nama-y-percent="<?= $event['sertifikat_nama_y_percent'] ?>"
                                        data-sertifikat-nama-x-percent="<?= $event['sertifikat_nama_x_percent'] ?>"
                                        data-sertifikat-nomor-fs="<?= $event['sertifikat_nomor_fs'] ?>"
                                        data-sertifikat-nomor-y-percent="<?= $event['sertifikat_nomor_y_percent'] ?>"
                                        data-sertifikat-nomor-x-percent="<?= $event['sertifikat_nomor_x_percent'] ?>">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>

                                    <button
                                        class="hapus-btn w-10 h-10 bg-red-100 hover:bg-red-200 text-red-600 rounded-xl flex items-center justify-center transition-all duration-300 border border-red-200"
                                        title="Hapus Event" data-id="<?= $event['id'] ?>"
                                        data-judul="<?= htmlspecialchars($event['judul']) ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-8 text-center">
                        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-calendar-times text-slate-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-600 mb-2">Belum Ada Event</h3>
                        <p class="text-slate-500 text-sm mb-4">
                            <?php if ($search): ?>
                                Tidak ditemukan event dengan kata kunci "<?= htmlspecialchars($search) ?>"
                            <?php else: ?>
                                Mulai dengan membuat event pertama Anda
                            <?php endif; ?>
                        </p>
                        <button data-modal-target="#addEventModal"
                            class="bg-amber-500 hover:bg-amber-600 text-white font-medium py-2 px-4 rounded-xl transition-colors text-sm">
                            <i class="fas fa-plus mr-1"></i> Buat Event
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="flex flex-col sm:flex-row items-center justify-between mt-8 gap-4">
                <!-- Results Info -->
                <div class="text-sm text-slate-600">
                    Menampilkan <span class="font-semibold"><?= count($events) ?></span> dari
                    <span class="font-semibold"><?= $total_records ?></span> event
                </div>

                <!-- Pagination Controls -->
                <div class="flex items-center space-x-2">
                    <!-- Previous Button -->
                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>"
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
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="w-10 h-10 rounded-xl border flex items-center justify-center text-sm font-medium transition-all duration-300 
                                <?= $i == $page
                                    ? 'bg-amber-500 text-white border-amber-500 shadow-lg'
                                    : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Next Button -->
                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>"
                        class="w-10 h-10 bg-white border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-colors flex items-center justify-center <?= $page >= $total_pages ? 'opacity-50 pointer-events-none' : '' ?>">
                        <i class="fas fa-chevron-right text-sm"></i>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once BASE_PATH . '/admin/templates/modal_tambah_event.php';
require_once BASE_PATH . '/admin/templates/modal_edit_event.php';
require_once BASE_PATH . '/admin/templates/modal_visual_editor.php';
require_once BASE_PATH . '/admin/templates/footer.php';
?>