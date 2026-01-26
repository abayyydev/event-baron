<?php
// admin/proses_checkin.php

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
require_once BASE_PATH . '/core/koneksi.php';

session_start();
header('Content-Type: application/json');

// Cek Login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'penyelenggara') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized Access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
    exit;
}

$kode_scan = trim($_POST['kode_unik'] ?? '');
$event_id = $_POST['event_id'] ?? 0; // Wajib ada untuk validasi Kartu Santri

if (empty($kode_scan)) {
    echo json_encode(['status' => 'error', 'message' => 'Kode QR tidak terbaca.']);
    exit;
}

try {
    // --- LOGIKA PENCARIAN CERDAS ---

    // SKENARIO 1: Scan menggunakan E-TICKET (Kode Unik Transaksi)
    // Format biasanya: WS-1-XXXXXX
    $sql = "SELECT p.*, w.judul, w.jam_selesai, w.nominal_denda 
            FROM pendaftaran p 
            JOIN workshops w ON p.workshop_id = w.id 
            WHERE p.kode_unik = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$kode_scan]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // SKENARIO 2: Scan menggunakan KARTU SANTRI (Barcode Santri)
    // Jika Skenario 1 gagal, kita cek apakah ini barcode santri
    if (!$data && !empty($event_id)) {
        // Cari pendaftaran berdasarkan Barcode Santri DAN Event ID yang sedang aktif
        $sql_santri = "SELECT p.*, w.judul, w.jam_selesai, w.nominal_denda 
                       FROM pendaftaran p
                       JOIN workshops w ON p.workshop_id = w.id
                       JOIN santri s ON p.santri_id = s.id
                       WHERE s.barcode_code = ? AND p.workshop_id = ?";

        $stmt_santri = $pdo->prepare($sql_santri);
        $stmt_santri->execute([$kode_scan, $event_id]);
        $data = $stmt_santri->fetch(PDO::FETCH_ASSOC);
    }

    // Jika masih tidak ketemu
    if (!$data) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan. Pastikan Santri sudah terdaftar di event ini.']);
        exit;
    }

    // --- LOGIKA PROSES (SAMA SEPERTI SEBELUMNYA) ---

    $now = date('Y-m-d H:i:s');
    $waktu_sekarang = time();
    $response = [];

    // A. LOGIKA CHECK-IN
    // Cek jika status kehadiran belum 'hadir' ATAU belum ada jam checkin
    if (empty($data['check_in_at']) || $data['status_kehadiran'] != 'hadir') {

        $upd = $pdo->prepare("UPDATE pendaftaran SET check_in_at = ?, status_kehadiran = 'hadir' WHERE id = ?");
        $upd->execute([$now, $data['id']]);

        $response = [
            'status' => 'success',
            'type' => 'checkin',
            'data' => [
                'nama_peserta' => $data['nama_peserta'],
                'event' => $data['judul'],
                'waktu' => date('H:i', $waktu_sekarang)
            ],
            'message' => 'Check-in Berhasil!'
        ];
    }
    // B. LOGIKA CHECK-OUT
    else if (empty($data['check_out_at'])) {

        // Cek Denda
        $jam_selesai_event = strtotime(date('Y-m-d') . ' ' . $data['jam_selesai']);
        $kena_denda = false;

        if ($data['nominal_denda'] > 0 && $waktu_sekarang > $jam_selesai_event) {
            $kena_denda = true;
        }

        if ($kena_denda && $data['status_denda'] != 'lunas') {
            // Update status denda
            $upd = $pdo->prepare("UPDATE pendaftaran SET status_denda = 'kena_denda' WHERE id = ?");
            $upd->execute([$data['id']]);

            echo json_encode([
                'status' => 'error', // UI Merah/Warning
                'type' => 'denda',
                'message' => 'Terlambat Check-out! Denda: Rp ' . number_format($data['nominal_denda'], 0, ',', '.')
            ]);
            exit;
        } else {
            // Check-out Normal
            $upd = $pdo->prepare("UPDATE pendaftaran SET check_out_at = ? WHERE id = ?");
            $upd->execute([$now, $data['id']]);

            $response = [
                'status' => 'success',
                'type' => 'checkout',
                'data' => [
                    'nama_peserta' => $data['nama_peserta'],
                    'event' => $data['judul'],
                    'waktu' => date('H:i', $waktu_sekarang)
                ],
                'message' => 'Check-out Berhasil!'
            ];
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Peserta ini sudah selesai (Check-out).']);
        exit;
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>