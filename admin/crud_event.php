<?php
// admin/crud_event.php

// --- 1. CONFIG & BUFFERING (PENTING) ---
ob_start(); // Tangkap semua output
ini_set('display_errors', 0); // Jangan tampilkan error di browser (biar tidak merusak JSON)
error_reporting(E_ALL); // Tetap log error

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/core/koneksi.php';
session_start();

// --- 2. HELPER RESPONSE ---
function send_json_response($status, $message, $data = null)
{
    // Hapus semua output sebelumnya (spasi, warning, html)
    if (ob_get_length())
        ob_clean();

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit();
}

// --- 3. SECURITY ---
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'penyelenggara' && $_SESSION['role'] != 'admin')) {
    send_json_response('error', 'Akses ditolak. Silakan login ulang.');
}

$penyelenggara_id = $_SESSION['penyelenggara_id_bersama'] ?? 0;
$action = $_POST['action'] ?? '';

// --- 4. DATA PREPARATION (SAFE INPUTS) ---
// Gunakan Null Coalescing Operator (??) agar tidak error jika field kosong
$judul = $_POST['judul'] ?? '';
$deskripsi = $_POST['deskripsi'] ?? '';
$tanggal_waktu = $_POST['tanggal_waktu'] ?? '';
$lokasi = $_POST['lokasi'] ?? '';
$tipe_event = $_POST['tipe_event'] ?? 'gratis';
$harga = ($tipe_event == 'berbayar') ? (int) ($_POST['harga'] ?? 0) : 0;
$visibilitas = $_POST['visibilitas'] ?? 'public';

// Data Sertifikat
$sertifikat_prefix = $_POST['sertifikat_prefix'] ?? '';
$sertifikat_nomor_awal = !empty($_POST['sertifikat_nomor_awal']) ? (int) $_POST['sertifikat_nomor_awal'] : 1;
$sertifikat_font = $_POST['sertifikat_font'] ?? 'Poppins-SemiBold.ttf';
$sertifikat_orientasi = $_POST['sertifikat_orientasi'] ?? 'portrait';

// Posisi & Ukuran (Edit Mode)
$s_nama_fs = $_POST['sertifikat_nama_fs'] ?? 120;
$s_nama_y = $_POST['sertifikat_nama_y_percent'] ?? 50;
$s_nama_x = $_POST['sertifikat_nama_x_percent'] ?? 50;
$s_nomor_fs = $_POST['sertifikat_nomor_fs'] ?? 40;
$s_nomor_y = $_POST['sertifikat_nomor_y_percent'] ?? 60;
$s_nomor_x = $_POST['sertifikat_nomor_x_percent'] ?? 50;

// Denda
$aktifkan_denda = isset($_POST['aktifkan_denda']);
if ($aktifkan_denda) {
    $jam_selesai = !empty($_POST['jam_selesai']) ? $_POST['jam_selesai'] : null;
    $nominal_denda = !empty($_POST['nominal_denda']) ? (int) $_POST['nominal_denda'] : 0;
} else {
    $jam_selesai = null;
    $nominal_denda = 0;
}

