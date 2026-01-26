<?php
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

$no_whatsapp = $_POST['no_whatsapp'] ?? null;
if (!$no_whatsapp) {
    send_json_response('error', 'Nomor WhatsApp wajib diisi.');
}

try {
    $role_target = '';
    $user_id = 0;

    // 1. CEK DI TABEL USERS (ADMIN) DULU
    $stmt = $pdo->prepare("SELECT id FROM users WHERE no_whatsapp = ?");
    $stmt->execute([$no_whatsapp]);
    $admin = $stmt->fetch();

    if ($admin) {
        $role_target = 'admin';
        $user_id = $admin['id'];
    } else {
        // 2. KALAU GAK ADA DI ADMIN, CEK DI SANTRI (WALI)
        $stmtSantri = $pdo->prepare("SELECT id FROM santri WHERE no_hp_wali = ?");
        $stmtSantri->execute([$no_whatsapp]);
        $santri = $stmtSantri->fetch();

        if ($santri) {
            $role_target = 'santri';
            $user_id = $santri['id'];
        } else {
            send_json_response('error', 'Nomor WhatsApp tidak terdaftar (Admin maupun Wali Santri).');
        }
    }

    // 3. GENERATE OTP
    $otpCode = rand(100000, 999999);
    $expiresAt = date('Y-m-d H:i:s', time() + (5 * 60)); // 5 Menit

    // 4. SIMPAN OTP KE TABEL YANG SESUAI
    if ($role_target == 'admin') {
        $stmt_update = $pdo->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE id = ?");
    } else {
        $stmt_update = $pdo->prepare("UPDATE santri SET otp_code = ?, otp_expires_at = ? WHERE id = ?");
    }
    $stmt_update->execute([$otpCode, $expiresAt, $user_id]);

    // 5. KIRIM VIA FONNTE
    $fonnte_token = 'eSJDYxaMoxjNvy8vTuDy'; // GANTI TOKEN SESUAI MILIKMU
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
            'target' => $no_whatsapp,
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

    send_json_response('success', 'Kode OTP terkirim ke WhatsApp Anda.', $response);

} catch (PDOException $e) {
    send_json_response('error', 'DB Error: ' . $e->getMessage());
}
?>