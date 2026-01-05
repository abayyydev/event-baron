<?php
// core/koneksi.php

// --- PERBAIKAN: Cek dulu apakah sudah didefinisikan ---
if (!defined('BASE_URL')) {
    // Sesuaikan dengan nama folder project kamu di Laragon
    define('BASE_URL', 'http://localhost/workshop-app-baron/');
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