// --- 5. LOGIC CRUD ---
try {
    switch ($action) {
        case 'tambah':
            // Upload Poster
            $poster = null;
            if (isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
                $ext = strtolower(pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION));
                $poster = 'poster_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['poster']['tmp_name'], BASE_PATH . "/assets/img/posters/" . $poster);
            }

            // Upload Sertifikat
            $sertifikat_template = null;
            if (isset($_FILES['sertifikat_template']) && $_FILES['sertifikat_template']['error'] == 0) {
                $ext = strtolower(pathinfo($_FILES['sertifikat_template']['name'], PATHINFO_EXTENSION));
                $sertifikat_template = 'template_' . uniqid() . '.' . $ext;
                $target_dir = BASE_PATH . "/assets/img/sertifikat_templates/";
                if (!is_dir($target_dir))
                    mkdir($target_dir, 0777, true);
                move_uploaded_file($_FILES['sertifikat_template']['tmp_name'], $target_dir . $sertifikat_template);
            }

            $sql = "INSERT INTO workshops (
                penyelenggara_id, judul, deskripsi, poster, lokasi, 
                tipe_event, harga, tanggal_waktu, jam_selesai, nominal_denda,
                sertifikat_template, sertifikat_prefix, sertifikat_nomor_awal, 
                sertifikat_font, sertifikat_orientasi, visibilitas
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $penyelenggara_id,
                $judul,
                $deskripsi,
                $poster,
                $lokasi,
                $tipe_event,
                $harga,
                $tanggal_waktu,
                $jam_selesai,
                $nominal_denda,
                $sertifikat_template,
                $sertifikat_prefix,
                $sertifikat_nomor_awal,
                $sertifikat_font,
                $sertifikat_orientasi,
                $visibilitas
            ]);

            send_json_response('success', 'Event berhasil ditambahkan!');
            break;

        case 'edit':
            $event_id = $_POST['event_id'] ?? 0;

            // Handle Poster Update
            $poster = $_POST['poster_lama'] ?? null;
            if (isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
                $ext = strtolower(pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION));
                $new_poster = 'poster_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['poster']['tmp_name'], BASE_PATH . "/assets/img/posters/" . $new_poster)) {
                    if ($poster && file_exists(BASE_PATH . "/assets/img/posters/" . $poster)) {
                        @unlink(BASE_PATH . "/assets/img/posters/" . $poster);
                    }
                    $poster = $new_poster;
                }
            }

            // Handle Template Update
            $sertifikat_template = $_POST['sertifikat_template_lama'] ?? null;
            if (isset($_FILES['sertifikat_template']) && $_FILES['sertifikat_template']['error'] == 0) {
                $ext = strtolower(pathinfo($_FILES['sertifikat_template']['name'], PATHINFO_EXTENSION));
                $new_temp = 'template_' . uniqid() . '.' . $ext;
                $target_dir = BASE_PATH . "/assets/img/sertifikat_templates/";
                if (!is_dir($target_dir))
                    mkdir($target_dir, 0777, true);

                if (move_uploaded_file($_FILES['sertifikat_template']['tmp_name'], $target_dir . $new_temp)) {
                    if ($sertifikat_template && file_exists($target_dir . $sertifikat_template)) {
                        @unlink($target_dir . $sertifikat_template);
                    }
                    $sertifikat_template = $new_temp;
                }
            }

            $sql = "UPDATE workshops SET 
                judul=?, deskripsi=?, poster=?, lokasi=?, tipe_event=?, harga=?, 
                tanggal_waktu=?, jam_selesai=?, nominal_denda=?, 
                sertifikat_template=?, sertifikat_prefix=?, sertifikat_nomor_awal=?,
                sertifikat_nama_fs=?, sertifikat_nama_y_percent=?, sertifikat_nama_x_percent=?,
                sertifikat_nomor_fs=?, sertifikat_nomor_y_percent=?, sertifikat_nomor_x_percent=?,
                sertifikat_font=?, sertifikat_orientasi=?, visibilitas=? 
                WHERE id=? AND penyelenggara_id=?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $judul,
                $deskripsi,
                $poster,
                $lokasi,
                $tipe_event,
                $harga,
                $tanggal_waktu,
                $jam_selesai,
                $nominal_denda,
                $sertifikat_template,
                $sertifikat_prefix,
                $sertifikat_nomor_awal,
                $s_nama_fs,
                $s_nama_y,
                $s_nama_x,
                $s_nomor_fs,
                $s_nomor_y,
                $s_nomor_x,
                $sertifikat_font,
                $sertifikat_orientasi,
                $visibilitas,
                $event_id,
                $penyelenggara_id
            ]);

            send_json_response('success', 'Event berhasil diperbarui!');
            break;

        case 'hapus':
            $event_id = $_POST['event_id'] ?? null;
            if (!$event_id)
                send_json_response('error', 'ID Event tidak valid.');

            $stmt_get = $pdo->prepare("SELECT poster, sertifikat_template FROM workshops WHERE id = ? AND penyelenggara_id = ?");
            $stmt_get->execute([$event_id, $penyelenggara_id]);
            $row = $stmt_get->fetch();

            if ($row) {
                if (!empty($row['poster']) && file_exists(BASE_PATH . "/assets/img/posters/" . $row['poster'])) {
                    @unlink(BASE_PATH . "/assets/img/posters/" . $row['poster']);
                }
                if (!empty($row['sertifikat_template']) && file_exists(BASE_PATH . "/assets/img/sertifikat_templates/" . $row['sertifikat_template'])) {
                    @unlink(BASE_PATH . "/assets/img/sertifikat_templates/" . $row['sertifikat_template']);
                }
                $stmt_del = $pdo->prepare("DELETE FROM workshops WHERE id = ?");
                $stmt_del->execute([$event_id]);
                send_json_response('success', 'Event dihapus.');
            } else {
                send_json_response('error', 'Event tidak ditemukan.');
            }
            break;

        default:
            send_json_response('error', 'Aksi tidak valid.');
    }
} catch (PDOException $e) {
    send_json_response('error', 'Database Error: ' . $e->getMessage());
}
?>