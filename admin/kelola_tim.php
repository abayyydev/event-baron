<?php
$page_title = 'Kelola Tim';
$current_page = 'kelola_tim';
require_once BASE_PATH . '/admin/templates/header.php';
require_once 'core/koneksi.php';

$owner_id = $_SESSION['user_id'];

// Ambil daftar anggota tim
$stmt = $pdo->prepare("SELECT id, nama_lengkap, email, created_at FROM users WHERE owner_id = ? ORDER BY created_at DESC");
$stmt->execute([$owner_id]);
$anggota_tim = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung statistik
$total_anggota = count($anggota_tim);
$anggota_aktif = $total_anggota; // Asumsi semua aktif untuk sekarang
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
                            <i class="fas fa-users-cog text-white text-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-800">Kelola Tim</h1>
                            <p class="text-slate-600">Kelola anggota tim yang dapat mengelola event bersama Anda</p>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-slate-800"><?= $total_anggota ?></div>
                            <div class="text-sm text-slate-600">Total Anggota</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-slate-800"><?= $anggota_aktif ?></div>
                            <div class="text-sm text-slate-600">Aktif</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-slate-800"><?= count($anggota_tim) ?></div>
                            <div class="text-sm text-slate-600">Event Aktif</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-slate-800"><?= date('M Y') ?></div>
                            <div class="text-sm text-slate-600">Bulan Ini</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Form Tambah Anggota -->
            <div class="xl:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6 sticky top-6">
                    <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center">
                        <i class="fas fa-user-plus text-amber-500 mr-3"></i>
                        Tambah Anggota Baru
                    </h2>

                    <form id="addMemberForm" action="proses_tambah_anggota" method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="tambah_anggota">

                        <div>
                            <label for="nama_lengkap" class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fas fa-user mr-2 text-slate-400"></i>
                                Nama Lengkap
                            </label>
                            <input type="text" name="nama_lengkap" id="nama_lengkap"
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition-all duration-300 bg-white"
                                placeholder="Masukkan nama lengkap" required>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fas fa-envelope mr-2 text-slate-400"></i>
                                Email
                            </label>
                            <input type="email" name="email" id="email"
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition-all duration-300 bg-white"
                                placeholder="email@contoh.com" required>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fas fa-lock mr-2 text-slate-400"></i>
                                Password
                            </label>
                            <input type="password" name="password" id="password"
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition-all duration-300 bg-white"
                                placeholder="Minimal 8 karakter" required minlength="8">
                            <p class="text-xs text-slate-500 mt-1">Password minimal 8 karakter</p>
                        </div>

                        <div>
                            <label for="role" class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fas fa-user-tag mr-2 text-slate-400"></i>
                                Role
                            </label>
                            <select name="role" id="role"
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition-all duration-300 bg-white">
                                <option value="penyelenggara">Penyelenggara</option>
                                <option value="admin">Admin</option>
                                <option value="moderator">Moderator</option>
                            </select>
                        </div>

                        <button type="submit"
                            class="w-full bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center justify-center">
                            <i class="fas fa-plus mr-3"></i>
                            Tambahkan Anggota
                        </button>
                    </form>

                    <!-- Tips Section -->
                    <div class="mt-6 p-4 bg-amber-50 rounded-xl border border-amber-200">
                        <h4 class="font-semibold text-amber-800 mb-2 flex items-center">
                            <i class="fas fa-lightbulb mr-2 text-amber-600"></i>
                            Tips
                        </h4>
                        <ul class="text-sm text-amber-700 space-y-1">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle mt-1 mr-2 text-amber-500 text-xs"></i>
                                <span>Pastikan email anggota valid</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle mt-1 mr-2 text-amber-500 text-xs"></i>
                                <span>Bagikan password dengan aman</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle mt-1 mr-2 text-amber-500 text-xs"></i>
                                <span>Atur role sesuai kebutuhan</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Daftar Anggota Tim -->
            <div class="xl:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
                    <!-- Header Tabel -->
                    <div class="bg-gradient-to-r from-slate-800 to-slate-900 text-white px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold flex items-center">
                                <i class="fas fa-users mr-3"></i>
                                Daftar Anggota Tim
                            </h2>
                            <span class="bg-white/20 px-3 py-1 rounded-full text-sm font-medium">
                                <?= $total_anggota ?> Anggota
                            </span>
                        </div>
                    </div>

                    <!-- Tabel Anggota -->
                    <div class="divide-y divide-slate-200">
                        <?php if (count($anggota_tim) > 0): ?>
                            <?php foreach ($anggota_tim as $anggota): ?>
                                <div class="px-6 py-4 hover:bg-slate-50/50 transition-colors group">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div
                                                class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center text-white font-semibold text-lg">
                                                <?= strtoupper(substr($anggota['nama_lengkap'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <h3
                                                    class="font-semibold text-slate-800 group-hover:text-amber-600 transition-colors">
                                                    <?= htmlspecialchars($anggota['nama_lengkap']) ?>
                                                </h3>
                                                <p class="text-sm text-slate-600 flex items-center mt-1">
                                                    <i class="fas fa-envelope mr-2 text-slate-400 text-xs"></i>
                                                    <?= htmlspecialchars($anggota['email']) ?>
                                                </p>
                                                <p class="text-xs text-slate-500 mt-1">
                                                    Bergabung: <?= date('d M Y', strtotime($anggota['created_at'])) ?>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-3">
                                            <!-- Status Badge -->
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                <i class="fas fa-circle mr-1 text-xs animate-pulse"></i>
                                                Aktif
                                            </span>

                                            <!-- Action Buttons -->
                                            <div class="flex items-center space-x-2">
                                                <button
                                                    class="edit-anggota-btn w-10 h-10 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 border border-blue-200"
                                                    data-id="<?= $anggota['id'] ?>"
                                                    data-nama="<?= htmlspecialchars($anggota['nama_lengkap']) ?>"
                                                    data-email="<?= htmlspecialchars($anggota['email']) ?>"
                                                    title="Edit Anggota">
                                                    <i class="fas fa-edit text-sm"></i>
                                                </button>

                                                <button
                                                    class="hapus-anggota-btn w-10 h-10 bg-red-100 hover:bg-red-200 text-red-600 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 border border-red-200"
                                                    data-id="<?= $anggota['id'] ?>"
                                                    data-nama="<?= htmlspecialchars($anggota['nama_lengkap']) ?>"
                                                    title="Hapus Anggota">
                                                    <i class="fas fa-trash-alt text-sm"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Empty State -->
                            <div class="px-6 py-12 text-center">
                                <div
                                    class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-users text-slate-400 text-3xl"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-slate-600 mb-2">Belum Ada Anggota Tim</h3>
                                <p class="text-slate-500 mb-6">Mulai dengan menambahkan anggota pertama ke tim Anda</p>
                                <div class="text-sm text-slate-400">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Anggota tim dapat membantu mengelola event dan peserta
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
                        <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center">
                            <i class="fas fa-share-alt text-blue-500 mr-3"></i>
                            Undang Anggota
                        </h3>
                        <p class="text-slate-600 text-sm mb-4">
                            Bagikan link undangan kepada calon anggota tim
                        </p>
                        <button
                            class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-xl transition-colors flex items-center justify-center">
                            <i class="fas fa-link mr-3"></i>
                            Generate Invite Link
                        </button>
                    </div>

                    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
                        <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center">
                            <i class="fas fa-chart-bar text-emerald-500 mr-3"></i>
                            Aktivitas Tim
                        </h3>
                        <p class="text-slate-600 text-sm mb-4">
                            Lihat laporan aktivitas anggota tim
                        </p>
                        <button
                            class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-3 px-4 rounded-xl transition-colors flex items-center justify-center">
                            <i class="fas fa-chart-line mr-3"></i>
                            Lihat Laporan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Fungsi untuk menghapus anggota
    function hapusAnggota(anggotaId, namaAnggota) {
        Swal.fire({
            title: 'Hapus Anggota?',
            html: `Apakah Anda yakin ingin menghapus <strong>${namaAnggota}</strong> dari tim?`,
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
                    text: 'Sedang menghapus anggota',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Kirim request hapus ke server
                fetch(`hapus_anggota?id=${anggotaId}`, {
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
                            text: 'Terjadi kesalahan saat menghapus anggota',
                            icon: 'error',
                            confirmButtonColor: '#ef4444'
                        });
                    });
            }
        });
    }

    // Event listener untuk tombol hapus
    document.addEventListener('DOMContentLoaded', function () {
        const hapusButtons = document.querySelectorAll('.hapus-anggota-btn');
        hapusButtons.forEach(button => {
            button.addEventListener('click', function () {
                const anggotaId = this.getAttribute('data-id');
                const namaAnggota = this.getAttribute('data-nama');
                hapusAnggota(anggotaId, namaAnggota);
            });
        });

        // Event listener untuk tombol edit
        const editButtons = document.querySelectorAll('.edit-anggota-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                const anggotaId = this.getAttribute('data-id');
                const namaAnggota = this.getAttribute('data-nama');
                const email = this.getAttribute('data-email');

                // Tampilkan modal edit atau form inline
                Swal.fire({
                    title: 'Edit Anggota',
                    html: `
                    <form id="editMemberForm">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Nama Lengkap</label>
                            <input type="text" id="editNama" value="${namaAnggota}" class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                            <input type="email" id="editEmail" value="${email}" class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                        </div>
                    </form>
                `,
                    showCancelButton: true,
                    confirmButtonText: 'Simpan',
                    cancelButtonText: 'Batal',
                    preConfirm: () => {
                        return {
                            nama: document.getElementById('editNama').value,
                            email: document.getElementById('editEmail').value
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Proses update data
                        console.log('Update data:', result.value);
                    }
                });
            });
        });

        // Form validation
        const addMemberForm = document.getElementById('addMemberForm');
        if (addMemberForm) {
            addMemberForm.addEventListener('submit', function (e) {
                const password = document.getElementById('password').value;
                if (password.length < 8) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Password Terlalu Pendek',
                        text: 'Password harus minimal 8 karakter',
                        icon: 'warning',
                        confirmButtonColor: '#f59e0b'
                    });
                }
            });
        }
    });
</script>

<?php require_once BASE_PATH . '/admin/templates/footer.php'; ?>