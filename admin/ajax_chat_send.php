<?php
// admin/ajax_chat_send.php

session_start();
require_once __DIR__ . '/../core/koneksi.php';

// Set header JSON
header('Content-Type: application/json');

// 1. Cek Sesi Login (Harus Penyelenggara/Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penyelenggara') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Cek Method POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ws_id = $_POST['workshop_id'] ?? 0;
    $pesan = trim($_POST['pesan'] ?? '');

    // Validasi input
    if (empty($pesan) || empty($ws_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Pesan tidak boleh kosong.']);
        exit;
    }

    try {
        // 3. Simpan ke Database
        // PENTING: Tambahkan user_type = 'admin'
        $stmt_chat = $pdo->prepare("INSERT INTO workshop_discussions (workshop_id, user_id, user_type, message) VALUES (?, ?, 'admin', ?)");
        $stmt_chat->execute([$ws_id, $user_id, $pesan]);

        // 4. Berhasil
        echo json_encode([
            'status' => 'success',
            'timestamp' => date('H:i'), // Kirim waktu server
            'message' => 'Pesan terkirim'
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
}
?>