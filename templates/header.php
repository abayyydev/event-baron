<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Pondok Pesantren Al Ihsan Baron - Mencetak Generasi Islami</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#166534', // Green-800
                        secondary: '#EAB308', // Yellow-500
                        accent: '#FDE047', // Yellow-300
                        darkgreen: '#14532d',
                        lightgreen: '#dcfce7'
                    }
                }
            }
        }
    </script>
    <style>
        /* Mobile menu */
        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
        }

        .mobile-menu.open {
            transform: translateX(0);
        }

        .overlay {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out;
        }

        .overlay.open {
            opacity: 1;
            visibility: visible;
        }

        /* Menu item animation */
        .menu-item {
            opacity: 0;
            transform: translateX(-20px);
            transition: all 0.3s ease;
        }

        .mobile-menu.open .menu-item {
            opacity: 1;
            transform: translateX(0);
        }

        .mobile-menu.open .menu-item:nth-child(1) {
            transition-delay: 0.1s;
        }

        .mobile-menu.open .menu-item:nth-child(2) {
            transition-delay: 0.15s;
        }

        .mobile-menu.open .menu-item:nth-child(3) {
            transition-delay: 0.2s;
        }

        .mobile-menu.open .menu-item:nth-child(4) {
            transition-delay: 0.25s;
        }

        .mobile-menu.open .menu-item:nth-child(5) {
            transition-delay: 0.3s;
        }

        .mobile-menu.open .menu-item:nth-child(6) {
            transition-delay: 0.35s;
        }

        /* Custom animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .animate-fade-in {
            animation: fadeIn 1s ease-out forwards;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }

        /* Card hover effect */
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        /* Glassmorphism effect */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #166534;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #EAB308;
        }

        /* Stagger animation for cards */
        .stagger-animation>* {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.5s ease-out forwards;
        }

        .stagger-animation>*:nth-child(1) {
            animation-delay: 0.1s;
        }

        .stagger-animation>*:nth-child(2) {
            animation-delay: 0.2s;
        }

        .stagger-animation>*:nth-child(3) {
            animation-delay: 0.3s;
        }

        .stagger-animation>*:nth-child(4) {
            animation-delay: 0.4s;
        }

        .stagger-animation>*:nth-child(5) {
            animation-delay: 0.5s;
        }

        .stagger-animation>*:nth-child(6) {
            animation-delay: 0.6s;
        }
    </style>

</head>

<body class="bg-gray-50 font-sans scroll-smooth">
    <!-- Header/Navbar -->
    <header class="bg-white shadow-md sticky top-0 z-50 transition-all duration-300">
        <nav class="container mx-auto px-6 py-3 flex justify-between items-center">
            <a href="index.php" class="flex items-center space-x-4">
                <!-- Logo -->
                <img src="./assets/img/images/logo-pondok.png" alt="Logo" class="w-12 h-12 object-contain">

                <!-- Text Area -->
                <div class="flex flex-col leading-tight">
                    <span class="text-xl font-bold text-green-900">Al Ihsan Baron</span>
                    <span class="text-xs text-green-600 font-semibold tracking-wider">PONDOK PESANTREN</span>
                </div>
            </a>


            <!-- Desktop Menu -->
            <div class="hidden md:flex space-x-8 text-gray-700 font-medium items-center">
                <a href="index.php"
                    class="hover:text-yellow-600 transition duration-300 py-2 border-b-2 border-transparent hover:border-yellow-500">Beranda</a>
                <a href="index.php#profil"
                    class="hover:text-yellow-600 transition duration-300 py-2 border-b-2 border-transparent hover:border-yellow-500">Profil</a>
                <a href="index.php#pendidikan"
                    class="hover:text-yellow-600 transition duration-300 py-2 border-b-2 border-transparent hover:border-yellow-500">Jenjang</a>
                <a href="index.php#agenda"
                    class="hover:text-yellow-600 transition duration-300 py-2 border-b-2 border-transparent hover:border-yellow-500">Event</a>

                <!-- CTA Button -->
                <a href="#daftar"
                    class="bg-gradient-to-r from-green-700 to-green-600 hover:from-green-800 hover:to-green-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
                    Daftar Sekarang
                </a>
            </div>

            <!-- Mobile Menu Button -->
            <button id="menuToggle"
                class="md:hidden text-green-800 focus:outline-none transition-transform duration-300 hover:scale-110">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </nav>

        <!-- Mobile Menu -->
        <div id="mobileMenu"
            class="mobile-menu fixed inset-y-0 left-0 w-72 bg-white shadow-xl z-50 md:hidden flex flex-col h-full">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-green-50">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-green-800 rounded-full flex items-center justify-center text-white">
                        <i class="fas fa-mosque text-sm"></i>
                    </div>
                    <span class="text-lg font-bold text-green-900">Al Ihsan Baron</span>
                </div>
                <button id="closeMenu"
                    class="text-gray-500 hover:text-red-500 focus:outline-none transition-transform duration-300 hover:rotate-90">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="p-6 flex flex-col space-y-2 text-gray-700 font-medium overflow-y-auto flex-grow">
                <a href="index.php"
                    class="menu-item hover:text-green-700 hover:bg-green-50 transition-all duration-300 py-3 px-4 rounded-lg flex items-center">
                    <i class="fas fa-home mr-3 w-6 text-center text-green-600"></i>
                    Beranda
                </a>
                <a href="index.php#profil"
                    class="menu-item hover:text-green-700 hover:bg-green-50 transition-all duration-300 py-3 px-4 rounded-lg flex items-center">
                    <i class="fas fa-info-circle mr-3 w-6 text-center text-green-600"></i>
                    Profil & Sejarah
                </a>
                <a href="index.php#pendidikan"
                    class="menu-item hover:text-green-700 hover:bg-green-50 transition-all duration-300 py-3 px-4 rounded-lg flex items-center">
                    <i class="fas fa-graduation-cap mr-3 w-6 text-center text-green-600"></i>
                    Jenjang Pendidikan
                </a>
                <a href="index.php#agenda"
                    class="menu-item hover:text-green-700 hover:bg-green-50 transition-all duration-300 py-3 px-4 rounded-lg flex items-center">
                    <i class="fas fa-calendar-alt mr-3 w-6 text-center text-green-600"></i>
                    Agenda & Event
                </a>

                <div class="menu-item mt-6 pt-6 border-t border-gray-100">
                    <a href="#daftar"
                        class="bg-gradient-to-r from-green-700 to-green-600 text-white font-semibold py-3 px-4 rounded-lg text-center block shadow-md hover:shadow-lg">
                        Daftar Sekarang
                    </a>
                </div>

                <!-- Social Media Links -->
                <div class="menu-item flex justify-center space-x-6 mt-8">
                    <a href="#" class="text-gray-400 hover:text-green-600 transition duration-300"><i
                            class="fab fa-instagram text-xl"></i></a>
                    <a href="#" class="text-gray-400 hover:text-green-600 transition duration-300"><i
                            class="fab fa-facebook text-xl"></i></a>
                    <a href="#" class="text-gray-400 hover:text-green-600 transition duration-300"><i
                            class="fab fa-youtube text-xl"></i></a>
                </div>
            </div>
        </div>

        <!-- Overlay -->
        <div id="overlay" class="overlay fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"></div>
    </header>