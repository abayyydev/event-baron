<?php
// admin/proses_santri.php

session_start();

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
require_once BASE_PATH . '/core/koneksi.php';

// Cek Sesi (Hanya Penyelenggara/Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penyelenggara') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- TAMBAH SANTRI ---
    if ($action === 'add') {
        $nis = trim($_POST['nis']);
        $kelas = trim($_POST['kelas']);
        $nama = trim($_POST['nama_lengkap']);
        $email = trim($_POST['email']);
        $wali = trim($_POST['nama_wali']);
        $hp_wali = trim($_POST['no_hp_wali']);
        $jk = $_POST['jenis_kelamin'];
        $password = $_POST['password'];

        // 1. Cek Duplikat (NIS atau Email)
        $stmtCek = $pdo->prepare("SELECT id FROM santri WHERE nis = ? OR email = ?");
        $stmtCek->execute([$nis, $email]);
        if ($stmtCek->rowCount() > 0) {
            $_SESSION['error'] = "Gagal! NIS atau Email sudah terdaftar.";
            header("Location: kelola_user.php");
            exit;
        }

        // 2. Generate Data Pendukung
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        // Buat Barcode Code Unik: STR-{Tahun}-{RandomHex}
        $barcode_code = 'STR-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 4));

        // 3. Insert ke Database
        try {
            $sql = "INSERT INTO santri (nis, kelas, nama_lengkap, email, nama_wali, no_hp_wali, jenis_kelamin, password, barcode_code) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nis, $kelas, $nama, $email, $wali, $hp_wali, $jk, $hashed_pass, $barcode_code]);

            $_SESSION['success'] = "Data santri berhasil ditambahkan.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Gagal menambah data: " . $e->getMessage();
        }
    }

    // --- EDIT SANTRI ---
    elseif ($action === 'edit') {
        $id = $_POST['id'];
        $nis = trim($_POST['nis']);
        $kelas = trim($_POST['kelas']);
        $nama = trim($_POST['nama_lengkap']);
        $email = trim($_POST['email']);
        $wali = trim($_POST['nama_wali']);
        $hp_wali = trim($_POST['no_hp_wali']);
        $jk = $_POST['jenis_kelamin'];
        $password = $_POST['password'];

        // 1. Cek Duplikat (Kecuali punya sendiri)
        $stmtCek = $pdo->prepare("SELECT id FROM santri WHERE (nis = ? OR email = ?) AND id != ?");
        $stmtCek->execute([$nis, $email, $id]);
        if ($stmtCek->rowCount() > 0) {
            $_SESSION['error'] = "Gagal Update! NIS atau Email sudah digunakan santri lain.";
            header("Location: kelola_user.php");
            exit;
        }

        try {
            // Cek apakah password diubah?
            if (!empty($password)) {
                $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE santri SET nis=?, kelas=?, nama_lengkap=?, email=?, nama_wali=?, no_hp_wali=?, jenis_kelamin=?, password=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nis, $kelas, $nama, $email, $wali, $hp_wali, $jk, $hashed_pass, $id]);
            } else {
                $sql = "UPDATE santri SET nis=?, kelas=?, nama_lengkap=?, email=?, nama_wali=?, no_hp_wali=?, jenis_kelamin=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nis, $kelas, $nama, $email, $wali, $hp_wali, $jk, $id]);
            }
            $_SESSION['success'] = "Data santri berhasil diperbarui.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Gagal update: " . $e->getMessage();
        }
    }

    // --- HAPUS SANTRI ---
    elseif ($action === 'delete') {
        $id = $_POST['id'];
        try {
            // Note: Idealnya hapus dulu data di tabel pendaftaran & diskusi terkait santri ini
            // Tapi jika Foreign Key di database sudah CASCADE, cukup hapus induknya saja.

            // Hapus Pendaftaran (Manual Safety)
            $pdo->prepare("DELETE FROM pendaftaran WHERE santri_id = ?")->execute([$id]);

            // Hapus Santri
            $stmt = $pdo->prepare("DELETE FROM santri WHERE id = ?");
            $stmt->execute([$id]);

            $_SESSION['success'] = "Data santri berhasil dihapus.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Gagal menghapus: " . $e->getMessage();
        }
    }

    header("Location: kelola_user.php");
    exit();
}
?>