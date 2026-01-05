<?php
$page_title = "Edit Profil";
$current_page = "profil";

require_once __DIR__ . '/templates/header.php';

$id_user = $_SESSION['user_id'];
$message = '';
$message_type = '';

// PROSES UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $no_whatsapp = trim($_POST['no_whatsapp']);
    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi_password'];

    if (empty($nama_lengkap) || empty($no_whatsapp)) {
        echo "<script>Swal.fire('Error', 'Nama dan WhatsApp wajib diisi.', 'error');</script>";
    } else {
        try {
            if (!empty($password_baru)) {
                if ($password_baru !== $konfirmasi) {
                    echo "<script>Swal.fire('Error', 'Konfirmasi password tidak cocok.', 'error');</script>";
                } else {
                    $hash = password_hash($password_baru, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET nama_lengkap=?, no_whatsapp=?, password=? WHERE id=?");
                    $stmt->execute([$nama_lengkap, $no_whatsapp, $hash, $id_user]);
                    echo "<script>Swal.fire('Sukses', 'Profil dan Password berhasil diupdate.', 'success');</script>";
                }
            } else {
                $stmt = $pdo->prepare("UPDATE users SET nama_lengkap=?, no_whatsapp=? WHERE id=?");
                $stmt->execute([$nama_lengkap, $no_whatsapp, $id_user]);
                echo "<script>Swal.fire('Sukses', 'Profil berhasil diupdate.', 'success');</script>";
            }
            $_SESSION['nama_user'] = $nama_lengkap; // Update sesi
        } catch (PDOException $e) {
            echo "<script>Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');</script>";
        }
    }
}

// AMBIL DATA
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id_user]);
$user = $stmt->fetch();
?>

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        <div class="bg-gray-50 p-6 border-b border-gray-200 flex items-center">
            <div
                class="w-16 h-16 bg-primary rounded-full flex items-center justify-center text-white text-2xl font-bold shadow-md mr-4">
                <?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Profil</h1>
                <p class="text-sm text-gray-500">Perbarui informasi akun Anda di sini.</p>
            </div>
        </div>

        <form action="" method="POST" class="p-6 md:p-8 space-y-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i
                            class="fas fa-user"></i></span>
                    <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap']) ?>"
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                        required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Email (Tidak dapat diubah)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i
                            class="fas fa-envelope"></i></span>
                    <input type="email" value="<?= htmlspecialchars($user['email']) ?>"
                        class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed"
                        readonly>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nomor WhatsApp</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i
                            class="fab fa-whatsapp"></i></span>
                    <input type="number" name="no_whatsapp" value="<?= htmlspecialchars($user['no_whatsapp']) ?>"
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition"
                        required>
                </div>
            </div>

            <hr class="border-gray-200 border-dashed my-4">

            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-lock text-secondary mr-2"></i> Ganti Password
                </h3>
                <div class="bg-yellow-50 p-3 rounded-lg border border-yellow-100 mb-4 text-xs text-yellow-700">
                    <i class="fas fa-info-circle mr-1"></i> Biarkan kosong jika tidak ingin mengganti password.
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Password Baru</label>
                        <input type="password" name="password_baru" placeholder="••••••••"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Ulangi Password</label>
                        <input type="password" name="konfirmasi_password" placeholder="••••••••"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary transition">
                    </div>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit"
                    class="w-full bg-primary hover:bg-green-800 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition transform hover:-translate-y-1 flex items-center justify-center">
                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>