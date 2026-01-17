<?php
session_start();
// Define BASE_PATH manual karena file ini diproses background (bukan view)
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
require_once BASE_PATH . '/core/koneksi.php';

// Cek Sesi (Hanya Penyelenggara)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penyelenggara') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- TAMBAH USER ---
    if ($action === 'add') {
        $nama = trim($_POST['nama_lengkap']);
        $email = trim($_POST['email']);
        $wa = trim($_POST['no_whatsapp']);
        $jk = $_POST['jenis_kelamin'];
        $password = $_POST['password'];
        $role = 'peserta'; // Hardcode role peserta

        // 1. Cek Email Kembar
        $stmtCek = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmtCek->execute([$email]);
        if ($stmtCek->rowCount() > 0) {
            echo "<script>alert('Email sudah digunakan!'); window.location='kelola_user.php';</script>";
            exit;
        }

        // 2. Hash Password
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insert
        try {
            $sql = "INSERT INTO users (nama_lengkap, email, no_whatsapp, jenis_kelamin, password, role) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nama, $email, $wa, $jk, $hashed_pass, $role]);

            $_SESSION['success'] = "Peserta baru berhasil ditambahkan.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Gagal menambah data: " . $e->getMessage();
        }
    }

    // --- EDIT USER ---
    elseif ($action === 'edit') {
        $id = $_POST['user_id'];
        $nama = trim($_POST['nama_lengkap']);
        $email = trim($_POST['email']);
        $wa = trim($_POST['no_whatsapp']);
        $jk = $_POST['jenis_kelamin'];
        $password = $_POST['password'];

        try {
            // Cek apakah password diubah?
            if (!empty($password)) {
                $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET nama_lengkap=?, email=?, no_whatsapp=?, jenis_kelamin=?, password=? WHERE id=? AND role='peserta'";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nama, $email, $wa, $jk, $hashed_pass, $id]);
            } else {
                $sql = "UPDATE users SET nama_lengkap=?, email=?, no_whatsapp=?, jenis_kelamin=? WHERE id=? AND role='peserta'";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nama, $email, $wa, $jk, $id]);
            }
            $_SESSION['success'] = "Data peserta berhasil diperbarui.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Gagal update: " . $e->getMessage();
        }
    }

    // --- HAPUS USER ---
    elseif ($action === 'delete') {
        $id = $_POST['user_id'];
        try {
            // Hapus (Pastikan WHERE role='peserta' agar tidak sengaja menghapus admin lain via inspect element)
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'peserta'");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Data peserta berhasil dihapus.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Gagal menghapus: " . $e->getMessage();
        }
    }

    header("Location: kelola_user.php");
    exit();
}
?>