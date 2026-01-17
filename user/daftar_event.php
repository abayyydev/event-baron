<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

$page_title = "Pendaftaran Event";
$current_page = "Dashboard";

require_once 'templates/header.php';
require_once '../core/koneksi.php';

// 1. VALIDASI ID
if (!isset($_GET['id'])) {
    echo "<script>window.location='dashboard.php';</script>";
    exit;
}
$event_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id']; // ID User yang login

// 2. AMBIL DATA EVENT
$stmt = $pdo->prepare("SELECT * FROM workshops WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    echo "
    <div class='min-h-screen flex items-center justify-center bg-gray-50'>
        <div class='text-center p-8'>
            <i class='fas fa-exclamation-circle text-4xl text-red-500 mb-4'></i>
            <h2 class='text-xl font-bold text-gray-800'>Event Tidak Ditemukan</h2>
            <a href='dashboard.php' class='mt-4 inline-block text-emerald-600 font-bold hover:underline'>Kembali ke Dashboard</a>
        </div>
    </div>";
    require_once 'templates/footer.php';
    exit;
}

// Cek status pendaftaran user untuk event ini (Mencegah duplikasi)
$stmt_check = $pdo->prepare("SELECT * FROM pendaftaran WHERE workshop_id = ? AND user_id = ?");
$stmt_check->execute([$event_id, $user_id]);
$existing_reg = $stmt_check->fetch(PDO::FETCH_ASSOC);

// Cek apakah event berbayar
$harga_event = (int) $event['harga'];
$is_free = ($event['tipe_event'] == 'gratis' || $harga_event <= 0);

// 3. AMBIL FORM FIELDS (Pertanyaan Tambahan)
$stmt_fields = $pdo->prepare("SELECT * FROM form_fields WHERE workshop_id = ? ORDER BY urutan ASC");
$stmt_fields->execute([$event_id]);
$form_fields = $stmt_fields->fetchAll(PDO::FETCH_ASSOC);

// 4. AMBIL DATA USER (Untuk Auto-fill form)
$stmt_user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

