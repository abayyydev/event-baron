<?php
// user/daftar_event.php

if (session_status() === PHP_SESSION_NONE)
    session_start();

// 1. CEK LOGIN SANTRI
if (!isset($_SESSION['santri_id']) || $_SESSION['role'] !== 'peserta') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Pendaftaran Event";
require_once 'templates/header.php';
require_once '../core/koneksi.php';

// Variabel script alert
$swal_script = "";

// 2. VALIDASI ID EVENT
if (!isset($_GET['id'])) {
    echo "<script>window.location='dashboard.php';</script>";
    exit;
}

$event_id = (int) $_GET['id'];
$santri_id = $_SESSION['santri_id'];

// 3. AMBIL DATA EVENT
$stmt = $pdo->prepare("SELECT * FROM workshops WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    echo "<div class='min-h-screen flex items-center justify-center bg-gray-50 text-gray-500'>Event tidak ditemukan.</div>";
    require_once 'templates/footer.php';
    exit;
}

$harga_event = (int) $event['harga'];
$is_free = ($event['tipe_event'] == 'gratis' || $harga_event <= 0);

// 4. AMBIL DATA SANTRI (Untuk Auto-fill form default)
$stmt_santri = $pdo->prepare("SELECT * FROM santri WHERE id = ?");
$stmt_santri->execute([$santri_id]);
$santri_data = $stmt_santri->fetch(PDO::FETCH_ASSOC);

// 5. AMBIL RIWAYAT PENDAFTARAN (SIAPA SAJA YG SUDAH DIDAFTARKAN AKUN INI)
// Kita tidak memblokir form, tapi menampilkan list yang sudah daftar
$stmt_history = $pdo->prepare("SELECT * FROM pendaftaran WHERE workshop_id = ? AND santri_id = ? ORDER BY created_at DESC");
$stmt_history->execute([$event_id, $santri_id]);
$history_pendaftar = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

// 6. AMBIL FORM FIELDS TAMBAHAN
$stmt_fields = $pdo->prepare("SELECT * FROM form_fields WHERE workshop_id = ? ORDER BY urutan ASC");
$stmt_fields->execute([$event_id]);
$form_fields = $stmt_fields->fetchAll(PDO::FETCH_ASSOC);

// 7. PROSES PENDAFTARAN (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_peserta = trim($_POST['nama_peserta']);
    $email_peserta = trim($_POST['email_peserta']);
    $telepon_peserta = trim($_POST['telepon_peserta']);
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $jawaban_custom = $_POST['jawaban'] ?? [];

    try {
        // Cek apakah NAMA PESERTA ini sudah didaftarkan di event ini oleh akun ini?
        // (Mencegah double submit orang yang sama, tapi membolehkan orang beda)
        $stmt_cek_nama = $pdo->prepare("SELECT id FROM pendaftaran WHERE workshop_id = ? AND santri_id = ? AND nama_peserta = ?");
        $stmt_cek_nama->execute([$event_id, $santri_id, $nama_peserta]);

        if ($stmt_cek_nama->rowCount() > 0) {
            $swal_script = "
            <script>
                Swal.fire({
                    icon: 'warning',
                    title: 'Sudah Terdaftar',
                    text: 'Peserta dengan nama ini sudah Anda daftarkan sebelumnya.',
                    confirmButtonColor: '#fbbf24'
                });
            </script>";
        } else {
            // Generate Kode Unik
            $kode_unik = "WS-" . $event_id . "-" . strtoupper(bin2hex(random_bytes(3)));
            $status_awal = $is_free ? 'free' : 'pending';

            $pdo->beginTransaction();

            $sql_ins = "INSERT INTO pendaftaran 
                        (workshop_id, santri_id, kode_unik, nama_peserta, email_peserta, telepon_peserta, jenis_kelamin, status_pembayaran, didaftarkan_oleh) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'mandiri')";

            $stmt_ins = $pdo->prepare($sql_ins);
            $stmt_ins->execute([
                $event_id,
                $santri_id,
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

            if ($is_free) {
                // EVENT GRATIS
                $pdo->commit();
                $swal_script = "
                <script>
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Pendaftaran berhasil disimpan.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        window.location = 'daftar_event.php?id=$event_id'; // Refresh halaman
                    });
                </script>";
            } else {
                // EVENT BERBAYAR (DUITKU)
                // ... Konfigurasi Duitku (Sama seperti sebelumnya) ...
                $merchantCode = 'D20354';
                $apiKey = 'cc7e768f19d886126b3ef8b1babe81b8';
                $duitku_url = 'https://passport.duitku.com/webapi/api/merchant/v2/inquiry';

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
                    'paymentMethod' => 'SQ',
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
                        $upd = $pdo->prepare("UPDATE pendaftaran SET payment_url = ? WHERE id = ?");
                        $upd->execute([$result['paymentUrl'], $pendaftaran_id]);
                        $pdo->commit();
                        $swal_script = "<script>window.location.href = '" . $result['paymentUrl'] . "';</script>";
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
        $err_msg = addslashes($e->getMessage());
        $swal_script = "<script>Swal.fire('Gagal', 'Terjadi kesalahan: $err_msg', 'error');</script>";
    }
}
?>

