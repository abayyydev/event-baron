<?php
session_start();
require_once 'core/koneksi.php';

function send_json_response($status, $message)
{
    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'penyelenggara') {
    send_json_response('error', 'Akses ditolak.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['action']) || $_POST['action'] !== 'tambah_anggota') {
    send_json_response('error', 'Request tidak valid.');
}

$nama_lengkap = $_POST['nama_lengkap'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role = 'penyelenggara'; // Semua anggota tim adalah penyelenggara
$owner_id = $_SESSION['user_id']; // "Induk"-nya adalah user yang sedang login

if (empty($nama_lengkap) || empty($email) || empty($_POST['password'])) {
    send_json_response('error', 'Semua field wajib diisi.');
}

try {
    $stmt = $pdo->prepare("INSERT INTO users (nama_lengkap, email, password, role, owner_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nama_lengkap, $email, $password, $role, $owner_id]);

    // Alih-alih JSON, kita redirect kembali ke halaman tim
    header("Location: kelola_tim?status=sukses");
    exit();

} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) { // Error duplikat email
        header("Location: kelola_tim?status=gagal&msg=Email sudah terdaftar.");
        exit();
    } else {
        header("Location: kelola_tim?status=gagal&msg=" . urlencode($e->getMessage()));
        exit();
    }
}
?>