<?php
session_start();
require_once __DIR__ . '/../core/koneksi.php';

header('Content-Type: application/json');

// Cek Login User
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ws_id = $_POST['workshop_id'] ?? 0;
    $pesan = trim($_POST['pesan'] ?? '');

    if (empty($pesan) || empty($ws_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Pesan tidak boleh kosong']);
        exit;
    }

    try {
        // 1. Validasi: Cek apakah diskusi aktif
        $stmt_check = $pdo->prepare("SELECT is_diskusi_active FROM workshops WHERE id = ?");
        $stmt_check->execute([$ws_id]);
        $ws = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if (!$ws) {
            echo json_encode(['status' => 'error', 'message' => 'Event tidak ditemukan']);
            exit;
        }

        if ($ws['is_diskusi_active'] == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Maaf, ruang diskusi sedang dikunci oleh penyelenggara.']);
            exit;
        }

        // 2. Simpan Pesan
        $stmt_chat = $pdo->prepare("INSERT INTO workshop_discussions (workshop_id, user_id, message) VALUES (?, ?, ?)");
        $stmt_chat->execute([$ws_id, $user_id, $pesan]);

        echo json_encode([
            'status' => 'success',
            'timestamp' => date('H:i'),
            'message' => 'Pesan terkirim'
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>