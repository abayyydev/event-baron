<?php
// admin/proses_tambah_peserta_internal.php

require_once '../core/koneksi.php';

header('Content-Type: application/json');

// 1. Validasi Request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
    exit;
}

$event_id = $_POST['workshop_id'] ?? 0;
$santri_ids = $_POST['santri_ids'] ?? [];

if (empty($event_id) || empty($santri_ids)) {
    echo json_encode(['status' => 'error', 'message' => 'Tidak ada santri yang dipilih.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 2. Ambil Data Santri Terpilih (Sekarang termasuk jenis_kelamin)
    $placeholders = implode(',', array_fill(0, count($santri_ids), '?'));

    $stmt_santri = $pdo->prepare("SELECT * FROM santri WHERE id IN ($placeholders)");
    $stmt_santri->execute($santri_ids);
    $data_santri = $stmt_santri->fetchAll(PDO::FETCH_ASSOC);

    // 3. Siapkan Query Insert & Cek Duplikat
    $stmt_check = $pdo->prepare("SELECT id FROM pendaftaran WHERE workshop_id = ? AND santri_id = ?");

    // Perhatikan: Kolom 'jenis_kelamin' ikut di-insert
    $sql_insert = "INSERT INTO pendaftaran 
        (workshop_id, santri_id, kode_unik, nama_peserta, email_peserta, telepon_peserta, jenis_kelamin, 
         status_pembayaran, status_kehadiran, didaftarkan_oleh) 
        VALUES 
        (:eid, :sid, :kode, :nama, :email, :telp, :jk, 'free', 'absen', 'admin')";

    $stmt_insert = $pdo->prepare($sql_insert);

    $count_success = 0;
    $count_skip = 0;

    foreach ($data_santri as $s) {
        // A. Cek apakah sudah terdaftar
        $stmt_check->execute([$event_id, $s['id']]);
        if ($stmt_check->rowCount() > 0) {
            $count_skip++;
            continue; // Skip jika sudah ada
        }

        // B. Generate Data
        $kode_unik = "WS-" . $event_id . "-" . strtoupper(bin2hex(random_bytes(3)));

        // Email Dummy jika santri tidak punya email
        $email_insert = !empty($s['email']) ? $s['email'] : $s['nis'] . '@santri.ponpes';

        // Pastikan jenis kelamin diambil dari data santri
        // Jika di data santri kosong/null, default ke 'Laki-laki'
        $jenis_kelamin = !empty($s['jenis_kelamin']) ? $s['jenis_kelamin'] : 'Laki-laki';

        // C. Eksekusi Insert
        $stmt_insert->execute([
            'eid' => $event_id,
            'sid' => $s['id'],
            'kode' => $kode_unik,
            'nama' => $s['nama_lengkap'],
            'email' => $email_insert,
            'telp' => $s['no_hp_wali'],
            'jk' => $jenis_kelamin // <--- Ini yang baru
        ]);

        $count_success++;
    }

    $pdo->commit();

    // Pesan feedback
    $msg = "$count_success santri berhasil ditambahkan.";
    if ($count_skip > 0) {
        $msg .= " ($count_skip santri dilewati karena sudah terdaftar)";
    }

    echo json_encode(['status' => 'success', 'message' => $msg]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>