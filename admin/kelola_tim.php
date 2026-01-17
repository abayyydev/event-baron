<?php
// Pastikan BASE_PATH didefinisikan jika belum
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

$page_title = 'Kelola Tim';
$current_page = 'kelola_tim';
require_once BASE_PATH . '/admin/templates/header.php';
require_once BASE_PATH . '/core/koneksi.php';

$owner_id = $_SESSION['user_id'];

// Ambil daftar anggota tim
$stmt = $pdo->prepare("SELECT id, nama_lengkap, email, no_whatsapp, role, created_at FROM users WHERE owner_id = ? ORDER BY created_at DESC");
$stmt->execute([$owner_id]);
$anggota_tim = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung statistik
$total_anggota = count($anggota_tim);
$anggota_aktif = $total_anggota;

// Handling Notifikasi PHP (Redirect dari Tambah)
$alert_script = "";
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'sukses') {
        $alert_script = "
            Swal.fire({
                title: 'Berhasil!',
                text: 'Anggota tim berhasil ditambahkan.',
                icon: 'success',
                confirmButtonColor: '#10b981',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.history.replaceState(null, null, window.location.pathname);
            });
        ";
    } elseif ($_GET['status'] == 'gagal') {
        $msg = $_GET['msg'] ?? 'Terjadi kesalahan.';
        $alert_script = "
            Swal.fire({
                title: 'Gagal!',
                text: '" . htmlspecialchars($msg) . "',
                icon: 'error',
                confirmButtonColor: '#ef4444'
            }).then(() => {
                window.history.replaceState(null, null, window.location.pathname);
            });
        ";
    }
}
?>

