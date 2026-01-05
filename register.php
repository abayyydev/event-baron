<?php
// register.php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once 'core/koneksi.php'; // Memanggil $pdo dari file koneksi

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil Input (Tanpa escape string manual)
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $no_wa = trim($_POST['no_wa']);
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi_password'];

    // 1. Validasi Password
    if ($password !== $konfirmasi) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        try {
            // 2. Cek apakah email sudah terdaftar menggunakan Prepared Statement
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $error = "Email sudah terdaftar! Silakan login.";
            } else {
                // 3. Insert Data Baru
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $role = 'peserta';
                // owner_id otomatis NULL, tidak perlu ditulis di query

                $sql = "INSERT INTO users (nama_lengkap, email, no_whatsapp, password, role) 
                        VALUES (?, ?, ?, ?, ?)";

                $stmt_insert = $pdo->prepare($sql);
                // Eksekusi dengan array data urut sesuai tanda tanya (?)
                $saved = $stmt_insert->execute([$nama, $email, $no_wa, $password_hash, $role]);

                if ($saved) {
                    $success = "Registrasi berhasil! Silakan login.";
                    echo "<meta http-equiv='refresh' content='2;url=" . BASE_URL . "login'>";
                }
            }
        } catch (PDOException $e) {
            // Tangkap error database
            $error = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Daftar Akun Peserta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="card p-4 mx-auto" style="max-width: 500px;">
            <h4 class="text-center mb-4">Daftar Akun Peserta</h4>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>No WhatsApp</label>
                    <input type="number" name="no_wa" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Ulangi Password</label>
                    <input type="password" name="konfirmasi_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Daftar</button>
            </form>
            <div class="text-center mt-3">
                <a href="<?= BASE_URL ?>login">Sudah punya akun? Login</a>
            </div>
        </div>
    </div>
</body>

</html>