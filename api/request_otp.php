<?php
session_start();
require_once '../core/koneksi.php';

header('Content-Type: application/json');

// 🔹 Fungsi kirim JSON response (dengan opsi debug)
function send_json_response($status, $message, $debug = null)
{
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'debug' => $debug
    ]);
    exit();
}

// 🔹 Cek metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response('error', 'Metode request tidak valid.');
}

$no_whatsapp = $_POST['no_whatsapp'] ?? null;
if (!$no_whatsapp) {
    send_json_response('error', 'Nomor WhatsApp wajib diisi.');
}

try {
    // 🔹 Cek apakah nomor terdaftar
    $stmt = $pdo->prepare("SELECT id FROM users WHERE no_whatsapp = ?");
    $stmt->execute([$no_whatsapp]);
    if ($stmt->rowCount() === 0) {
        send_json_response('error', 'Nomor WhatsApp tidak terdaftar.');
    }

    // 🔹 Buat OTP dan waktu kedaluwarsa (5 menit)
    $otpCode = rand(100000, 999999);
    $expiresAt = date('Y-m-d H:i:s', time() + (5 * 60));

    // 🔹 Simpan OTP ke database
    $stmt_update = $pdo->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE no_whatsapp = ?");
    $stmt_update->execute([$otpCode, $expiresAt, $no_whatsapp]);

    // 🔹 Kirim OTP via Fonnte
    $fonnte_token = 'Z8uhStEJetBt3v6Hhau8'; // 🔸 GANTI dengan token kamu yang valid
    $message = "Kode OTP Anda adalah: *{$otpCode}*. Jangan berikan kode ini kepada siapapun.";

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

    // 🔹 Logging ke file
    $logPath = __DIR__ . '/log_fonnte.txt';
    $logData = date('Y-m-d H:i:s') . " | Nomor: {$no_whatsapp} | OTP: {$otpCode} | CurlError: {$curlError} | Response: {$response}\n";
    file_put_contents($logPath, $logData, FILE_APPEND);

    // 🔹 Tangani error dari cURL
    if ($curlError) {
        send_json_response('error', 'Gagal mengirim OTP: ' . $curlError, $response);
    }

    // 🔹 Decode hasil dari Fonnte
    $fonnteResult = json_decode($response, true);
    if (!$fonnteResult || (isset($fonnteResult['status']) && $fonnteResult['status'] == false)) {
        $reason = $fonnteResult['reason'] ?? 'Tidak diketahui';
        send_json_response('error', 'Gagal kirim OTP: ' . $reason, $response);
    }

    // 🔹 Sukses kirim OTP
    send_json_response('success', 'OTP telah dikirim ke nomor WhatsApp Anda.', $response);

} catch (PDOException $e) {
    send_json_response('error', 'Terjadi masalah pada database: ' . $e->getMessage());
}
?>