<?php
// user/daftar_event.php
$page_title = "Pendaftaran Event";
$current_page = "dashboard";

require_once 'templates/header.php';

// 1. VALIDASI ID
if (!isset($_GET['id'])) {
    echo "<script>window.location='dashboard.php';</script>";
    exit;
}
$event_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id']; // ID User yang login (Booker)

// 2. AMBIL DATA EVENT
$stmt = $pdo->prepare("SELECT * FROM workshops WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    echo "<div class='p-6 text-center text-red-500'>Event tidak ditemukan.</div>";
    require_once 'templates/footer.php';
    exit;
}

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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_peserta = trim($_POST['nama_peserta']);
    $email_peserta = trim($_POST['email_peserta']);
    $telepon_peserta = trim($_POST['telepon_peserta']);
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $jawaban_custom = $_POST['jawaban'] ?? [];

    try {
        // Cek apakah EMAIL ini sudah terdaftar di event ini
        $stmt_cek = $pdo->prepare("SELECT id FROM pendaftaran WHERE workshop_id = ? AND email_peserta = ?");
        $stmt_cek->execute([$event_id, $email_peserta]);

        if ($stmt_cek->rowCount() > 0) {
            echo "<script>Swal.fire('Info', 'Email peserta ini sudah terdaftar di event tersebut.', 'info');</script>";
        } else {
            // Generate Kode Unik
            $kode_unik = "WS-" . $event_id . "-" . strtoupper(bin2hex(random_bytes(3)));

            // Status Awal
            // Jika gratis -> free, Jika berbayar -> pending (menunggu respon duitku)
            $status_awal = $is_free ? 'free' : 'pending';

            // Mulai Transaksi Database
            $pdo->beginTransaction();

            // 1. Insert Data Pendaftaran
            // Pastikan kolom user_id sudah ada di tabel pendaftaran
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

            // 2. Insert Jawaban Custom (Jika ada)
            if (!empty($jawaban_custom)) {
                $sql_jawaban = "INSERT INTO pendaftaran_data (pendaftaran_id, field_id, value) VALUES (?, ?, ?)";
                $stmt_jawaban = $pdo->prepare($sql_jawaban);

                foreach ($jawaban_custom as $field_id => $nilai) {
                    $stmt_jawaban->execute([$pendaftaran_id, $field_id, $nilai]);
                }
            }

            // ============================================================
            // LOGIKA PEMBAYARAN (DUITKU vs GRATIS)
            // ============================================================

            if ($is_free) {
                // --- ALUR A: EVENT GRATIS ---
                // Tidak perlu ke Duitku, langsung commit dan sukses
                $pdo->commit();

                echo "<script>
                    Swal.fire({
                        title: 'Pendaftaran Berhasil!',
                        text: 'Tiket event gratis berhasil didapatkan.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location = 'riwayat_transaksi.php';
                    });
                </script>";

            } else {
                // --- ALUR B: EVENT BERBAYAR (DUITKU) ---

                // Konfigurasi Duitku (Sandbox)
                $merchantCode = 'D20354';
                $apiKey = 'cc7e768f19d886126b3ef8b1babe81b8';
                $duitku_url = 'https://passport.duitku.com/webapi/api/merchant/v2/inquiry'; // Sandbox URL

                // Data Request
                $paymentAmount = $harga_event;
                $merchantOrderId = $kode_unik;
                $productDetails = 'Tiket: ' . substr($event['judul'], 0, 50);
                $callbackUrl = 'https://ukmelrahma.my.id/callback.php'; // Ganti domain Anda
                $returnUrl = 'https://ukmelrahma.my.id/user/riwayat_transaksi.php'; // Kembali ke riwayat
                $expiryPeriod = 60; // 60 menit

                // Signature MD5
                $signature = md5($merchantCode . $merchantOrderId . $paymentAmount . $apiKey);

                $params = array(
                    'merchantCode' => $merchantCode,
                    'paymentAmount' => $paymentAmount,
                    'merchantOrderId' => $merchantOrderId,
                    'productDetails' => $productDetails,
                    'additionalParam' => '',
                    'merchantUserInfo' => '',
                    'paymentMethod' => 'SQ',
                    'customerVaName' => $nama_peserta,
                    'email' => $email_peserta,
                    'phoneNumber' => preg_replace('/^0/', '62', $telepon_peserta),
                    'callbackUrl' => $callbackUrl,
                    'returnUrl' => $returnUrl,
                    'signature' => $signature,
                    'expiryPeriod' => $expiryPeriod
                );

                // Kirim CURL
                // ... (Kode paramater $params di atas biarkan saja) ...

                // --- GANTI MULAI DARI SINI ---
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $duitku_url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen(json_encode($params))
                ));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Abaikan SSL (khusus dev/laragon)
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);     // Abaikan Host SSL

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch); // Cek error koneksi
                curl_close($ch);

                // DEBUGGING: Cek hasil respon
                if ($httpCode == 200) {
                    $result = json_decode($response, true);

                    if (isset($result['paymentUrl'])) {
                        // 1. Simpan Payment URL
                        $upd = $pdo->prepare("UPDATE pendaftaran SET payment_url = ? WHERE id = ?");
                        $upd->execute([$result['paymentUrl'], $pendaftaran_id]);

                        $pdo->commit();

                        echo "<script>
                            window.location.href = '" . $result['paymentUrl'] . "';
                        </script>";
                    } else {
                        // Duitku merespon 200 tapi isinya error logika (misal: signature salah)
                        $pesan_error = $result['statusMessage'] ?? 'Respon tidak dikenali';
                        throw new Exception("Duitku Error: " . $pesan_error);
                    }
                } else {
                    // Error Koneksi (400, 401, 500, atau koneksi putus)
                    // Tampilkan pesan detail untuk debugging
                    $pesan_error = "HTTP Code: $httpCode. ";
                    if ($curlError) {
                        $pesan_error .= "CURL Error: $curlError. ";
                    }
                    if ($response) {
                        $pesan_error .= "Response: $response";
                    }
                    throw new Exception("Gagal koneksi Duitku. " . $pesan_error);
                }
                // --- SAMPAI SINI ---
            } // End Else (Berbayar)
        }
    } catch (Exception $e) {
        // Rollback jika terjadi error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "<script>Swal.fire('Gagal', 'Terjadi kesalahan: " . addslashes($e->getMessage()) . "', 'error');</script>";
    }
}
?>

