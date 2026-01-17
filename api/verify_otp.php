<?php
session_start();
require_once '../core/koneksi.php';

header('Content-Type: application/json');

function send_json_response($status, $message, $data = null)
{
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response('error', 'Metode request tidak valid.');
}

$no_whatsapp = $_POST['no_whatsapp'] ?? null;
$otp_code = $_POST['otp_code'] ?? null;

if (!$no_whatsapp || !$otp_code) {
    send_json_response('error', 'Nomor WhatsApp dan OTP wajib diisi.');
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE no_whatsapp = ? AND otp_code = ?");
    $stmt->execute([$no_whatsapp, $otp_code]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        send_json_response('error', 'Kode OTP salah.');
    }

    // Cek kedaluwarsa
    if (strtotime($user['otp_expires_at']) < time()) {
        send_json_response('error', 'Kode OTP telah kedaluwarsa. Silakan minta lagi.');
    }

    // --- SESI LOGIN (DISESUAIKAN DENGAN DASHBOARD & LOGIN EMAIL) ---

    // 1. Tentukan ID Bersama (Logic bawaan Anda)
    $id_bersama = $user['owner_id'] ? $user['owner_id'] : $user['id'];

    // 2. Set Session Wajib
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['owner_id'] = $user['owner_id'];
    $_SESSION['penyelenggara_id_bersama'] = $id_bersama;

    // 3. Set Session Data Diri
    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
    $_SESSION['email'] = $user['email'];

    // --- PERBAIKAN DI SINI ---
    if (!empty($user['foto_profil'])) {
        // Kita tambahkan folder 'assets/uploads/profil/' di depan nama file dari database
        $_SESSION['foto_profil'] = 'assets/uploads/profil/' . $user['foto_profil'];
    } else {
        // Default jika tidak ada foto
        $_SESSION['foto_profil'] = 'assets/img/default-avatar.png';
    }

    // Bersihkan OTP setelah digunakan
    $stmt_clear = $pdo->prepare("UPDATE users SET otp_code = NULL, otp_expires_at = NULL WHERE id = ?");
    $stmt_clear->execute([$user['id']]);

    send_json_response('success', 'Login berhasil!');

} catch (PDOException $e) {
    send_json_response('error', 'Terjadi masalah pada database: ' . $e->getMessage());
}
?>