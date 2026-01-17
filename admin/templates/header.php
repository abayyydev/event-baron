<?php
// Mulai sesi jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cegah caching agar user tidak bisa back setelah logout
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Cek sesi login & role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penyelenggara') {
    header("Location: ../login.php");
    exit();
}

$is_owner = !isset($_SESSION['owner_id']) || $_SESSION['owner_id'] === null;

// Data user dari session
$nama_user = $_SESSION['nama_lengkap'] ?? 'User';

// --- LOGIKA PERBAIKAN PATH FOTO PROFIL ---
$foto_db = $_SESSION['foto_profil'] ?? '';

if (!empty($foto_db)) {
    // Cek apakah string path mengandung "assets/"
    if (strpos($foto_db, 'assets/') !== false) {
        // Jika di database tersimpan "assets/uploads/...", tambahkan "../" di depannya
        // karena file ini (header.php) dipanggil oleh file di dalam folder admin/
        $foto_profil = '../' . $foto_db;
    } else {
        // Jika di database hanya nama file (misal: "foto.jpg"), arahkan ke folder upload yang benar
        $foto_profil = '../assets/uploads/profil/' . $foto_db;
    }
} else {
    // Foto default jika kosong
    $foto_profil = '../assets/img/download.jpg';
}

// Pastikan current_page terdefinisi
$current_page = $current_page ?? basename($_SERVER['PHP_SELF'], ".php");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Admin Panel' ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        // Palet warna modern 2024 - Hijau & Emas
                        primary: {
                            50: '#f0fdf6',
                            100: '#dcfce8',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',   // Hijau utama
                            700: '#15803d',   // Hijau gelap
                            800: '#166534',   // Hijau sidebar
                            900: '#14532d',   // Hijau paling gelap
                            950: '#052e16',
                        },
                        gold: {
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#f59e0b',   // Emas utama
                            600: '#d97706',
                            700: '#b45309',
                            800: '#92400e',
                            900: '#78350f',
                        },
                        slate: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.3s ease-in-out',
                        'slide-in': 'slideIn 0.3s ease-out',
                        'pulse-subtle': 'pulseSubtle 2s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideIn: {
                            '0%': { transform: 'translateX(-100%)' },
                            '100%': { transform: 'translateX(0)' },
                        },
                        pulseSubtle: {
                            '0%, 100%': { opacity: '1' },
                            '50%': { opacity: '0.8' },
                        }
                    },
                    boxShadow: {
                        'soft': '0 4px 20px -2px rgba(0, 0, 0, 0.08)',
                        'soft-lg': '0 10px 40px -4px rgba(0, 0, 0, 0.12)',
                        'gold-glow': '0 0 20px rgba(245, 158, 11, 0.15)',
                    }
                }
            }
        }
    </script>

    <style>
        /* Smooth Glassmorphism Effects */
        .glassmorphism {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .glassmorphism-dark {
            background: rgba(22, 101, 52, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(245, 158, 11, 0.1);
        }

        /* Modern Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #16a34a, #f59e0b);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #15803d, #d97706);
        }

        /* Hide scrollbar for cleaner look */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(135deg, #16a34a 0%, #f59e0b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Custom transitions */
        .sidebar-transition {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .menu-transition {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Utility classes for collapsed state */
        #sidebar.collapsed {
            width: 5rem;
        }

        #sidebar.collapsed .menu-text,
        #sidebar.collapsed .group-title,
        #sidebar.collapsed .logo-full {
            display: none !important;
            opacity: 0;
            width: 0;
        }

        #sidebar.collapsed .logo-icon {
            display: block !important;
        }

        #sidebar.collapsed .menu-link {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
            border-radius: 12px;
        }

        #sidebar.collapsed .menu-icon {
            margin-right: 0;
            transform: scale(1.1);
        }

        #sidebar.collapsed .user-profile {
            justify-content: center;
            padding: 0.75rem;
        }

        #sidebar.collapsed .user-info {
            display: none;
        }

        /* Active state glow effect */
        .active-glow {
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.25);
        }

        /* Logo switching */
        .logo-icon {
            display: none;
        }

        /* Modern card styling */
        .modern-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(22, 163, 74, 0.1);
            transition: all 0.3s ease;
        }

        .modern-card:hover {
            box-shadow: 0 10px 40px -4px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-50 to-primary-50 text-slate-800 font-sans antialiased overflow-hidden">

    <div class="flex h-screen relative">

        <!-- Mobile Overlay -->
        <div id="mobile-overlay"
            class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 hidden lg:hidden transition-all duration-300 opacity-0">
        </div>

        <!-- Modern Sidebar -->
        <aside id="sidebar"
            class="sidebar-transition absolute lg:relative z-50 w-64 h-full bg-gradient-to-b from-primary-800 via-primary-900 to-primary-950 text-white flex flex-col transform -translate-x-full lg:translate-x-0 shadow-2xl border-r border-gold-500/20">

            <!-- Sidebar Header -->
            <div
                class="h-20 flex items-center justify-center border-b border-white/10 relative px-4 shrink-0 glassmorphism-dark">
                <div class="logo-full transition-all duration-300 flex items-center justify-start pl-2">
                    <img src="../assets/img/images/admin-ajax_2.png" alt="Logo Pondok"
                        class="h-10 w-auto object-contain drop-shadow-md hover:scale-105 transition-transform duration-300">
                </div>

                <div class="logo-icon transition-all duration-300">
                    <img src="../assets/img/images/logo-pondok.png" alt="Logo Pondok"
                        class="h-10 w-auto object-contain drop-shadow-md hover:scale-105 transition-transform duration-300">
                </div>

                <button id="closeSidebarMobile"
                    class="lg:hidden absolute right-4 text-gold-300 hover:text-gold-100 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1 no-scrollbar">
                <?php
                function renderMenu($link, $icon, $label, $activePage)
                {
                    // Cek aktif
                    $isActive = ($activePage == $link || strpos($activePage, $link) !== false);

                    // Desain modern dengan indikator aktif
                    $baseClass = "menu-link flex items-center px-4 py-3 rounded-xl transition-all duration-200 group mb-1 relative overflow-hidden menu-transition";

                    if ($isActive) {
                        // Active State dengan gradient
                        $colors = "bg-gradient-to-r from-primary-700/80 to-primary-800 text-white shadow-lg active-glow";
                        $iconColor = "text-gold-400";
                        $indicator = '<div class="absolute right-3 w-1.5 h-1.5 rounded-full bg-gold-400 animate-pulse-subtle"></div>';
                    } else {
                        // Inactive State dengan hover effect
                        $colors = "text-slate-300 hover:bg-white/5 hover:text-white hover:shadow-md hover:translate-x-1";
                        $iconColor = "text-slate-400 group-hover:text-gold-300 group-hover:scale-110 transition-all";
                        $indicator = '';
                    }

                    echo '
                    <a href="' . $link . '" class="' . $baseClass . ' ' . $colors . '" title="' . $label . '">
                        <div class="relative">
                            <i class="fas ' . $icon . ' ' . $iconColor . ' w-5 text-center text-base menu-icon flex-shrink-0 transition-all"></i>
                        </div>
                        <span class="menu-text ml-3 text-sm font-medium tracking-wide whitespace-nowrap transition-opacity duration-300">' . $label . '</span>
                        ' . $indicator . '
                        <div class="absolute inset-0 bg-gradient-to-r from-gold-500/0 to-gold-500/0 group-hover:from-gold-500/5 group-hover:to-gold-500/10 transition-all duration-300"></div>
                    </a>';
                }

                function renderGroupTitle($title)
                {
                    echo '<p class="group-title px-4 text-[11px] font-bold text-primary-300/60 uppercase tracking-widest mt-6 mb-3 flex items-center">
                            <span class="h-px flex-1 bg-primary-700/50 mr-3"></span>
                            ' . $title . '
                            <span class="h-px flex-1 bg-primary-700/50 ml-3"></span>
                          </p>';
                }
                ?>

                <?php renderGroupTitle('Navigasi Utama'); ?>
                <?php renderMenu('dashboard', 'fa-home', 'Dashboard', $current_page); ?>
                <?php renderMenu('kelola_event', 'fa-calendar-alt', 'Kelola Event', $current_page); ?>
                <?php renderMenu('kelola_pendaftar', 'fa-clipboard-list', 'Data Pendaftar', $current_page); ?>

                <?php if ($is_owner): ?>
                    <?php renderGroupTitle('Administrator'); ?>
                    <?php renderMenu('kelola_tim', 'fa-user-tie', 'Kelola Tim', $current_page); ?>
                    <?php renderMenu('kelola_user', 'fa-user-group', 'Data Santri', $current_page); ?>

                <?php endif; ?>
                <?php renderMenu('diskusi', 'fa-comments', 'Ruang Diskusi', $current_page); ?>
                <?php renderGroupTitle('Pengaturan'); ?>
                <?php renderMenu('kelola_font', 'fa-font', 'Kelola Font', $current_page); ?>
            </nav>

            <!-- User Profile & Logout -->

        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col h-full relative overflow-hidden bg-gradient-to-br from-slate-50 to-primary-50">

            <!-- Modern Header -->
            <header
                class="h-20 bg-white/80 backdrop-blur-md border-b border-slate-200/60 flex items-center justify-between px-6 sticky top-0 z-30 shadow-soft">

                <div class="flex items-center gap-4">
                    <button id="sidebarToggleBtn"
                        class="p-2.5 rounded-xl text-slate-600 hover:bg-primary-50 hover:text-primary-700 focus:outline-none transition-all duration-200 hover:shadow-sm border border-slate-200 hover:border-primary-300">
                        <i class="fas fa-bars text-lg"></i>
                    </button>

                    <div class="ml-2">
                        <h1 class="text-xl font-bold text-slate-900 leading-tight gradient-text">
                            <?= isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard' ?>
                        </h1>
                        <p class="text-sm text-slate-500 mt-0.5">Panel Kontrol Penyelenggara</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Notification Bell -->
                    <div class="relative">
                        <button
                            class="relative p-2.5 rounded-xl text-slate-500 hover:text-gold-600 hover:bg-gold-50 transition-all duration-200 group">
                            <i class="far fa-bell text-xl"></i>
                            <span
                                class="absolute top-2 right-3 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white animate-pulse"></span>
                            <span
                                class="absolute -top-1 -right-1 w-5 h-5 bg-gold-500 rounded-full text-[10px] font-bold text-white flex items-center justify-center border border-white shadow-sm">3</span>
                        </button>
                    </div>

                    <div class="h-10 w-px bg-gradient-to-b from-transparent via-slate-300 to-transparent"></div>

                    <!-- User Profile Dropdown -->
                    <div class="relative group">
                        <button class="flex items-center gap-3 focus:outline-none group">
                            <div class="text-right hidden lg:block">
                                <p class="text-sm font-bold text-slate-800 leading-none">
                                    <?= htmlspecialchars($nama_user) ?>
                                </p>
                                <div class="flex items-center justify-end gap-1 mt-0.5">
                                    <span
                                        class="text-xs text-primary-600 font-semibold px-2 py-0.5 rounded-full bg-primary-100">Penyelenggara</span>
                                </div>
                            </div>
                            <div class="relative">
                                <img src="<?= htmlspecialchars($foto_profil) ?>" alt="Profile"
                                    class="w-11 h-11 rounded-xl object-cover border-2 border-primary-200 shadow-sm group-hover:border-gold-400 group-hover:shadow-gold-glow transition-all duration-300">
                                <div
                                    class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-green-500 border-2 border-white">
                                </div>
                            </div>
                            <i
                                class="fas fa-chevron-down text-slate-400 group-hover:text-gold-500 text-xs transition-transform group-hover:rotate-180 duration-200"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            class="absolute right-0 mt-4 w-56 bg-white rounded-2xl shadow-soft-lg border border-slate-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right z-50 overflow-hidden animate-fade-in">
                            <!-- Header dropdown -->
                            <div
                                class="px-4 py-3 border-b border-slate-100 bg-gradient-to-r from-primary-50 to-gold-50">
                                <p class="text-sm font-bold text-slate-900 truncate"><?= htmlspecialchars($nama_user) ?>
                                </p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span
                                        class="text-xs font-medium text-primary-700 bg-primary-100 px-2 py-0.5 rounded-full">Penyelenggara</span>
                                    <span class="text-xs text-slate-500">•</span>
                                    <span class="text-xs text-slate-500">Online</span>
                                </div>
                            </div>

                            <!-- Menu items -->
                            <div class="py-2">
                                <a href="profil_penyelenggara.php"
                                    class="flex items-center px-4 py-2.5 text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700 transition-colors group/item">
                                    <i class="far fa-user mr-3 w-4 text-primary-500 group-hover/item:text-gold-500"></i>
                                    <span>Profil Saya</span>
                                </a>
                            </div>

                            <div class="border-t border-slate-100 my-1"></div>

                            <a href="../logout.php"
                                class="flex items-center px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors group/item">
                                <i
                                    class="fas fa-sign-out-alt mr-3 w-4 group-hover/item:rotate-90 transition-transform"></i>
                                <span>Keluar Sistem</span>
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-6 custom-scrollbar">


                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const sidebar = document.getElementById('sidebar');
                        const toggleBtn = document.getElementById('sidebarToggleBtn');
                        const closeBtnMobile = document.getElementById('closeSidebarMobile');
                        const overlay = document.getElementById('mobile-overlay');

                        // Cek LocalStorage untuk preferensi user
                        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

                        // Apply state awal saat load
                        if (window.innerWidth >= 1024 && isCollapsed) {
                            sidebar.classList.add('collapsed');
                        } else {
                            sidebar.classList.remove('collapsed');
                        }

                        // Fungsi toggle sidebar
                        function toggleSidebar() {
                            if (window.innerWidth < 1024) {
                                // Logic Mobile
                                const isClosed = sidebar.classList.contains('-translate-x-full');
                                if (isClosed) {
                                    openMobileSidebar();
                                } else {
                                    closeMobileSidebar();
                                }
                            } else {
                                // Logic Desktop
                                sidebar.classList.toggle('collapsed');
                                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                            }
                        }

                        function openMobileSidebar() {
                            sidebar.classList.remove('-translate-x-full');
                            overlay.classList.remove('hidden');
                            setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                        }

                        function closeMobileSidebar() {
                            sidebar.classList.add('-translate-x-full');
                            overlay.classList.add('opacity-0');
                            setTimeout(() => overlay.classList.add('hidden'), 300);
                        }

                        // Event Listeners
                        toggleBtn.addEventListener('click', (e) => {
                            e.stopPropagation();
                            toggleSidebar();
                        });

                        if (closeBtnMobile) {
                            closeBtnMobile.addEventListener('click', closeMobileSidebar);
                        }

                        overlay.addEventListener('click', closeMobileSidebar);

                        // Reset saat resize window
                        window.addEventListener('resize', () => {
                            if (window.innerWidth >= 1024) {
                                overlay.classList.add('hidden', 'opacity-0');
                                sidebar.classList.remove('-translate-x-full');

                                const shouldCollapse = localStorage.getItem('sidebarCollapsed') === 'true';
                                if (shouldCollapse) {
                                    sidebar.classList.add('collapsed');
                                } else {
                                    sidebar.classList.remove('collapsed');
                                }
                            } else {
                                sidebar.classList.add('-translate-x-full');
                                sidebar.classList.remove('collapsed');
                            }
                        });
                    });
                </script>