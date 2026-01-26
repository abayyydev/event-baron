<?php
// user/profil.php

if (session_status() === PHP_SESSION_NONE)
    session_start();

// 1. Cek Login Santri
if (!isset($_SESSION['santri_id']) || $_SESSION['role'] !== 'peserta') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Edit Profil Saya";
$current_page = "profil"; // Active state sidebar

require_once __DIR__ . '/templates/header.php';
require_once '../core/koneksi.php';

$id_santri = $_SESSION['santri_id'];

// 2. PROSES UPDATE DATA
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $no_hp_wali = trim($_POST['no_hp_wali']);
    // $jenis_kelamin = $_POST['jenis_kelamin']; // Opsional jika ada kolomnya di DB santri
    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi_password'];

    // Validasi Foto
    $foto_nama = $_FILES['foto_profil']['name'];
    $foto_tmp = $_FILES['foto_profil']['tmp_name'];
    $foto_error = $_FILES['foto_profil']['error'];
    $foto_size = $_FILES['foto_profil']['size'];

    if (empty($nama_lengkap) || empty($no_hp_wali)) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire('Gagal', 'Nama dan WhatsApp Wali wajib diisi.', 'error');
            });
        </script>";
    } else {
        try {
            $query_foto = "";
            $params_foto = [];

            // A. Handle Upload Foto
            if ($foto_error === 0) {
                $ext = strtolower(pathinfo($foto_nama, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png'];

                if (!in_array($ext, $allowed))
                    throw new Exception("Format foto harus JPG, JPEG, atau PNG.");
                if ($foto_size > 5000000)
                    throw new Exception("Ukuran foto maksimal 5MB.");

                $nama_file_baru = uniqid() . '.' . $ext;
                $tujuan = '../assets/uploads/santri/' . $nama_file_baru; // Folder khusus santri

                // Buat folder jika belum ada
                if (!is_dir('../assets/uploads/santri/')) {
                    mkdir('../assets/uploads/santri/', 0777, true);
                }

                if (move_uploaded_file($foto_tmp, $tujuan)) {
                    // Hapus foto lama
                    $stmt_old = $pdo->prepare("SELECT foto_santri FROM santri WHERE id = ?");
                    $stmt_old->execute([$id_santri]);
                    $old_photo = $stmt_old->fetchColumn();

                    if ($old_photo && file_exists('../assets/uploads/santri/' . $old_photo)) {
                        unlink('../assets/uploads/santri/' . $old_photo);
                    }

                    $query_foto = ", foto_santri = ?";
                    $params_foto[] = $nama_file_baru;

                    // Update session
                    $_SESSION['foto_profil'] = 'assets/uploads/santri/' . $nama_file_baru;
                }
            }

            // B. Prepare Query Update
            // Catatan: Pastikan kolom 'jenis_kelamin' ada di tabel santri jika ingin diupdate.
            // Jika tidak ada, hapus bagian jenis_kelamin.
            $sql = "UPDATE santri SET nama_lengkap=?, no_hp_wali=? $query_foto";
            $params = [$nama_lengkap, $no_hp_wali];

            if (!empty($params_foto)) {
                $params = array_merge($params, $params_foto);
            }

            // C. Handle Password
            if (!empty($password_baru)) {
                if ($password_baru !== $konfirmasi) {
                    throw new Exception("Konfirmasi password tidak cocok.");
                }
                $sql .= ", password=?";
                $params[] = password_hash($password_baru, PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id=?";
            $params[] = $id_santri;

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

// 3. AMBIL DATA TERBARU
$stmt = $pdo->prepare("SELECT * FROM santri WHERE id = ?");
$stmt->execute([$id_santri]);
$user = $stmt->fetch();

// Path Foto Preview
$path_foto_preview = !empty($user['foto_santri'])
    ? '../assets/uploads/santri/' . $user['foto_santri']
    : '../assets/img/avatar-santri.png';

// Data Kartu
$barcode_code = $user['barcode_code'] ?? 'STR-UNKNOWN';
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . $barcode_code;
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 pt-6 pb-20">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <div class="lg:col-span-1 space-y-8">
            
            <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-6 text-center relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-primary-800 to-primary-600"></div>

                <div class="relative z-10 mt-8">
                    <div class="w-32 h-32 mx-auto bg-white p-1 rounded-full shadow-md group relative">
                        <img src="<?= htmlspecialchars($path_foto_preview) ?>" alt="Foto Profil"
                            class="w-full h-full rounded-full object-cover border-2 border-slate-100">
                        <div class="absolute bottom-0 right-0 bg-gold-500 w-8 h-8 rounded-full flex items-center justify-center border-2 border-white shadow-sm">
                            <i class="fas fa-camera text-white text-xs"></i>
                        </div>
                    </div>

                    <h2 class="mt-4 text-lg font-bold text-slate-800"><?= htmlspecialchars($user['nama_lengkap']) ?></h2>
                    <p class="text-sm text-primary-600 font-medium bg-primary-50 inline-block px-3 py-1 rounded-full mt-1 uppercase">
                        Santri / Peserta
                    </p>
                    <p class="text-xs text-slate-400 mt-2">NIS: <?= htmlspecialchars($user['nis']) ?></p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-6">
                <h3 class="font-bold text-slate-800 mb-4 flex items-center">
                    <i class="fas fa-id-card text-gold-500 mr-2"></i> Kartu Digital
                </h3>

                <div id="memberCardArea" class="relative w-full aspect-[1.58/1] bg-gradient-to-br from-slate-900 to-slate-800 rounded-xl overflow-hidden shadow-2xl p-4 text-white border border-slate-600">
                    
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gold-500/20 rounded-full blur-2xl -mr-8 -mt-8"></div>
                    <div class="absolute bottom-0 left-0 w-20 h-20 bg-primary-500/20 rounded-full blur-2xl -ml-5 -mb-5"></div>

                    <div class="relative z-10 h-full flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-2">
                                <img src="../assets/img/images/logo-pondok.png" class="w-8 h-8 object-contain">
                                <div>
                                    <p class="text-[8px] text-gold-400 font-bold tracking-widest uppercase">Member Card</p>
                                    <p class="text-[6px] text-slate-300 uppercase tracking-wide">Ponpes Al Ihsan Baron</p>
                                </div>
                            </div>
                            <div class="bg-white p-0.5 rounded">
                                <img src="<?= $qr_url ?>" class="w-10 h-10">
                            </div>
                        </div>

                        <div>
                            <p class="text-[8px] text-slate-400 uppercase mb-0.5">Nama Santri</p>
                            <p class="text-sm font-bold truncate tracking-wide"><?= strtoupper($user['nama_lengkap']) ?></p>
                            <div class="flex gap-4 mt-2">
                                <div>
                                    <p class="text-[8px] text-slate-400 uppercase">NIS</p>
                                    <p class="text-xs font-mono font-semibold text-gold-300"><?= $user['nis'] ?></p>
                                </div>
                                <div>
                                    <p class="text-[8px] text-slate-400 uppercase">Kelas</p>
                                    <p class="text-xs font-semibold"><?= htmlspecialchars($user['kelas'] ?? '-') ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="text-right border-t border-white/10 pt-2">
                            <p class="text-[8px] font-mono text-slate-500"><?= $barcode_code ?></p>
                        </div>
                    </div>
                </div>

                <button onclick="downloadCard()" class="w-full mt-4 bg-slate-900 hover:bg-primary-600 text-white text-sm font-bold py-2.5 rounded-xl transition-all flex items-center justify-center gap-2 shadow-lg">
                    <i class="fas fa-download"></i> Simpan Kartu
                </button>
            </div>

        </div>

        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">Edit Data Diri</h3>
                    <span class="text-xs text-slate-400"><i class="fas fa-lock mr-1"></i> Data Aman</span>
                </div>

                <form action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Update Foto Profil</label>
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
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="fas fa-user"></i></span>
                                <input type="text" name="nama_lengkap"
                                    value="<?= htmlspecialchars($user['nama_lengkap']) ?>"
                                    class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all"
                                    required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">NIS (Nomor Induk)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="fas fa-id-badge"></i></span>
                                <input type="text" value="<?= htmlspecialchars($user['nis']) ?>"
                                    class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-500 cursor-not-allowed"
                                    readonly>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Wali</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="fas fa-user-friends"></i></span>
                                <input type="text" value="<?= htmlspecialchars($user['nama_wali']) ?>"
                                    class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-500 cursor-not-allowed"
                                    readonly>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">WhatsApp Wali</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="fab fa-whatsapp"></i></span>
                                <input type="number" name="no_hp_wali"
                                    value="<?= htmlspecialchars($user['no_hp_wali']) ?>"
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function downloadCard() {
        const cardArea = document.getElementById("memberCardArea");
        
        // Efek loading tombol
        const btn = event.currentTarget;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        btn.disabled = true;

        html2canvas(cardArea, {
            scale: 2, // Tingkatkan resolusi
            backgroundColor: null,
            useCORS: true
        }).then(canvas => {
            // Buat link download
            const link = document.createElement("a");
            link.download = "Kartu_Santri_<?= $user['nis'] ?>.png";
            link.href = canvas.toDataURL("image/png");
            link.click();

            // Kembalikan tombol
            btn.innerHTML = originalText;
            btn.disabled = false;
            
            Swal.fire({
                icon: 'success',
                title: 'Tersimpan!',
                text: 'Kartu santri berhasil didownload.',
                timer: 1500,
                showConfirmButton: false
            });
        }).catch(err => {
            console.error(err);
            btn.innerHTML = originalText;
            btn.disabled = false;
            Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan kartu.', 'error');
        });
    }
</script>

<?php require_once 'templates/footer.php'; ?>