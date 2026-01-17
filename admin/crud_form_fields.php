<?php
// Mulai session
session_start();

// Set header JSON agar Javascript bisa membacanya
header('Content-Type: application/json');

// Matikan display error agar pesan error PHP tidak merusak format JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../core/koneksi.php';

// Fungsi helper untuk kirim response
function send_response($status, $message, $data = null)
{
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit();
}

// Cek Login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penyelenggara') {
    send_response('error', 'Akses ditolak. Silakan login ulang.');
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'tambah':
            // 1. Ambil Data
            $workshop_id = $_POST['workshop_id'] ?? null;
            $label = $_POST['label'] ?? null;
            $field_type = $_POST['field_type'] ?? null;
            $options = $_POST['options'] ?? '';
            $is_required = isset($_POST['is_required']) ? 1 : 0;
            $placeholder = $_POST['placeholder'] ?? '';

            // 2. Validasi
            if (empty($workshop_id) || empty($label) || empty($field_type)) {
                send_response('error', 'Data tidak lengkap (Label/Tipe wajib diisi).');
            }

            // 3. Query SQL (Ada 6 tanda tanya)
            $sql = "INSERT INTO form_fields (workshop_id, label, field_type, options, is_required, placeholder) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);

            // 4. Eksekusi (Harus ada 6 variabel)
            $stmt->execute([
                $workshop_id,
                $label,
                $field_type,
                $options,
                $is_required,
                $placeholder
            ]);

            send_response('success', 'Pertanyaan berhasil ditambahkan!');
            break;

        case 'edit':
            // 1. Ambil Data
            $id = $_POST['field_id'] ?? null;
            $label = $_POST['label'] ?? null;
            $field_type = $_POST['field_type'] ?? null;
            $options = $_POST['options'] ?? '';
            $is_required = isset($_POST['is_required']) ? 1 : 0;
            $placeholder = $_POST['placeholder'] ?? '';

            if (empty($id)) {
                send_response('error', 'ID Field tidak ditemukan.');
            }

            // 2. Query SQL (Ada 6 tanda tanya: 5 data + 1 ID di WHERE)
            $sql = "UPDATE form_fields SET label=?, field_type=?, options=?, is_required=?, placeholder=? WHERE id=?";
            $stmt = $pdo->prepare($sql);

            // 3. Eksekusi (Harus ada 6 variabel, urutan harus sama dengan SQL)
            $stmt->execute([
                $label,
                $field_type,
                $options,
                $is_required,
                $placeholder,
                $id
            ]);

            send_response('success', 'Pertanyaan berhasil diperbarui!');
            break;

        case 'hapus':
            $id = $_POST['field_id'] ?? null;

            if (empty($id)) {
                send_response('error', 'ID tidak valid.');
            }

            // Query SQL (Ada 1 tanda tanya)
            $sql = "DELETE FROM form_fields WHERE id=?";
            $stmt = $pdo->prepare($sql);

            // Eksekusi (Harus ada 1 variabel)
            $stmt->execute([$id]);

            send_response('success', 'Pertanyaan berhasil dihapus!');
            break;

        default:
            send_response('error', 'Aksi tidak valid: ' . htmlspecialchars($action));
    }

} catch (PDOException $e) {
    // Tangkap error database dan kirim ke Javascript
    send_response('error', 'Database Error: ' . $e->getMessage());
} catch (Exception $e) {
    send_response('error', 'System Error: ' . $e->getMessage());
}
?>