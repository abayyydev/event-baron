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
    <title>Login Sistem - Ponpes Al Ihsan Baron</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="/assets/img/images/logo-pondok.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .tab-btn {
            cursor: pointer;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #166534 0%, #14532D 100%);
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4" style="background-color: #0A2F1C;">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-gradient-primary p-6 text-center">
            <h1 class="text-2xl font-bold text-white">Ponpes Al Ihsan Baron</h1>
            <p class="text-green-100 text-sm">Sistem Informasi Manajemen Event</p>
        </div>

        <div class="flex border-b border-gray-200">
            <button id="tab-umum"
                class="flex-1 py-4 text-center font-semibold text-green-800 border-b-2 border-green-600 bg-green-50 tab-btn">
                <i class="fas fa-user-circle mr-2"></i> Login Akun
            </button>
            <button id="tab-wa"
                class="flex-1 py-4 text-center font-semibold text-gray-500 hover:text-green-600 tab-btn">
                <i class="fab fa-whatsapp mr-2"></i> Login WhatsApp
            </button>
        </div>

        <div class="p-6">
            <?php if (isset($error)): ?>
                <div class="mb-4 p-3 bg-red-100 border-l-4 border-red-500 text-red-700 text-sm">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form id="form-umum" method="POST" action="">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email / NIS / No. WA</label>
                    <input type="text" name="identifier"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="Contoh: 2025001 atau 0812..." required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" name="password"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="••••••••" required>
                </div>
                <button type="submit"
                    class="w-full bg-green-700 hover:bg-green-800 text-white font-bold py-3 px-4 rounded-lg transition duration-300">
                    Masuk Sekarang
                </button>
                <p class="text-center text-xs text-gray-500 mt-4">
                    *Gunakan NIS (Santri) atau Email (Admin) untuk login.
                </p>
            </form>

            <form id="form-wa" class="hidden">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nomor WhatsApp</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-500 font-bold">+62</span>
                        <input type="text" id="wa_number"
                            class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="81234567890">
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Masukkan nomor WA terdaftar (Admin / Wali Santri).</p>
                </div>
                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300 flex justify-center items-center gap-2">
                    <i class="fas fa-paper-plane"></i> Kirim Kode OTP
                </button>
            </form>

            <form id="form-otp" class="hidden">
                <div class="mb-4 text-center">
                    <p class="text-sm text-gray-600">Masukkan kode OTP yang dikirim ke WhatsApp</p>
                    <p id="otp-target" class="font-bold text-green-700 text-lg my-2"></p>
                </div>
                <div class="mb-6">
                    <input type="text" id="otp_code"
                        class="w-full text-center text-2xl tracking-widest px-4 py-3 rounded-lg border-2 border-green-500 focus:outline-none"
                        placeholder="******" maxlength="6">
                </div>
                <button type="submit"
                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-4 rounded-lg transition duration-300 shadow-lg">
                    Verifikasi Login
                </button>
                <button type="button" id="btn-back-wa" class="w-full mt-3 text-sm text-gray-500 hover:text-gray-700">
                    Ganti Nomor
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Tab Switching
        const tabUmum = document.getElementById('tab-umum');
        const tabWa = document.getElementById('tab-wa');
        const formUmum = document.getElementById('form-umum');
        const formWa = document.getElementById('form-wa');
        const formOtp = document.getElementById('form-otp');

        tabUmum.addEventListener('click', () => {
            tabUmum.className = "flex-1 py-4 text-center font-semibold text-green-800 border-b-2 border-green-600 bg-green-50 tab-btn";
            tabWa.className = "flex-1 py-4 text-center font-semibold text-gray-500 hover:text-green-600 tab-btn";
            formUmum.classList.remove('hidden');
            formWa.classList.add('hidden');
            formOtp.classList.add('hidden');
        });

        tabWa.addEventListener('click', () => {
            tabWa.className = "flex-1 py-4 text-center font-semibold text-green-800 border-b-2 border-green-600 bg-green-50 tab-btn";
            tabUmum.className = "flex-1 py-4 text-center font-semibold text-gray-500 hover:text-green-600 tab-btn";
            formWa.classList.remove('hidden');
            formUmum.classList.add('hidden');
            formOtp.classList.add('hidden');
        });

        // --- 1. REQUEST OTP ---
        document.getElementById('form-wa').addEventListener('submit', async (e) => {
            e.preventDefault();
            const wa = document.getElementById('wa_number').value;
            if (!wa) return Swal.fire('Error', 'Nomor WA wajib diisi', 'error');

            Swal.showLoading();
            const formData = new FormData();
            formData.append('no_whatsapp', '62' + wa);

            try {
                const req = await fetch('api/request_otp.php', { method: 'POST', body: formData });
                const res = await req.json();

                if (res.status === 'success') {
                    Swal.fire('Terkirim', res.message, 'success');
                    document.getElementById('otp-target').innerText = '+62' + wa;
                    formWa.classList.add('hidden');
                    formOtp.classList.remove('hidden');
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Gagal koneksi ke server', 'error');
            }
        });

        // --- 2. VERIFY OTP & REDIRECT ---
        document.getElementById('form-otp').addEventListener('submit', async (e) => {
            e.preventDefault();
            const wa = document.getElementById('wa_number').value;
            const otp = document.getElementById('otp_code').value;

            Swal.showLoading();
            const formData = new FormData();
            formData.append('no_whatsapp', '62' + wa);
            formData.append('otp_code', otp);

            try {
                const req = await fetch('api/verify_otp.php', { method: 'POST', body: formData });
                const res = await req.json();

                if (res.status === 'success') {
                    // Redirect Dinamis (Admin ke Admin, Santri ke User)
                    if (res.redirect === 'admin') {
                        window.location.href = 'admin/dashboard.php';
                    } else {
                        window.location.href = 'user/dashboard.php';
                    }
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Gagal verifikasi', 'error');
            }
        });

        document.getElementById('btn-back-wa').addEventListener('click', () => {
            formOtp.classList.add('hidden');
            formWa.classList.remove('hidden');
        });
    </script>
</body>

</html>