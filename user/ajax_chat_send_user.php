<?php
// user/ajax_chat_send_user.php

if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once __DIR__ . '/../core/koneksi.php';

header('Content-Type: application/json');

// 1. CEK LOGIN SANTRI
if (!isset($_SESSION['santri_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Silakan login kembali.']);
    exit;
}

$user_id = $_SESSION['santri_id']; // Ambil ID Santri

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ws_id = $_POST['workshop_id'] ?? 0;
    $pesan = trim($_POST['pesan'] ?? '');

    // Default user_type 'santri' karena ini file di folder user/
    $user_type = $_POST['user_type'] ?? 'santri';

    if (empty($pesan) || empty($ws_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Pesan tidak boleh kosong']);
        exit;
    }

    try {
        // 2. VALIDASI: Cek Status Diskusi di Workshop
        $stmt_check = $pdo->prepare("SELECT is_diskusi_active FROM workshops WHERE id = ?");
        $stmt_check->execute([$ws_id]);
        $ws = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if (!$ws) {
            echo json_encode(['status' => 'error', 'message' => 'Workshop tidak ditemukan']);
            exit;
        }

        if ($ws['is_diskusi_active'] == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Maaf, diskusi sedang dikunci oleh admin.']);
            exit;
        }

        // 3. SIMPAN PESAN
        // Kita masukkan user_type agar sistem tahu ini chat dari santri
        $stmt_chat = $pdo->prepare("INSERT INTO workshop_discussions (workshop_id, user_id, user_type, message) VALUES (?, ?, ?, ?)");
        $stmt_chat->execute([$ws_id, $user_id, $user_type, $pesan]);

        echo json_encode([
            'status' => 'success',
            'timestamp' => date('H:i'), // Format jam untuk update UI real-time
            'message' => 'Pesan terkirim'
        ]);

    } catch (PDOException $e) {
        // Log error jika perlu, tapi jangan tampilkan detail error database ke user
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan sistem.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
}
?>