<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
$page_title = 'Kelola Event';
$current_page = 'kelola_event';
require_once BASE_PATH . '/admin/templates/header.php';
require_once BASE_PATH . '/core/koneksi.php';

$penyelenggara_id = $_SESSION['penyelenggara_id_bersama'];

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

try {
    // Hitung Total Data
    $countSql = "SELECT COUNT(*) FROM workshops WHERE penyelenggara_id = ? AND judul LIKE ?";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([$penyelenggara_id, "%$search%"]);
    $total_records = $countStmt->fetchColumn();
    $total_pages = ceil($total_records / $limit);

    // Ambil Data
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

<!-- Pastikan Tailwind CSS dimuat di header.php, jika tidak tambahkan CDN ini untuk testing -->
<!-- <script src="https://cdn.tailwindcss.com"></script> -->
<!-- <script> tailwind.config = { theme: { extend: { colors: { emerald: { 850: '#064e3b', 950: '#022c22' }, amber: { 450: '#fbbf24' } } } } } </script> -->

<div class="min-h-screen bg-gray-50 font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div>
                <h1 class="text-4xl font-extrabold text-emerald-900 tracking-tight">
                    Kelola <span class="text-amber-500">Event</span>
                </h1>
                <p class="text-gray-500 mt-2 text-sm sm:text-base">Atur jadwal, pendaftaran, dan materi workshop Anda
                    dengan mudah.</p>
            </div>
            <a href="form_event.php"
                class="group relative inline-flex items-center justify-center px-6 py-3 text-sm font-semibold text-white transition-all duration-200 bg-emerald-600 rounded-full hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-600 shadow-lg shadow-emerald-200">
                <span
                    class="absolute inset-y-0 left-0 w-[2px] bg-amber-400 transition-all group-hover:w-full group-hover:bg-amber-500 opacity-20 rounded-full"></span>
                <i class="fas fa-plus mr-2 z-10"></i> <span class="z-10">Buat Event Baru</span>
            </a>
        </div>

        <!-- Search & Filter Card (Posisi Static/Normal) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-2 mb-8">
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-search text-emerald-400"></i>
                </div>
                <input type="text" id="liveSearchInput" value="<?= htmlspecialchars($search) ?>"
                    placeholder="Ketik judul event untuk mencari..."
                    class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 border-transparent text-gray-900 placeholder-gray-400 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all duration-300 shadow-inner">
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                    <span id="loadingIcon" class="hidden text-amber-500 animate-spin">
                        <i class="fas fa-circle-notch"></i>
                    </span>
                </div>
            </div>
        </div>

        <!-- Container Data (Target Live Update) -->
        <div id="dataContainer">

            <!-- Desktop View (Table) -->
            <!-- Note: overflow-visible applied here to allow dropdowns to spill out -->
            <div class="hidden lg:block bg-white rounded-2xl shadow-xl border border-gray-100 overflow-visible">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-emerald-900 text-white text-sm uppercase tracking-wider">
                            <th class="px-6 py-5 font-semibold rounded-tl-2xl">Detail Event</th>
                            <th class="px-6 py-5 font-semibold">Waktu & Tempat</th>
                            <th class="px-6 py-5 font-semibold text-center">Status</th>
                            <th class="px-6 py-5 font-semibold text-center">Kategori</th>
                            <th class="px-6 py-5 font-semibold text-center rounded-tr-2xl">Kontrol</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (count($events) > 0): ?>
                                <?php foreach ($events as $event): ?>
                                        <tr class="hover:bg-emerald-50/40 transition-colors duration-200 group relative">
                                            <!-- Kolom Event -->
                                            <td class="px-6 py-5">
                                                <div class="flex gap-4 items-center">
                                                    <div
                                                        class="relative h-16 w-24 flex-shrink-0 overflow-hidden rounded-lg shadow-md border border-gray-200 group-hover:shadow-lg transition-all">
                                                        <?php if ($event['poster']): ?>
                                                                <img src="../assets/img/posters/<?= htmlspecialchars($event['poster']) ?>"
                                                                    class="h-full w-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                                                        <?php else: ?>
                                                                <div
                                                                    class="h-full w-full bg-gray-100 flex items-center justify-center text-gray-400">
                                                                    <i class="fas fa-image text-xl"></i>
                                                                </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <h3
                                                            class="font-bold text-gray-800 text-base mb-1 group-hover:text-emerald-700 transition-colors">
                                                            <?= htmlspecialchars($event['judul']) ?>
                                                        </h3>
                                                        <p class="text-xs text-gray-500 line-clamp-1">
                                                            <?= htmlspecialchars(substr($event['deskripsi'] ?? '', 0, 50)) ?>...
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Kolom Jadwal -->
                                            <td class="px-6 py-5">
                                                <div class="flex flex-col gap-1">
                                                    <div class="flex items-center text-sm text-gray-700 font-medium">
                                                        <i class="far fa-calendar-alt w-5 text-amber-500"></i>
                                                        <?= date('d M Y', strtotime($event['tanggal_waktu'])) ?>
                                                    </div>
                                                    <div class="flex items-center text-xs text-gray-500 ml-5">
                                                        <?= date('H:i', strtotime($event['tanggal_waktu'])) ?> WIB
                                                    </div>
                                                    <div class="flex items-center text-sm text-gray-600 mt-1">
                                                        <i class="fas fa-map-pin w-5 text-emerald-600"></i>
                                                        <span class="truncate max-w-[150px]"
                                                            title="<?= htmlspecialchars($event['lokasi']) ?>">
                                                            <?= htmlspecialchars($event['lokasi']) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Kolom Visibilitas -->
                                            <td class="px-6 py-5 text-center">
                                                <?php $vis = $event['visibilitas'] ?? 'public'; ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                            <?= $vis === 'public'
                                                ? 'bg-blue-50 text-blue-700 border border-blue-100'
                                                : 'bg-gray-100 text-gray-600 border border-gray-200' ?>">
                                                    <i class="fas <?= $vis === 'public' ? 'fa-globe' : 'fa-lock' ?> mr-1.5"></i>
                                                    <?= ucfirst($vis) ?>
                                                </span>
                                            </td>

                                            <!-- Kolom Tipe -->
                                            <td class="px-6 py-5 text-center">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                            <?= $event['tipe_event'] === 'berbayar'
                                                ? 'bg-amber-50 text-amber-700 border border-amber-200'
                                                : 'bg-emerald-50 text-emerald-700 border border-emerald-200' ?>">
                                                    <?= $event['tipe_event'] === 'berbayar' ? 'Rp Berbayar' : 'Gratis' ?>
                                                </span>
                                            </td>

                                            <!-- Kolom Aksi -->
                                            <td class="px-6 py-5 text-center">
                                                <div class="relative inline-block text-left dropdown-container">
                                                    <button type="button" onclick="toggleDropdown(<?= $event['id'] ?>)"
                                                        class="inline-flex justify-center w-full rounded-lg border border-gray-200 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all">
                                                        Menu <i class="fas fa-chevron-down ml-2 mt-1 text-xs"></i>
                                                    </button>

                                                    <!-- Dropdown Menu -->
                                                    <div id="dropdown-menu-<?= $event['id'] ?>"
                                                        class="hidden absolute right-0 mt-2 w-56 rounded-xl shadow-2xl bg-white ring-1 ring-black ring-opacity-5 z-50 transform transition-all origin-top-right">
                                                        <div class="p-1">
                                                            <a href="form_event.php?id=<?= $event['id'] ?>"
                                                                class="group flex items-center px-4 py-2 text-sm text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-700">
                                                                <i
                                                                    class="fas fa-pencil-alt mr-3 text-gray-400 group-hover:text-emerald-500"></i>
                                                                Edit Event
                                                            </a>
                                                            <a href="kelola_form.php?id=<?= $event['id'] ?>"
                                                                class="group flex items-center px-4 py-2 text-sm text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-700">
                                                                <i
                                                                    class="fas fa-list-alt mr-3 text-gray-400 group-hover:text-emerald-500"></i>
                                                                Atur Form
                                                            </a>
                                                            <a href="kelola_kuesioner.php?id=<?= $event['id'] ?>"
                                                                class="group flex items-center px-4 py-2 text-sm text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-700">
                                                                <i
                                                                    class="fas fa-poll-h mr-3 text-gray-400 group-hover:text-emerald-500"></i>
                                                                Kuesioner
                                                            </a>
                                                            <a href="hasil_kuesioner.php?id=<?= $event['id'] ?>"
                                                                class="group flex items-center px-4 py-2 text-sm text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-700">
                                                                <i
                                                                    class="fas fa-chart-bar mr-3 text-gray-400 group-hover:text-emerald-500"></i>
                                                                Lihat Hasil
                                                            </a>
                                                            <button
                                                                onclick="openModalMateri(<?= $event['id'] ?>, '<?= htmlspecialchars($event['judul'], ENT_QUOTES) ?>')"
                                                                class="w-full text-left group flex items-center px-4 py-2 text-sm text-gray-700 rounded-lg hover:bg-amber-50 hover:text-amber-700">
                                                                <i
                                                                    class="fas fa-file-upload mr-3 text-gray-400 group-hover:text-amber-500"></i>
                                                                Upload Materi
                                                            </button>
                                                            <div class="border-t border-gray-100 my-1"></div>
                                                            <button
                                                                onclick="konfirmasiHapus(<?= $event['id'] ?>, '<?= htmlspecialchars($event['judul']) ?>')"
                                                                class="w-full text-left group flex items-center px-4 py-2 text-sm text-red-600 rounded-lg hover:bg-red-50">
                                                                <i
                                                                    class="fas fa-trash-alt mr-3 text-red-400 group-hover:text-red-600"></i>
                                                                Hapus
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                <?php endforeach; ?>
                        <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="bg-emerald-50 p-4 rounded-full mb-3">
                                                <i class="fas fa-search text-3xl text-emerald-300"></i>
                                            </div>
                                            <h3 class="text-lg font-medium text-gray-900">Tidak ada event ditemukan</h3>
                                            <p class="text-gray-500 text-sm mt-1">Coba ubah kata kunci pencarian Anda atau buat
                                                event baru.</p>
                                        </div>
                                    </td>
                                </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile View (Modern Cards) -->
            <div class="lg:hidden grid grid-cols-1 gap-6">
                <?php if (count($events) > 0): ?>
                        <?php foreach ($events as $event): ?>
                                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden relative">
                                    <!-- Header Card with Image & Status -->
                                    <div class="relative h-40 bg-gray-200">
                                        <?php if ($event['poster']): ?>
                                                <img src="../assets/img/posters/<?= htmlspecialchars($event['poster']) ?>"
                                                    class="w-full h-full object-cover">
                                        <?php else: ?>
                                                <div class="w-full h-full bg-emerald-900 flex items-center justify-center">
                                                    <i class="fas fa-image text-4xl text-emerald-700"></i>
                                                </div>
                                        <?php endif; ?>

                                        <div class="absolute top-3 right-3 flex gap-2">
                                            <span
                                                class="px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wide bg-white/90 backdrop-blur text-emerald-800 shadow-sm">
                                                <?= ($event['visibilitas'] ?? 'public') ?>
                                            </span>
                                            <span
                                                class="px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wide 
                                        <?= $event['tipe_event'] === 'berbayar' ? 'bg-amber-500 text-white' : 'bg-emerald-500 text-white' ?> shadow-sm">
                                                <?= $event['tipe_event'] ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <div class="p-5">
                                        <h3 class="font-bold text-gray-900 text-xl mb-2 leading-tight">
                                            <?= htmlspecialchars($event['judul']) ?>
                                        </h3>

                                        <div class="space-y-2 mb-5">
                                            <div class="flex items-center text-sm text-gray-600">
                                                <i class="far fa-calendar text-amber-500 w-6"></i>
                                                <span><?= date('d M Y, H:i', strtotime($event['tanggal_waktu'])) ?></span>
                                            </div>
                                            <div class="flex items-center text-sm text-gray-600">
                                                <i class="fas fa-map-marker-alt text-emerald-500 w-6"></i>
                                                <span class="truncate"><?= htmlspecialchars($event['lokasi']) ?></span>
                                            </div>
                                        </div>

                                        <!-- Action Buttons Grid (Updated: 6 buttons directly) -->
                                        <div class="grid grid-cols-3 gap-2 border-t border-gray-100 pt-4">
                                            <!-- 1. Edit -->
                                            <a href="form_event.php?id=<?= $event['id'] ?>"
                                                class="flex flex-col items-center justify-center p-2 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 transition">
                                                <i class="fas fa-pencil-alt text-lg mb-1"></i>
                                                <span class="text-[10px] font-medium">Edit</span>
                                            </a>
                                            <!-- 2. Form -->
                                            <a href="kelola_form.php?id=<?= $event['id'] ?>"
                                                class="flex flex-col items-center justify-center p-2 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                                                <i class="fas fa-list-alt text-lg mb-1"></i>
                                                <span class="text-[10px] font-medium">Form</span>
                                            </a>
                                            <!-- 3. Kuesioner -->
                                            <a href="kelola_kuesioner.php?id=<?= $event['id'] ?>"
                                                class="flex flex-col items-center justify-center p-2 rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100 transition">
                                                <i class="fas fa-poll-h text-lg mb-1"></i>
                                                <span class="text-[10px] font-medium">Kuesioner</span>
                                            </a>
                                            <!-- 4. Hasil -->
                                            <a href="hasil_kuesioner.php?id=<?= $event['id'] ?>"
                                                class="flex flex-col items-center justify-center p-2 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                                                <i class="fas fa-chart-bar text-lg mb-1"></i>
                                                <span class="text-[10px] font-medium">Hasil</span>
                                            </a>
                                            <!-- 5. Materi -->
                                            <button
                                                onclick="openModalMateri(<?= $event['id'] ?>, '<?= htmlspecialchars($event['judul'], ENT_QUOTES) ?>')"
                                                class="flex flex-col items-center justify-center p-2 rounded-lg bg-orange-50 text-orange-700 hover:bg-orange-100 transition">
                                                <i class="fas fa-file-upload text-lg mb-1"></i>
                                                <span class="text-[10px] font-medium">Materi</span>
                                            </button>
                                            <!-- 6. Hapus -->
                                            <button
                                                onclick="konfirmasiHapus(<?= $event['id'] ?>, '<?= htmlspecialchars($event['judul'], ENT_QUOTES) ?>')"
                                                class="flex flex-col items-center justify-center p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition">
                                                <i class="fas fa-trash-alt text-lg mb-1"></i>
                                                <span class="text-[10px] font-medium">Hapus</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                        <?php endforeach; ?>
                <?php else: ?>
                        <div class="text-center p-8 bg-white rounded-xl shadow-sm border border-gray-100">
                            <p class="text-gray-500">Belum ada data.</p>
                        </div>
                <?php endif; ?>
            </div>

            <!-- Pagination (Tetap menggunakan reload karena limitasi struktur) -->
            <?php if ($total_pages > 1): ?>
                    <div class="flex justify-center mt-10">
                        <nav class="flex gap-2 p-2 bg-white rounded-xl shadow-sm border border-gray-100">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="w-10 h-10 flex items-center justify-center rounded-lg text-sm font-bold transition-all duration-300
                                <?= $i == $page
                                    ? 'bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-md transform scale-105'
                                    : 'text-gray-500 hover:bg-emerald-50 hover:text-emerald-600' ?>">
                                        <?= $i ?>
                                    </a>
                            <?php endfor; ?>
                        </nav>
                    </div>
            <?php endif; ?>

        </div> <!-- End Data Container -->

    </div>
</div>

<!-- Modal Materi (Modernized) -->
<div id="modalMateri"
    class="fixed inset-0 z-[60] hidden bg-emerald-900/40 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 opacity-0 transition-all duration-300"
        id="modalMateriContent">
        <div class="bg-emerald-900 p-6 flex justify-between items-center text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-amber-400 rounded-full opacity-20 blur-xl">
            </div>
            <div>
                <h3 class="text-xl font-bold">Upload Materi</h3>
                <p class="text-emerald-200 text-sm">Bagikan bahan belajar untuk peserta</p>
            </div>
            <button onclick="closeModalMateri()"
                class="relative z-10 w-8 h-8 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="proses_upload_materi.php" method="POST" enctype="multipart/form-data" class="p-8 space-y-5">
            <input type="hidden" name="workshop_id" id="input_materi_workshop_id">

            <div class="bg-amber-50 p-4 rounded-xl border border-amber-100 flex items-start gap-3">
                <i class="fas fa-info-circle text-amber-500 mt-1"></i>
                <div>
                    <p class="text-xs text-amber-600 font-semibold uppercase tracking-wide">Event Terpilih</p>
                    <p id="label_event_judul" class="font-bold text-gray-800 text-sm"></p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Judul Materi</label>
                <input type="text" name="judul_materi" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none bg-gray-50 focus:bg-white"
                    placeholder="Misal: Slide Presentasi Sesi 1">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Deskripsi</label>
                <textarea name="deskripsi" rows="2"
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow outline-none bg-gray-50 focus:bg-white"
                    placeholder="Deskripsi singkat materi..."></textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">File (PDF/PPT)</label>
                <div
                    class="relative border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-emerald-400 transition-colors bg-gray-50">
                    <input type="file" name="file_materi" accept=".pdf,.ppt,.pptx" required
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                    <p class="text-sm text-gray-500 font-medium">Klik untuk upload atau drag file kesini</p>
                    <p class="text-xs text-gray-400 mt-1">Max 10MB</p>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit"
                    class="w-full bg-gradient-to-r from-emerald-600 to-emerald-800 hover:from-emerald-700 hover:to-emerald-900 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-emerald-200 transition-all transform hover:-translate-y-0.5">
                    Upload Materi
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // --- 1. Logic Modal Materi (Modern Animation) ---
    const modalMateri = document.getElementById('modalMateri');
    const modalContent = document.getElementById('modalMateriContent');

    function openModalMateri(id, judul) {
        document.getElementById('input_materi_workshop_id').value = id;
        document.getElementById('label_event_judul').innerText = judul;

        modalMateri.classList.remove('hidden');
        // Force reflow
        void modalMateri.offsetWidth;

        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');

        // Tutup dropdown jika ada yang terbuka
        document.querySelectorAll('[id^="dropdown-menu-"]').forEach(el => el.classList.add('hidden'));
    }

    function closeModalMateri() {
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modalMateri.classList.add('hidden');
        }, 300);
    }

    // --- 2. Logic Dropdown Menu ---
    function toggleDropdown(id) {
        const menu = document.getElementById(`dropdown-menu-${id}`);
        const isHidden = menu.classList.contains('hidden');

        // Tutup semua dulu
        document.querySelectorAll('[id^="dropdown-menu-"]').forEach(el => el.classList.add('hidden'));

        if (isHidden) {
            menu.classList.remove('hidden');
        }
    }

    // Klik di luar menutup dropdown
    document.addEventListener('click', function (event) {
        if (!event.target.closest('.dropdown-container')) {
            document.querySelectorAll('[id^="dropdown-menu-"]').forEach(el => el.classList.add('hidden'));
        }
    });

    // --- 3. Logic Hapus (SweetAlert) ---
    function konfirmasiHapus(id, judul) {
        Swal.fire({
            title: 'Hapus Event?',
            text: `"${judul}" akan dihapus permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#059669', // Emerald 600
            cancelButtonColor: '#d1d5db', // Gray 300
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-lg px-6',
                cancelButton: 'rounded-lg px-6 text-gray-700'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'proses_hapus_event.php';
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'id';
                inputId.value = id;
                form.appendChild(inputId);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // --- 4. Logic Live Search (AJAX Refresh) ---
    const searchInput = document.getElementById('liveSearchInput');
    const loadingIcon = document.getElementById('loadingIcon');
    const dataContainer = document.getElementById('dataContainer');
    let timeout = null;

    searchInput.addEventListener('input', function () {
        clearTimeout(timeout);
        const query = this.value;
        loadingIcon.classList.remove('hidden');

        // Debounce (tunggu user selesai mengetik 500ms)
        timeout = setTimeout(() => {
            const url = new URL(window.location.href);
            url.searchParams.set('search', query);
            url.searchParams.set('page', 1); // Reset ke halaman 1 saat search

            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.getElementById('dataContainer').innerHTML;

                    // Efek Fade Out/In sederhana
                    dataContainer.style.opacity = '0.5';
                    setTimeout(() => {
                        dataContainer.innerHTML = newContent;
                        dataContainer.style.opacity = '1';
                        loadingIcon.classList.add('hidden');

                        // Update URL browser tanpa reload (agar kalau di-refresh tetap di hasil pencarian)
                        window.history.pushState({}, '', url);
                    }, 200);
                })
                .catch(err => {
                    console.error('Error fetching data:', err);
                    loadingIcon.classList.add('hidden');
                });
        }, 500);
    });
</script>

<?php require_once BASE_PATH . '/admin/templates/footer.php'; ?>