<div class="max-w-6xl mx-auto">

    <a href="dashboard.php"
        class="inline-flex items-center text-gray-600 hover:text-primary mb-6 transition font-medium">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
    </a>

    <div class="flex flex-col lg:flex-row gap-8">

        <div class="w-full lg:w-2/3">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8 relative group">
                <div class="h-64 md:h-80 bg-gray-200 relative overflow-hidden">
                    <?php if ($event['poster']): ?>
                        <img src="<?= BASE_URL ?>assets/img/posters/<?= htmlspecialchars($event['poster']) ?>"
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    <?php else: ?>
                        <div
                            class="w-full h-full flex items-center justify-center bg-gradient-to-r from-green-800 to-green-600 text-white">
                            <i class="fas fa-calendar-alt text-6xl opacity-30"></i>
                        </div>
                    <?php endif; ?>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>

                    <div class="absolute bottom-0 left-0 p-6 md:p-8 text-white">
                        <span
                            class="bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-3 inline-block shadow-md">
                            <?= $is_free ? 'Gratis' : 'Berbayar' ?>
                        </span>
                        <h1 class="text-2xl md:text-4xl font-bold mb-2 leading-tight">
                            <?= htmlspecialchars($event['judul']) ?>
                        </h1>
                        <p class="text-green-100 text-sm md:text-base">
                            <i class="far fa-calendar-alt mr-2"></i>
                            <?= date('l, d F Y', strtotime($event['tanggal_waktu'])) ?> •
                            <i class="far fa-clock ml-2 mr-2"></i>
                            <?= date('H:i', strtotime($event['tanggal_waktu'])) ?> WIB
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-start">
                    <div class="bg-blue-50 p-3 rounded-full mr-4 text-blue-600">
                        <i class="fas fa-map-marker-alt text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-700 text-sm uppercase mb-1">Lokasi</h4>
                        <p class="text-gray-600 font-medium"><?= htmlspecialchars($event['lokasi']) ?></p>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-start">
                    <div class="bg-yellow-50 p-3 rounded-full mr-4 text-yellow-600">
                        <i class="fas fa-tag text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-700 text-sm uppercase mb-1">Biaya Pendaftaran</h4>
                        <p class="text-gray-800 font-bold text-lg">
                            <?= $is_free ? 'Gratis' : 'Rp ' . number_format($harga_event, 0, ',', '.') ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-align-left text-primary mr-3"></i> Deskripsi Event
                </h3>
                <div class="prose max-w-none text-gray-600 leading-relaxed">
                    <?= nl2br(htmlspecialchars($event['deskripsi'])) ?>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-1/3">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden sticky top-24 border border-gray-100">
                <div
                    class="bg-gradient-to-r from-secondary to-yellow-600 p-6 text-white text-center relative overflow-hidden">
                    <div
                        class="absolute top-0 right-0 w-20 h-20 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/3">
                    </div>
                    <h3 class="text-xl font-bold relative z-10">Form Pendaftaran</h3>
                    <p class="text-yellow-100 text-xs mt-1 relative z-10">Lengkapi data diri Anda di bawah ini</p>
                </div>

                <div class="p-6">
                    <form action="" method="POST" class="space-y-5">

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="nama_peserta"
                                value="<?= htmlspecialchars($user_data['nama_lengkap']) ?>"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary transition bg-gray-50 focus:bg-white"
                                placeholder="Nama lengkap Anda" required>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Email <span
                                    class="text-red-500">*</span></label>
                            <input type="email" name="email_peserta"
                                value="<?= htmlspecialchars($user_data['email']) ?>"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary transition bg-gray-50 focus:bg-white"
                                placeholder="email@contoh.com" required>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">No. WhatsApp <span
                                    class="text-red-500">*</span></label>
                            <input type="number" name="telepon_peserta"
                                value="<?= htmlspecialchars($user_data['no_whatsapp']) ?>"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary focus:border-primary transition bg-gray-50 focus:bg-white"
                                placeholder="08xxxxxxxx" required>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Kelamin</label>
                            <div class="flex gap-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="jenis_kelamin" value="Laki-laki"
                                        class="text-primary focus:ring-primary h-4 w-4" checked>
                                    <span class="ml-2 text-gray-700">Laki-laki</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="jenis_kelamin" value="Perempuan"
                                        class="text-primary focus:ring-primary h-4 w-4">
                                    <span class="ml-2 text-gray-700">Perempuan</span>
                                </label>
                            </div>
                        </div>

                        <?php if (!empty($form_fields)): ?>
                            <hr class="border-dashed border-gray-200">
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                                <h4 class="text-sm font-bold text-blue-800 mb-3 uppercase">Info Tambahan</h4>
                                <?php foreach ($form_fields as $field): ?>
                                    <div class="mb-4 last:mb-0">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            <?= htmlspecialchars($field['label']) ?>
                                            <?php if ($field['is_required']): ?><span
                                                    class="text-red-500">*</span><?php endif; ?>
                                        </label>

                                        <?php if ($field['field_type'] == 'select'): ?>
                                            <select name="jawaban[<?= $field['id'] ?>]"
                                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 transition"
                                                <?= $field['is_required'] ? 'required' : '' ?>>
                                                <option value="">-- Pilih --</option>
                                                <?php foreach (explode(',', $field['options']) as $opt): ?>
                                                    <option value="<?= trim($opt) ?>"><?= trim($opt) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php else: ?>
                                            <input type="text" name="jawaban[<?= $field['id'] ?>]"
                                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 transition"
                                                <?= $field['is_required'] ? 'required' : '' ?>>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="pt-2">
                            <button type="submit"
                                onclick="return confirm('Apakah data yang Anda masukkan sudah benar?')"
                                class="w-full bg-gradient-to-r from-primary to-green-700 hover:from-green-800 hover:to-green-900 text-white font-bold py-4 rounded-xl shadow-lg hover:shadow-xl transition transform hover:-translate-y-1 flex items-center justify-center">
                                <i class="fas fa-paper-plane mr-2"></i>
                                <?= $is_free ? 'Daftar Sekarang' : 'Lanjut Pembayaran' ?>
                            </button>
                            <p class="text-center text-xs text-gray-500 mt-3">
                                Dengan mendaftar, Anda menyetujui syarat & ketentuan event ini.
                            </p>
                        </div>

                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once 'templates/footer.php'; ?>