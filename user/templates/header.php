<?php
// user/templates/header.php

// 1. Mulai sesi aman
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Cegah caching browser (agar logout beneran bersih)
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 3. Cek Login & Role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'peserta') {
    // Jika bukan peserta, tendang ke login
    header("Location: ../login.php");
    exit();
}

// 4. Data User
$nama_user = $_SESSION['nama_lengkap'] ?? 'Santri';

// --- LOGIKA PERBAIKAN PATH FOTO PROFIL ---
// Karena file ini ada di folder "user/", maka akses ke assets harus naik satu level "../"
$foto_db = $_SESSION['foto_profil'] ?? '';

if (!empty($foto_db)) {
    // Cek apakah di database tersimpan path lengkap "assets/..."
    if (strpos($foto_db, 'assets/') !== false) {
        // Jika iya, tambahkan "../" didepannya
        $foto_profil = '../' . $foto_db;
    } else {
        // Jika hanya nama file, arahkan manual ke folder upload
        $foto_profil = '../assets/uploads/profil/' . $foto_db;
    }
} else {
    // Foto default
    $foto_profil = '../assets/img/default-avatar.png';
}

// 5. Setup Halaman Aktif (Default Dashboard)
$page_title = $page_title ?? 'Dashboard Santri';
$current_page = $current_page ?? basename($_SERVER['PHP_SELF'], ".php");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> | Panel Santri</title>

    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                    colors: {
                        primary: {
                            50: '#f0fdf6', 100: '#dcfce8', 200: '#bbf7d0', 300: '#86efac',
                            400: '#4ade80', 500: '#22c55e', 600: '#16a34a', 700: '#15803d',
                            800: '#166534', 900: '#14532d', 950: '#052e16',
                        },
                        gold: {
                            100: '#fef3c7', 400: '#fbbf24', 500: '#f59e0b', 600: '#d97706',
                        }
                    },
                    boxShadow: {
                        'soft': '0 4px 20px -2px rgba(0, 0, 0, 0.08)',
                        'glow': '0 0 15px rgba(245, 158, 11, 0.3)',
                    }
                }
            }
        }
    </script>

    <style>
        /* Custom Styles */
        body { font-family: 'Inter', sans-serif; }
        
        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .no-scrollbar::-webkit-scrollbar { display: none; }

        /* Sidebar Transitions */
        .sidebar-transition { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        
        /* Collapsed Sidebar Logic */
        #sidebar.collapsed { width: 5rem; }
        #sidebar.collapsed .menu-text, 
        #sidebar.collapsed .group-title,
        #sidebar.collapsed .logo-text { display: none !important; }
        #sidebar.collapsed .menu-link { justify-content: center; padding-left: 0; padding-right: 0; }
        #sidebar.collapsed .logo-container { justify-content: center; padding-left: 0; }
        
        /* Menu Active State */
        .menu-link.active {
            background: linear-gradient(135deg, #166534 0%, #15803d 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(22, 101, 52, 0.3);
            border-right: 3px solid #f59e0b;
        }
        .menu-link.active i { color: #fbbf24; }
        
        /* Glass Effect for Header */
        .glass-header {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased overflow-hidden">

    <div class="flex h-screen relative">

        <div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden transition-opacity duration-300 opacity-0"></div>

        <aside id="sidebar" class="sidebar-transition absolute lg:relative z-50 w-64 h-full bg-white border-r border-slate-200 flex flex-col transform -translate-x-full lg:translate-x-0">
            
            <div class="h-20 flex items-center px-6 border-b border-slate-100 logo-container bg-primary-900 text-white">
                <div class="flex items-center gap-3 w-full">
                    <img src="../assets/img/images/logo-pondok.png" alt="Logo" class="h-10 w-10 object-contain drop-shadow-md">
                    <div class="logo-text overflow-hidden whitespace-nowrap">
                        <h1 class="font-bold text-lg leading-tight tracking-wide">AL IHSAN</h1>
                        <p class="text-[10px] text-gold-400 font-medium uppercase tracking-wider">Panel Santri</p>
                    </div>
                </div>
                <button id="closeSidebarMobile" class="lg:hidden absolute right-4 text-white/70 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-1 no-scrollbar">
                
                <?php
                // Helper function untuk render menu
                function renderMenu($link, $icon, $label, $activePage) {
                    $isActive = ($activePage == basename($link, ".php"));
                    $activeClass = $isActive ? 'active' : 'text-slate-600 hover:bg-slate-50 hover:text-primary-700';
                    
                    echo '
                    <a href="'.$link.'" class="'.$activeClass.' menu-link flex items-center px-4 py-3.5 rounded-xl transition-all duration-200 group mb-1" title="'.$label.'">
                        <i class="fas '.$icon.' w-6 text-center text-lg transition-colors group-hover:scale-110"></i>
                        <span class="menu-text ml-3 text-sm font-medium tracking-wide">'.$label.'</span>
                    </a>';
                }

                function renderGroup($title) {
                    echo '<div class="group-title px-4 mt-6 mb-2 text-xs font-bold text-slate-400 uppercase tracking-wider">'.$title.'</div>';
                }
                ?>

                <?php renderGroup('Menu Utama'); ?>
                <?php renderMenu('dashboard.php', 'fa-home', 'Dashboard', $current_page); ?>
                <?php renderMenu('katalog_event.php', 'fa-calendar-alt', 'Katalog Event', $current_page); ?>
                <?php renderMenu('tiket_saya.php', 'fa-ticket-alt', 'Tiket Saya', $current_page); ?>
                
                <?php renderGroup('Aktivitas'); ?>
                <?php renderMenu('riwayat_transaksi.php', 'fa-receipt', 'Riwayat Transaksi', $current_page); ?>
                <?php renderMenu('materi.php', 'fa-book-open', 'Materi Belajar', $current_page); ?>
                <?php renderMenu('sertifikat.php', 'fa-certificate', 'E-Sertifikat', $current_page); ?>

                <?php renderGroup('Akun'); ?>
                <?php renderMenu('profil.php', 'fa-user-circle', 'Profil Saya', $current_page); ?>
                
            </nav>

            <div class="p-4 border-t border-slate-100 bg-slate-50">
                <a href="../logout.php" class="flex items-center px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors w-full group">
                    <i class="fas fa-sign-out-alt w-6 text-center group-hover:rotate-180 transition-transform duration-300"></i>
                    <span class="menu-text ml-3 text-sm font-bold">Keluar</span>
                </a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col h-full relative w-full transition-all duration-300">

            <header class="h-20 glass-header flex items-center justify-between px-6 sticky top-0 z-30 shadow-sm">
                
                <div class="flex items-center gap-4">
                    <button id="sidebarToggleBtn" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-primary-700 transition-colors focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800 tracking-tight"><?= htmlspecialchars($page_title) ?></h2>
                        <p class="text-xs text-slate-500 hidden sm:block">Sistem Informasi Manajemen Event</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>

                    <div class="relative group">
                        <button class="flex items-center gap-3 focus:outline-none">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-bold text-slate-700 leading-none"><?= htmlspecialchars($nama_user) ?></p>
                                <span class="text-[10px] font-semibold text-primary-600 bg-primary-100 px-2 py-0.5 rounded-full inline-block mt-1">Santri</span>
                            </div>
                            <div class="relative">
                                <img src="<?= htmlspecialchars($foto_profil) ?>" alt="Profile" class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-md group-hover:border-primary-400 transition-colors">
                                <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></div>
                            </div>
                            <i class="fas fa-chevron-down text-slate-400 text-xs sm:block hidden group-hover:text-primary-600 transition-colors"></i>
                        </button>

                        <div class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right z-50">
                            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50">
                                <p class="text-xs text-slate-500">Login sebagai</p>
                                <p class="text-sm font-bold text-slate-800 truncate"><?= htmlspecialchars($nama_user) ?></p>
                            </div>
                            <a href="profil.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700">
                                <i class="far fa-user mr-2 w-4"></i> Edit Profil
                            </a>
                            <a href="ganti_password.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700">
                                <i class="fas fa-key mr-2 w-4"></i> Ganti Password
                            </a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <a href="../logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 font-medium">
                                <i class="fas fa-sign-out-alt mr-2 w-4"></i> Keluar
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8 bg-slate-50 custom-scrollbar">
                
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const sidebar = document.getElementById('sidebar');
                        const toggleBtn = document.getElementById('sidebarToggleBtn');
                        const closeBtnMobile = document.getElementById('closeSidebarMobile');
                        const overlay = document.getElementById('mobile-overlay');

                        // Cek LocalStorage untuk status sidebar di Desktop
                        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                        if (window.innerWidth >= 1024 && isCollapsed) {
                            sidebar.classList.add('collapsed');
                        }

                        function toggleSidebar() {
                            if (window.innerWidth < 1024) {
                                // Mobile Logic
                                const isClosed = sidebar.classList.contains('-translate-x-full');
                                if (isClosed) {
                                    sidebar.classList.remove('-translate-x-full');
                                    overlay.classList.remove('hidden');
                                    setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                                } else {
                                    closeMobileSidebar();
                                }
                            } else {
                                // Desktop Logic
                                sidebar.classList.toggle('collapsed');
                                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                            }
                        }

                        function closeMobileSidebar() {
                            sidebar.classList.add('-translate-x-full');
                            overlay.classList.add('opacity-0');
                            setTimeout(() => overlay.classList.add('hidden'), 300);
                        }

                        toggleBtn.addEventListener('click', (e) => {
                            e.stopPropagation();
                            toggleSidebar();
                        });

                        closeBtnMobile.addEventListener('click', closeMobileSidebar);
                        overlay.addEventListener('click', closeMobileSidebar);

                        // Handle Resize
                        window.addEventListener('resize', () => {
                            if (window.innerWidth >= 1024) {
                                overlay.classList.add('hidden', 'opacity-0');
                                sidebar.classList.remove('-translate-x-full');
                            } else {
                                sidebar.classList.add('-translate-x-full');
                                sidebar.classList.remove('collapsed');
                            }
                        });
                    });
                </script>