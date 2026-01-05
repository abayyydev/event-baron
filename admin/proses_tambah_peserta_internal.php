<?php
// admin/proses_tambah_peserta_internal.php
if (!defined('BASE_PATH'))
    define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/core/koneksi.php';
session_start();

// Validasi Akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'penyelenggara') {
    die("Akses ditolak.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_ids'])) {
    $workshop_id = $_POST['workshop_id'];
    $user_ids = $_POST['user_ids']; // Ini array [1, 5, 10, ...]

    $sukses = 0;

    try {
        $pdo->beginTransaction();

        // Siapkan query insert sekali saja di luar loop untuk efisiensi prepare
        $sql_ins = "INSERT INTO pendaftaran (workshop_id, user_id, kode_unik, nama_peserta, email_peserta, telepon_peserta, jenis_kelamin, status_pembayaran) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'free')";
        $stmt_ins = $pdo->prepare($sql_ins);

        // Loop setiap ID User yang dicentang
        foreach ($user_ids as $uid) {
            // 1. Ambil Data User Lengkap
            $stmt_u = $pdo->prepare("SELECT nama_lengkap, email, no_whatsapp, jenis_kelamin FROM users WHERE id = ?");
            $stmt_u->execute([$uid]);
            $user = $stmt_u->fetch();

            if ($user) {
                // 2. Cek apakah sudah terdaftar (double check)
                $stmt_cek = $pdo->prepare("SELECT id FROM pendaftaran WHERE workshop_id = ? AND user_id = ?");
                $stmt_cek->execute([$workshop_id, $uid]);

                if ($stmt_cek->rowCount() == 0) {
                    // 3. Generate QR Code / Kode Unik
                    $kode_unik = "WS-" . $workshop_id . "-" . strtoupper(bin2hex(random_bytes(3)));

                    // 4. Insert ke Pendaftaran
                    $stmt_ins->execute([
                        $workshop_id,
                        $uid,
                        $kode_unik,
                        $user['nama_lengkap'],
                        $user['email'],
                        $user['no_whatsapp'],
                        $user['jenis_kelamin']
                    ]);
                    $sukses++;
                }
            }
        }

        $pdo->commit();

        // Redirect kembali dengan pesan sukses
        header("Location: kelola_pendaftar.php?id=$workshop_id&status=success&msg=" . urlencode("$sukses Santri berhasil ditambahkan."));

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
} else {
    // Jika tidak ada yang dicentang
    header("Location: kelola_pendaftar.php?id={$_POST['workshop_id']}&status=error&msg=" . urlencode("Tidak ada peserta yang dipilih."));
}
?>