<?php
// Selalu aktifkan error reporting saat development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Panggil koneksi dan mulai session
require_once '../core/koneksi.php'; // Sesuaikan path jika perlu
session_start();

// Fungsi helper untuk mengirim respons JSON
function send_json_response($status, $message, $data = null)
{
    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit();
}

// Keamanan: Pastikan yang akses adalah penyelenggara yang login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'penyelenggara') {
    send_json_response('error', 'Akses ditolak. Anda harus login sebagai penyelenggara.');
}

// Ambil ID penyelenggara dari session untuk memastikan mereka hanya bisa mengubah event milik sendiri
$penyelenggara_id = $_SESSION['penyelenggara_id_bersama'];
$action = $_POST['action'] ?? '';

// Validasi dasar input
if (empty($action)) {
    send_json_response('error', 'Aksi tidak valid.');
}

// Routing aksi CRUD
switch ($action) {
    case 'tambah':
        // Ambil data dari POST
        $workshop_id = $_POST['workshop_id'] ?? null;
        $label = trim($_POST['label'] ?? '');
        $field_type = $_POST['field_type'] ?? '';
        $options = trim($_POST['options'] ?? '');
        $is_required = isset($_POST['is_required']) ? 1 : 0;
        $placeholder = trim($_POST['placeholder'] ?? '');

        // Validasi
        if (empty($workshop_id) || empty($label) || empty($field_type)) {
            send_json_response('error', 'Semua field yang wajib diisi harus diisi.');
        }

        try {
            // Query untuk menambah field baru
            $sql = "INSERT INTO form_fields (workshop_id, label, field_type, options, is_required, placeholder) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$workshop_id, $label, $field_type, $options, $is_required, $placeholder]);
            send_json_response('success', 'Pertanyaan baru berhasil ditambahkan!');
        } catch (PDOException $e) {
            send_json_response('error', 'Database Error: ' . $e->getMessage());
        }
        break;

    case 'edit':
        // Ambil data dari POST
        $field_id = $_POST['field_id'] ?? null;
        $workshop_id = $_POST['workshop_id'] ?? null;
        $label = trim($_POST['label'] ?? '');
        $field_type = $_POST['field_type'] ?? '';
        $options = trim($_POST['options'] ?? '');
        $is_required = isset($_POST['is_required']) ? 1 : 0;
        $placeholder = trim($_POST['placeholder'] ?? '');

        // Validasi
        if (empty($field_id) || empty($workshop_id) || empty($label) || empty($field_type)) {
            send_json_response('error', 'Data tidak lengkap untuk proses edit.');
        }

        try {
            // Query untuk update field
            $sql = "UPDATE form_fields SET label=?, field_type=?, options=?, is_required=?, placeholder=? 
                    WHERE id=? AND workshop_id IN (SELECT id FROM workshops WHERE penyelenggara_id=?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$label, $field_type, $options, $is_required, $placeholder, $field_id, $penyelenggara_id]);
            send_json_response('success', 'Pertanyaan berhasil diperbarui!');
        } catch (PDOException $e) {
            send_json_response('error', 'Database Error: ' . $e->getMessage());
        }
        break;

    case 'hapus':
        $field_id = $_POST['field_id'] ?? null;

        if (empty($field_id)) {
            send_json_response('error', 'ID field tidak ditemukan.');
        }

        try {
            // Query untuk hapus field, dengan validasi kepemilikan
            $sql = "DELETE FROM form_fields 
                    WHERE id=? AND workshop_id IN (SELECT id FROM workshops WHERE penyelenggara_id=?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$field_id, $penyelenggara_id]);

            if ($stmt->rowCount() > 0) {
                send_json_response('success', 'Pertanyaan berhasil dihapus!');
            } else {
                send_json_response('error', 'Gagal menghapus, data tidak ditemukan atau Anda tidak memiliki izin.');
            }
        } catch (PDOException $e) {
            send_json_response('error', 'Database Error: ' . $e->getMessage());
        }
        break;

    default:
        send_json_response('error', 'Aksi tidak dikenal.');
        break;
}
?>