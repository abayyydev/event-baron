<?php
// admin/kelola_user.php

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
$page_title = 'Data Santri';
$current_page = 'kelola_user';

require_once BASE_PATH . '/admin/templates/header.php';
require_once BASE_PATH . '/core/koneksi.php';

// 1. LOGIKA PENCARIAN & PAGINATION
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter Pencarian
$whereClause = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $whereClause .= " AND (nama_lengkap LIKE ? OR nis LIKE ? OR kelas LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Hitung Total Data
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM santri $whereClause");
$countStmt->execute($params);
$total_records = $countStmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Ambil Data Santri
$sql = "SELECT * FROM santri $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$santri_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="min-h-screen">

    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-emerald-900">Data Master Santri</h1>
            <p class="text-gray-500 mt-1">Kelola data santri, email login, dan wali.</p>
        </div>
        <button onclick="openModalSantri()"
            class="bg-gold-500 hover:bg-gold-600 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center gap-2">
            <i class="fas fa-user-plus"></i> Tambah Santri
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
        <form method="GET" class="flex gap-4">
            <div class="relative flex-grow">
                <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                    class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100 outline-none transition"
                    placeholder="Cari Nama, NIS, Email, atau Kelas...">
            </div>
            <button type="submit"
                class="bg-emerald-700 hover:bg-emerald-800 text-white px-6 rounded-xl font-medium transition shadow-md">
                Cari
            </button>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-emerald-900 text-white">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-sm uppercase tracking-wider">No</th>
                        <th class="px-6 py-4 font-semibold text-sm uppercase tracking-wider">Identitas Santri</th>
                        <th class="px-6 py-4 font-semibold text-sm uppercase tracking-wider">Wali & Kontak</th>
                        <th class="px-6 py-4 font-semibold text-sm uppercase tracking-wider text-center">Kelas</th>
                        <th class="px-6 py-4 font-semibold text-sm uppercase tracking-wider text-center">JK</th>
                        <th class="px-6 py-4 font-semibold text-sm uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (count($santri_list) > 0): ?>
                        <?php foreach ($santri_list as $index => $row):
                            $foto = !empty($row['foto_santri']) ? '../assets/uploads/santri/' . $row['foto_santri'] : '../assets/img/avatar-santri.png';
                            ?>
                            <tr class="hover:bg-emerald-50 transition-colors group">
                                <td class="px-6 py-4 text-gray-500 font-medium w-16">
                                    <?= $offset + $index + 1 ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">

                                        <div>
                                            <p class="font-bold text-gray-800 group-hover:text-emerald-700 transition">
                                                <?= htmlspecialchars($row['nama_lengkap']) ?>
                                            </p>
                                            <div class="flex flex-col gap-0.5 mt-1">
                                                <span class="text-xs text-gray-500 font-mono flex items-center gap-1">
                                                    <i class="fas fa-id-card text-emerald-400"></i>
                                                    <?= htmlspecialchars($row['nis']) ?>
                                                </span>
                                                <span class="text-xs text-gray-500 flex items-center gap-1">
                                                    <i class="fas fa-envelope text-emerald-400"></i>
                                                    <?= htmlspecialchars($row['email'] ?? '-') ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 text-sm text-gray-600">
                                            <i class="fas fa-user-friends text-gold-500 w-4"></i>
                                            <?= htmlspecialchars($row['nama_wali']) ?>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm text-gray-600">
                                            <i class="fab fa-whatsapp text-green-500 w-4"></i>
                                            <?= htmlspecialchars($row['no_hp_wali']) ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="bg-gray-100 text-gray-600 py-1 px-3 rounded-full text-xs font-bold border border-gray-200">
                                        <?= htmlspecialchars($row['kelas']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($row['jenis_kelamin'] == 'Laki-laki'): ?>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">L</span>
                                    <?php else: ?>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800">P</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick='editSantri(<?= json_encode($row) ?>)'
                                            class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 flex items-center justify-center transition"
                                            title="Edit">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <button
                                            onclick="hapusSantri(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama_lengkap']) ?>')"
                                            class="w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center transition"
                                            title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                        <i class="fas fa-users-slash text-gray-400 text-2xl"></i>
                                    </div>
                                    <p class="font-medium">Belum ada data santri.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-between items-center">
                <span class="text-sm text-gray-500">Hal <?= $page ?> dari <?= $total_pages ?></span>
                <div class="flex gap-1">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-bold transition <?= $i == $page ? 'bg-emerald-600 text-white' : 'bg-white border text-gray-600 hover:bg-gray-100' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="modalSantri" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" onclick="closeModalSantri()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div
            class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <div class="bg-emerald-900 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas fa-user-graduate"></i> <span id="modalTitle">Tambah Santri</span>
                </h3>
                <button onclick="closeModalSantri()" class="text-white hover:text-gray-200 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form action="proses_santri.php" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="id" id="santriId">
                <input type="hidden" name="action" id="formAction" value="add">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">NIS</label>
                        <input type="text" name="nis" id="inputNIS" required
                            class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Kelas/Asrama</label>
                        <input type="text" name="kelas" id="inputKelas" required
                            class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="inputNama" required
                        class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email (Untuk Login)</label>
                    <input type="email" name="email" id="inputEmail" required
                        class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none"
                        placeholder="contoh@santri.com">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Wali</label>
                        <input type="text" name="nama_wali" id="inputWali" required
                            class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">WhatsApp Wali</label>
                        <input type="number" name="no_hp_wali" id="inputHP" required
                            class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jenis Kelamin</label>
                    <select name="jenis_kelamin" id="inputJK"
                        class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Password <span id="passHint"
                            class="text-xs text-red-500 font-normal hidden">(Kosongkan jika tidak ingin
                            mengubah)</span></label>
                    <input type="password" name="password" id="inputPassword"
                        class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none"
                        placeholder="******">
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <button type="button" onclick="closeModalSantri()"
                        class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium transition">Batal</button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-gold-500 text-white hover:bg-gold-600 font-bold shadow-lg transition">Simpan
                        Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const modal = document.getElementById('modalSantri');
    const title = document.getElementById('modalTitle');
    const formAction = document.getElementById('formAction');
    const santriId = document.getElementById('santriId');
    const hint = document.getElementById('passHint');

    // Inputs
    const inNIS = document.getElementById('inputNIS');
    const inKelas = document.getElementById('inputKelas');
    const inNama = document.getElementById('inputNama');
    const inEmail = document.getElementById('inputEmail'); // New
    const inWali = document.getElementById('inputWali');
    const inHP = document.getElementById('inputHP');
    const inJK = document.getElementById('inputJK');
    const inPass = document.getElementById('inputPassword');

    function openModalSantri() {
        // Reset Form untuk Mode Tambah
        title.innerText = 'Tambah Santri Baru';
        formAction.value = 'add';
        santriId.value = '';
        hint.classList.add('hidden');
        inPass.required = true; // Password wajib saat tambah

        // Clear Values
        inNIS.value = ''; inKelas.value = ''; inNama.value = ''; inEmail.value = '';
        inWali.value = ''; inHP.value = ''; inJK.value = 'Laki-laki'; inPass.value = '';

        modal.classList.remove('hidden');
    }

    function editSantri(data) {
        // Isi Form untuk Mode Edit
        title.innerText = 'Edit Data Santri';
        formAction.value = 'edit';
        santriId.value = data.id;
        hint.classList.remove('hidden');
        inPass.required = false; // Password opsional saat edit

        // Populate Values
        inNIS.value = data.nis;
        inKelas.value = data.kelas;
        inNama.value = data.nama_lengkap;
        inEmail.value = data.email || '';
        inWali.value = data.nama_wali;
        inHP.value = data.no_hp_wali;
        inJK.value = data.jenis_kelamin;
        inPass.value = '';

        modal.classList.remove('hidden');
    }

    function closeModalSantri() {
        modal.classList.add('hidden');
    }

    function hapusSantri(id, nama) {
        Swal.fire({
            title: 'Hapus Santri?',
            text: `Anda yakin ingin menghapus data santri "${nama}"? Data pendaftaran dan presensi terkait akan ikut terhapus.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Buat form submit dinamis untuk kirim POST request (lebih aman dari GET)
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'proses_santri.php';

                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'id';
                inputId.value = id;

                const inputAction = document.createElement('input');
                inputAction.type = 'hidden';
                inputAction.name = 'action';
                inputAction.value = 'delete';

                form.appendChild(inputId);
                form.appendChild(inputAction);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>

<?php if (isset($_SESSION['success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '<?= $_SESSION['success'] ?>',
            timer: 2000,
            showConfirmButton: false
        });
    </script>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: '<?= $_SESSION['error'] ?>',
        });
    </script>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php require_once BASE_PATH . '/admin/templates/footer.php'; ?>