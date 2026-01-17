<?php
session_start();
require_once '../core/koneksi.php';

// Cek Akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'penyelenggara') {
    // Jika request JSON (Edit/Hapus)
    if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false || isset($_GET['action'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
        exit;
    }
    // Jika request Form (Tambah)
    header("Location: kelola_tim.php?status=gagal&msg=Akses ditolak.");
    exit;
}

$owner_id = $_SESSION['user_id'];

// Deteksi Jenis Request (JSON atau Form Data)
$json_input = json_decode(file_get_contents('php://input'), true);
$action = $_POST['action'] ?? $_GET['action'] ?? $json_input['action'] ?? '';

// ==========================================
// 1. ACTION: TAMBAH (Form Submit / Redirect)
// ==========================================
if ($action === 'tambah') {
    $nama = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $wa = trim($_POST['no_whatsapp']);
    $password = $_POST['password'];

    if (empty($nama) || empty($email) || empty($password)) {
        header("Location: kelola_tim.php?status=gagal&msg=Semua field wajib diisi.");
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (nama_lengkap, email, no_whatsapp, password, role, owner_id) VALUES (?, ?, ?, ?, 'penyelenggara', ?)");
        $stmt->execute([$nama, $email, $wa, $hash, $owner_id]);

        header("Location: kelola_tim.php?status=sukses");
        exit;
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            header("Location: kelola_tim.php?status=gagal&msg=Email atau No WA sudah terdaftar.");
        } else {
            header("Location: kelola_tim.php?status=gagal&msg=" . urlencode($e->getMessage()));
        }
        exit;
    }
}

// ==========================================
// 2. ACTION: EDIT (AJAX JSON)
// ==========================================
elseif ($action === 'edit') {
    header('Content-Type: application/json');

    $id = $json_input['id'] ?? null;
    $nama = trim($json_input['nama'] ?? '');
    $email = trim($json_input['email'] ?? '');
    $wa = trim($json_input['no_whatsapp'] ?? '');

    if (!$id || empty($nama) || empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
        exit;
    }

    try {
        // Cek Kepemilikan
        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE id = ? AND owner_id = ?");
        $stmt_check->execute([$id, $owner_id]);
        if ($stmt_check->rowCount() === 0) {
            echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan.']);
            exit;
        }

        // Update
        $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, email = ?, no_whatsapp = ? WHERE id = ?");
        $stmt->execute([$nama, $email, $wa, $id]);

        echo json_encode(['status' => 'success', 'message' => 'Data anggota berhasil diperbarui.']);
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            echo json_encode(['status' => 'error', 'message' => 'Email/WA sudah digunakan user lain.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    exit;
}

// ==========================================
// 3. ACTION: HAPUS (AJAX JSON/GET)
// ==========================================
elseif ($action === 'hapus') {
    header('Content-Type: application/json');
    $id = $_GET['id'] ?? null;

    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan']);
        exit;
    }

    try {
        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE id = ? AND owner_id = ?");
        $stmt_check->execute([$id, $owner_id]);
        if ($stmt_check->rowCount() === 0) {
            echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['status' => 'success', 'message' => 'Anggota berhasil dihapus.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Default jika action tidak dikenali
else {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Action tidak valid.']);
    exit;
}
?>