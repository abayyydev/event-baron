<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once 'core/koneksi.php';

// --- 1. CEK JIKA SUDAH LOGIN ---
if (isset($_SESSION['user_id']) || isset($_SESSION['santri_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'peserta') {
        header("Location: " . BASE_URL . "user/dashboard");
    } else {
        header("Location: " . BASE_URL . "admin/dashboard");
    }
    exit;
}

$error = null;

// --- 2. PROSES LOGIN (PASSWORD BASED) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['identifier'])) {
    $identifier = trim($_POST['identifier']); // Bisa Email, NIS, atau No WA
    $password = $_POST['password'];

    if (empty($identifier) || empty($password)) {
        $error = "Identitas dan Password wajib diisi!";
    } else {
        try {
            // A. CEK KE TABEL USERS (ADMIN/PENYELENGGARA)
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$identifier]);
            $user = $stmt->fetch();

            if ($user) {
                // --- LOGIC LOGIN ADMIN ---
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['owner_id'] = $user['owner_id'];
                    $_SESSION['penyelenggara_id_bersama'] = $user['owner_id'] ? $user['owner_id'] : $user['id'];

                    // Foto Profil Admin
                    if (!empty($user['foto_profil'])) {
                        $_SESSION['foto_profil'] = (strpos($user['foto_profil'], 'assets/') !== false)
                            ? $user['foto_profil']
                            : 'assets/uploads/profil/' . $user['foto_profil'];
                    } else {
                        $_SESSION['foto_profil'] = 'assets/img/download.jpg';
                    }

                    header("Location: " . BASE_URL . "admin/dashboard");
                    exit;
                } else {
                    $error = "Password salah (Admin).";
                }
            } else {
                // B. JIKA TIDAK ADA DI USERS, CEK KE TABEL SANTRI (NIS atau NO WA)
                // Cek apakah inputnya NIS atau No HP Wali
                $stmtSantri = $pdo->prepare("SELECT * FROM santri WHERE nis = ? OR no_hp_wali = ?");
                $stmtSantri->execute([$identifier, $identifier]);
                $santri = $stmtSantri->fetch();

                if ($santri) {
                    // --- LOGIC LOGIN SANTRI ---
                    if (password_verify($password, $santri['password'])) {
                        $_SESSION['santri_id'] = $santri['id'];
                        $_SESSION['user_id'] = $santri['id'];
                        $_SESSION['nama_lengkap'] = $santri['nama_lengkap'];
                        $_SESSION['role'] = 'peserta';
                        $_SESSION['nis'] = $santri['nis'];
                        $_SESSION['barcode_code'] = $santri['barcode_code'];

                        // Foto Profil Santri
                        if (!empty($santri['foto_santri'])) {
                            $_SESSION['foto_profil'] = (strpos($santri['foto_santri'], 'assets/') !== false)
                                ? $santri['foto_santri']
                                : 'assets/uploads/santri/' . $santri['foto_santri'];
                        } else {
                            $_SESSION['foto_profil'] = 'assets/img/avatar-santri.png';
                        }

                        header("Location: " . BASE_URL . "user/dashboard");
                        exit;
                    } else {
                        $error = "Password salah (Santri).";
                    }
                } else {
                    $error = "Akun tidak ditemukan (Email/NIS/WA salah).";
                }
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
    <title>Login - Ponpes Al Ihsan Baron</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="/assets/img/images/logo-pondok.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            font-family: 'Poppins', sans-serif;
        }

        .bg-gradient-auth {
            background: linear-gradient(135deg, #0A3D2B 0%, #1A5D3A 50%, #2D7D46 100%);
        }

        .bg-gradient-gold {
            background: linear-gradient(135deg, #D4AF37 0%, #F4D03F 50%, #D4AF37 100%);
        }

        .gold-border {
            border: 2px solid #D4AF37;
        }

        .gold-text {
            color: #D4AF37;
        }

        .gold-shadow {
            box-shadow: 0 4px 20px rgba(212, 175, 55, 0.3);
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .input-focus {
            transition: all 0.3s ease;
        }

        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
        }

        .tab-active {
            position: relative;
            overflow: hidden;
        }

        .tab-active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #D4AF37, #F4D03F);
            border-radius: 3px 3px 0 0;
        }

        .otp-input {
            letter-spacing: 10px;
            font-weight: 600;
        }

        .floating-element {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-15px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .pulse-glow {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(212, 175, 55, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(212, 175, 55, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(212, 175, 55, 0);
            }
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%230A3D2B' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-auth flex items-center justify-center p-4 bg-pattern">

    <!-- Background Decorative Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div
            class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-gold-400/5 to-transparent rounded-full floating-element">
        </div>
        <div class="absolute bottom-20 -left-40 w-96 h-96 bg-gradient-to-tr from-emerald-400/5 to-transparent rounded-full floating-element"
            style="animation-delay: 2s;"></div>
    </div>

    <div
        class="relative w-full max-w-4xl flex flex-col md:flex-row rounded-3xl overflow-hidden shadow-2xl card-hover glass-effect">

        <!-- Left Panel - Branding & Info -->
        <div
            class="md:w-2/5 bg-gradient-to-br from-emerald-900 to-emerald-800 p-8 md:p-10 text-white relative overflow-hidden">
            <!-- Pattern Overlay -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0"
                    style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 30px 30px;">
                </div>
            </div>

            <!-- Logo Area -->
            <div class="relative z-10 text-center mb-8">
                <div
                    class="w-24 h-24 mx-auto mb-4 rounded-full bg-gradient-gold flex items-center justify-center gold-shadow pulse-glow">
                    <i class="fas fa-mosque text-3xl text-emerald-900"></i>
                </div>
                <h1 class="text-3xl font-bold mb-2">Ponpes Al Ihsan Baron</h1>
            </div>

            <!-- Feature List -->
            <div class="relative z-10 space-y-6 mb-8">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 rounded-full bg-emerald-700/50 flex items-center justify-center gold-border">
                        <i class="fas fa-calendar-check text-gold-400"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold">Event Management</h3>
                        <p class="text-emerald-200 text-sm">Kelola kegiatan dengan mudah</p>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 rounded-full bg-emerald-700/50 flex items-center justify-center gold-border">
                        <i class="fas fa-qrcode text-gold-400"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold">Digital Presensi</h3>
                        <p class="text-emerald-200 text-sm">Scan barcode untuk absensi</p>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 rounded-full bg-emerald-700/50 flex items-center justify-center gold-border">
                        <i class="fas fa-comments text-gold-400"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold">Notifikasi WA</h3>
                        <p class="text-emerald-200 text-sm">Update realtime via WhatsApp</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="relative z-10 text-center mt-8 pt-6 border-t border-emerald-700/50">
                <p class="text-emerald-300 text-sm">
                    <i class="fas fa-shield-alt mr-2"></i>
                    Sistem Terenkripsi & Terpercaya
                </p>
            </div>
        </div>

        <!-- Right Panel - Login Forms -->
        <div class="md:w-3/5 p-8 md:p-10">
            <!-- Header -->
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-emerald-900 mb-2">Selamat Datang Kembali</h2>
                <p class="text-gray-600">Masuk ke sistem untuk melanjutkan aktivitas</p>
            </div>

            <!-- Tab Navigation -->
            <div class="flex mb-8 bg-emerald-50 rounded-xl p-1">
                <button id="tab-umum"
                    class="flex-1 py-4 rounded-xl text-center font-semibold transition-all duration-300 tab-active bg-white shadow-md">
                    <i class="fas fa-user-lock mr-2 gold-text"></i> Login Akun
                </button>
                <button id="tab-wa"
                    class="flex-1 py-4 rounded-xl text-center font-semibold text-gray-600 hover:text-emerald-700 transition-all duration-300">
                    <i class="fab fa-whatsapp mr-2"></i> WhatsApp
                </button>
            </div>

            <!-- Error Message -->
            <?php if (isset($error)): ?>
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg flex items-start">
                    <i class="fas fa-exclamation-circle text-red-500 mt-1 mr-3"></i>
                    <div class="flex-1">
                        <p class="text-red-700 font-medium">Perhatian!</p>
                        <p class="text-red-600 text-sm"><?php echo $error; ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Password Login Form -->
            <form id="form-umum" method="POST" action="">
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">
                            <i class="fas fa-id-card mr-2 gold-text"></i>Email / NIS / No. WhatsApp
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-emerald-600"></i>
                            </div>
                            <input type="text" name="identifier"
                                class="w-full pl-10 pr-4 py-4 rounded-xl border border-emerald-200 bg-white focus:outline-none input-focus focus:border-emerald-400"
                                placeholder="contoh@email.com atau 2025001" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">
                            <i class="fas fa-key mr-2 gold-text"></i>Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-emerald-600"></i>
                            </div>
                            <input type="password" name="password"
                                class="w-full pl-10 pr-10 py-4 rounded-xl border border-emerald-200 bg-white focus:outline-none input-focus focus:border-emerald-400"
                                placeholder="Masukkan password Anda" required>
                            <button type="button"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-emerald-600"
                                onclick="togglePassword(this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white font-bold py-4 px-4 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center space-x-2">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Masuk ke Sistem</span>
                    </button>
                </div>
            </form>

            <!-- WhatsApp Login Form -->
            <form id="form-wa" class="hidden">
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2">
                            <i class="fab fa-whatsapp mr-2 text-green-500"></i>Nomor WhatsApp Terdaftar
                        </label>
                        <div class="relative">
                            <input type="text" id="wa_number"
                                class="w-full pl-12 py-4 rounded-xl border border-emerald-200 bg-white focus:outline-none input-focus focus:border-emerald-400"
                                placeholder="812 3456 7890">
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Masukkan nomor WhatsApp yang sudah terdaftar di sistem
                        </p>
                    </div>

                    <button type="submit"
                        class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-4 px-4 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center space-x-2">
                        <i class="fas fa-paper-plane"></i>
                        <span>Kirim Kode OTP</span>
                    </button>
                </div>
            </form>

            <!-- OTP Verification Form -->
            <form id="form-otp" class="hidden">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100 flex items-center justify-center">
                        <i class="fas fa-sms text-2xl text-emerald-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Verifikasi OTP</h3>
                    <p class="text-sm text-gray-600">Kode OTP telah dikirim ke WhatsApp</p>
                    <p id="otp-target" class="font-bold text-emerald-700 text-lg mt-2"></p>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-3 text-center">
                        <i class="fas fa-shield-alt mr-2 gold-text"></i>Masukkan 6 Digit Kode OTP
                    </label>
                    <input type="text" id="otp_code"
                        class="w-full text-center text-3xl tracking-widest py-4 rounded-xl border-2 border-emerald-300 focus:outline-none focus:border-emerald-500 input-focus otp-input"
                        placeholder="______" maxlength="6">
                    <p class="text-center text-xs text-gray-500 mt-3">
                        <i class="fas fa-clock mr-1"></i>
                        Kode OTP berlaku selama 5 menit
                    </p>
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-amber-600 text-white font-bold py-4 px-4 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center space-x-2">
                    <i class="fas fa-check-circle"></i>
                    <span>Verifikasi & Masuk</span>
                </button>

                <button type="button" id="btn-back-wa"
                    class="w-full mt-4 text-sm text-gray-500 hover:text-emerald-700 font-medium flex items-center justify-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Ganti Nomor WhatsApp</span>
                </button>
            </form>

            <!-- Footer Links -->
            <div class="mt-8 pt-6 border-t border-gray-100 text-center">

                <p class="text-xs text-gray-500 mt-3">
                    <i class="fas fa-copyright mr-1"></i>
                    <?php echo date('Y'); ?> Ponpes Al Ihsan Baron - Sistem Manajemen Event
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Password Toggle
        function togglePassword(button) {
            const input = button.parentElement.querySelector('input');
            const icon = button.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Tab Switching with Animation
        const tabUmum = document.getElementById('tab-umum');
        const tabWa = document.getElementById('tab-wa');
        const formUmum = document.getElementById('form-umum');
        const formWa = document.getElementById('form-wa');
        const formOtp = document.getElementById('form-otp');

        const switchTab = (activeTab, activeForm) => {
            // Reset all tabs
            [tabUmum, tabWa].forEach(tab => {
                tab.className = "flex-1 py-4 rounded-xl text-center font-semibold text-gray-600 hover:text-emerald-700 transition-all duration-300";
            });

            // Set active tab
            activeTab.className = "flex-1 py-4 rounded-xl text-center font-semibold transition-all duration-300 tab-active bg-white shadow-md";

            // Reset all forms
            [formUmum, formWa, formOtp].forEach(form => {
                form.classList.add('hidden');
            });

            // Show active form
            activeForm.classList.remove('hidden');
            activeForm.classList.add('animate__animated', 'animate__fadeIn');
        };

        tabUmum.addEventListener('click', () => switchTab(tabUmum, formUmum));
        tabWa.addEventListener('click', () => switchTab(tabWa, formWa));

        // Auto move to next OTP input (if using multiple inputs)
        document.getElementById('otp_code')?.addEventListener('input', function (e) {
            if (this.value.length === 6) {
                this.blur();
            }
        });

        // --- 1. REQUEST OTP ---
        document.getElementById('form-wa').addEventListener('submit', async (e) => {
            e.preventDefault();
            const wa = document.getElementById('wa_number').value;

            if (!wa) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Nomor WhatsApp wajib diisi',
                    confirmButtonColor: '#059669'
                });
                return;
            }

            Swal.fire({
                title: 'Mengirim OTP...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const formData = new FormData();
            formData.append('no_whatsapp', wa);

            try {
                const req = await fetch('api/request_otp.php', {
                    method: 'POST',
                    body: formData
                });
                const res = await req.json();

                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'OTP Terkirim!',
                        text: res.message,
                        confirmButtonColor: '#059669',
                        timer: 2000
                    });

                    document.getElementById('otp-target').innerText = wa.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
                    formWa.classList.add('hidden');
                    formOtp.classList.remove('hidden');

                    // Auto focus OTP input
                    setTimeout(() => {
                        document.getElementById('otp_code').focus();
                    }, 300);

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.message,
                        confirmButtonColor: '#059669'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Koneksi Gagal',
                    text: 'Tidak dapat terhubung ke server',
                    confirmButtonColor: '#059669'
                });
            }
        });

        // --- 2. VERIFY OTP & REDIRECT ---
        document.getElementById('form-otp').addEventListener('submit', async (e) => {
            e.preventDefault();
            const wa = document.getElementById('wa_number').value;
            const otp = document.getElementById('otp_code').value;

            if (otp.length !== 6) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Kode Tidak Lengkap',
                    text: 'Harap masukkan 6 digit kode OTP',
                    confirmButtonColor: '#059669'
                });
                return;
            }

            Swal.fire({
                title: 'Memverifikasi...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const formData = new FormData();
            formData.append('no_whatsapp', wa);
            formData.append('otp_code', otp);

            try {
                const req = await fetch('api/verify_otp.php', {
                    method: 'POST',
                    body: formData
                });
                const res = await req.json();

                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Login Berhasil!',
                        text: 'Mengalihkan ke dashboard...',
                        confirmButtonColor: '#059669',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Redirect Dinamis
                        if (res.redirect === 'admin') {
                            window.location.href = 'admin/dashboard.php';
                        } else {
                            window.location.href = 'user/dashboard.php';
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Verifikasi Gagal',
                        text: res.message,
                        confirmButtonColor: '#059669'
                    });
                }
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Sistem Error',
                    text: 'Terjadi kesalahan pada sistem',
                    confirmButtonColor: '#059669'
                });
            }
        });

        // Back to WA form
        document.getElementById('btn-back-wa').addEventListener('click', () => {
            formOtp.classList.add('hidden');
            formWa.classList.remove('hidden');
            switchTab(tabWa, formWa);
        });

        // Add floating animation to decorative elements
        document.querySelectorAll('.floating-element').forEach((el, index) => {
            el.style.animationDelay = `${index * 2}s`;
        });
    </script>
</body>

</html>