<div class="min-h-screen bg-gray-50 font-sans pb-32">

    <!-- Hero Header Section -->
    <div class="bg-emerald-900 pb-24 pt-10 px-4 rounded-b-[3rem] shadow-xl relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-emerald-800 rounded-full opacity-50 blur-3xl">
        </div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-amber-500 rounded-full opacity-20 blur-2xl">
        </div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <span
                            class="text-emerald-200 text-xs font-bold uppercase tracking-widest border border-emerald-700/50 px-2 py-1 rounded-md">Manajemen
                            User</span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-white mt-2 leading-tight">
                        Kelola Tim
                    </h1>
                    <p class="text-emerald-100/90 mt-2 text-sm md:text-base max-w-xl">
                        Atur anggota tim yang memiliki akses untuk mengelola event bersama Anda.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 w-full md:w-auto">
                    <div
                        class="bg-white/10 backdrop-blur-md border border-white/10 rounded-xl p-4 flex flex-col items-center justify-center min-w-[120px]">
                        <span class="text-3xl font-bold text-white"><?= $total_anggota ?></span>
                        <span class="text-[10px] text-emerald-200 uppercase font-bold tracking-wider mt-1">Total
                            Anggota</span>
                    </div>
                    <div
                        class="bg-amber-500/20 backdrop-blur-md border border-amber-500/30 rounded-xl p-4 flex flex-col items-center justify-center min-w-[120px]">
                        <span class="text-3xl font-bold text-amber-400"><?= $anggota_aktif ?></span>
                        <span class="text-[10px] text-amber-200 uppercase font-bold tracking-wider mt-1">Aktif</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto px-4 -mt-12 relative z-20">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <!-- LEFT COLUMN: Form Tambah (4 Cols) -->
            <div class="lg:col-span-4 space-y-6 order-2 lg:order-1">
                <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-6 md:p-8 sticky top-6">
                    <div class="flex items-center gap-4 mb-6 border-b border-gray-100 pb-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg shadow-sm">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">Tambah Anggota</h2>
                            <p class="text-xs text-gray-500">Undang staf baru ke dashboard</p>
                        </div>
                    </div>

                    <!-- UPDATE ACTION URL KE PROSES_ANGGOTA.PHP -->
                    <form id="addMemberForm" action="proses_anggota.php" method="POST" class="space-y-5">
                        <input type="hidden" name="action" value="tambah">

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Nama
                                Lengkap</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i
                                        class="fas fa-user"></i></span>
                                <input type="text" name="nama_lengkap"
                                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all outline-none text-sm"
                                    placeholder="Contoh: Budi Santoso" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Email</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i
                                        class="fas fa-envelope"></i></span>
                                <input type="email" name="email"
                                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all outline-none text-sm"
                                    placeholder="email@tim.com" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">No.
                                WhatsApp</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i
                                        class="fas fa-phone"></i></span>
                                <input type="number" name="no_whatsapp"
                                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all outline-none text-sm"
                                    placeholder="08xxxxxxxx" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Password</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i
                                        class="fas fa-lock"></i></span>
                                <input type="password" name="password" id="password"
                                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all outline-none text-sm"
                                    placeholder="Min. 8 Karakter" required minlength="8">
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1 ml-1">*Password default untuk login awal.</p>
                        </div>

                        <button type="submit"
                            class="w-full bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-emerald-500/30 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2 mt-4">
                            <i class="fas fa-plus-circle"></i> Tambahkan
                        </button>
                    </form>

                    <div class="mt-8 bg-amber-50 p-4 rounded-xl border border-amber-100">
                        <h4 class="text-xs font-bold text-amber-800 mb-2 flex items-center uppercase tracking-wider">
                            <i class="fas fa-lightbulb mr-2 text-amber-500"></i> Tips Keamanan
                        </h4>
                        <ul class="text-xs text-amber-800/80 space-y-1.5 list-disc list-inside">
                            <li>Gunakan email & WA aktif.</li>
                            <li>Password sebaiknya kombinasi huruf & angka.</li>
                            <li>Hapus akses anggota yang sudah tidak aktif.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Daftar Anggota (8 Cols) -->
            <div class="lg:col-span-8 order-1 lg:order-2">
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden min-h-[500px]">

                    <div
                        class="bg-gradient-to-r from-emerald-900 to-emerald-950 px-6 py-5 border-b border-emerald-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <h2 class="text-lg font-bold text-white flex items-center gap-3">
                            <span
                                class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-emerald-300">
                                <i class="fas fa-users"></i>
                            </span>
                            Daftar Anggota Tim
                        </h2>
                    </div>

                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-gray-50/50 text-gray-500 text-xs font-bold uppercase tracking-wider border-b border-gray-100">
                                    <th class="px-6 py-4">Anggota</th>
                                    <th class="px-6 py-4">Kontak</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php if (count($anggota_tim) > 0): ?>
                                    <?php foreach ($anggota_tim as $anggota): ?>
                                        <tr class="hover:bg-emerald-50/30 transition-colors group">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div
                                                        class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-100 to-emerald-200 text-emerald-700 flex items-center justify-center font-bold shadow-sm">
                                                        <?= strtoupper(substr($anggota['nama_lengkap'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <p
                                                            class="font-bold text-gray-800 text-sm group-hover:text-emerald-700 transition-colors">
                                                            <?= htmlspecialchars($anggota['nama_lengkap']) ?></p>
                                                        <p class="text-[10px] text-gray-400">Bergabung:
                                                            <?= date('d M Y', strtotime($anggota['created_at'])) ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-600 space-y-1">
                                                    <div class="flex items-center gap-2">
                                                        <i class="fas fa-envelope text-gray-300 w-4"></i>
                                                        <?= htmlspecialchars($anggota['email']) ?>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <i class="fas fa-phone text-gray-300 w-4"></i>
                                                        <?= htmlspecialchars($anggota['no_whatsapp'] ?? '-') ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                                    Aktif
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <button
                                                        class="edit-anggota-btn w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition flex items-center justify-center border border-blue-100"
                                                        data-id="<?= $anggota['id'] ?>"
                                                        data-nama="<?= htmlspecialchars($anggota['nama_lengkap']) ?>"
                                                        data-email="<?= htmlspecialchars($anggota['email']) ?>"
                                                        data-wa="<?= htmlspecialchars($anggota['no_whatsapp']) ?>" title="Edit">
                                                        <i class="fas fa-pencil-alt text-xs"></i>
                                                    </button>
                                                    <button
                                                        class="hapus-anggota-btn w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition flex items-center justify-center border border-red-100"
                                                        data-id="<?= $anggota['id'] ?>"
                                                        data-nama="<?= htmlspecialchars($anggota['nama_lengkap']) ?>"
                                                        title="Hapus">
                                                        <i class="fas fa-trash-alt text-xs"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-16 text-center text-gray-500">
                                            Belum ada anggota tim.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile View -->
                    <div class="md:hidden p-4 space-y-4">
                        <?php if (count($anggota_tim) > 0): ?>
                            <?php foreach ($anggota_tim as $anggota): ?>
                                <div
                                    class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-start gap-4 relative overflow-hidden">
                                    <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
                                    <div
                                        class="w-12 h-12 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-600 font-bold shadow-sm flex-shrink-0">
                                        <?= strtoupper(substr($anggota['nama_lengkap'], 0, 1)) ?>
                                    </div>
                                    <div class="flex-grow min-w-0">
                                        <h3 class="font-bold text-gray-800 text-sm truncate">
                                            <?= htmlspecialchars($anggota['nama_lengkap']) ?></h3>
                                        <p class="text-xs text-gray-500 truncate mt-0.5"><i class="fas fa-envelope mr-1"></i>
                                            <?= htmlspecialchars($anggota['email']) ?></p>
                                        <p class="text-xs text-gray-500 truncate mt-0.5"><i class="fas fa-phone mr-1"></i>
                                            <?= htmlspecialchars($anggota['no_whatsapp'] ?? '-') ?></p>

                                        <div class="flex items-center justify-between mt-3">
                                            <span
                                                class="text-[10px] text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded font-bold uppercase tracking-wider">Aktif</span>
                                            <div class="flex gap-2">
                                                <button
                                                    class="edit-anggota-btn text-blue-600 bg-blue-50 p-1.5 rounded-lg text-xs"
                                                    data-id="<?= $anggota['id'] ?>"
                                                    data-nama="<?= htmlspecialchars($anggota['nama_lengkap']) ?>"
                                                    data-email="<?= htmlspecialchars($anggota['email']) ?>"
                                                    data-wa="<?= htmlspecialchars($anggota['no_whatsapp']) ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button
                                                    class="hapus-anggota-btn text-red-600 bg-red-50 p-1.5 rounded-lg text-xs"
                                                    data-id="<?= $anggota['id'] ?>"
                                                    data-nama="<?= htmlspecialchars($anggota['nama_lengkap']) ?>">
                                                    <i class="fas fa-trash-alt"></i> Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Tampilkan notifikasi PHP saat halaman load
    <?php if ($alert_script)
        echo $alert_script; ?>

    // --- 1. Fungsi Hapus Anggota (AJAX) ---
    document.querySelectorAll('.hapus-anggota-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const nama = this.dataset.nama;

            Swal.fire({
                title: 'Hapus Anggota?',
                html: `Hapus <strong>${nama}</strong> dari tim?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#d1d5db',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                customClass: { popup: 'rounded-3xl' }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading(), showConfirmButton: false });

                    // UPDATE URL FETCH KE PROSES_ANGGOTA.PHP?ACTION=HAPUS
                    fetch(`proses_anggota.php?action=hapus&id=${id}`, {
                        method: 'POST'
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire('Terhapus!', data.message, 'success').then(() => location.reload());
                            } else {
                                Swal.fire('Gagal!', data.message, 'error');
                            }
                        })
                        .catch(err => Swal.fire('Error', 'Gagal koneksi server', 'error'));
                }
            });
        });
    });

    // --- 2. Fungsi Edit Anggota (AJAX) ---
    document.querySelectorAll('.edit-anggota-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const nama = this.dataset.nama;
            const email = this.dataset.email;
            const wa = this.dataset.wa;

            Swal.fire({
                title: 'Edit Anggota',
                html: `
                    <div class="text-left space-y-3">
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase">Nama Lengkap</label>
                            <input type="text" id="editNama" value="${nama}" class="w-full px-4 py-2 border border-gray-300 rounded-lg outline-none focus:border-emerald-500 mt-1">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase">Email</label>
                            <input type="email" id="editEmail" value="${email}" class="w-full px-4 py-2 border border-gray-300 rounded-lg outline-none focus:border-emerald-500 mt-1">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase">WhatsApp</label>
                            <input type="number" id="editWA" value="${wa}" class="w-full px-4 py-2 border border-gray-200 rounded-lg outline-none focus:border-emerald-500 mt-1">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                confirmButtonColor: '#059669',
                cancelButtonText: 'Batal',
                customClass: { popup: 'rounded-3xl' },
                preConfirm: () => {
                    const newNama = document.getElementById('editNama').value;
                    const newEmail = document.getElementById('editEmail').value;
                    const newWA = document.getElementById('editWA').value;
                    if (!newNama || !newEmail || !newWA) {
                        Swal.showValidationMessage('Semua field harus diisi');
                    }
                    return { id: id, nama: newNama, email: newEmail, no_whatsapp: newWA, action: 'edit' }; // Tambah action: edit
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menyimpan...', didOpen: () => Swal.showLoading(), showConfirmButton: false });

                    // UPDATE FETCH URL KE PROSES_ANGGOTA.PHP
                    fetch('proses_anggota.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(result.value)
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
                            } else {
                                Swal.fire('Gagal!', data.message, 'error');
                            }
                        })
                        .catch(err => Swal.fire('Error', 'Gagal koneksi server', 'error'));
                }
            });
        });
    });

    // Validasi Form Tambah (Password)
    const addForm = document.getElementById('addMemberForm');
    if (addForm) {
        addForm.addEventListener('submit', function (e) {
            const pwd = document.getElementById('password').value;
            if (pwd.length < 8) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Password Lemah',
                    text: 'Password minimal 8 karakter.',
                    confirmButtonColor: '#f59e0b'
                });
            }
        });
    }

</script>

<?php require_once BASE_PATH . '/admin/templates/footer.php'; ?>