// 5. PROSES PENDAFTARAN (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$existing_reg) {
    $nama_peserta = trim($_POST['nama_peserta']);
    $email_peserta = trim($_POST['email_peserta']);
    $telepon_peserta = trim($_POST['telepon_peserta']);
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $jawaban_custom = $_POST['jawaban'] ?? [];

    try {
        // Double check email registration manually just in case
        $stmt_cek = $pdo->prepare("SELECT id FROM pendaftaran WHERE workshop_id = ? AND email_peserta = ?");
        $stmt_cek->execute([$event_id, $email_peserta]);

        if ($stmt_cek->rowCount() > 0) {
            echo "<script>
                Swal.fire({
                    icon: 'warning',
                    title: 'Sudah Terdaftar',
                    text: 'Email ini sudah terdaftar untuk event tersebut.',
                    confirmButtonColor: '#fbbf24'
                });
            </script>";
        } else {
            // Generate Kode Unik
            $kode_unik = "WS-" . $event_id . "-" . strtoupper(bin2hex(random_bytes(3)));
            $status_awal = $is_free ? 'free' : 'pending';

            // Mulai Transaksi
            $pdo->beginTransaction();

            // Insert Data Pendaftaran
            $sql_ins = "INSERT INTO pendaftaran (workshop_id, user_id, kode_unik, nama_peserta, email_peserta, telepon_peserta, jenis_kelamin, status_pembayaran) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_ins = $pdo->prepare($sql_ins);
            $stmt_ins->execute([
                $event_id,
                $user_id,
                $kode_unik,
                $nama_peserta,
                $email_peserta,
                $telepon_peserta,
                $jenis_kelamin,
                $status_awal
            ]);

            $pendaftaran_id = $pdo->lastInsertId();

            // Insert Jawaban Custom
            if (!empty($jawaban_custom)) {
                $sql_jawaban = "INSERT INTO pendaftaran_data (pendaftaran_id, field_id, value) VALUES (?, ?, ?)";
                $stmt_jawaban = $pdo->prepare($sql_jawaban);

                foreach ($jawaban_custom as $field_id => $nilai) {
                    $stmt_jawaban->execute([$pendaftaran_id, $field_id, $nilai]);
                }
            }

            // LOGIKA PEMBAYARAN
            if ($is_free) {
                // EVENT GRATIS
                $pdo->commit();
                echo "<script>
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Pendaftaran event gratis berhasil.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        window.location = 'riwayat_transaksi.php';
                    });
                </script>";
            } else {
                // EVENT BERBAYAR (Duitku)
                // --- Konfigurasi Duitku ---
                $merchantCode = 'D20354'; // Ganti dengan Merchant Code Anda
                $apiKey = 'cc7e768f19d886126b3ef8b1babe81b8'; // Ganti dengan API Key Anda
                $duitku_url = 'https://passport.duitku.com/webapi/api/merchant/v2/inquiry'; // Sandbox URL

                $paymentAmount = $harga_event;
                $merchantOrderId = $kode_unik;
                $productDetails = 'Tiket: ' . substr($event['judul'], 0, 50);
                $callbackUrl = 'https://ukmelrahma.my.id/callback.php';
                $returnUrl = 'https://ukmelrahma.my.id/user/riwayat_transaksi.php';
                $expiryPeriod = 60;

                $signature = md5($merchantCode . $merchantOrderId . $paymentAmount . $apiKey);

                $params = array(
                    'merchantCode' => $merchantCode,
                    'paymentAmount' => $paymentAmount,
                    'merchantOrderId' => $merchantOrderId,
                    'productDetails' => $productDetails,
                    'additionalParam' => '',
                    'merchantUserInfo' => '',
                    'paymentMethod' => 'SQ', // QRIS Default (bisa diganti)
                    'customerVaName' => $nama_peserta,
                    'email' => $email_peserta,
                    'phoneNumber' => preg_replace('/^0/', '62', $telepon_peserta),
                    'callbackUrl' => $callbackUrl,
                    'returnUrl' => $returnUrl,
                    'signature' => $signature,
                    'expiryPeriod' => $expiryPeriod
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $duitku_url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen(json_encode($params))
                ));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode == 200) {
                    $result = json_decode($response, true);
                    if (isset($result['paymentUrl'])) {
                        // Update Payment URL ke Database
                        $upd = $pdo->prepare("UPDATE pendaftaran SET payment_url = ? WHERE id = ?");
                        $upd->execute([$result['paymentUrl'], $pendaftaran_id]);
                        $pdo->commit();

                        echo "<script>window.location.href = '" . $result['paymentUrl'] . "';</script>";
                    } else {
                        throw new Exception("Duitku Error: " . ($result['statusMessage'] ?? 'Unknown Error'));
                    }
                } else {
                    throw new Exception("Gagal koneksi ke Payment Gateway.");
                }
            }
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        echo "<script>Swal.fire('Gagal', 'Terjadi kesalahan: " . addslashes($e->getMessage()) . "', 'error');</script>";
    }
}
?>

