<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once 'core/koneksi.php'; // Pastikan path ini benar

// --- 1. CEK JIKA SUDAH LOGIN (REDIRECT OTOMATIS) ---
if (isset($_SESSION['user_id'])) {
    // Jika role peserta -> lempar ke halaman user
    if ($_SESSION['role'] == 'peserta') {
        header("Location: " . BASE_URL . "user/dashboard");
    }
    // Jika role admin/penyelenggara -> lempar ke halaman admin
    else {
        header("Location: " . BASE_URL . "admin/dashboard");
    }
    exit;
}

$error = null;

// --- 2. PROSES LOGIN SAAT TOMBOL DITEKAN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Email dan Password wajib diisi!";
    } else {
        try {
            // Ambil data user berdasarkan email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Verifikasi Password
                if (password_verify($password, $user['password'])) {

                    // --- SET SESSION ---
                    // --- SET SESSION DI LOGIN.PHP (UPDATE TERBARU) ---

                    $_SESSION['user_id'] = $user['id'];

                    // 1. Ganti 'nama_user' jadi 'nama_lengkap' (MENIRU WA)
                    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];

                    $_SESSION['role'] = $user['role'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['owner_id'] = $user['owner_id'];

                    // 2. Logic ID Bersama (MENIRU WA - Lebih aman daripada ambil dari DB langsung)
// Artinya: Kalau dia punya owner (staf), pakai ID owner. Kalau dia owner, pakai ID sendiri.
                    $id_bersama = $user['owner_id'] ? $user['owner_id'] : $user['id'];
                    $_SESSION['penyelenggara_id_bersama'] = $id_bersama;

                    // 3. Foto Profil (MENIRU WA)
                    $_SESSION['foto_profil'] = !empty($user['foto_profil']) ? $user['foto_profil'] : 'assets/img/admin.jpg';
                    // Perhatikan path 'assets/...' sesuaikan dengan lokasi file login.php Anda

                    // --- LOGIKA REDIRECT BERDASARKAN ROLE ---
                    if ($user['role'] == 'peserta') {
                        // Jika Peserta -> Ke Dashboard User
                        header("Location: " . BASE_URL . "user/dashboard");
                    } else {
                        // Jika Admin/Panitia -> Ke Dashboard Admin
                        header("Location: " . BASE_URL . "admin/dashboard");
                    }
                    exit;

                } else {
                    $error = "Password yang Anda masukkan salah.";
                }
            } else {
                $error = "Email tidak terdaftar dalam sistem.";
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Ponpes Al Ihsan Baron</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#166534', // Hijau Ponpes
                        secondary: '#D4AF37', // Emas
                        accent: '#FBBF24',
                        hijauMuda: '#22C55E',
                        hijauTua: '#14532D',
                        emasMuda: '#FDE047',
                        emasTua: '#B8860B',
                        bgDark: '#0A2F1C'
                    }
                }
            }
        }
    </script>
    <style>
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
            animation: fadeIn 0.8s ease-out forwards;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(212, 175, 55, 0.3);
        }

        .form-input {
            transition: all 0.3s ease;
        }

        .form-input:focus {
            box-shadow: 0 0 0 3px rgba(22, 101, 52, 0.1);
            border-color: #D4AF37;
        }

        .whatsapp-gradient {
            background: linear-gradient(135deg, #166534 0%, #22C55E 100%);
        }

        .email-gradient {
            background: linear-gradient(135deg, #14532D 0%, #166534 100%);
        }

        .otp-input {
            letter-spacing: 0.5em;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .floating-shapes {
            position: fixed;
            border-radius: 50%;
            background: rgba(212, 175, 55, 0.1);
            z-index: 0;
        }

        .arabic-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23D4AF37' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
        }

        .prefix-input-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .prefix-input {
            padding-left: 3.5rem;
        }

        .prefix {
            position: absolute;
            left: 1rem;
            color: #6b7280;
            pointer-events: none;
            font-weight: 500;
        }

        .islamic-border {
            border: 2px solid transparent;
            border-image: linear-gradient(45deg, #D4AF37, #166534, #D4AF37) 1;
        }

        .islamic-badge {
            background: linear-gradient(135deg, #D4AF37, #B8860B);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
        }

        .login-tab {
            transition: all 0.3s ease;
        }

        .login-tab.active {
            background: linear-gradient(135deg, #166534, #22C55E);
            color: white;
            border-bottom: 3px solid #D4AF37;
        }

        .login-tab:not(.active) {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.8);
        }

        .arabic-text {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: bold;
        }

        .mosque-icon {
            position: relative;
            width: 40px;
            height: 40px;
        }

        .mosque-icon:before {
            content: "🕌";
            font-size: 2rem;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(22, 101, 52, 0.1);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, #D4AF37, #B8860B);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(to bottom, #B8860B, #D4AF37);
        }

        .card-shadow {
            box-shadow: 0 10px 30px rgba(20, 83, 45, 0.3);
        }

        .gold-gradient {
            background: linear-gradient(135deg, #D4AF37, #FDE047, #D4AF37);
        }

        .green-gradient {
            background: linear-gradient(135deg, #14532D, #166534, #22C55E);
        }
    </style>
</head>

<body
    class="bg-gradient-to-br from-bgDark via-hijauTua to-primary min-h-screen p-6 relative overflow-auto arabic-pattern">
    <!-- Floating Elements dengan tema islami -->
    <div class="floating-shapes w-64 h-64 -top-32 -left-32 animate-float"></div>
    <div class="floating-shapes w-48 h-48 -bottom-24 -right-24 animate-float" style="animation-delay: 1s;"></div>
    <div class="floating-shapes w-32 h-32 top-1/4 right-1/4 animate-float" style="animation-delay: 2s;"></div>

    <!-- Arabic Calligraphy Decoration -->
    <div class="absolute top-10 right-10 text-3xl text-emasMuda opacity-20">
        <span class="arabic-text">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ</span>
    </div>
    <div class="absolute bottom-10 left-10 text-2xl text-emasMuda opacity-20">
        <span class="arabic-text">مَّا شَاءَ اللَّهُ</span>
    </div>

    <!-- Main Container -->
    <div class="min-h-screen flex flex-col items-center justify-center py-8 relative z-10">
        <!-- Header dengan tema Ponpes -->
        <div class="text-center mb-8 animate-fade-in">
            <div class="flex justify-center mb-4">
                <div
                    class="w-20 h-20 bg-gradient-to-br from-primary to-hijauTua rounded-full flex items-center justify-center shadow-lg border-4 border-emasMuda p-2">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mosque-icon">
                    </div>
                </div>
            </div>
            <h1 class="text-4xl font-bold text-white mb-2">Ponpes Al Ihsan Baron</h1>
            <div class="flex items-center justify-center gap-2 mb-2">
                <span class="islamic-badge">Islamic Boarding School</span>
                <span class="islamic-badge" style="background: linear-gradient(135deg, #22C55E, #166534);">Admin
                    Panel</span>
            </div>
            <p class="text-emasMuda text-lg italic">"Mencetak Generasi Qur'ani, Berakhlak Mulia"</p>
        </div>

        <!-- Main Login Card -->
        <div class="bg-white/95 rounded-2xl shadow-2xl overflow-hidden animate-fade-in mb-8 w-full max-w-md card-shadow border-2 border-emasMuda"
            style="animation-delay: 0.2s;">

            <!-- Login Tabs dengan desain islami -->
            <div class="flex border-b-2 border-emasMuda/30">
                <button id="email-tab"
                    class="login-tab active flex-1 py-4 font-bold text-center border-r-2 border-emasMuda/30 transition-all duration-300">
                    <i class="fas fa-envelope mr-2"></i>
                    Login Email
                </button>
                <button id="whatsapp-tab"
                    class="login-tab flex-1 py-4 font-bold text-center transition-all duration-300">
                    <i class="fab fa-whatsapp mr-2"></i>
                    Login WhatsApp
                </button>
            </div>

            <!-- Email Login View -->
            <div id="email-login-view">
                <!-- Header dengan gradient Hijau -->
                <div class="green-gradient text-white p-6 text-center relative">
                    <div class="absolute top-2 right-2">
                        <i class="fas fa-star text-emasMuda text-xl"></i>
                    </div>
                    <div
                        class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-emasMuda">
                        <i class="fas fa-envelope text-2xl text-emasMuda"></i>
                    </div>
                    <h2 class="text-2xl font-bold mb-2">Login dengan Email</h2>
                    <p class="text-emasMuda text-sm">Gunakan akun resmi Ponpes Al Ihsan</p>
                </div>

                <!-- Form Section -->
                <div class="p-6">
                    <?php if (isset($error)): ?>
                        <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg islamic-border">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form id="emailLoginForm" method="POST" action="">
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-envelope mr-2 text-primary"></i>
                                Alamat Email
                            </label>
                            <input type="email" id="email" name="email"
                                class="w-full p-4 border border-gray-300 rounded-lg form-input text-lg focus:border-emasMuda focus:ring-2 focus:ring-emasMuda/30"
                                required placeholder="admin@alihsanbaron.sch.id" />
                        </div>

                        <div class="mb-6">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-primary"></i>
                                Password
                            </label>
                            <div class="relative">
                                <input type="password" id="password" name="password"
                                    class="w-full p-4 border border-gray-300 rounded-lg form-input text-lg pr-12 focus:border-emasMuda focus:ring-2 focus:ring-emasMuda/30"
                                    required placeholder="••••••••" />
                                <button type="button" id="togglePassword"
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-primary">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full green-gradient hover:opacity-90 text-white font-bold py-4 px-6 rounded-lg transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center justify-center islamic-border">
                            <i class="fas fa-sign-in-alt mr-3 text-emasMuda"></i>
                            Masuk ke Dashboard
                        </button>
                    </form>

                    <!-- Informasi Tambahan -->
                    <div class="mt-6 p-4 bg-emasMuda/10 rounded-lg border border-emasMuda/30">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-primary mt-1 mr-3"></i>
                            <div class="text-sm text-gray-700">
                                <p class="font-bold text-primary mb-1">Akses Khusus Admin</p>
                                <p class="text-gray-600">Hanya staf dan pengurus Ponpes Al Ihsan yang memiliki akses ke
                                    panel admin ini.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WhatsApp Login View -->
            <div id="whatsapp-login-view" class="hidden">
                <!-- Header dengan gradient Hijau -->
                <div class="green-gradient text-white p-6 text-center relative">
                    <div class="absolute top-2 left-2">
                        <i class="fas fa-mosque text-emasMuda text-xl"></i>
                    </div>
                    <div
                        class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-emasMuda">
                        <i class="fab fa-whatsapp text-2xl text-emasMuda"></i>
                    </div>
                    <h2 class="text-2xl font-bold mb-2">Login via WhatsApp</h2>
                    <p class="text-emasMuda text-sm">Verifikasi dengan nomor terdaftar</p>
                </div>

                <!-- Form Section -->
                <div class="p-6">
                    <form id="phoneLoginForm">
                        <div class="mb-6">
                            <label for="no_whatsapp" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-phone mr-2 text-primary"></i>
                                Nomor WhatsApp
                            </label>

                            <div class="relative">
                                <!-- Prefix +62 -->
                                <span
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-600 font-medium bg-gray-100 px-2 py-1 rounded-l border-r border-gray-300">+62</span>

                                <!-- Input -->
                                <input type="tel" id="no_whatsapp" name="no_whatsapp"
                                    class="w-full p-4 pl-20 border border-gray-300 rounded-lg text-lg focus:ring-2 focus:ring-emasMuda focus:border-emasMuda outline-none"
                                    required placeholder="81234567890" pattern="[0-9]{9,13}" />
                            </div>

                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Contoh: 81234567890 (tanpa +62)
                            </p>
                        </div>

                        <button type="submit"
                            class="w-full green-gradient hover:opacity-90 text-white font-bold py-4 px-6 rounded-lg transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center justify-center islamic-border">
                            <i class="fab fa-whatsapp mr-3 text-xl text-emasMuda"></i>
                            Kirim Kode Verifikasi
                        </button>
                    </form>

                    <!-- Informasi Tambahan -->
                    <div class="mt-6 p-4 bg-emasMuda/10 rounded-lg border border-emasMuda/30">
                        <div class="flex items-start">
                            <i class="fas fa-shield-alt text-primary mt-1 mr-3"></i>
                            <div class="text-sm text-gray-700">
                                <p class="font-bold text-primary mb-1">Keamanan Terjamin</p>
                                <p class="text-gray-600">OTP akan dikirim via WhatsApp. Data Anda aman bersama kami.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- OTP Verification View -->
            <div id="otp-verify-view" class="hidden">
                <!-- Header dengan gradient Emas -->
                <div class="gold-gradient text-white p-6 text-center relative">
                    <div class="absolute top-2 right-2">
                        <i class="fas fa-check-circle text-white text-xl"></i>
                    </div>
                    <div
                        class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-white">
                        <i class="fas fa-key text-2xl text-white"></i>
                    </div>
                    <h2 class="text-2xl font-bold mb-2 text-hijauTua">Verifikasi Kode</h2>
                    <p class="text-white text-sm">Masukkan kode 6 digit</p>
                </div>

                <!-- Form Section -->
                <div class="p-6">
                    <div class="text-center mb-6">
                        <p class="text-gray-600 mb-2">Kode OTP telah dikirim ke:</p>
                        <p class="text-lg font-bold text-primary" id="display-whatsapp-number"></p>
                        <div class="flex items-center justify-center mt-2 text-sm text-gray-500">
                            <i class="fas fa-clock mr-2 text-emasTua"></i>
                            <span class="font-medium">Kode berlaku 10 menit</span>
                        </div>
                    </div>

                    <form id="otpVerifyForm">
                        <div class="mb-6">
                            <label for="otp_code" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-key mr-2 text-emasTua"></i>
                                Kode OTP (6 digit)
                            </label>
                            <input type="text" id="otp_code" name="otp_code"
                                class="w-full p-4 border-2 border-emasMuda rounded-lg form-input text-center otp-input focus:ring-2 focus:ring-emasMuda/30"
                                maxLength="6" required placeholder="••••••" pattern="[0-9]{6}"
                                autocomplete="one-time-code" />
                        </div>

                        <button type="submit"
                            class="w-full gold-gradient hover:opacity-90 text-hijauTua font-bold py-4 px-6 rounded-lg transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center justify-center border-2 border-emasMuda">
                            <i class="fas fa-sign-in-alt mr-3"></i>
                            Verifikasi & Masuk
                        </button>
                    </form>

                    <!-- Back Button -->
                    <div class="text-center mt-4">
                        <button id="backToPhoneBtn"
                            class="text-primary hover:text-hijauTua font-bold transition duration-300 flex items-center justify-center mx-auto">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali ke Login WhatsApp
                        </button>
                    </div>

                    <!-- Resend OTP -->
                    <div class="mt-6 text-center p-4 bg-emasMuda/5 rounded-lg border border-emasMuda/20">
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-redo mr-1 text-primary"></i>
                            Tidak menerima kode?
                            <button id="resendOtpBtn" class="text-primary hover:text-hijauTua font-bold ml-1">
                                Kirim ulang OTP
                            </button>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer dengan tema islami -->
        <div class="text-center mt-6 animate-fade-in" style="animation-delay: 0.4s;">
            <div class="flex items-center justify-center gap-4 mb-3">
                <div class="w-8 h-8 rounded-full bg-emasMuda/20 flex items-center justify-center">
                    <i class="fas fa-mosque text-emasMuda"></i>
                </div>
                <div class="w-8 h-8 rounded-full bg-emasMuda/20 flex items-center justify-center">
                    <i class="fas fa-book-quran text-emasMuda"></i>
                </div>
                <div class="w-8 h-8 rounded-full bg-emasMuda/20 flex items-center justify-center">
                    <i class="fas fa-star-and-crescent text-emasMuda"></i>
                </div>
            </div>

            <p class="text-emasMuda text-sm mb-2">
                <i class="fas fa-lock mr-1"></i>
                Sistem Terenkripsi • Aman & Terpercaya
            </p>
            <p class="text-emasMuda/80 text-xs">
                <i class="fas fa-copyright mr-1"></i>
                2025 Ponpes Al Ihsan Baron • Lembaga Pendidikan Islam Terpadu
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- JavaScript tetap sama seperti sebelumnya -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const emailTab = document.getElementById('email-tab');
            const whatsappTab = document.getElementById('whatsapp-tab');
            const emailView = document.getElementById('email-login-view');
            const whatsappView = document.getElementById('whatsapp-login-view');
            const phoneView = document.getElementById('whatsapp-login-view');
            const otpView = document.getElementById('otp-verify-view');
            const phoneInput = document.getElementById('no_whatsapp');
            const otpInput = document.getElementById('otp_code');
            const resendOtpBtn = document.getElementById('resendOtpBtn');
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');

            // Toggle password visibility
            togglePassword.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });

            // Tab switching functionality
            emailTab.addEventListener('click', function () {
                emailTab.classList.add('active');
                whatsappTab.classList.remove('active');
                emailView.classList.remove('hidden');
                whatsappView.classList.add('hidden');
                otpView.classList.add('hidden');
            });

            whatsappTab.addEventListener('click', function () {
                whatsappTab.classList.add('active');
                emailTab.classList.remove('active');
                whatsappView.classList.remove('hidden');
                emailView.classList.add('hidden');
                otpView.classList.add('hidden');
            });

            // Format phone input - hanya menerima angka
            phoneInput.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');
                e.target.value = value;
            });

            // Format OTP input - hanya menerima angka dan memungkinkan paste
            otpInput.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');
                e.target.value = value;

                // Auto submit ketika 6 digit terisi
                if (value.length === 6) {
                    document.getElementById('otpVerifyForm').dispatchEvent(new Event('submit'));
                }
            });

            // Navigasi antar view
            document.getElementById('backToPhoneBtn').addEventListener('click', () => {
                otpView.classList.add('hidden');
                whatsappView.classList.remove('hidden');
            });

            // Resend OTP functionality
            resendOtpBtn.addEventListener('click', async function () {
                if (!phoneInput.value) {
                    Swal.fire({ icon: 'error', title: 'Oops!', text: 'Nomor WhatsApp harus diisi terlebih dahulu.' });
                    return;
                }

                Swal.fire({
                    title: 'Mengirim ulang OTP...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const formData = new FormData();
                formData.append('no_whatsapp', '62' + phoneInput.value);

                try {
                    const response = await fetch('api/request_otp.php', { method: 'POST', body: formData });
                    const result = await response.json();

                    if (result.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Kode OTP baru telah dikirim.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: result.message });
                    }
                } catch (error) {
                    Swal.fire({ icon: 'error', title: 'Oops!', text: 'Gagal terhubung ke server.' });
                }
            });

            // Form Minta OTP
            document.getElementById('phoneLoginForm').addEventListener('submit', async function (e) {
                e.preventDefault();

                if (!phoneInput.value || phoneInput.value.length < 9) {
                    Swal.fire({ icon: 'error', title: 'Oops!', text: 'Nomor WhatsApp harus minimal 9 digit (setelah 62).' });
                    return;
                }

                Swal.fire({
                    title: 'Mengirim OTP...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const formData = new FormData();
                formData.append('no_whatsapp', '62' + phoneInput.value);

                try {
                    const response = await fetch('api/request_otp.php', { method: 'POST', body: formData });
                    const result = await response.json();

                    if (result.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: result.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        document.getElementById('display-whatsapp-number').textContent = '+62' + phoneInput.value;
                        whatsappView.classList.add('hidden');
                        otpView.classList.remove('hidden');

                        // Focus pada input OTP
                        setTimeout(() => {
                            otpInput.focus();
                        }, 300);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: result.message });
                    }
                } catch (error) {
                    Swal.fire({ icon: 'error', title: 'Oops!', text: 'Gagal terhubung ke server.' });
                }
            });

            // Form Verifikasi OTP
            document.getElementById('otpVerifyForm').addEventListener('submit', async function (e) {
                e.preventDefault();

                if (!otpInput.value || otpInput.value.length !== 6) {
                    Swal.fire({ icon: 'error', title: 'Oops!', text: 'Kode OTP harus 6 digit.' });
                    return;
                }

                Swal.fire({
                    title: 'Memverifikasi...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const formData = new FormData(this);
                formData.append('no_whatsapp', '62' + phoneInput.value);

                try {
                    const response = await fetch('api/verify_otp.php', { method: 'POST', body: formData });
                    const result = await response.json();

                    if (result.status === 'success') {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Login Berhasil!',
                            text: 'Anda akan diarahkan ke dasbor.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        window.location.href = 'admin/dashboard.php';
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: result.message });
                    }
                } catch (error) {
                    Swal.fire({ icon: 'error', title: 'Oops!', text: 'Gagal terhubung ke server.' });
                }
            });

            // Enter key navigation
            document.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    if (!emailView.classList.contains('hidden')) {
                        document.getElementById('emailLoginForm').dispatchEvent(new Event('submit'));
                    } else if (!whatsappView.classList.contains('hidden')) {
                        document.getElementById('phoneLoginForm').dispatchEvent(new Event('submit'));
                    } else if (!otpView.classList.contains('hidden')) {
                        document.getElementById('otpVerifyForm').dispatchEvent(new Event('submit'));
                    }
                }
            });
        });
    </script>
</body>

</html>