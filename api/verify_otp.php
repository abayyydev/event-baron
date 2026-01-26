<?php
session_start();
require_once '../core/koneksi.php';

header('Content-Type: application/json');

function send_json_response($status, $message, $role_redirect = null)
{
    echo json_encode(['status' => $status, 'message' => $message, 'redirect' => $role_redirect]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response('error', 'Metode salah.');
}

$no_whatsapp = $_POST['no_whatsapp'] ?? null;
$otp_code = $_POST['otp_code'] ?? null;

if (!$no_whatsapp || !$otp_code) {
    send_json_response('error', 'Data tidak lengkap.');
}

try {
    // 1. CEK DI TABEL USERS (ADMIN)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE no_whatsapp = ? AND otp_code = ?");
    $stmt->execute([$no_whatsapp, $otp_code]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // --- LOGIC ADMIN ---
        if (strtotime($user['otp_expires_at']) < time()) {
            send_json_response('error', 'Kode OTP kadaluwarsa.');
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['owner_id'] = $user['owner_id'];
        $_SESSION['penyelenggara_id_bersama'] = $user['owner_id'] ? $user['owner_id'] : $user['id'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['email'] = $user['email'];

        // Handle Foto
        if (!empty($user['foto_profil'])) {
            $_SESSION['foto_profil'] = (strpos($user['foto_profil'], 'assets/') !== false)
                ? $user['foto_profil']
                : 'assets/uploads/profil/' . $user['foto_profil'];
        } else {
            $_SESSION['foto_profil'] = 'assets/img/download.jpg';
        }

        // Bersihkan OTP
        $pdo->prepare("UPDATE users SET otp_code = NULL WHERE id = ?")->execute([$user['id']]);
        send_json_response('success', 'Login Berhasil!', 'admin'); // Redirect ke Admin
    }

    // 2. JIKA BUKAN ADMIN, CEK DI TABEL SANTRI
    else {
        $stmtSantri = $pdo->prepare("SELECT * FROM santri WHERE no_hp_wali = ? AND otp_code = ?");
        $stmtSantri->execute([$no_whatsapp, $otp_code]);
        $santri = $stmtSantri->fetch(PDO::FETCH_ASSOC);

        if ($santri) {
            // --- LOGIC SANTRI ---
            if (strtotime($santri['otp_expires_at']) < time()) {
                send_json_response('error', 'Kode OTP kadaluwarsa.');
            }

            $_SESSION['santri_id'] = $santri['id'];
            $_SESSION['user_id'] = $santri['id']; // Fallback
            $_SESSION['nama_lengkap'] = $santri['nama_lengkap'];
            $_SESSION['role'] = 'peserta';
            $_SESSION['nis'] = $santri['nis'];
            $_SESSION['barcode_code'] = $santri['barcode_code'];

            // Handle Foto
            if (!empty($santri['foto_santri'])) {
                $_SESSION['foto_profil'] = (strpos($santri['foto_santri'], 'assets/') !== false)
                    ? $santri['foto_santri']
                    : 'assets/uploads/santri/' . $santri['foto_santri'];
            } else {
                $_SESSION['foto_profil'] = 'assets/img/avatar-santri.png';
            }

            // Bersihkan OTP
            $pdo->prepare("UPDATE santri SET otp_code = NULL WHERE id = ?")->execute([$santri['id']]);
            send_json_response('success', 'Login Berhasil!', 'user'); // Redirect ke User
        } else {
            send_json_response('error', 'Kode OTP salah atau nomor tidak cocok.');
        }
    }

} catch (PDOException $e) {
    send_json_response('error', 'DB Error: ' . $e->getMessage());
}
?>