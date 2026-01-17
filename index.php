<?php
// index.php (File Router Utama)
define('BASE_PATH', __DIR__);
// Ambil path yang diminta dari parameter 'url' yang dikirim oleh .htaccess
$request_path = trim($_GET['url'] ?? '', '/');

// Pisahkan path menjadi segmen jika perlu (misal: "admin/dashboard" menjadi ["admin", "dashboard"])
$segments = explode('/', $request_path);

// Tentukan file PHP mana yang akan di-include berdasarkan path
$file_to_include = null;

// --- Definisi Rute ---
switch ($segments[0]) {
    case '': // Jika path kosong (halaman utama)
    case 'home': // Jika path /home
        $file_to_include = 'home.php'; // Muat file homepage Anda (yang tadi direname)
        break;
    case 'login':
        $file_to_include = 'login.php';
        break;
    case 'logout':
        $file_to_include = 'logout.php';
        break;
    case 'register': // Jika Anda punya register.php
        $file_to_include = 'register.php';
        break;
    case 'detail_workshop':
        // Parameter ID event masih bisa diakses via $_GET['id'] karena flag QSA di .htaccess
        $file_to_include = 'detail_workshop.php';
        break;
    case 'sukses_pendaftaran':
        $file_to_include = 'sukses_pendaftaran.php';
        break;

    // --- Rute Admin ---
    case 'admin':
        $admin_page = $segments[1] ?? 'dashboard'; // Default ke dashboard jika hanya /admin
        switch ($admin_page) {
            case 'dashboard':
                $file_to_include = 'admin/dashboard.php';
                break;
            case 'kelola_event':
                $file_to_include = 'admin/kelola_event.php';
                break;
            case 'crud_event': // Perlu ditambahkan jika diakses langsung
                $file_to_include = 'admin/crud_event.php';
                break;
            case 'kelola_form':
                $file_to_include = 'admin/kelola_form.php';
                break;
            case 'crud_form_fields': // Perlu ditambahkan
                $file_to_include = 'admin/crud_form_fields.php';
                break;
            case 'kelola_pendaftar':
                $file_to_include = 'admin/kelola_pendaftar.php';
                break;
            case 'lihat_detail_pendaftar':
                $file_to_include = 'admin/lihat_detail_pendaftar.php';
                break;
            case 'scan_checkin':
                $file_to_include = 'admin/scan_checkin.php';
                break;
            case 'proses_checkin': // Perlu ditambahkan
                $file_to_include = 'admin/proses_checkin.php';
                break;
            case 'kelola_tim':
                $file_to_include = 'admin/kelola_tim.php';
                break;
            case 'kelola_user':
                $file_to_include = 'admin/kelola_user.php';
                break;
            case 'kelola_font':
                $file_to_include = 'admin/kelola_font.php';
                break;
            case 'diskusi':
                $file_to_include = 'admin/diskusi.php';
                break;
            case 'proses_tambah_anggota': // Perlu ditambahkan
                $file_to_include = 'admin/proses_tambah_anggota.php';
                break;
            case 'proses_kirim_sertifikat': // Perlu ditambahkan
                $file_to_include = 'admin/proses_kirim_sertifikat.php';
                break;
            // Tambahkan case lain untuk semua file admin Anda...
            default:
                $file_to_include = '404.php'; // Halaman tidak ditemukan
                http_response_code(404);
                break;
        }
        break;

    case 'user':
        // Cek sesi login user di sini (Opsional, atau di dalam file masing-masing)
        // session_start(); 
        // if (!isset($_SESSION['role']) || $_SESSION['role'] != 'peserta') { ... redirect login ... }

        $user_page = $segments[1] ?? 'dashboard';
        switch ($user_page) {
            case 'dashboard':
                $file_to_include = 'user/dashboard.php';
                break;
            case 'cetak_tiket':
                $file_to_include = 'user/cetak_tiket.php';
                break;
            case 'profile':
                $file_to_include = 'user/profile.php';
                break;
            default:
                $file_to_include = '404.php';
                break;
        }
        break;

    // --- Rute API ---
    case 'api':
        $api_endpoint = $segments[1] ?? null;
        switch ($api_endpoint) {
            case 'request_otp':
                $file_to_include = 'api/request_otp.php';
                break;
            case 'verify_otp':
                $file_to_include = 'api/verify_otp.php';
                break;
            case 'update_payment_status':
                $file_to_include = 'api/update_payment_status.php';
                break;
            // Tambahkan case untuk API lain jika ada
            default:
                $file_to_include = '404.php'; // API endpoint tidak ditemukan
                http_response_code(404);
                break;
        }
        break;

    // Jika tidak ada rute yang cocok
    default:
        $file_to_include = '404.php'; // Halaman tidak ditemukan
        http_response_code(404);
        break;
}

// --- Muat File yang Sesuai ---
if ($file_to_include && file_exists($file_to_include)) {
    require_once $file_to_include;
} elseif ($file_to_include === '404.php') {
    // Coba muat halaman 404 kustom Anda
    if (file_exists('404.php')) {
        require_once '404.php';
    } else {
        // Fallback jika 404.php tidak ada
        die('404 Not Found');
    }
} else {
    // Ini seharusnya tidak terjadi jika routingnya benar
    http_response_code(500);
    die('Internal Server Error - Routing failed');
}
?>