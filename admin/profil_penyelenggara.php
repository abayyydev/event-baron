<?php
$page_title = 'Profil Saya';
$current_page = 'profil'; // Untuk menandai menu aktif di sidebar
require_once '../admin/templates/header.php'; // Sesuaikan path header
require_once '../core/koneksi.php'; // Sesuaikan path koneksi

// 1. Ambil ID User dari Session
$user_id = $_SESSION['user_id'];

// 2. Proses Form Submit (Update Profil)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $no_whatsapp = trim($_POST['no_whatsapp']);
    $jenis_kelamin = $_POST['jenis_kelamin'];

    // Validasi Foto
    $foto_nama = $_FILES['foto_profil']['name'];
    $foto_tmp = $_FILES['foto_profil']['tmp_name'];
    $foto_error = $_FILES['foto_profil']['error'];
    $foto_size = $_FILES['foto_profil']['size'];

    // Mulai Transaksi Update
    try {
        // Logika Upload Foto
        $query_update_foto = "";
        $params = [$nama_lengkap, $no_whatsapp, $jenis_kelamin];

        if ($foto_error === 0) {
            // Cek ekstensi
            $ext = strtolower(pathinfo($foto_nama, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png'];

            if (!in_array($ext, $allowed)) {
                throw new Exception("Format foto harus JPG, JPEG, atau PNG.");
            }

            if ($foto_size > 5242880) {
                throw new Exception("Ukuran foto terlalu besar (Maksimal 5MB).");
            }

            // Generate nama file baru unik
            $nama_file_baru = uniqid() . '.' . $ext;
            $tujuan = '../assets/uploads/profil/' . $nama_file_baru;

            // Upload
            if (move_uploaded_file($foto_tmp, $tujuan)) {
                // Hapus foto lama jika ada (optional)
                $stmt_cek = $pdo->prepare("SELECT foto_profil FROM users WHERE id = ?");
                $stmt_cek->execute([$user_id]);
                $foto_lama = $stmt_cek->fetchColumn();

                if ($foto_lama && file_exists('../assets/uploads/profil/' . $foto_lama)) {
                    unlink('../assets/uploads/profil/' . $foto_lama);
                }

                $query_update_foto = ", foto_profil = ?";
                $params[] = $nama_file_baru;

                // Update session foto agar langsung berubah di header
                $_SESSION['foto_profil'] = $tujuan;
            }
        }

        // Tambahkan ID ke parameter terakhir
        $params[] = $user_id;

        $sql = "UPDATE users SET nama_lengkap = ?, no_whatsapp = ?, jenis_kelamin = ? $query_update_foto WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Update Session Nama
        $_SESSION['nama_lengkap'] = $nama_lengkap;

        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Profil berhasil diperbarui.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = 'profil_penyelenggara.php';
                });
            });
        </script>";

    } catch (Exception $e) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire('Gagal', '" . $e->getMessage() . "', 'error');
            });
        </script>";
    }
}

// 3. Ambil Data User Terbaru dari Database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Tentukan path foto profil
$path_foto = !empty($user['foto_profil'])
    ? '../assets/uploads/profil/' . $user['foto_profil']
    : '../assets/img/default-avatar.png'; // Pastikan ada gambar default
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        <div class="md:col-span-1">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 text-center relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-primary-800 to-primary-600"></div>

                <div class="relative z-10 mt-10">
                    <div class="w-32 h-32 mx-auto bg-white p-1 rounded-full shadow-md">
                        <img src="<?= htmlspecialchars($path_foto) ?>" alt="Foto Profil"
                            class="w-full h-full rounded-full object-cover border-2 border-gray-100">
                    </div>

                    <h2 class="mt-4 text-xl font-bold text-gray-800">
                        <?= htmlspecialchars($user['nama_lengkap']) ?>
                    </h2>
                    <p
                        class="text-sm text-primary-600 font-medium bg-primary-50 inline-block px-3 py-1 rounded-full mt-1 uppercase">
                        <?= htmlspecialchars($user['role']) ?>
                    </p>
                    <p class="text-sm text-gray-500 mt-2">
                        <?= htmlspecialchars($user['email']) ?>
                    </p>
                </div>

                <div class="mt-6 border-t border-gray-100 pt-4 text-left">
                    <div class="flex items-center text-sm text-gray-600 mb-2">
                        <i class="fas fa-calendar-alt w-6 text-center text-primary-500 mr-2"></i>
                        Bergabung:
                        <?= date('d M Y', strtotime($user['created_at'])) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="md:col-span-2">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-bold text-gray-800">Edit Informasi Profil</h3>
                </div>

                <form action="" method="POST" enctype="multipart/form-data">

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ganti Foto Profil</label>
                        <input type="file" name="foto_profil" accept=".jpg, .jpeg, .png" class="block w-full text-sm text-slate-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-primary-50 file:text-primary-700
                                hover:file:bg-primary-100
                                transition-all cursor-pointer">
                        <p class="text-xs text-gray-500 mt-1">Format: JPG, JPEG, PNG. Maksimal 5MB.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="nama_lengkap" class="block text-sm font-medium text-gray-700 mb-1">Nama
                                Lengkap</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" name="nama_lengkap" id="nama_lengkap" required
                                    value="<?= htmlspecialchars($user['nama_lengkap']) ?>"
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly
                                    class="w-full pl-10 pr-4 py-2 border border-gray-200 bg-gray-50 text-gray-500 rounded-lg cursor-not-allowed">
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Email tidak dapat diubah.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="no_whatsapp" class="block text-sm font-medium text-gray-700 mb-1">No.
                                WhatsApp</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <i class="fab fa-whatsapp"></i>
                                </span>
                                <input type="text" name="no_whatsapp" id="no_whatsapp"
                                    value="<?= htmlspecialchars($user['no_whatsapp']) ?>"
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all">
                            </div>
                        </div>

                        <div>
                            <label for="jenis_kelamin" class="block text-sm font-medium text-gray-700 mb-1">Jenis
                                Kelamin</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <i class="fas fa-venus-mars"></i>
                                </span>
                                <select name="jenis_kelamin" id="jenis_kelamin"
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all bg-white">
                                    <option value="Laki-laki" <?= $user['jenis_kelamin'] == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                                    <option value="Perempuan" <?= $user['jenis_kelamin'] == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-100">
                        <button type="submit"
                            class="px-6 py-2.5 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center">
                            <i class="fas fa-save mr-2"></i> Simpan Perubahan
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

<?php require_once '../admin/templates/footer.php'; ?>