<?php
session_start();
require_once '../core/koneksi.php'; // Sesuaikan path

header('Content-Type: application/json');

function send_json_response($status, $message)
{
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

// Keamanan
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'penyelenggara') {
    send_json_response('error', 'Akses ditolak.');
}

$pendaftaran_id = $_POST['pendaftaran_id'] ?? null;
$new_status = $_POST['status'] ?? null; // Sekarang 'paid' atau 'failed'
$penyelenggara_id_bersama = $_SESSION['penyelenggara_id_bersama'];

// Validasi status baru
if (!$pendaftaran_id || !$new_status || !in_array($new_status, ['paid', 'failed'])) {
    send_json_response('error', 'Data tidak valid.');
}

try {
    // 1. Ambil data pendaftaran & event (termasuk harga)
    $sql_data = "SELECT p.*, w.judul as event_judul, w.harga as event_harga 
                 FROM pendaftaran p 
                 JOIN workshops w ON p.workshop_id = w.id 
                 WHERE p.id = ? AND w.penyelenggara_id = ?";
    $stmt_data = $pdo->prepare($sql_data);
    $stmt_data->execute([$pendaftaran_id, $penyelenggara_id_bersama]);
    $data = $stmt_data->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        send_json_response('error', 'Data pendaftaran tidak ditemukan.');
    }

    $event_harga = (int) ($data['event_harga'] ?? 0);
    $is_paid_event = ($event_harga > 0); // Cek apakah event berbayar

    // Hanya izinkan update status 'paid'/'failed' jika event berbayar
    if (!$is_paid_event) {
        // Jika event gratis, statusnya seharusnya 'free' atau 'pending'
        // Kita tidak menyediakan tombol untuk mengubah ke 'paid'/'failed'
        send_json_response('error', 'Aksi tidak valid untuk event gratis.');
    }

    // 2. Update status pembayaran di database
    $sql_update = "UPDATE pendaftaran SET status_pembayaran = ? WHERE id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$new_status, $pendaftaran_id]);

    if ($stmt_update->rowCount() > 0) {
        // 3. Persiapan kirim notifikasi WhatsApp
        $fonnte_token = 'GANTI_DENGAN_API_KEY_FOONTE_ANDA'; // <-- GANTI API KEY FOONTE
        $target = $data['telepon_peserta'];
        $nama_peserta = $data['nama_peserta'];
        $ticket_id = $data['kode_unik'];
        $event_judul = $data['event_judul'];
        $tanggal_update = date('d/m/Y H:i:s');
        $jumlah_tagihan = number_format($event_harga, 0, ',', '.');
        $jumlah_pembayaran = $jumlah_tagihan; // Asumsi
        $admin_kontak = '085691489851'; // Kontak admin jika failed

        // 4. Buat template pesan berdasarkan status 'paid' atau 'failed'
        $message = "";
        $status_text = ($new_status == 'paid') ? "PAID/LUNAS" : "FAILED"; // Sesuaikan teks status

        $message .= "Update Konfirmasi Pembayaran\n\n";
        $message .= "Ticket ID : {$ticket_id}\n";
        $message .= "Peserta : {$nama_peserta}\n";
        $message .= "Tanggal : {$tanggal_update}\n";
        $message .= "Jumlah Tagihan : Rp. {$jumlah_tagihan}\n";
        $message .= "Jumlah Pembayaran : Rp. {$jumlah_pembayaran}\n";
        $message .= "Status Pembayaran : *{$status_text}*\n\n";

        if ($new_status == 'failed') {
            $message .= "Pembayaran Anda gagal diverifikasi. Silakan hubungi admin di {$admin_kontak} untuk klarifikasi.\n";
        } else { // Status 'paid'
            $message .= "Pembayaran Anda telah kami terima. Terima kasih telah mendaftar pada event {$event_judul}.\n";
        }

        $message .= "\n=========================\n";
        $message .= "Salam Hormat,\n*BEM Event Team*"; // Sesuaikan nama tim

        // 5. Kirim pesan via Foonte
        if ($message && $target && $fonnte_token !== 'GANTI_DENGAN_API_KEY_FOONTE_ANDA') {
            $curl = curl_init();
            // ... (Kode cURL sama seperti sebelumnya) ...
            $response_fonnte = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                error_log("Fonnte API Error: " . $err);
                send_json_response('warning', 'Status pembayaran berhasil diupdate, tetapi notifikasi WA gagal dikirim.');
            } else {
                send_json_response('success', 'Status berhasil diupdate dan notifikasi WA terkirim.');
            }
        } else {
            send_json_response('warning', 'Status berhasil diupdate, tetapi API Key Foonte belum diatur/target kosong. Notifikasi WA tidak dikirim.');
        }

    } else {
        send_json_response('info', 'Status pembayaran tidak berubah (mungkin sudah sama).');
    }

} catch (PDOException $e) {
    send_json_response('error', 'Database Error: ' . $e->getMessage());
} catch (Exception $e) {
    send_json_response('error', 'Terjadi Kesalahan: ' . $e->getMessage());
}
?>