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

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8">
            <div class="mb-6 lg:mb-0">
                <h1 class="text-3xl font-bold text-slate-800 mb-2">Kelola Event</h1>
                <p class="text-slate-600">Kelola event, pendaftaran, dan kuesioner Anda.</p>
            </div>
            <a href="form_event.php"
                class="bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center">
                <i class="fas fa-plus mr-3"></i> Tambah Event
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6 mb-8">
            <div class="flex flex-col lg:flex-row gap-4 items-center justify-between">
                <div class="flex-grow relative w-full">
                    <form method="GET" class="relative">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                            placeholder="Cari event berdasarkan judul..."
                            class="w-full pl-12 pr-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition-all duration-300 bg-slate-50/50">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-slate-400"></i>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden min-h-[400px]">
            <div class="hidden lg:block overflow-visible">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-800 text-white">
                        <tr>
                            <th class="px-6 py-4 font-semibold rounded-tl-lg">Event</th>
                            <th class="px-6 py-4 font-semibold">Jadwal & Lokasi</th>
                            <th class="px-6 py-4 font-semibold text-center">Visibilitas</th>
                            <th class="px-6 py-4 font-semibold text-center">Tipe</th>
                            <th class="px-6 py-4 font-semibold text-center rounded-tr-lg">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (count($events) > 0): ?>
                            <?php foreach ($events as $event): ?>
                                <tr class="hover:bg-slate-50 transition-colors group">

                                    <td class="px-6 py-4 align-top">
                                        <div class="flex gap-4">
                                            <?php if ($event['poster']): ?>
                                                <img src="../assets/img/posters/<?= htmlspecialchars($event['poster']) ?>"
                                                    class="h-16 w-24 object-cover rounded-lg shadow-sm border border-slate-200 flex-shrink-0">
                                            <?php else: ?>
                                                <div
                                                    class="h-16 w-24 bg-slate-100 rounded-lg flex items-center justify-center border border-slate-200 flex-shrink-0 text-slate-400">
                                                    <i class="fas fa-image text-xl"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <h3
                                                    class="font-bold text-slate-800 text-base mb-1 group-hover:text-amber-600 transition-colors">
                                                    <?= htmlspecialchars($event['judul']) ?>
                                                </h3>
                                                <p class="text-xs text-slate-500 line-clamp-2">
                                                    <?= htmlspecialchars(substr($event['deskripsi'] ?? '', 0, 80)) ?>...
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 align-top">
                                        <div class="space-y-1">
                                            <div class="flex items-center text-sm text-slate-700">
                                                <i class="fas fa-calendar-alt w-5 text-amber-500"></i>
                                                <span
                                                    class="font-medium"><?= date('d M Y', strtotime($event['tanggal_waktu'])) ?></span>
                                            </div>
                                            <div class="flex items-center text-xs text-slate-500 ml-5">
                                                <?= date('H:i', strtotime($event['tanggal_waktu'])) ?> WIB
                                            </div>
                                            <div class="flex items-center text-sm text-slate-600 mt-2">
                                                <i class="fas fa-map-marker-alt w-5 text-blue-500"></i>
                                                <span class="truncate max-w-[150px]"
                                                    title="<?= htmlspecialchars($event['lokasi']) ?>">
                                                    <?= htmlspecialchars($event['lokasi']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 align-middle text-center">
                                        <?php
                                        // Fallback jika kolom belum ada di DB (biar gak error fatal)
                                        $visibilitas = $event['visibilitas'] ?? 'public';
                                        ?>
                                        <?php if ($visibilitas === 'public'): ?>
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 border border-indigo-200">
                                                <i class="fas fa-globe mr-1.5"></i> Public
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-300">
                                                <i class="fas fa-lock mr-1.5"></i> Internal
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-6 py-4 align-middle text-center">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium 
                                            <?= $event['tipe_event'] === 'berbayar' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' ?>">
                                            <?= $event['tipe_event'] === 'berbayar' ? 'Rp Berbayar' : 'Gratis' ?>
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 align-middle text-center relative">
                                        <div class="relative inline-block text-left">
                                            <button type="button" onclick="toggleDropdown(<?= $event['id'] ?>)"
                                                class="inline-flex justify-center w-full rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                                                Aksi <i class="fas fa-chevron-down ml-2 -mr-1 mt-0.5"></i>
                                            </button>

                                            <div id="dropdown-menu-<?= $event['id'] ?>"
                                                class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-xl shadow-2xl bg-white ring-1 ring-black ring-opacity-5 z-50 divide-y divide-gray-100">
                                                <div class="py-1">
                                                    <a href="form_event.php?id=<?= $event['id'] ?>"
                                                        class="group flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700">
                                                        <i
                                                            class="fas fa-pencil-alt mr-3 text-slate-400 group-hover:text-blue-500"></i>
                                                        Edit Event
                                                    </a>
                                                    <a href="kelola_form.php?id=<?= $event['id'] ?>"
                                                        class="group flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-700">
                                                        <i
                                                            class="fas fa-list-alt mr-3 text-slate-400 group-hover:text-emerald-500"></i>
                                                        Atur Form
                                                    </a>
                                                    <a href="kelola_kuesioner.php?id=<?= $event['id'] ?>"
                                                        class="group flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-purple-50 hover:text-purple-700">
                                                        <i
                                                            class="fas fa-poll-h mr-3 text-slate-400 group-hover:text-purple-500"></i>
                                                        Kuesioner
                                                    </a>
                                                </div>
                                                <div class="py-1">
                                                    <button
                                                        onclick="konfirmasiHapus(<?= $event['id'] ?>, '<?= htmlspecialchars($event['judul']) ?>')"
                                                        class="group flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
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
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500 bg-slate-50">
                                    <div class="flex flex-col items-center">
                                        <i class="far fa-folder-open text-4xl mb-3 text-slate-300"></i>
                                        <p>Belum ada event ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="lg:hidden">
                <?php if (count($events) > 0): ?>
                    <?php foreach ($events as $event): ?>
                        <div class="p-4 border-b border-slate-200">
                            <div class="flex gap-4 mb-3">
                                <?php if ($event['poster']): ?>
                                    <img src="../assets/img/posters/<?= htmlspecialchars($event['poster']) ?>"
                                        class="h-20 w-20 object-cover rounded-lg border border-slate-200">
                                <?php else: ?>
                                    <div
                                        class="h-20 w-20 bg-slate-100 rounded-lg flex items-center justify-center border text-slate-400">
                                        <i class="fas fa-image text-2xl"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h3 class="font-bold text-slate-800 text-lg leading-tight mb-1">
                                        <?= htmlspecialchars($event['judul']) ?>
                                    </h3>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        <span
                                            class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border 
                                            <?= ($event['visibilitas'] ?? 'public') === 'public' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-slate-100 text-slate-600 border-slate-300' ?>">
                                            <?= ($event['visibilitas'] ?? 'public') ?>
                                        </span>
                                        <span
                                            class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border 
                                            <?= $event['tipe_event'] === 'berbayar' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' ?>">
                                            <?= $event['tipe_event'] ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="text-sm text-slate-600 space-y-1 mb-4 pl-1">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-alt w-6 text-amber-500 text-center"></i>
                                    <?= date('d M Y, H:i', strtotime($event['tanggal_waktu'])) ?>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-map-marker-alt w-6 text-blue-500 text-center"></i>
                                    <?= htmlspecialchars($event['lokasi']) ?>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <a href="kelola_form.php?id=<?= $event['id'] ?>"
                                    class="btn-mobile bg-emerald-100 text-emerald-700 border-emerald-200">
                                    <i class="fas fa-list-alt"></i> Form
                                </a>
                                <a href="kelola_kuesioner.php?id=<?= $event['id'] ?>"
                                    class="btn-mobile bg-purple-100 text-purple-700 border-purple-200">
                                    <i class="fas fa-poll-h"></i> Kuesioner
                                </a>
                                <a href="form_event.php?id=<?= $event['id'] ?>"
                                    class="btn-mobile bg-blue-100 text-blue-700 border-blue-200">
                                    <i class="fas fa-pencil-alt"></i> Edit
                                </a>
                                <button
                                    onclick="konfirmasiHapus(<?= $event['id'] ?>, '<?= htmlspecialchars($event['judul'], ENT_QUOTES) ?>')"
                                    class="btn-mobile bg-red-100 text-red-700 border-red-200">
                                    <i class="fas fa-trash-alt"></i> Hapus
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-8 text-center text-slate-500">Belum ada event.</div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="flex justify-center mt-8">
                <nav class="flex gap-2">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
                            class="w-10 h-10 flex items-center justify-center rounded-lg text-sm font-medium transition-colors
                           <?= $i == $page ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Logic Dropdown
    function toggleDropdown(id) {
        // Tutup semua dropdown lain
        document.querySelectorAll('[id^="dropdown-menu-"]').forEach(el => {
            if (el.id !== `dropdown-menu-${id}`) el.classList.add('hidden');
        });

        // Toggle dropdown yang diklik
        const menu = document.getElementById(`dropdown-menu-${id}`);
        menu.classList.toggle('hidden');
    }

    // Tutup dropdown jika klik di luar
    window.onclick = function (event) {
        if (!event.target.closest('.relative.inline-block')) {
            document.querySelectorAll('[id^="dropdown-menu-"]').forEach(el => {
                el.classList.add('hidden');
            });
        }
    }

    // Logic Hapus dengan SweetAlert
    function konfirmasiHapus(id, judul) {
        Swal.fire({
            title: 'Hapus Event?',
            text: `Event "${judul}" akan dihapus permanen beserta data pendaftarnya.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Buat form dinamis untuk submit POST request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'proses_hapus_event.php'; // Pastikan file ini ada

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
</script>

<style>
    /* Style tambahan untuk tombol mobile */
    .btn-mobile {
        @apply flex items-center justify-center gap-2 py-2 rounded-lg text-sm font-medium border hover:brightness-95 transition-all;
    }
</style>

<?php require_once BASE_PATH . '/admin/templates/footer.php'; ?>