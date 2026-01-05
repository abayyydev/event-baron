<?php
// =======================================================================
// 1. KONFIGURASI AWAL (CRITICAL UNTUK JSON)
// =======================================================================
// Tangkap semua output agar tidak bocor ke browser sebelum waktunya
ob_start();

// Matikan display error ke layar (agar JSON tidak tercampur HTML error)
ini_set('display_errors', 0);
error_reporting(E_ALL); // Tetap catat error di log server

session_start();
set_time_limit(300); // Batas waktu eksekusi 5 menit

// Load File Penting
require_once '../core/koneksi.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// =======================================================================
// 2. FUNGSI HELPER
// =======================================================================
function send_json_response($status, $message, $data = null)
{
    // HAPUS semua output sampah (HTML error/warning) yang terlanjur tercetak
    if (ob_get_length()) {
        ob_clean();
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit(); // Matikan script
}

try {
    // ===================================================================
    // 3. VALIDASI INPUT & SESI
    // ===================================================================
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') != 'penyelenggara') {
        send_json_response('error', 'Akses ditolak. Silakan login kembali.');
    }

    // Ambil ID Pendaftar (Support input dari 'id' atau 'pendaftaran_id')
    $pendaftaran_id = $_POST['id'] ?? $_POST['pendaftaran_id'] ?? null;

    if (!$pendaftaran_id) {
        send_json_response('error', 'ID Pendaftar tidak dikirim.');
    }

    $penyelenggara_id = $_SESSION['penyelenggara_id_bersama'] ?? 0;

    // ===================================================================
    // 4. AMBIL DATA DARI DATABASE
    // ===================================================================
    $sql = "SELECT p.*, 
                   w.judul, w.tanggal_waktu, w.penyelenggara_id,
                   w.sertifikat_template, w.sertifikat_font, w.sertifikat_orientasi,
                   w.sertifikat_nama_fs, w.sertifikat_nama_x_percent, w.sertifikat_nama_y_percent,
                   w.sertifikat_nomor_fs, w.sertifikat_nomor_x_percent, w.sertifikat_nomor_y_percent,
                   w.sertifikat_prefix, w.sertifikat_nomor_awal
            FROM pendaftaran p 
            JOIN workshops w ON p.workshop_id = w.id 
            WHERE p.id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$pendaftaran_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        send_json_response('error', 'Data pendaftar tidak ditemukan.');
    }

    // Validasi Kelayakan
    if (empty($data['sertifikat_template'])) {
        send_json_response('error', 'Template sertifikat belum di-upload di pengaturan event.');
    }
    if (($data['status_kehadiran'] ?? '') != 'hadir') {
        send_json_response('error', 'Peserta belum Check-in (Status: Belum Hadir).');
    }
    if (empty($data['email_peserta']) || !filter_var($data['email_peserta'], FILTER_VALIDATE_EMAIL)) {
        send_json_response('error', 'Email peserta tidak valid.');
    }

    // Variabel Data
    $nama_peserta = trim($data['nama_peserta']);
    $judul_event = $data['judul'];
    $tanggal_event = date('d F Y', strtotime($data['tanggal_waktu']));

    // ===================================================================
    // 5. GENERATE NOMOR SERTIFIKAT
    // ===================================================================
    if (!empty($data['sertifikat_nomor'])) {
        $nomor_sertifikat = $data['sertifikat_nomor'];
    } else {
        // Hitung urutan baru
        $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM pendaftaran WHERE workshop_id = ? AND sertifikat_nomor IS NOT NULL");
        $stmt_count->execute([$data['workshop_id']]);
        $jumlah_existing = (int) $stmt_count->fetchColumn();

        $nomor_urut = (int) ($data['sertifikat_nomor_awal'] ?? 1) + $jumlah_existing;
        $prefix = $data['sertifikat_prefix'] ?? '/SRT';
        $nomor_sertifikat = str_pad($nomor_urut, 3, '0', STR_PAD_LEFT) . $prefix;

        // Simpan nomor ke database
        $stmt_save = $pdo->prepare("UPDATE pendaftaran SET sertifikat_nomor = ? WHERE id = ?");
        $stmt_save->execute([$nomor_sertifikat, $pendaftaran_id]);
    }

    // ===================================================================
    // 6. SETUP PATH FILE (GD LIBRARY)
    // ===================================================================
    $project_root = dirname(__DIR__); // Root folder project

    // A. Path Input Template (Sesuai error Anda sebelumnya)
    $template_path = $project_root . '/assets/img/sertifikat_templates/' . $data['sertifikat_template'];

    // B. Path Input Font
    $font_filename = !empty($data['sertifikat_font']) ? $data['sertifikat_font'] : 'Poppins-SemiBold.ttf';
    $font_path = $project_root . '/assets/fonts/' . $font_filename;

    // C. Path Output (Tempat menyimpan hasil generate)
    $output_dir = $project_root . '/assets/img/sertifikat_storage/';

    // --- Validasi File Exist ---
    if (!file_exists($template_path)) {
        send_json_response('error', 'File template tidak ditemukan di: assets/uploads/sertifikat/' . $data['sertifikat_template']);
    }

    // Fallback Font jika tidak ketemu
    if (!file_exists($font_path)) {
        // Coba cari font default
        $font_path = $project_root . '/assets/fonts/Poppins-SemiBold.ttf';
        if (!file_exists($font_path)) {
            send_json_response('error', 'File font tidak ditemukan. Harap upload font ke folder assets/fonts/');
        }
    }

    // Buat folder output jika belum ada
    if (!is_dir($output_dir)) {
        if (!mkdir($output_dir, 0777, true)) {
            send_json_response('error', 'Gagal membuat folder penyimpanan sertifikat.');
        }
    }

    // ===================================================================
    // 7. PROSES GAMBAR
    // ===================================================================
    // Load Image
    $ext = strtolower(pathinfo($template_path, PATHINFO_EXTENSION));
    $image = null;
    if ($ext == 'png')
        $image = @imagecreatefrompng($template_path);
    elseif ($ext == 'jpg' || $ext == 'jpeg')
        $image = @imagecreatefromjpeg($template_path);

    if (!$image) {
        send_json_response('error', 'Gagal memuat gambar template. Pastikan format valid (JPG/PNG).');
    }

    $text_color = imagecolorallocate($image, 15, 15, 106); // Warna Biru Gelap
    $IMG_WIDTH = imagesx($image);
    $IMG_HEIGHT = imagesy($image);

    // Fungsi Tulis Tengah
    function write_centered_text($img, $size, $angle, $pct_x, $pct_y, $color, $font, $text, $w, $h)
    {
        $box = imagettfbbox($size, $angle, $font, $text);
        $text_w = abs($box[2] - $box[0]);
        $text_h = abs($box[7] - $box[1]);

        $x = ($w * ($pct_x / 100));
        $y = ($h * ($pct_y / 100));

        // Auto center jika posisi X di kisaran 45-55%
        if ($pct_x >= 45 && $pct_x <= 55) {
            $x = $x - ($text_w / 2);
        }

        // Adjust Y baseline (GD menulis dari bawah ke atas untuk Y)
        $y += ($text_h / 2);

        imagettftext($img, $size, $angle, $x, $y, $color, $font, $text);
    }

    // Tulis Nama
    write_centered_text(
        $image,
        $data['sertifikat_nama_fs'] ?: 48,
        0,
        $data['sertifikat_nama_x_percent'] ?: 50,
        $data['sertifikat_nama_y_percent'] ?: 50,
        $text_color,
        $font_path,
        $nama_peserta,
        $IMG_WIDTH,
        $IMG_HEIGHT
    );

    // Tulis Nomor
    write_centered_text(
        $image,
        $data['sertifikat_nomor_fs'] ?: 24,
        0,
        $data['sertifikat_nomor_x_percent'] ?: 50,
        $data['sertifikat_nomor_y_percent'] ?: 65,
        $text_color,
        $font_path,
        $nomor_sertifikat,
        $IMG_WIDTH,
        $IMG_HEIGHT
    );

    // Simpan File
    $file_name = 'Sertifikat_' . preg_replace('/[^a-zA-Z0-9]/', '_', $nama_peserta) . '_' . time() . '.jpg';
    $output_path = $output_dir . $file_name;

    // Simpan kualitas 90%
    if (!imagejpeg($image, $output_path, 90)) {
        imagedestroy($image);
        send_json_response('error', 'Gagal menyimpan file JPG ke folder sertifikat_storage.');
    }

    imagedestroy($image); // Hapus dari memori

    // ===================================================================
    // 8. KIRIM EMAIL (PHPMailer)
    // ===================================================================
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ldk.elrahmajabar@gmail.com'; // Ganti email Anda
        $mail->Password = 'kmpj hzva bojp hzjs';        // App Password Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('ldk.elrahmajabar@gmail.com', 'Panitia Event');
        $mail->addAddress($data['email_peserta'], $nama_peserta);

        $mail->isHTML(true);
        $mail->Subject = 'Sertifikat Event: ' . $judul_event;

        $bodyContent = "
            <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                <h3>Assalamu'alaikum Warahmatullahi Wabarakatuh</h3>
                <p>Halo <b>{$nama_peserta}</b>,</p>
                <p>Terima kasih telah berpartisipasi dalam event <b>{$judul_event}</b> yang diselenggarakan pada tanggal <b>{$tanggal_event}</b>.</p>
                <p>Bersama ini kami lampirkan <b>E-Sertifikat</b> Anda (No: {$nomor_sertifikat}).</p>
                <br>
                <p>Semoga ilmu yang didapatkan bermanfaat.</p>
                <p>Wassalamu'alaikum Warahmatullahi Wabarakatuh.</p>
                <br>
                <small>Salam Hangat,<br>Panitia LDK EL RAHMA</small>
            </div>
        ";

        $mail->Body = $bodyContent;
        $mail->addAttachment($output_path, "Sertifikat - {$nama_peserta}.jpg");

        $mail->send();

    } catch (Exception $e) {
        // Jika gagal kirim email, hapus file gambar agar tidak menumpuk
        @unlink($output_path);
        error_log("Mailer Error: " . $mail->ErrorInfo);
        send_json_response('error', 'Gagal mengirim email: ' . $mail->ErrorInfo);
    }

    // ===================================================================
    // 9. FINALISASI
    // ===================================================================

    // Update status di database
    $stmt_fin = $pdo->prepare("UPDATE pendaftaran SET sertifikat_status = 'terkirim' WHERE id = ?");
    $stmt_fin->execute([$pendaftaran_id]);

    // Opsi: Hapus file setelah kirim (hemat space) ATAU biarkan (arsip)
    // Jika ingin dihapus, uncomment baris di bawah:
    // @unlink($output_path); 

    send_json_response('success', 'Sertifikat berhasil dikirim ke email peserta.');

} catch (Exception $e) {
    // Tangkap error sistem lain
    error_log("System Error: " . $e->getMessage());
    send_json_response('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
}
?>