<div class="min-h-screen bg-slate-50 font-sans pb-20">

    <div class="bg-primary-900 pb-20 pt-10 px-4 rounded-b-[3rem] shadow-xl relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-primary-800 rounded-full opacity-50 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-gold-500 rounded-full opacity-20 blur-2xl"></div>

        <div class="max-w-6xl mx-auto relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <a href="dashboard.php" class="text-primary-200 hover:text-white transition-all bg-white/10 hover:bg-white/20 p-2 rounded-full backdrop-blur-sm group">
                        <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                    </a>
                    <span class="text-primary-200 text-xs font-bold uppercase tracking-widest border border-primary-700/50 px-2 py-1 rounded-md">Registrasi Event</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight leading-tight">
                    <?= htmlspecialchars($event['judul']) ?>
                </h1>
                <div class="flex flex-wrap gap-4 mt-3 text-sm text-primary-100/90">
                    <span class="flex items-center"><i class="fas fa-calendar-alt mr-2 text-gold-400"></i>
                        <?= date('d M Y', strtotime($event['tanggal_waktu'])) ?></span>
                    <span class="flex items-center"><i class="fas fa-map-marker-alt mr-2 text-primary-400"></i>
                        <?= htmlspecialchars($event['lokasi']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 -mt-12 relative z-20">
        <div class="flex flex-col lg:flex-row gap-8">

            <div class="w-full lg:w-2/3 space-y-8">
                
                <?php if (count($history_pendaftar) > 0): ?>
                        <div class="bg-white rounded-3xl shadow-lg border border-primary-100 overflow-hidden">
                            <div class="bg-primary-50 px-6 py-4 border-b border-primary-100 flex justify-between items-center">
                                <h3 class="font-bold text-primary-800 flex items-center gap-2">
                                    <i class="fas fa-history"></i> Riwayat Pendaftaran Anda
                                </h3>
                                <span class="text-xs bg-white px-2 py-1 rounded-lg border border-primary-100 text-primary-600">
                                    <?= count($history_pendaftar) ?> Tiket
                                </span>
                            </div>
                            <div class="p-4 space-y-3">
                                <?php foreach ($history_pendaftar as $hist): ?>
                                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                                            <div>
                                                <p class="font-bold text-slate-700 text-sm"><?= htmlspecialchars($hist['nama_peserta']) ?></p>
                                                <p class="text-xs text-slate-500 mt-0.5">
                                                    Kode: <span class="font-mono text-primary-600"><?= $hist['kode_unik'] ?></span>
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <?php if ($hist['status_pembayaran'] == 'paid' || $hist['status_pembayaran'] == 'free'): ?>
                                                        <span class="text-[10px] font-bold text-white bg-green-500 px-2 py-1 rounded-full">LUNAS</span>
                                                        <a href="cetak_tiket.php?id=<?= $hist['id'] ?>" class="block text-[10px] text-blue-600 underline mt-1">Lihat Tiket</a>
                                                <?php else: ?>
                                                        <span class="text-[10px] font-bold text-white bg-amber-500 px-2 py-1 rounded-full">PENDING</span>
                                                        <a href="<?= $hist['payment_url'] ?>" class="block text-[10px] text-blue-600 underline mt-1">Bayar</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                <?php endif; ?>

                <div class="bg-white p-8 rounded-3xl shadow-lg border border-slate-100">
                    <h3 class="text-xl font-bold text-slate-800 mb-4 flex items-center">
                        <i class="fas fa-align-left text-primary-500 mr-3"></i> Deskripsi Event
                    </h3>
                    <div class="prose prose-primary max-w-none text-slate-600 leading-relaxed text-sm md:text-base">
                        <?= nl2br(htmlspecialchars($event['deskripsi'])) ?>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-1/3">
                <div class="sticky top-4">
                    <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-primary-600 to-primary-800 p-6 text-white text-center relative overflow-hidden">
                            <h3 class="text-xl font-bold relative z-10">Form Pendaftaran</h3>
                            <p class="text-primary-100 text-xs mt-1 relative z-10">Daftarkan Diri / Wali / Keluarga</p>
                        </div>

                        <form action="" method="POST" class="p-6 space-y-5">
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Peserta</label>
                                <input type="text" name="nama_peserta"
                                    value="<?= htmlspecialchars($santri_data['nama_lengkap']) ?>"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all outline-none text-sm font-medium text-slate-700"
                                    placeholder="Masukkan nama peserta..."
                                    required>
                                <p class="text-[10px] text-slate-400 mt-1">*Bisa diganti jika mendaftarkan Wali/Orang Lain.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Email (Untuk E-Tiket)</label>
                                <input type="email" name="email_peserta"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all outline-none text-sm font-medium"
                                    placeholder="Masukkan email aktif..." 
                                    required>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">WhatsApp</label>
                                <input type="number" name="telepon_peserta"
                                    value="<?= htmlspecialchars($santri_data['no_hp_wali']) ?>"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all outline-none text-sm font-medium"
                                    placeholder="08xxxxxxxx"
                                    required>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Jenis Kelamin</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="jenis_kelamin" value="Laki-laki" class="peer sr-only" checked>
                                        <div class="text-center py-2 border rounded-lg text-sm text-slate-600 peer-checked:bg-primary-50 peer-checked:text-primary-700 peer-checked:border-primary-200 transition-all hover:bg-slate-50">
                                            Laki-laki
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="jenis_kelamin" value="Perempuan" class="peer sr-only">
                                        <div class="text-center py-2 border rounded-lg text-sm text-slate-600 peer-checked:bg-primary-50 peer-checked:text-primary-700 peer-checked:border-primary-200 transition-all hover:bg-slate-50">
                                            Perempuan
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <?php if (!empty($form_fields)): ?>
                                    <div class="pt-2 border-t border-dashed border-slate-200">
                                        <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 space-y-4">
                                            <h4 class="text-xs font-bold text-blue-700 uppercase tracking-wider mb-2">Data Tambahan</h4>
                                            <?php foreach ($form_fields as $field): ?>
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-600 mb-1">
                                                            <?= htmlspecialchars($field['label']) ?>
                                                            <?php if ($field['is_required']): ?><span class="text-red-500">*</span><?php endif; ?>
                                                        </label>

                                                        <?php if ($field['field_type'] == 'select'): ?>
                                                                <select name="jawaban[<?= $field['id'] ?>]"
                                                                    class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-sm outline-none bg-white"
                                                                    <?= $field['is_required'] ? 'required' : '' ?>>
                                                                    <option value="">-- Pilih --</option>
                                                                    <?php foreach (explode(',', $field['options']) as $opt): ?>
                                                                            <option value="<?= trim($opt) ?>"><?= trim($opt) ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                        <?php else: ?>
                                                                <input type="text" name="jawaban[<?= $field['id'] ?>]"
                                                                    class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-sm outline-none bg-white"
                                                                    placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>"
                                                                    <?= $field['is_required'] ? 'required' : '' ?>>
                                                        <?php endif; ?>
                                                    </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                            <?php endif; ?>

                            <button type="submit" onclick="return confirm('Pastikan data peserta sudah benar. Lanjutkan?')"
                                class="w-full bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-primary-200 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                                <span><?= $is_free ? 'Daftarkan Peserta' : 'Lanjut Pembayaran' ?></span>
                                <i class="fas fa-arrow-right"></i>
                            </button>

                            <p class="text-center text-[10px] text-slate-400">
                                Dengan mendaftar, Anda menyetujui aturan kegiatan pondok.
                            </p>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
if (!empty($swal_script)) {
    echo $swal_script;
}
?>

<?php require_once 'templates/footer.php'; ?>