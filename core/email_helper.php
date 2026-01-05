<?php
// ============================================================
// Import library dari Composer
// ============================================================
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * Membuat tiket dalam format PDF
 *
 * @param array $data Data lengkap untuk tiket
 * @return string Path file PDF yang telah dibuat
 */
function generateTicketPdf(array $data): string
{
    // ============================================================
    // 1. Buat folder sementara (temp) jika belum ada
    // ============================================================
    $tempDir = __DIR__ . '/../temp';
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
    }

    // ============================================================
    // 2. Generate QR Code
    // ============================================================
    $qrFilePath = $tempDir . '/' . $data['kode_unik'] . '.png';

    $qrResult = Builder::create()
        ->writer(new PngWriter())
        ->data($data['kode_unik'])
        ->size(300)
        ->margin(10)
        ->build();

    $qrResult->saveToFile($qrFilePath);
    $data['qr_code_path'] = $qrFilePath; // Tambahkan path QR code ke data

    // ============================================================
    // 3. Ambil HTML Template Tiket
    // ============================================================
    ob_start();
    include __DIR__ . '/../templates/ticket_template.php';
    $html = ob_get_clean();

    // ============================================================
    // 4. Buat file PDF
    // ============================================================
    try {
        $mpdf = new Mpdf();
        $mpdf->WriteHTML($html);
        $pdfPath = $tempDir . '/tiket-' . $data['kode_unik'] . '.pdf';
        $mpdf->Output($pdfPath, Destination::FILE);

        // Hapus file QR Code sementara
        unlink($qrFilePath);

        return $pdfPath;
    } catch (\Mpdf\MpdfException $e) {
        die('Gagal membuat PDF: ' . $e->getMessage());
    }
}

/**
 * Mengirim email dengan lampiran tiket (PDF)
 *
 * @param string $toEmail Email penerima
 * @param string $toName Nama penerima
 * @param string $subject Subjek email
 * @param string $body Isi email (HTML)
 * @param string $attachmentPath Path file PDF
 * @return bool True jika berhasil, False jika gagal
 */
function sendTicketEmail(string $toEmail, string $toName, string $subject, string $body, string $attachmentPath): bool
{
    $mail = new PHPMailer(true);
    $mail->SMTPDebug = 2;
    try {
        // ============================================================
        // 1. Konfigurasi SMTP (contoh: Gmail)
        // ============================================================
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ldk.elrahmajabar@gmail.com'; // GANTI
        $mail->Password = 'kmpj hzva bojp hzjs';     // GANTI (App Password Gmail)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // ============================================================
        // 2. Pengirim & Penerima
        // ============================================================
        $mail->setFrom('ldk.elrahmajabar@gmail.com', 'Panitia Event UKM El-Rahma');
        $mail->addAddress($toEmail, $toName);

        // ============================================================
        // 3. Konten Email
        // ============================================================
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = 'Ini adalah e-tiket Anda. Silakan buka lampiran untuk melihatnya.';

        // ============================================================
        // 4. Lampiran PDF
        // ============================================================
        if (file_exists($attachmentPath)) {
            $mail->addAttachment($attachmentPath);
        } else {
            throw new Exception('Lampiran tiket tidak ditemukan: ' . $attachmentPath);
        }

        // ============================================================
        // 5. Kirim email
        // ============================================================
        $mail->send();

        // Hapus file setelah terkirim
        unlink($attachmentPath);

        return true;
    } catch (Exception $e) {
        error_log('Email gagal dikirim: ' . $mail->ErrorInfo);
        return false;
    }
}
