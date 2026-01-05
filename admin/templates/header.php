<?php
// Mulai sesi
session_start();


// Cegah caching supaya tombol Back setelah logout tidak menampilkan halaman lama
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Cek sesi login & role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penyelenggara') {
    header("Location: ../login.php");
    exit();
}


$is_owner = !isset($_SESSION['owner_id']) || $_SESSION['owner_id'] === null;

// Data user dari session
$nama_user = $_SESSION['nama_lengkap'] ?? 'User';
$current_page = $current_page ?? '';
$foto_profil = $_SESSION['foto_profil'] ?? '../assets/img/admin.jpg';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Admin Panel' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css">

    <style>
        :root {
            --primary-color: #166534;
            /* Hijau utama */
            --primary-light: #ECFDF5;
            /* Hijau muda */
            --primary-dark: #14532D;
            /* Hijau gelap */
            --secondary-color: #16A34A;
            /* Hijau secondary */
            --accent-color: #4ADE80;
            /* Hijau accent */
        }


        /* Fix untuk sidebar full height */
        html,
        body {
            height: 100%;
        }

        .sidebar {
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 50;
            transition: transform 0.3s ease-in-out;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            background: linear-gradient(180deg,
                    var(--primary-color) 0%,
                    var(--primary-dark) 100%);
        }

        .sidebar-collapsed {
            transform: translateX(-100%);
        }

        @media (min-width: 1024px) {
            .sidebar {
                transform: translateX(0);
                height: 100vh;
                position: fixed;
            }

            .sidebar-collapsed {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 16rem;
                width: calc(100% - 16rem);
            }

            .main-content-expanded {
                margin-left: 0;
                width: 100%;
            }

            /* Sembunyikan overlay di desktop */
            .overlay {
                display: none !important;
            }
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 40;
        }

        .overlay.active {
            display: block;
        }

        /* Custom scrollbar untuk sidebar */
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: var(--primary-light);
            border-radius: 4px;
            opacity: 0.3;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background-color: var(--accent-color);
        }

        /* Efek hover untuk menu */
        .menu-item {
            transition: all 0.2s ease;
        }

        .menu-item:hover {
            transform: translateX(5px);
            background: rgba(255, 255, 255, 0.1) !important;
        }

        /* Glass effect untuk header */
        .glass-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }


        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Overlay for mobile sidebar -->
    <div class="overlay" id="sidebarOverlay"></div>

    <div class="flex h-full">
        <!-- Sidebar -->
        <div class="sidebar w-64 text-white flex flex-col shadow-lg overflow-y-auto" id="sidebar">
            <!-- Header Sidebar -->
            <div
                class="px-6 py-4 border-b border-green-700 flex justify-between items-center bg-green-900/50 sticky top-0 z-10">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
                        <img src="../assets/img/images/logo.png" alt="Logo PONPES Al Ihsan Baron" class="w-5 h-4">
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white">PONPES Al Ihsan Baron</h2>
                        <p class="text-xs text-green-200">Admin Panel</p>
                    </div>
                </div>
                <button id="closeSidebar" class="text-green-200 hover:text-white lg:hidden transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- User Profile Section -->
            <div class="px-6 py-4 border-b border-green-700 flex items-center bg-green-900/30">
                <div class="relative">
                    <img src="<?= htmlspecialchars($foto_profil) ?>" alt="Profile"
                        class="w-12 h-12 rounded-full object-cover border-2 border-accent shadow-lg">
                    <span
                        class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-green-900"></span>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-semibold text-white truncate"><?= htmlspecialchars($nama_user) ?></p>
                    <p class="text-xs text-green-200">Penyelenggara</p>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-grow px-3 py-4 space-y-1">
                <a href="dashboard"
                    class="menu-item flex items-center px-4 py-3 rounded-lg text-green-100 hover:text-white hover:bg-green-700/50 transition-all <?= ($current_page == 'dashboard') ? 'bg-green-700/50 text-white font-semibold shadow-lg' : '' ?>">
                    <i class="fas fa-tachometer-alt w-5 mr-3 text-center text-accent"></i>
                    <span>Dashboard</span>
                    <?= ($current_page == 'dashboard') ? '<span class="ml-auto w-2 h-2 bg-accent rounded-full"></span>' : '' ?>
                </a>

                <a href="kelola_event"
                    class="menu-item flex items-center px-4 py-3 rounded-lg text-green-100 hover:text-white hover:bg-green-700/50 transition-all <?= ($current_page == 'kelola_event') ? 'bg-green-700/50 text-white font-semibold shadow-lg' : '' ?>">
                    <i class="fas fa-calendar-alt w-5 mr-3 text-center text-accent"></i>
                    <span>Kelola Event</span>
                    <?= ($current_page == 'kelola_event') ? '<span class="ml-auto w-2 h-2 bg-accent rounded-full"></span>' : '' ?>
                </a>

                <a href="kelola_pendaftar"
                    class="menu-item flex items-center px-4 py-3 rounded-lg text-green-100 hover:text-white hover:bg-green-700/50 transition-all <?= ($current_page == 'data_pendaftar') ? 'bg-green-700/50 text-white font-semibold shadow-lg' : '' ?>">
                    <i class="fas fa-users w-5 mr-3 text-center text-accent"></i>
                    <span>Data Pendaftar</span>
                    <?= ($current_page == 'data_pendaftar') ? '<span class="ml-auto w-2 h-2 bg-accent rounded-full"></span>' : '' ?>
                </a>

                <?php if ($is_owner): ?>
                    <a href="kelola_tim"
                        class="menu-item flex items-center px-4 py-3 rounded-lg text-green-100 hover:text-white hover:bg-green-700/50 transition-all <?= ($current_page == 'kelola_tim') ? 'bg-green-700/50 text-white font-semibold shadow-lg' : '' ?>">
                        <i class="fas fa-users-cog w-5 mr-3 text-center text-accent"></i>
                        <span>Kelola Tim</span>
                        <?= ($current_page == 'kelola_tim') ? '<span class="ml-auto w-2 h-2 bg-accent rounded-full"></span>' : '' ?>
                    </a>
                <?php endif; ?>
                <a href="kelola_font.php"
                    class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-800 hover:text-white transition-colors <?= $current_page == 'kelola_font' ? 'bg-slate-800 text-white border-r-4 border-amber-500' : '' ?>">
                    <i class="fas fa-font w-6 text-center mr-2"></i>
                    <span class="font-medium">Kelola Font</span>
                </a>
            </nav>

            <!-- Logout Button in Sidebar -->
            <div class="px-3 py-4 border-t border-green-700 sticky bottom-0 bg-green-900/50 backdrop-blur-sm">
                <a href="../logout"
                    class="menu-item flex items-center px-4 py-3 rounded-lg text-red-200 hover:text-white hover:bg-red-600/50 transition-all group">
                    <i class="fas fa-sign-out-alt w-5 mr-3 text-center group-hover:animate-pulse"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content flex-1 flex flex-col transition-all duration-300" id="main-content">
            <!-- Header -->
            <header class="glass-header shadow-lg p-4 flex justify-between items-center sticky top-0 z-30">
                <div class="flex items-center space-x-4">
                    <button id="toggleSidebar"
                        class="text-primary hover:text-secondary transition-colors focus:outline-none lg:hidden">
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <!-- Breadcrumb dan Page Title -->
                    <div class="flex flex-col">
                        <h1 class="text-xl font-bold gradient-text">
                            <?= isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard' ?>
                        </h1>
                        <nav class="flex items-center space-x-1 text-sm text-gray-600">
                            <a href="dashboard" class="hover:text-primary transition-colors">Admin</a>
                            <span class="text-gray-400">/</span>
                            <span
                                class="text-gray-800 font-medium"><?= isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard' ?></span>
                        </nav>
                    </div>
                </div>

                <!-- User info on header -->
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <button class="relative p-2 text-gray-600 hover:text-primary transition-colors">
                        <i class="fas fa-bell text-lg"></i>
                        <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>

                    <!-- User Profile -->
                    <div class="flex items-center space-x-3">
                        <span class="text-gray-700 hidden md:inline text-sm">
                            Halo, <strong class="text-primary"><?= htmlspecialchars($nama_user) ?></strong>
                        </span>
                        <div class="relative group">
                            <img src="<?= htmlspecialchars($foto_profil) ?>" alt="Profile"
                                class="w-10 h-10 rounded-full object-cover border-2 border-primary shadow-md cursor-pointer transition-transform group-hover:scale-105">
                            <span
                                class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></span>

                            <!-- Dropdown Menu -->
                            <div
                                class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-40">
                                <div class="p-4 border-b border-gray-100">
                                    <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($nama_user) ?>
                                    </p>
                                    <p class="text-xs text-gray-600">Penyelenggara</p>
                                </div>
                                <a href="../logout"
                                    class="flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fas fa-sign-out-alt mr-3"></i>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-grow p-4 md:p-6 bg-gradient-to-br from-gray-50 to-green-50/30 min-h-[calc(100vh-80px)]">
                <!-- Content will be inserted here from the individual pages -->