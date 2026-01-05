<?php
// admin/proses_checkin.php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
require_once BASE_PATH . '/core/koneksi.php';

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
    exit;
}

$pendaftaran_id = $_POST['pendaftaran_id'] ?? null;
// Bisa support scan by ID Pendaftaran atau Kode Unik (tergantung scanner)
// Anggap scanner mengirim pendaftaran_id atau kode_unik

if (!$pendaftaran_id) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak terbaca.']);
    exit;
}

try {
    // 1. AMBIL DATA PENDAFTARAN & EVENT
    // Kita cari berdasarkan ID atau Kode Unik
    $sql = "SELECT p.*, w.judul, w.jam_selesai, w.nominal_denda 
            FROM pendaftaran p 
            JOIN workshops w ON p.workshop_id = w.id 
            WHERE p.id = ? OR p.kode_unik = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$pendaftaran_id, $pendaftaran_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        echo json_encode(['status' => 'error', 'message' => 'Peserta tidak ditemukan.']);
        exit;
    }

    $now = date('Y-m-d H:i:s');
    $response = [];

    // 2. LOGIKA CHECK-IN / CHECK-OUT

    // A. JIKA BELUM CHECK-IN -> LAKUKAN CHECK-IN
    if (empty($data['check_in_at'])) {
        $upd = $pdo->prepare("UPDATE pendaftaran SET check_in_at = ?, status_kehadiran = 'hadir' WHERE id = ?");
        $upd->execute([$now, $data['id']]);

        echo json_encode([
            'status' => 'success',
            'type' => 'checkin',
            'nama' => $data['nama_peserta'],
            'waktu' => date('H:i', strtotime($now)),
            'message' => 'Berhasil Check-in!'
        ]);
        exit;
    }

    // B. JIKA SUDAH CHECK-IN TAPI BELUM CHECK-OUT -> CEK DENDA
    else if (empty($data['check_out_at'])) {

        // Cek Apakah Melebihi Batas Waktu
        $batas_waktu = $data['jam_selesai'];
        $kena_denda = false;

        // Logic Denda: Jika waktu sekarang > jam selesai DAN denda > 0
        if ($data['nominal_denda'] > 0 && strtotime($now) > strtotime($batas_waktu)) {
            $kena_denda = true;
        }

        if ($kena_denda) {
            // JANGAN UPDATE check_out_at dulu, tapi kirim status DENDA
            // Update status denda di database
            $upd = $pdo->prepare("UPDATE pendaftaran SET status_denda = 'kena_denda' WHERE id = ?");
            $upd->execute([$data['id']]);

            echo json_encode([
                'status' => 'denda', // Trigger Popup Denda di JS
                'nama' => $data['nama_peserta'],
                'denda' => number_format($data['nominal_denda'], 0, ',', '.'),
                'nominal_raw' => $data['nominal_denda'],
                'batas' => date('H:i d M', strtotime($batas_waktu)),
                'message' => 'Melebihi Batas Waktu!'
            ]);
            exit;
        } else {
            // Lakukan Check-out Normal
            $upd = $pdo->prepare("UPDATE pendaftaran SET check_out_at = ? WHERE id = ?");
            $upd->execute([$now, $data['id']]);

            echo json_encode([
                'status' => 'success',
                'type' => 'checkout',
                'nama' => $data['nama_peserta'],
                'waktu' => date('H:i', strtotime($now)),
                'message' => 'Berhasil Check-out!'
            ]);
            exit;
        }
    }

    // C. JIKA SUDAH CHECK-OUT SEBELUMNYA
    else {
        echo json_encode(['status' => 'error', 'message' => 'Peserta ini sudah Check-out sebelumnya.']);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>