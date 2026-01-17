<?php
session_start();
require_once __DIR__ . '/../core/koneksi.php';

// Set header JSON agar respon dikenali JavaScript
header('Content-Type: application/json');

// 1. Cek Sesi Login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi habis, silakan login ulang.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Cek Method POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data
    $ws_id = $_POST['workshop_id'] ?? 0;
    $pesan = trim($_POST['pesan'] ?? '');

    // Validasi input
    if (empty($pesan) || empty($ws_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Pesan tidak boleh kosong.']);
        exit;
    }

    try {
        // 3. Simpan ke Database
        $stmt_chat = $pdo->prepare("INSERT INTO workshop_discussions (workshop_id, user_id, message) VALUES (?, ?, ?)");
        $stmt_chat->execute([$ws_id, $user_id, $pesan]);

        // 4. Berhasil
        echo json_encode([
            'status' => 'success',
            'timestamp' => date('H:i'), // Kirim waktu server untuk update UI
            'message' => 'Pesan terkirim'
        ]);
    } catch (PDOException $e) {
        // Gagal Database
        echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
}
?>