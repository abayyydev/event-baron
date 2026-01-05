<?php
// File: user/download_sertifikat.php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once '../core/koneksi.php';

// 1. CEK LOGIN & ROLE
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'peserta') {
    die("Akses ditolak. Silakan login.");
}

// 2. VALIDASI ID
if (!isset($_GET['id'])) {
    die("ID Pendaftaran tidak ditemukan.");
}

$id_pendaftaran = $_GET['id'];
$email_peserta = $_SESSION['email'];

try {
    // 3. AMBIL DATA PENDAFTARAN & SETTING SERTIFIKAT
    // Join tabel pendaftaran dengan workshops untuk ambil settingan koordinat & font
    $sql = "SELECT p.*, w.judul, 
                   w.sertifikat_template, w.sertifikat_font,
                   w.sertifikat_nama_x_percent, w.sertifikat_nama_y_percent, w.sertifikat_nama_fs,
                   w.sertifikat_nomor_x_percent, w.sertifikat_nomor_y_percent, w.sertifikat_nomor_fs,
                   w.sertifikat_prefix, w.sertifikat_nomor_awal
            FROM pendaftaran p 
            JOIN workshops w ON p.workshop_id = w.id 
            WHERE p.id = :id AND p.email_peserta = :email";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id_pendaftaran, 'email' => $email_peserta]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4. VALIDASI KELAYAKAN
    if (!$data) {
        die("Data tidak ditemukan.");
    }
    if ($data['status_kehadiran'] != 'hadir') {
        die("Maaf, sertifikat hanya diberikan kepada peserta yang berstatus HADIR saat acara.");
    }
    if (empty($data['sertifikat_template'])) {
        die("Template sertifikat belum diatur oleh panitia.");
    }

    // 5. AUTO-GENERATE NOMOR SERTIFIKAT (JIKA BELUM ADA)
    if (empty($data['sertifikat_nomor'])) {
        // Ambil nomor urut terakhir di workshop ini
        $stmt_last = $pdo->prepare("SELECT COUNT(*) FROM pendaftaran WHERE workshop_id = ? AND sertifikat_nomor IS NOT NULL");
        $stmt_last->execute([$data['workshop_id']]);
        $nomor_urut = $stmt_last->fetchColumn() + $data['sertifikat_nomor_awal'];

        // Format Nomor: 001/SRT/2025 (3 digit angka + prefix)
        $no_baru = sprintf("%03d", $nomor_urut) . $data['sertifikat_prefix'];

        // Simpan ke database agar permanen
        $stmt_update = $pdo->prepare("UPDATE pendaftaran SET sertifikat_nomor = ?, sertifikat_status = 'terkirim' WHERE id = ?");
        $stmt_update->execute([$no_baru, $id_pendaftaran]);

        // Update variabel data saat ini
        $data['sertifikat_nomor'] = $no_baru;
    }

    // 6. PROSES GAMBAR (GD LIBRARY)

    // Path File
    // Asumsi: Template ada di folder assets/uploads/sertifikat/
    // Asumsi: Font ada di folder assets/fonts/
    $path_template = "../assets/img/sertifikat_templates/" . $data['sertifikat_template'];
    $path_font = "../assets/fonts/" . ($data['sertifikat_font'] ?: 'Poppins-SemiBold.ttf');

    if (!file_exists($path_template)) {
        die("File template gambar tidak ditemukan di server: " . $path_template);
    }
    if (!file_exists($path_font)) {
        die("File font tidak ditemukan di server: " . $path_font);
    }

    // Load Gambar berdasarkan ekstensi
    $ext = strtolower(pathinfo($path_template, PATHINFO_EXTENSION));
    if ($ext == 'png') {
        $image = imagecreatefrompng($path_template);
    } elseif ($ext == 'jpg' || $ext == 'jpeg') {
        $image = imagecreatefromjpeg($path_template);
    } else {
        die("Format gambar tidak didukung (Gunakan JPG/PNG).");
    }

    // Warna Teks (Hitam - RGB: 0,0,0) - Bisa disesuaikan
    $color = imagecolorallocate($image, 50, 50, 50);

    // Ukuran Gambar
    $img_width = imagesx($image);
    $img_height = imagesy($image);

    // --- FUNGSI TULIS TEXT TENGAH (CENTER) ---
    function tulisTeksTengah($image, $size, $angle, $x_percent, $y_percent, $color, $font, $text, $img_width, $img_height)
    {
        // Hitung bounding box teks
        $bbox = imagettfbbox($size, $angle, $font, $text);
        $text_width = $bbox[2] - $bbox[0];

        // Hitung posisi X dan Y berdasarkan persentase
        // Jika X percent mendekati 50 (45-55), kita anggap CENTER align
        $x_pixel = ($x_percent / 100) * $img_width;
        $y_pixel = ($y_percent / 100) * $img_height;

        if ($x_percent >= 45 && $x_percent <= 55) {
            $x_pixel = $x_pixel - ($text_width / 2); // Geser ke kiri setengah lebar teks
        }

        imagettftext($image, $size, $angle, $x_pixel, $y_pixel, $color, $font, $text);
    }

    // A. TULIS NAMA PESERTA
    // Ambil settingan font size nama (default 60 jika kosong)
    $fs_nama = $data['sertifikat_nama_fs'] ?: 60;
    tulisTeksTengah(
        $image,
        $fs_nama,
        0,
        $data['sertifikat_nama_x_percent'],
        $data['sertifikat_nama_y_percent'],
        $color,
        $path_font,
        $data['nama_peserta'],
        $img_width,
        $img_height
    );

    // B. TULIS NOMOR SERTIFIKAT
    // Ambil settingan font size nomor (default 30 jika kosong)
    $fs_nomor = $data['sertifikat_nomor_fs'] ?: 30;
    tulisTeksTengah(
        $image,
        $fs_nomor,
        0,
        $data['sertifikat_nomor_x_percent'],
        $data['sertifikat_nomor_y_percent'],
        $color,
        $path_font,
        "No: " . $data['sertifikat_nomor'],
        $img_width,
        $img_height
    );

    // 7. OUTPUT DOWNLOAD
    // Bersihkan output buffer agar gambar tidak rusak
    if (ob_get_length())
        ob_clean();

    // Set Header untuk Download
    $filename = "Sertifikat-" . str_replace(' ', '-', $data['nama_peserta']) . ".jpg";

    header('Content-Type: image/jpeg');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // Render gambar ke browser sebagai kualitas 90%
    imagejpeg($image, null, 90);

    // Hapus dari memori
    imagedestroy($image);
    exit;

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>