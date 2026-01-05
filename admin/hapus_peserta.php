<?php
session_start();
require_once '../core/koneksi.php';

// Cek apakah user sudah login dan memiliki akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penyelenggara') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Cek method request
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get ID dari URL parameter
$pendaftaran_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($pendaftaran_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit();
}

try {
    // Cek apakah pendaftaran exist dan user memiliki akses
    $stmt = $pdo->prepare("
        SELECT p.*, w.penyelenggara_id 
        FROM pendaftaran p 
        JOIN workshops w ON p.workshop_id = w.id 
        WHERE p.id = ? AND w.penyelenggara_id = ?
    ");
    $stmt->execute([$pendaftaran_id, $_SESSION['penyelenggara_id_bersama']]);
    $pendaftaran = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pendaftaran) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Data pendaftaran tidak ditemukan']);
        exit();
    }

    // Hapus data terkait terlebih dahulu (jika ada)
    $pdo->beginTransaction();

    // Hapus data pendaftaran_data (form fields)
    $stmt_data = $pdo->prepare("DELETE FROM pendaftaran_data WHERE pendaftaran_id = ?");
    $stmt_data->execute([$pendaftaran_id]);

    // Hapus pendaftaran utama
    $stmt_hapus = $pdo->prepare("DELETE FROM pendaftaran WHERE id = ?");
    $stmt_hapus->execute([$pendaftaran_id]);

    $pdo->commit();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Peserta berhasil dihapus dari event'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
?>