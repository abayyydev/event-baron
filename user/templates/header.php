<?php
// user/templates/header.php

// 1. Start Session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. DEFINE BASE_URL (Opsional, agar link tidak broken)
// Sesuaikan dengan folder project Anda di localhost
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    // Ganti 'workshop-app-baron' sesuai nama folder di htdocs/www laragon Anda
    define('BASE_URL', $protocol . "://" . $host . "/workshop-app-baron/");
}

// 3. INCLUDE KONEKSI (Gunakan __DIR__ agar path akurat)
// __DIR__ = user/templates/
// Naik 2 level ke atas (../../) untuk mencari folder core
require_once __DIR__ . '/../../core/koneksi.php';

// 4. CEK LOGIN OTOMATIS
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'peserta') {
    header("Location: " . BASE_URL . "login.php");
    exit;
}

// Default Title
if (!isset($page_title))
    $page_title = "Dashboard Peserta";
if (!isset($current_page))
    $current_page = "dashboard";

// Data User untuk Navbar
$nama_user_nav = $_SESSION['nama_user'] ?? 'Peserta';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | Ponpes Al Ihsan Baron</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#166534', // Hijau Ponpes
                        secondary: '#D4AF37', // Emas
                        dark: '#0f4c25',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .sidebar-active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid #D4AF37;
            /* Emas */
            color: #fff;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">

    <div class="flex h-screen overflow-hidden">

        <aside class="hidden md:flex flex-col w-64 bg-primary text-white shadow-xl z-20">
            <div class="h-16 flex items-center justify-center border-b border-green-800 bg-dark px-4">
                <div class="flex items-center gap-2 font-bold text-lg tracking-wide">
                    <i class="fas fa-mosque text-secondary"></i>
                    <span>SANTRI PANEL</span>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto py-4">
                <nav class="space-y-1 px-2">

                    <a href="dashboard.php"
                        class="flex items-center px-4 py-3 text-sm font-medium rounded-r-lg transition-colors hover:bg-green-700 <?= $current_page == 'dashboard' ? 'sidebar-active' : 'text-green-100' ?>">
                        <i class="fas fa-home w-6"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="riwayat_transaksi.php"
                        class="flex items-center px-4 py-3 text-sm font-medium rounded-r-lg transition-colors hover:bg-green-700 <?= $current_page == 'transaksi' ? 'sidebar-active' : 'text-green-100' ?>">
                        <i class="fas fa-receipt w-6"></i>
                        <span>Riwayat Transaksi</span>
                    </a>

                    <a href="materi.php"
                        class="flex items-center px-4 py-3 text-sm font-medium rounded-r-lg transition-colors hover:bg-green-700 <?= $current_page == 'materi' ? 'sidebar-active' : 'text-green-100' ?>">
                        <i class="fas fa-book-open w-6"></i>
                        <span>Materi Belajar</span>
                    </a>

                    <div class="pt-4 mt-4 border-t border-green-700">
                        <p class="px-4 text-xs font-semibold text-green-300 uppercase tracking-wider mb-2">Akun</p>

                        <a href="profil.php"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-r-lg transition-colors hover:bg-green-700 <?= $current_page == 'profil' ? 'sidebar-active' : 'text-green-100' ?>">
                            <i class="fas fa-user-cog w-6"></i>
                            <span>Edit Profil</span>
                        </a>

                        <a href="<?= BASE_URL ?>logout"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-r-lg text-red-200 hover:bg-red-900/50 hover:text-white transition-colors">
                            <i class="fas fa-sign-out-alt w-6"></i>
                            <span>Keluar</span>
                        </a>
                    </div>

                </nav>
            </div>

            <div class="p-4 border-t border-green-800 bg-dark">
                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-8 rounded-full bg-secondary flex items-center justify-center text-primary font-bold">
                        <?= strtoupper(substr($nama_user_nav, 0, 1)) ?>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm font-medium text-white truncate"><?= htmlspecialchars($nama_user_nav) ?></p>
                        <p class="text-xs text-green-300">Peserta</p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden relative">

            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-4 lg:px-8 z-10 relative">

                <button id="mobile-menu-btn" class="md:hidden text-gray-600 focus:outline-none">
                    <i class="fas fa-bars text-2xl"></i>
                </button>

                <h2 class="hidden md:block text-xl font-bold text-gray-800"><?= $page_title ?></h2>

                <div class="flex items-center gap-4">
                    <button class="relative p-2 text-gray-400 hover:text-primary transition">
                        <i class="far fa-bell text-xl"></i>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>

                    <div class="relative group">
                        <button class="flex items-center gap-2 focus:outline-none">
                            <span
                                class="text-sm font-medium text-gray-700 hidden md:block"><?= htmlspecialchars($nama_user_nav) ?></span>
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($nama_user_nav) ?>&background=166534&color=fff"
                                alt="Avatar" class="w-8 h-8 rounded-full border border-gray-200">
                        </button>
                        <div
                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden group-hover:block border border-gray-100">
                            <a href="profil.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profil
                                Saya</a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="<?= BASE_URL ?>logout"
                                class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Logout</a>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 lg:p-8">