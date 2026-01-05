<?php
session_start();

// Hapus semua data session
$_SESSION = [];

// Jika ada cookie session, hapus juga (opsional tapi direkomendasikan)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Cegah caching supaya halaman tidak bisa diakses kembali setelah logout
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Redirect ke halaman login
header("Location: login.php");
exit();