<div class="min-h-screen bg-gray-50 font-sans pb-20">

    <!-- Hero Section -->
    <div class="bg-emerald-900 pb-20 pt-10 px-4 rounded-b-[3rem] shadow-xl relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-emerald-800 rounded-full opacity-50 blur-3xl">
        </div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-amber-500 rounded-full opacity-20 blur-2xl">
        </div>

        <div
            class="max-w-6xl mx-auto relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <a href="dashboard.php"
                        class="text-emerald-200 hover:text-white transition-all bg-white/10 hover:bg-white/20 p-2 rounded-full backdrop-blur-sm group">
                        <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                    </a>
                    <span
                        class="text-emerald-200 text-xs font-bold uppercase tracking-widest border border-emerald-700/50 px-2 py-1 rounded-md">Registrasi
                        Event</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight leading-tight">
                    <?= htmlspecialchars($event['judul']) ?>
                </h1>
                <div class="flex flex-wrap gap-4 mt-3 text-sm text-emerald-100/90">
                    <span class="flex items-center"><i class="fas fa-calendar-alt mr-2 text-amber-400"></i>
                        <?= date('d M Y', strtotime($event['tanggal_waktu'])) ?></span>
                    <span class="flex items-center"><i class="fas fa-map-marker-alt mr-2 text-emerald-400"></i>
                        <?= htmlspecialchars($event['lokasi']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 -mt-12 relative z-20">
        <div class="flex flex-col lg:flex-row gap-8">

            <!-- LEFT: Event Details -->
            <div class="w-full lg:w-2/3 space-y-8">
                <!-- Poster Card -->
                <div class="bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden relative group">
                    <div class="h-64 md:h-96 bg-gray-200 relative overflow-hidden">
                        <?php if ($event['poster']): ?>
                            <img src="<?= BASE_URL ?>assets/img/posters/<?= htmlspecialchars($event['poster']) ?>"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        <?php else: ?>
                            <div
                                class="w-full h-full flex items-center justify-center bg-gradient-to-r from-emerald-800 to-emerald-600 text-white">
                                <i class="fas fa-calendar-alt text-6xl opacity-30"></i>
                            </div>
                        <?php endif; ?>

                        <!-- Price Tag -->
                        <div class="absolute top-4 right-4">
                            <span
                                class="bg-white/90 backdrop-blur text-emerald-900 px-4 py-2 rounded-xl text-sm font-extrabold shadow-lg">
                                <?= $is_free ? 'GRATIS' : 'Rp ' . number_format($harga_event, 0, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Description Card -->
                <div class="bg-white p-8 rounded-3xl shadow-lg border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-align-left text-emerald-500 mr-3"></i> Deskripsi Event
                    </h3>
                    <div class="prose prose-emerald max-w-none text-gray-600 leading-relaxed text-sm md:text-base">
                        <?= nl2br(htmlspecialchars($event['deskripsi'])) ?>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Registration Form (Sticky) -->
            <div class="w-full lg:w-1/3">
                <div class="sticky top-4">

                    <?php if ($existing_reg): ?>
                        <!-- STATE: SUDAH TERDAFTAR -->
                        <div class="bg-white rounded-3xl shadow-xl border border-emerald-100 overflow-hidden">
                            <div class="bg-emerald-50 p-6 text-center border-b border-emerald-100">
                                <div
                                    class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-3 text-emerald-600">
                                    <i class="fas fa-check-circle text-3xl"></i>
                                </div>
                                <h3 class="text-lg font-bold text-emerald-800">Anda Sudah Terdaftar!</h3>
                                <p class="text-sm text-emerald-600 mt-1">Kode: <span
                                        class="font-mono font-bold"><?= $existing_reg['kode_unik'] ?></span></p>
                            </div>
                            <div class="p-6">
                                <?php if ($existing_reg['status_pembayaran'] == 'pending'): ?>
                                    <p class="text-sm text-gray-600 mb-4 text-center">Menunggu pembayaran. Segera selesaikan
                                        tagihan Anda.</p>
                                    <a href="<?= $existing_reg['payment_url'] ?>" target="_blank"
                                        class="block w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-xl text-center shadow-lg shadow-amber-200 transition-transform hover:-translate-y-0.5">
                                        Bayar Sekarang
                                    </a>
                                <?php else: ?>
                                    <p class="text-sm text-gray-600 mb-4 text-center">Terima kasih telah mendaftar. Silakan cek
                                        tiket Anda.</p>
                                    <a href="riwayat_transaksi.php"
                                        class="block w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl text-center shadow-lg shadow-emerald-200 transition-transform hover:-translate-y-0.5">
                                        Lihat Tiket Saya
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- STATE: FORM PENDAFTARAN -->
                        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                            <div
                                class="bg-gradient-to-r from-emerald-600 to-emerald-800 p-6 text-white text-center relative overflow-hidden">
                                <div
                                    class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2">
                                </div>
                                <h3 class="text-xl font-bold relative z-10">Form Pendaftaran</h3>
                                <p class="text-emerald-100 text-xs mt-1 relative z-10">Isi data diri dengan benar</p>
                            </div>

                            <form action="" method="POST" class="p-6 space-y-5">
                                <!-- Nama -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Lengkap</label>
                                    <input type="text" name="nama_peserta"
                                        value="<?= htmlspecialchars($user_data['nama_lengkap']) ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all outline-none text-sm font-medium"
                                        placeholder="Nama Lengkap" required>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email</label>
                                    <input type="email" name="email_peserta"
                                        value="<?= htmlspecialchars($user_data['email']) ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all outline-none text-sm font-medium"
                                        placeholder="email@anda.com" required>
                                </div>

                                <!-- Telepon -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">WhatsApp</label>
                                    <input type="number" name="telepon_peserta"
                                        value="<?= htmlspecialchars($user_data['no_whatsapp']) ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all outline-none text-sm font-medium"
                                        placeholder="08xxxxxxxx" required>
                                </div>

                                <!-- Gender -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Jenis
                                        Kelamin</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <label class="cursor-pointer">
                                            <input type="radio" name="jenis_kelamin" value="Laki-laki" class="peer sr-only"
                                                checked>
                                            <div
                                                class="text-center py-2 border rounded-lg text-sm text-gray-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 peer-checked:border-emerald-200 transition-all hover:bg-gray-50">
                                                Laki-laki
                                            </div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="jenis_kelamin" value="Perempuan" class="peer sr-only">
                                            <div
                                                class="text-center py-2 border rounded-lg text-sm text-gray-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 peer-checked:border-emerald-200 transition-all hover:bg-gray-50">
                                                Perempuan
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Custom Fields -->
                                <?php if (!empty($form_fields)): ?>
                                    <div class="pt-2 border-t border-dashed border-gray-200">
                                        <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 space-y-4">
                                            <h4 class="text-xs font-bold text-blue-700 uppercase tracking-wider mb-2">Data
                                                Tambahan</h4>
                                            <?php foreach ($form_fields as $field): ?>
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-600 mb-1">
                                                        <?= htmlspecialchars($field['label']) ?>
                                                        <?php if ($field['is_required']): ?><span
                                                                class="text-red-500">*</span><?php endif; ?>
                                                    </label>

                                                    <?php if ($field['field_type'] == 'select'): ?>
                                                        <select name="jawaban[<?= $field['id'] ?>]"
                                                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-sm outline-none bg-white"
                                                            <?= $field['is_required'] ? 'required' : '' ?>>
                                                            <option value="">-- Pilih --</option>
                                                            <?php foreach (explode(',', $field['options']) as $opt): ?>
                                                                <option value="<?= trim($opt) ?>"><?= trim($opt) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    <?php else: ?>
                                                        <input type="text" name="jawaban[<?= $field['id'] ?>]"
                                                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-sm outline-none bg-white"
                                                            placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>"
                                                            <?= $field['is_required'] ? 'required' : '' ?>>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Submit Button -->
                                <button type="submit"
                                    onclick="return confirm('Pastikan data yang Anda masukkan sudah benar?')"
                                    class="w-full bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-emerald-200 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                                    <span><?= $is_free ? 'Daftar Sekarang' : 'Lanjut Pembayaran' ?></span>
                                    <i class="fas fa-arrow-right"></i>
                                </button>

                                <p class="text-center text-[10px] text-gray-400">
                                    Dengan mendaftar, Anda menyetujui syarat & ketentuan yang berlaku.
                                </p>
                            </form>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php require_once 'templates/footer.php'; ?>