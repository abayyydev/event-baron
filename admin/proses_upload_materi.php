<?php
session_start();
if (!defined('BASE_PATH'))
    define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/core/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_materi'])) {
    $workshop_id = $_POST['workshop_id'];
    $judul = $_POST['judul_materi'];
    $deskripsi = $_POST['deskripsi'];

    // Upload Logic
    $target_dir = BASE_PATH . "/assets/uploads/materi/";
    if (!is_dir($target_dir))
        mkdir($target_dir, 0777, true); // Buat folder jika belum ada

    $file_ext = strtolower(pathinfo($_FILES["file_materi"]["name"], PATHINFO_EXTENSION));
    $new_name = time() . "_" . uniqid() . "." . $file_ext;
    $target_file = $target_dir . $new_name;

    $allowed = ['pdf', 'ppt', 'pptx'];

    if (in_array($file_ext, $allowed)) {
        if (move_uploaded_file($_FILES["file_materi"]["tmp_name"], $target_file)) {
            // Simpan ke DB
            $stmt = $pdo->prepare("INSERT INTO workshop_materials (workshop_id, judul_materi, deskripsi, nama_file) VALUES (?, ?, ?, ?)");
            $stmt->execute([$workshop_id, $judul, $deskripsi, $new_name]);

            $_SESSION['success'] = "Materi berhasil diupload!";
        } else {
            $_SESSION['error'] = "Gagal mengupload file.";
        }
    } else {
        $_SESSION['error'] = "Format file tidak diizinkan. Hanya PDF, PPT, PPTX.";
    }

    header("Location: kelola_event.php");
    exit;
}