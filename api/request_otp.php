<?php
// api/request_otp.php

session_start();
require_once '../core/koneksi.php';

header('Content-Type: application/json');

function send_json_response($status, $message, $debug = null)
{
    echo json_encode(['status' => $status, 'message' => $message, 'debug' => $debug]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response('error', 'Metode request tidak valid.');
}

$raw_wa = $_POST['no_whatsapp'] ?? null;

if (!$raw_wa) {
    send_json_response('error', 'Nomor WhatsApp wajib diisi.');
}

// --- LOGIKA NORMALISASI NOMOR (ANTI ERROR) ---
// 1. Hapus semua karakter selain angka
$clean_num = preg_replace('/[^0-9]/', '', $raw_wa);

// 2. Deteksi dan Perbaiki Format
if (substr($clean_num, 0, 3) == '620') {
    // Kasus: 620858... (Input 08, tapi JS nambah 62) -> Ambil 0858...
    $wa_db = '0' . substr($clean_num, 3);
} elseif (substr($clean_num, 0, 2) == '62') {
    // Kasus: 62858... (Format Internasional) -> Ubah ke 0858...
    $wa_db = '0' . substr($clean_num, 2);
} elseif (substr($clean_num, 0, 1) == '0') {
    // Kasus: 0858... (Format Lokal) -> Biarkan
    $wa_db = $clean_num;
} else {
    // Kasus: 858... (Tanpa awalan) -> Tambah 0
    $wa_db = '0' . $clean_num;
}

// 3. Generate Format API (Harus 62...)
// Apapun format $wa_db (pasti 08...), ubah ke 62...
$wa_api = '62' . substr($wa_db, 1);

try {
    $role_target = '';
    $user_id = 0;

    // 1. CEK DATABASE (Cari format 08... ATAU 62... biar aman)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE no_whatsapp = ? OR no_whatsapp = ?");
    $stmt->execute([$wa_db, $wa_api]);
    $admin = $stmt->fetch();

    if ($admin) {
        $role_target = 'admin';
        $user_id = $admin['id'];
    } else {
        $stmtSantri = $pdo->prepare("SELECT id FROM santri WHERE no_hp_wali = ? OR no_hp_wali = ?");
        $stmtSantri->execute([$wa_db, $wa_api]);
        $santri = $stmtSantri->fetch();

        if ($santri) {
            $role_target = 'santri';
            $user_id = $santri['id'];
        } else {
            send_json_response('error', 'Nomor WhatsApp tidak terdaftar. Pastikan nomor sesuai dengan database (Admin/Wali).');
        }
    }

    // 2. GENERATE OTP
    $otpCode = rand(100000, 999999);
    $expiresAt = date('Y-m-d H:i:s', time() + (5 * 60)); // 5 Menit

    // 3. SIMPAN OTP
    if ($role_target == 'admin') {
        $stmt_update = $pdo->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE id = ?");
    } else {
        $stmt_update = $pdo->prepare("UPDATE santri SET otp_code = ?, otp_expires_at = ? WHERE id = ?");
    }
    $stmt_update->execute([$otpCode, $expiresAt, $user_id]);

    // 4. KIRIM VIA FONNTE (Pakai $wa_api yang 62...)
    $fonnte_token = 'eSJDYxaMoxjNvy8vTuDy'; // GANTI TOKEN ANDA
    $message = "🔐 *Kode Login Sistem Event*\n\nKode OTP Anda: *{$otpCode}*\nBerlaku selama 5 menit.\n\n_Ponpes Al Ihsan Baron_";

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(
            'target' => $wa_api,
            'message' => $message
        ),
        CURLOPT_HTTPHEADER => array("Authorization: {$fonnte_token}"),
    ));

    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($curlError) {
        send_json_response('error', 'Gagal koneksi Fonnte: ' . $curlError);
    }

    send_json_response('success', 'Kode OTP terkirim ke WhatsApp Anda.');

} catch (PDOException $e) {
    send_json_response('error', 'DB Error: ' . $e->getMessage());
}
?>