<?php
// ========================================================================
// 1. KONEKSI DATABASE & KONFIGURASI
// ========================================================================
require_once 'core/koneksi.php';
$apiKey = 'cc7e768f19d886126b3ef8b1babe81b8'; // Ganti dengan API key Anda

// ========================================================================
// 2. [PERUBAHAN KUNCI] AMBIL DATA DARI $_POST, BUKAN JSON
// ========================================================================
$merchantCode    = isset($_POST['merchantCode']) ? $_POST['merchantCode'] : null;
$amount          = isset($_POST['amount']) ? $_POST['amount'] : null;
$merchantOrderId = isset($_POST['merchantOrderId']) ? $_POST['merchantOrderId'] : null;
$resultCode      = isset($_POST['resultCode']) ? $_POST['resultCode'] : null;
$signature       = isset($_POST['signature']) ? $_POST['signature'] : null;

// ========================================================================
// 3. VALIDASI PARAMETER DASAR
// ========================================================================
if (!empty($merchantCode) && !empty($amount) && !empty($merchantOrderId) && !empty($signature)) {

    // ========================================================================
    // 4. VALIDASI SIGNATURE
    // ========================================================================
    $params = $merchantCode . $amount . $merchantOrderId . $apiKey;
    $calculatedSignature = md5($params);

    if ($signature == $calculatedSignature) {
        // Signature valid, lanjutkan proses
        try {
            if ($resultCode === '00') {
                // Status Pembayaran SUKSES
                $status = 'paid';
            } else {
                // Status Pembayaran GAGAL
                $status = 'failed';
            }

            // Update status di database Anda
            $stmt = $pdo->prepare("UPDATE pendaftaran SET status_pembayaran = ? WHERE kode_unik = ?");
            $stmt->execute([$status, $merchantOrderId]);
            
            // Beri respons OK ke Duitku agar tidak ada notifikasi berulang
            http_response_code(200);
            echo "OK";

            // Catat log sukses (opsional)
            $logMessage = date('Y-m-d H:i:s') . " | SUCCESS | Order: {$merchantOrderId} | Status: {$status} | ResultCode: {$resultCode}" . PHP_EOL;
            file_put_contents(__DIR__ . '/callback_log.txt', $logMessage, FILE_APPEND);

        } catch (Exception $e) {
            // Tangani jika ada error database
            http_response_code(500);
            echo "Database Error";
            
            // Catat log error database
            $logMessage = date('Y-m-d H:i:s') . " | DATABASE ERROR | Order: {$merchantOrderId} | Error: " . $e->getMessage() . PHP_EOL;
            file_put_contents(__DIR__ . '/callback_log.txt', $logMessage, FILE_APPEND);
        }

    } else {
        // Signature tidak valid
        http_response_code(400);
        echo "Bad Signature";

        // Catat log signature salah
        $logMessage = date('Y-m-d H:i:s') . " | BAD SIGNATURE | Order: {$merchantOrderId}" . PHP_EOL;
        file_put_contents(__DIR__ . '/callback_log.txt', $logMessage, FILE_APPEND);
    }

} else {
    // Parameter tidak lengkap
    http_response_code(400);
    echo "Bad Parameter";
    
    // Catat log parameter tidak lengkap
    $logMessage = date('Y-m-d H:i:s') . " | BAD PARAMETER | Received Data: " . file_get_contents('php://input') . PHP_EOL;
    file_put_contents(__DIR__ . '/callback_log.txt', $logMessage, FILE_APPEND);
}
?>