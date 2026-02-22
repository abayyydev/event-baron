<?php
// api/verify_otp.php

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

$raw_wa = $_POST['no_whatsapp'] ?? null;
$otp_code = $_POST['otp_code'] ?? null;

if (!$raw_wa || !$otp_code) {
    send_json_response('error', 'Data tidak lengkap.');
}

// --- LOGIKA NORMALISASI NOMOR (COPY DARI REQUEST_OTP) ---
$clean_num = preg_replace('/[^0-9]/', '', $raw_wa);

if (substr($clean_num, 0, 3) == '620') {
    $wa_db = '0' . substr($clean_num, 3);
} elseif (substr($clean_num, 0, 2) == '62') {
    $wa_db = '0' . substr($clean_num, 2);
} elseif (substr($clean_num, 0, 1) == '0') {
    $wa_db = $clean_num;
} else {
    $wa_db = '0' . $clean_num;
}
$wa_api = '62' . substr($wa_db, 1);

try {
    // 1. CEK DI TABEL USERS (ADMIN)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE (no_whatsapp = ? OR no_whatsapp = ?) AND otp_code = ?");
    $stmt->execute([$wa_db, $wa_api, $otp_code]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (strtotime($user['otp_expires_at']) < time()) {
            send_json_response('error', 'Kode OTP kadaluwarsa.');
        }

        // Set Session Admin
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['owner_id'] = $user['owner_id'];
        $_SESSION['penyelenggara_id_bersama'] = $user['owner_id'] ? $user['owner_id'] : $user['id'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['email'] = $user['email'];

        if (!empty($user['foto_profil'])) {
            $_SESSION['foto_profil'] = (strpos($user['foto_profil'], 'assets/') !== false) ? $user['foto_profil'] : 'assets/uploads/profil/' . $user['foto_profil'];
        } else {
            $_SESSION['foto_profil'] = 'assets/img/download.jpg';
        }

        // Reset OTP
        $pdo->prepare("UPDATE users SET otp_code = NULL WHERE id = ?")->execute([$user['id']]);
        send_json_response('success', 'Login Berhasil!', 'admin');
    }

    // 2. CEK DI TABEL SANTRI
    else {
        $stmtSantri = $pdo->prepare("SELECT * FROM santri WHERE (no_hp_wali = ? OR no_hp_wali = ?) AND otp_code = ?");
        $stmtSantri->execute([$wa_db, $wa_api, $otp_code]);
        $santri = $stmtSantri->fetch(PDO::FETCH_ASSOC);

        if ($santri) {
            if (strtotime($santri['otp_expires_at']) < time()) {
                send_json_response('error', 'Kode OTP kadaluwarsa.');
            }

            // Set Session Santri
            $_SESSION['santri_id'] = $santri['id'];
            $_SESSION['user_id'] = $santri['id'];
            $_SESSION['nama_lengkap'] = $santri['nama_lengkap'];
            $_SESSION['role'] = 'peserta';
            $_SESSION['nis'] = $santri['nis'];
            $_SESSION['barcode_code'] = $santri['barcode_code'];

            if (!empty($santri['foto_santri'])) {
                $_SESSION['foto_profil'] = (strpos($santri['foto_santri'], 'assets/') !== false) ? $santri['foto_santri'] : 'assets/uploads/santri/' . $santri['foto_santri'];
            } else {
                $_SESSION['foto_profil'] = 'assets/img/avatar-santri.png';
            }

            // Reset OTP
            $pdo->prepare("UPDATE santri SET otp_code = NULL WHERE id = ?")->execute([$santri['id']]);
            send_json_response('success', 'Login Berhasil!', 'user');
        } else {
            send_json_response('error', 'Kode OTP salah atau nomor tidak cocok.');
        }
    }

} catch (PDOException $e) {
    send_json_response('error', 'DB Error: ' . $e->getMessage());
}
?>