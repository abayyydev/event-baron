<?php
// File: user/download_sertifikat.php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once '../core/koneksi.php';

// 1. CEK LOGIN & ROLE SANTRI
if (!isset($_SESSION['santri_id']) || $_SESSION['role'] !== 'peserta') {
    die("Akses ditolak. Silakan login sebagai santri.");
}

// 2. VALIDASI ID
if (!isset($_GET['id'])) {
    die("ID Pendaftaran tidak ditemukan.");
}

$id_pendaftaran = (int) $_GET['id'];
$santri_id = $_SESSION['santri_id'];

try {
    // 3. AMBIL DATA PENDAFTARAN & SETTING SERTIFIKAT
    // Penting: WHERE clause menggunakan santri_id agar user lain tidak bisa download punya orang
    $sql = "SELECT p.*, w.judul, 
                   w.sertifikat_template, w.sertifikat_font,
                   w.sertifikat_nama_x_percent, w.sertifikat_nama_y_percent, w.sertifikat_nama_fs,
                   w.sertifikat_nomor_x_percent, w.sertifikat_nomor_y_percent, w.sertifikat_nomor_fs,
                   w.sertifikat_prefix, w.sertifikat_nomor_awal
            FROM pendaftaran p 
            JOIN workshops w ON p.workshop_id = w.id 
            WHERE p.id = :pid AND p.santri_id = :sid";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['pid' => $id_pendaftaran, 'sid' => $santri_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4. VALIDASI KELAYAKAN
    if (!$data) {
        die("Data sertifikat tidak ditemukan atau Anda tidak memiliki akses.");
    }
    if ($data['status_kehadiran'] != 'hadir') {
        die("Maaf, sertifikat hanya diberikan kepada peserta yang berstatus HADIR saat acara.");
    }
    if (empty($data['sertifikat_template'])) {
        die("Template sertifikat belum diatur oleh panitia.");
    }

    // 5. AUTO-GENERATE NOMOR SERTIFIKAT (JIKA BELUM ADA)
    if (empty($data['sertifikat_nomor'])) {
        // Ambil nomor urut terakhir di workshop ini yang sudah punya nomor
        $stmt_last = $pdo->prepare("SELECT COUNT(*) FROM pendaftaran WHERE workshop_id = ? AND sertifikat_nomor IS NOT NULL");
        $stmt_last->execute([$data['workshop_id']]);

        // Nomor awal default 1 jika tidak diset admin
        $start_num = !empty($data['sertifikat_nomor_awal']) ? $data['sertifikat_nomor_awal'] : 1;
        $nomor_urut = $stmt_last->fetchColumn() + $start_num;

        // Format Nomor: 001/SRT/2025 (3 digit angka + prefix)
        // Prefix bisa kosong
        $prefix = !empty($data['sertifikat_prefix']) ? '/' . $data['sertifikat_prefix'] : '';
        $no_baru = sprintf("%03d", $nomor_urut) . $prefix;

        // Simpan ke database agar permanen (Konsistensi data)
        $stmt_update = $pdo->prepare("UPDATE pendaftaran SET sertifikat_nomor = ?, sertifikat_status = 'terkirim' WHERE id = ?");
        $stmt_update->execute([$no_baru, $id_pendaftaran]);

        // Update variabel data saat ini untuk dicetak
        $data['sertifikat_nomor'] = $no_baru;
    }

    // 6. PROSES GAMBAR (GD LIBRARY)

    // Path File Assets (Naik satu level dari folder user/)
    $path_template = "../assets/uploads/sertifikat/" . $data['sertifikat_template']; // Sesuaikan folder upload admin
    $path_font = "../assets/fonts/" . ($data['sertifikat_font'] ?: 'Poppins-SemiBold.ttf');

    // Cek keberadaan file
    if (!file_exists($path_template)) {
        // Cek path alternatif jika admin menyimpan di img/sertifikat_templates/
        $path_template_alt = "../assets/img/sertifikat_templates/" . $data['sertifikat_template'];
        if (file_exists($path_template_alt)) {
            $path_template = $path_template_alt;
        } else {
            die("File template gambar tidak ditemukan di server.");
        }
    }

    // Cek font (gunakan default arial jika custom font tidak ada)
    $use_custom_font = true;
    if (!file_exists($path_font)) {
        // Fallback ke font sistem jika perlu, atau die
        // die("File font tidak ditemukan: " . $path_font);
        // Untuk keamanan, lebih baik die dulu agar admin sadar font hilang
        die("File font ($path_font) tidak ditemukan. Hubungi Admin.");
    }

    // Load Gambar berdasarkan ekstensi
    $ext = strtolower(pathinfo($path_template, PATHINFO_EXTENSION));
    if ($ext == 'png') {
        $image = imagecreatefrompng($path_template);
    } elseif ($ext == 'jpg' || $ext == 'jpeg') {
        $image = imagecreatefromjpeg($path_template);
    } else {
        die("Format gambar template harus JPG atau PNG.");
    }

    // Warna Teks (Hitam Pekat - RGB: 30,30,30)
    $color = imagecolorallocate($image, 30, 30, 30);

    // Ukuran Gambar
    $img_width = imagesx($image);
    $img_height = imagesy($image);

    // --- FUNGSI TULIS TEKS TENGAH (CENTER ALIGNMENT) ---
    function tulisTeks($image, $size, $angle, $x_percent, $y_percent, $color, $font, $text, $img_width, $img_height)
    {
        // Hitung bounding box teks untuk mendapatkan lebar teks
        $bbox = imagettfbbox($size, $angle, $font, $text);
        $text_width = abs($bbox[2] - $bbox[0]);
        $text_height = abs($bbox[7] - $bbox[1]);

        // Konversi Persentase ke Pixel
        $x_pixel = ($x_percent / 100) * $img_width;
        $y_pixel = ($y_percent / 100) * $img_height;

        // Logic Center Alignment
        // Jika X di sekitar 50% (tengah), kita asumsikan user ingin center align
        if ($x_percent >= 45 && $x_percent <= 55) {
            $x_pixel = ($img_width / 2) - ($text_width / 2);
        }

        imagettftext($image, $size, $angle, $x_pixel, $y_pixel, $color, $font, $text);
    }

    // A. TULIS NAMA PESERTA
    $fs_nama = $data['sertifikat_nama_fs'] ?: 60; // Default font size 60
    tulisTeks(
        $image,
        $fs_nama,
        0,
        $data['sertifikat_nama_x_percent'],
        $data['sertifikat_nama_y_percent'],
        $color,
        $path_font,
        $data['nama_peserta'], // Nama dari database pendaftaran
        $img_width,
        $img_height
    );

    // B. TULIS NOMOR SERTIFIKAT
    $fs_nomor = $data['sertifikat_nomor_fs'] ?: 30; // Default font size 30
    tulisTeks(
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
    // Bersihkan output buffer agar file gambar tidak korup
    if (ob_get_length())
        ob_clean();

    // Nama file saat didownload
    $filename = "Sertifikat-" . preg_replace('/[^A-Za-z0-9\-]/', '_', $data['nama_peserta']) . ".jpg";

    header('Content-Type: image/jpeg');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // Render gambar ke browser (Quality 90)
    imagejpeg($image, null, 90);

    // Bersihkan memori
    imagedestroy($image);
    exit;

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
} catch (Exception $e) {
    die("System Error: " . $e->getMessage());
}
?>