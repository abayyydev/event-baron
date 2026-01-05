<?php
// admin/crud_font.php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__)); 
}

require_once BASE_PATH . '/core/koneksi.php'; // Jika butuh cek sesi

session_start();
header('Content-Type: application/json');

// Cek Login Admin/Penyelenggara
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'penyelenggara') {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
    exit;
}

$action = $_POST['action'] ?? '';
$font_dir = BASE_PATH . '/assets/fonts/';

// Buat folder jika belum ada
if (!is_dir($font_dir)) {
    mkdir($font_dir, 0777, true);
}

switch ($action) {
    case 'upload':
        if (isset($_FILES['font_file']) && $_FILES['font_file']['error'] == 0) {
            $file = $_FILES['font_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['ttf', 'otf'];

            if (!in_array($ext, $allowed)) {
                echo json_encode(['status' => 'error', 'message' => 'Format file harus .ttf atau .otf']);
                exit;
            }

            // Bersihkan nama file (hapus spasi dll)
            $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
            $new_name = $filename . '.' . $ext;
            $target = $font_dir . $new_name;

            if (file_exists($target)) {
                echo json_encode(['status' => 'error', 'message' => 'Font dengan nama ini sudah ada.']);
                exit;
            }

            if (move_uploaded_file($file['tmp_name'], $target)) {
                echo json_encode(['status' => 'success', 'message' => 'Font berhasil diupload!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal mengupload file.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Tidak ada file yang dipilih.']);
        }
        break;

    case 'hapus':
        $filename = $_POST['filename'] ?? '';
        // Validasi keamanan sederhana (mencegah directory traversal)
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
            echo json_encode(['status' => 'error', 'message' => 'Nama file tidak valid.']);
            exit;
        }

        $target = $font_dir . $filename;
        
        // Cegah hapus font default jika mau
        if ($filename == 'Poppins-SemiBold.ttf') {
            echo json_encode(['status' => 'error', 'message' => 'Font default tidak boleh dihapus.']);
            exit;
        }

        if (file_exists($target)) {
            unlink($target);
            echo json_encode(['status' => 'success', 'message' => 'Font berhasil dihapus.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File tidak ditemukan.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid.']);
        break;
}
?>