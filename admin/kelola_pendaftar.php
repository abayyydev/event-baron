<?php
if (!defined('BASE_PATH')) {
    // Mendefinisikan root folder (naik satu level dari folder admin)
    define('BASE_PATH', dirname(__DIR__));
}
$page_title = 'Data Pendaftar';
$current_page = 'data_pendaftar';
require_once BASE_PATH . '/admin/templates/header.php';
require_once BASE_PATH . '/core/koneksi.php';

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
                                <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= htmlspecialchars($event['judul']) ?>
                                </h3>
                                <div class="space-y-2">
                                    <div class="flex items-center text-sm text-slate-600">
                                        <i
                                            class="fas fa-calendar-alt mr-2 text-amber-500"></i><?= date('d M Y, H:i', strtotime($event['tanggal_waktu'])) ?>
                                    </div>
                                    <div class="flex items-center space-x-4 text-sm">
                                        <span class="flex items-center text-slate-600"><i
                                                class="fas fa-users mr-1 text-blue-500"></i><?= $event['jumlah_pendaftar'] ?>
                                            Pendaftar</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 pt-4 border-t border-slate-200">
                            <a href="lihat_detail_pendaftar?event_id=<?= $event['id'] ?>"
                                class="w-full bg-gradient-to-r from-amber-500 to-orange-500 text-white font-semibold py-2 px-6 rounded-xl text-center">
                                <i class="fas fa-eye mr-2"></i> Lihat Detail
                            </a>
                            <button
                                onclick="openModalPeserta(<?= $event['id'] ?>, '<?= htmlspecialchars($event['judul'], ENT_QUOTES) ?>')"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl shadow-md transition flex items-center justify-center gap-2">
                                <i class="fas fa-user-plus"></i> Tambah Santri Internal
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="hidden lg:block bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
            <div class="bg-gradient-to-r from-slate-800 to-slate-900 text-white px-6 py-4">
                <div class="grid grid-cols-12 gap-4 items-center">
                    <div class="col-span-4"><span class="font-semibold">Event</span></div>
                    <div class="col-span-2"><span class="font-semibold">Tanggal</span></div>
                    <div class="col-span-2 text-center"><span class="font-semibold">Pendaftar</span></div>
                    <div class="col-span-2 text-center"><span class="font-semibold">Kehadiran</span></div>
                    <div class="col-span-2 text-center"><span class="font-semibold">Aksi</span></div>
                </div>
            </div>

            <div class="divide-y divide-slate-200">
                <?php if (count($events) > 0): ?>
                    <?php foreach ($events as $event): ?>
                        <div class="px-6 py-4 hover:bg-slate-50/50 transition-colors group">
                            <div class="grid grid-cols-12 gap-4 items-center">
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
                                            <p class="text-sm text-slate-500 truncate"><?= htmlspecialchars($event['lokasi']) ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-span-2">
                                    <div class="text-sm text-slate-700">
                                        <div class="font-medium"><?= date('d M Y', strtotime($event['tanggal_waktu'])) ?></div>
                                        <div class="text-slate-500"><?= date('H:i', strtotime($event['tanggal_waktu'])) ?> WIB
                                        </div>
                                    </div>
                                </div>

                                <div class="col-span-2 text-center">
                                    <div class="flex flex-col items-center">
                                        <span
                                            class="text-lg font-semibold text-slate-800"><?= $event['jumlah_pendaftar'] ?></span>
                                        <span class="text-xs text-slate-500">Pendaftar</span>
                                    </div>
                                </div>

                                <div class="col-span-2 text-center">
                                    <div class="flex flex-col items-center">
                                        <span
                                            class="text-lg font-semibold text-emerald-600"><?= $event['jumlah_hadir'] ?></span>
                                    </div>
                                </div>

                                <div class="col-span-2">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="lihat_detail_pendaftar?event_id=<?= $event['id'] ?>"
                                            class="w-12 h-12 bg-amber-500 hover:bg-amber-600 text-white rounded-xl flex items-center justify-center transition-all duration-300 shadow-md"
                                            title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($event['jumlah_pendaftar'] > 0): ?>
                                            <a href="export_pendaftar.php?event_id=<?= $event['id'] ?>"
                                                class="w-12 h-12 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl flex items-center justify-center transition-all duration-300 shadow-md"
                                                title="Export">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        <?php endif; ?>
                                        <button
                                            onclick="openModalPeserta(<?= $event['id'] ?>, '<?= htmlspecialchars($event['judul'], ENT_QUOTES) ?>')"
                                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded-lg shadow-md transition flex items-center gap-2 text-sm"
                                            title="Tambah Peserta">
                                            <i class="fas fa-user-plus"></i> <span class="hidden xl:inline"></span>
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
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="mt-8">
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="modalPilihPeserta"
    class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity duration-300">
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[85vh] animate-fade-in-up">

        <div
            class="p-5 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-blue-600 to-blue-700 text-white">
            <div>
                <h3 class="text-lg font-bold" id="modalTitle">Pilih Santri</h3>
                <p class="text-sm text-blue-100 opacity-90">Centang santri untuk ditambahkan ke event ini.</p>
            </div>
            <button onclick="closeModalPeserta()"
                class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-2 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="flex-1 overflow-hidden flex flex-col bg-white">
            <form action="proses_tambah_peserta_internal.php" method="POST" id="formInternal"
                class="flex flex-col h-full">
                <input type="hidden" name="workshop_id" id="inputWorkshopId">

                <div class="p-4 border-b border-gray-100 bg-gray-50">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        <input type="text" id="searchSantri" onkeyup="filterSantri()"
                            placeholder="Cari nama atau email santri..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-100 text-gray-600 text-xs uppercase sticky top-0 z-10">
                            <tr>
                                <th class="p-4 w-12 text-center border-b">
                                    <input type="checkbox" id="checkAll"
                                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                </th>
                                <th class="p-4 border-b">Nama Santri</th>
                                <th class="p-4 border-b">Kontak</th>
                                <th class="p-4 border-b text-center">JK</th>
                            </tr>
                        </thead>
                        <tbody id="listSantriBody" class="divide-y divide-gray-50">
                        </tbody>
                    </table>

                    <div id="loadingIndicator" class="hidden flex flex-col items-center justify-center py-10">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-2"></div>
                        <span class="text-gray-500 text-sm">Memuat data santri...</span>
                    </div>
                </div>

                <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-between items-center z-20">
                    <span class="text-sm font-medium text-blue-700 bg-blue-50 px-3 py-1 rounded-full"
                        id="selectedCount">
                        0 terpilih
                    </span>
                    <div class="flex gap-3">
                        <button type="button" onclick="closeModalPeserta()"
                            class="px-5 py-2.5 text-gray-600 hover:bg-gray-200 rounded-lg transition text-sm font-medium">
                            Batal
                        </button>
                        <button type="submit" id="btnSimpan" disabled
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5 text-sm">
                            <i class="fas fa-save mr-2"></i> Simpan Peserta
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('modalPilihPeserta');
    const listBody = document.getElementById('listSantriBody');
    const loading = document.getElementById('loadingIndicator');
    const inputId = document.getElementById('inputWorkshopId');
    const titleLabel = document.getElementById('modalTitle');
    const selectedCountLabel = document.getElementById('selectedCount');
    const btnSimpan = document.getElementById('btnSimpan');

    // Fungsi Utama: Membuka Modal & Load Data
    function openModalPeserta(workshopId, judulEvent) {
        // 1. Reset UI
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        inputId.value = workshopId;
        titleLabel.innerHTML = `Tambah Peserta: <span class="font-normal opacity-90 text-sm block mt-1">${judulEvent}</span>`;
        listBody.innerHTML = '';
        document.getElementById('checkAll').checked = false;
        updateCount();

        // 2. Tampilkan Loading
        loading.classList.remove('hidden');

        // 3. Fetch Data via AJAX
        fetch(`ajax_get_calon_peserta.php?event_id=${workshopId}`)
            .then(response => response.text())
            .then(html => {
                loading.classList.add('hidden');
                listBody.innerHTML = html;
            })
            .catch(err => {
                loading.classList.add('hidden');
                listBody.innerHTML = '<tr><td colspan="4" class="text-center text-red-500 p-4">Gagal memuat data. Periksa koneksi.</td></tr>';
                console.error(err);
            });
    }

    function closeModalPeserta() {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // UX: Klik baris untuk mencentang
    function toggleRow(row) {
        const checkbox = row.querySelector('.santri-checkbox');
        checkbox.checked = !checkbox.checked;

        // Efek visual seleksi
        if (checkbox.checked) {
            row.classList.add('bg-blue-50');
        } else {
            row.classList.remove('bg-blue-50');
        }
        updateCount();
    }

    // Check All Feature
    document.getElementById('checkAll').addEventListener('change', function () {
        const isChecked = this.checked;
        const visibleCheckboxes = document.querySelectorAll('#listSantriBody tr:not([style*="display: none"]) .santri-checkbox');

        visibleCheckboxes.forEach(box => {
            box.checked = isChecked;
            const row = box.closest('tr');
            if (isChecked) row.classList.add('bg-blue-50');
            else row.classList.remove('bg-blue-50');
        });
        updateCount();
    });

    // Update Counter & Enable Tombol Simpan
    function updateCount() {
        const count = document.querySelectorAll('.santri-checkbox:checked').length;
        selectedCountLabel.innerText = count + " santri terpilih";

        if (count > 0) {
            btnSimpan.disabled = false;
            btnSimpan.classList.remove('opacity-50');
        } else {
            btnSimpan.disabled = true;
            btnSimpan.classList.add('opacity-50');
        }
    }

    // Filter Pencarian di Client Side
    function filterSantri() {
        const input = document.getElementById('searchSantri').value.toLowerCase();
        const rows = document.querySelectorAll('#listSantriBody tr');

        rows.forEach(row => {
            const target = row.querySelector('.search-target');
            if (target) {
                const text = target.innerText.toLowerCase();
                if (text.includes(input)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            }
        });
    }

    // Tutup modal jika klik di luar area
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeModalPeserta();
        }
    });
</script>

<style>
    /* Custom Scrollbar untuk Modal */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.3s ease-out;
    }
</style>

<?php require_once BASE_PATH . '/admin/templates/footer.php'; ?>