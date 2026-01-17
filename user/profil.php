<?php
// user/profil.php

$page_title = "Edit Profil Saya";
$current_page = "Edit Profil";

// Panggil Header Universal (dari folder admin)
require_once __DIR__ . '/templates/header.php';
require_once '../core/koneksi.php'; // Pastikan koneksi DB

$id_user = $_SESSION['user_id'];

// PROSES UPDATE DATA
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $no_whatsapp = trim($_POST['no_whatsapp']);
    $jenis_kelamin = $_POST['jenis_kelamin']; // Tambahkan jenis kelamin
    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi_password'];

    // Validasi Foto
    $foto_nama = $_FILES['foto_profil']['name'];
    $foto_tmp = $_FILES['foto_profil']['tmp_name'];
    $foto_error = $_FILES['foto_profil']['error'];
    $foto_size = $_FILES['foto_profil']['size'];

    if (empty($nama_lengkap) || empty($no_whatsapp)) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire('Gagal', 'Nama dan WhatsApp wajib diisi.', 'error');
            });
        </script>";
    } else {
        try {
            // Logika Upload Foto
            $query_foto = "";
            $params_foto = [];

            if ($foto_error === 0) {
                $ext = strtolower(pathinfo($foto_nama, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png'];

                if (!in_array($ext, $allowed))
                    throw new Exception("Format foto harus JPG, JPEG, atau PNG.");
                if ($foto_size > 5000000)
                    throw new Exception("Ukuran foto maksimal 5MB."); // 5MB Limit

                $nama_file_baru = uniqid() . '.' . $ext;
                // Upload ke folder yang sama dengan admin agar terpusat
                $tujuan = '../assets/uploads/profil/' . $nama_file_baru;

                if (move_uploaded_file($foto_tmp, $tujuan)) {
                    // Hapus foto lama
                    $stmt_old = $pdo->prepare("SELECT foto_profil FROM users WHERE id = ?");
                    $stmt_old->execute([$id_user]);
                    $old_photo = $stmt_old->fetchColumn();

                    if ($old_photo && file_exists('../assets/uploads/profil/' . $old_photo)) {
                        unlink('../assets/uploads/profil/' . $old_photo);
                    }

                    $query_foto = ", foto_profil = ?";
                    $params_foto[] = $nama_file_baru; // Simpan nama file saja di DB

                    // Update session agar header langsung berubah
                    $_SESSION['foto_profil'] = $nama_file_baru;
                }
            }

            // Logika Update Password & Data Utama
            $sql = "UPDATE users SET nama_lengkap=?, no_whatsapp=?, jenis_kelamin=? $query_foto";
            $params = [$nama_lengkap, $no_whatsapp, $jenis_kelamin];

            // Gabungkan parameter foto jika ada
            if (!empty($params_foto)) {
                $params = array_merge($params, $params_foto);
            }

            if (!empty($password_baru)) {
                if ($password_baru !== $konfirmasi) {
                    throw new Exception("Konfirmasi password tidak cocok.");
                }
                $sql .= ", password=?";
                $params[] = password_hash($password_baru, PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id=?";
            $params[] = $id_user;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Update Session Nama
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            $_SESSION['nama_user'] = $nama_lengkap; // Backup session lama jika ada

            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Profil berhasil diperbarui.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = 'profil.php';
                    });
                });
            </script>";

        } catch (Exception $e) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire('Error', '" . $e->getMessage() . "', 'error');
                });
            </script>";
        }
    }
}

// AMBIL DATA TERBARU
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id_user]);
$user = $stmt->fetch();

// Tentukan path foto untuk ditampilkan di form (Preview)
$path_foto_preview = !empty($user['foto_profil'])
    ? '../assets/uploads/profil/' . $user['foto_profil']
    : '../assets/img/default-avatar.png';
?>

<div class="max-w-4xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <div class="lg:col-span-1">
            <div
                class="bg-white rounded-2xl shadow-lg border border-slate-100 p-6 text-center relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-primary-800 to-primary-600"></div>

                <div class="relative z-10 mt-8">
                    <div class="w-32 h-32 mx-auto bg-white p-1 rounded-full shadow-md group relative">
                        <img src="<?= htmlspecialchars($path_foto_preview) ?>" alt="Foto Profil"
                            class="w-full h-full rounded-full object-cover border-2 border-slate-100">

                        <div
                            class="absolute bottom-0 right-0 bg-gold-500 w-8 h-8 rounded-full flex items-center justify-center border-2 border-white shadow-sm">
                            <i class="fas fa-camera text-white text-xs"></i>
                        </div>
                    </div>

                    <h2 class="mt-4 text-lg font-bold text-slate-800"><?= htmlspecialchars($user['nama_lengkap']) ?>
                    </h2>
                    <p
                        class="text-sm text-primary-600 font-medium bg-primary-50 inline-block px-3 py-1 rounded-full mt-1 uppercase">
                        Santri / Peserta
                    </p>
                    <p class="text-xs text-slate-400 mt-2">Bergabung:
                        <?= date('d M Y', strtotime($user['created_at'])) ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">Informasi Pribadi</h3>
                    <span class="text-xs text-slate-400"><i class="fas fa-lock mr-1"></i> Data Aman</span>
                </div>

                <form action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Ganti Foto Profil</label>
                        <input type="file" name="foto_profil" accept=".jpg, .jpeg, .png" class="block w-full text-sm text-slate-500
                                file:mr-4 file:py-2.5 file:px-4
                                file:rounded-xl file:border-0
                                file:text-sm file:font-semibold
                                file:bg-primary-50 file:text-primary-700
                                hover:file:bg-primary-100
                                transition-all cursor-pointer border border-slate-200 rounded-xl">
                        <p class="text-xs text-slate-400 mt-1 ml-1">Maksimal 5MB (JPG, PNG)</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i
                                        class="fas fa-user"></i></span>
                                <input type="text" name="nama_lengkap"
                                    value="<?= htmlspecialchars($user['nama_lengkap']) ?>"
                                    class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all"
                                    required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Jenis Kelamin</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i
                                        class="fas fa-venus-mars"></i></span>
                                <select name="jenis_kelamin"
                                    class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all bg-white">
                                    <option value="Laki-laki" <?= $user['jenis_kelamin'] == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                                    <option value="Perempuan" <?= $user['jenis_kelamin'] == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i
                                        class="fas fa-envelope"></i></span>
                                <input type="email" value="<?= htmlspecialchars($user['email']) ?>"
                                    class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-500 cursor-not-allowed"
                                    readonly>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nomor WhatsApp</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i
                                        class="fab fa-whatsapp"></i></span>
                                <input type="number" name="no_whatsapp"
                                    value="<?= htmlspecialchars($user['no_whatsapp']) ?>"
                                    class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all"
                                    required>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-6 mt-2">
                        <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center">
                            <i class="fas fa-key text-gold-500 mr-2"></i> Ganti Password
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <input type="password" name="password_baru" placeholder="Password Baru"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all text-sm">
                            </div>
                            <div>
                                <input type="password" name="konfirmasi_password" placeholder="Ulangi Password"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all text-sm">
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">* Kosongkan jika tidak ingin mengubah password.</p>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit"
                            class="bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5 flex items-center">
                            <i class="fas fa-save mr-2"></i> Simpan Perubahan
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>