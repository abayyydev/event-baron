<?php
// core/koneksi.php

// --- PERBAIKAN: Cek dulu apakah sudah didefinisikan ---
// core/koneksi.php

if (!defined('BASE_URL')) {
    // Cek protokol (http atau https)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

    // Cek host (localhost atau nama domain)
    $host = $_SERVER['HTTP_HOST'];

    // Deteksi otomatis
    if ($host == 'localhost') {
        // Settingan Localhost (Sesuaikan foldernya)
        define('BASE_URL', 'http://localhost/workshop-app-baron/');
    } else {
        // Settingan Hosting (Otomatis ambil nama domain/subdomain)
        define('BASE_URL', $protocol . "://" . $host . "/");
    }
}
// ------------------------------------------------------

$host = 'localhost';
$db = 'db_workshop_app'; // Pastikan nama DB benar
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int) $e->getCode());
}
?>