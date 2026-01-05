<?php
session_start();
require_once 'core/koneksi.php';
require_once 'templates/header.php';


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}
$workshop_id = $_GET['id'];


try {
    $stmt = $pdo->prepare("SELECT * FROM workshops WHERE id = ?");
    $stmt->execute([$workshop_id]);
    $workshop = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$workshop) {
        header("Location: index.php");
        exit();
    }

    $event_harga = (int) ($workshop['harga'] ?? 0);
    $is_free_event = ($event_harga <= 0);

} catch (PDOException $e) {
    die("Error mengambil detail event: " . $e->getMessage());
}


$stmt_fields = $pdo->prepare("SELECT * FROM form_fields WHERE workshop_id = ? ORDER BY urutan ASC");
$stmt_fields->execute([$workshop_id]);
$form_fields = $stmt_fields->fetchAll(PDO::FETCH_ASSOC);

file_put_contents(__DIR__ . '/log_test.txt', date('Y-m-d H:i:s') . " | File log test jalan\n", FILE_APPEND);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $jawaban = $_POST['jawaban'] ?? [];
    $kode_unik = "WS-" . $workshop_id . "-" . strtoupper(bin2hex(random_bytes(8)));
    $initial_status = $is_free_event ? 'free' : 'pending';

    $pdo->beginTransaction();

    try {
        $stmt_pendaftaran = $pdo->prepare("INSERT INTO pendaftaran (workshop_id, kode_unik, status_pembayaran) VALUES (?, ?, ?)");
        $stmt_pendaftaran->execute([$workshop_id, $kode_unik, $initial_status]);
        $pendaftaran_id = $pdo->lastInsertId();

        $stmt_jawaban = $pdo->prepare("INSERT INTO jawaban_peserta (pendaftaran_id, field_id, jawaban) VALUES (?, ?, ?)");

        $nama_peserta_jawaban = '';
        $telepon_peserta_jawaban = '';
        $field_id_nama = null;
        $field_id_telepon = null;

        foreach ($form_fields as $field) {
            if (trim($field['label']) === 'Nama Lengkap')
                $field_id_nama = $field['id'];
            if (trim($field['label']) === 'Nomor Telepon')
                $field_id_telepon = $field['id'];
        }

        foreach ($jawaban as $field_id => $isi_jawaban) {
            $stmt_jawaban->execute([$pendaftaran_id, $field_id, $isi_jawaban]);
            if ($field_id == $field_id_nama)
                $nama_peserta_jawaban = $isi_jawaban;
            if ($field_id == $field_id_telepon)
                $telepon_peserta_jawaban = $isi_jawaban;
        }

        $target = preg_replace('/^0/', '62', trim($telepon_peserta_jawaban));

        file_put_contents(
            __DIR__ . '/debug_isfree.txt',
            date('Y-m-d H:i:s') . " | is_free_event=" . ($is_free_event ? 'true' : 'false') .
            " | telepon={$telepon_peserta_jawaban} | target={$target}\n",
            FILE_APPEND
        );

        echo "<script>
            console.log('--- DEBUG PENDAFTARAN ---');
            console.log('is_free_event: " . ($is_free_event ? 'true' : 'false') . "');
            console.log('kode_unik: " . $kode_unik . "');
            console.log('nama_peserta: " . addslashes($nama_peserta_jawaban) . "');
            console.log('telepon_asli: " . addslashes($telepon_peserta_jawaban) . "');
            console.log('target_format: " . addslashes($target) . "');
        </script>";


        $fonnte_response = '';
        $curlError = '';

        if ($is_free_event) {
            $fonnte_token = 'eSJDYxaMoxjNvy8vTuDy';
            $event_judul = $workshop['judul'];

            $message = "🎫 *Konfirmasi Pendaftaran Event Gratis*\n\n";
            $message .= "Halo {$nama_peserta_jawaban},\n\n";
            $message .= "Pendaftaran kamu untuk event *{$event_judul}* telah *berhasil* ✅\n\n";
            $message .= "📌 Ticket ID: {$kode_unik}\n";
            $message .= "Harap simpan pesan ini dan tunjukkan QR Code kamu saat registrasi ulang.\n";
            $message .= "\nSalam hangat,\n*Ponpes Al Ihsan Baron*";

            if (!empty($target) && !empty($fonnte_token)) {
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://api.fonnte.com/send',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 15,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => array(
                        'target' => $target,
                        'message' => $message
                    ),
                    CURLOPT_HTTPHEADER => array("Authorization: {$fonnte_token}"),
                ));

                $fonnte_response = curl_exec($curl);
                $curlError = curl_error($curl);
                curl_close($curl);

                $logPath = __DIR__ . '/log_fonnte.txt';
                $logData = date('Y-m-d H:i:s') .
                    " | Target: {$target} | Error: {$curlError} | Response: {$fonnte_response}\n";
                file_put_contents($logPath, $logData, FILE_APPEND);

            } else {
                file_put_contents(
                    __DIR__ . '/log_fonnte.txt',
                    date('Y-m-d H:i:s') . " | Gagal kirim: token atau target kosong\n",
                    FILE_APPEND
                );
            }

            echo "<script>
                console.log('Fonnte response: " . addslashes($fonnte_response) . "');
                console.log('Fonnte error: " . addslashes($curlError) . "');
            </script>";
        }

        $pdo->commit();

        echo "<script>
            console.log('Pendaftaran sukses, redirect ke sukses_pendaftaran.php');
        </script>";

        header("Location: sukses_pendaftaran.php?kode=" . $kode_unik);
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $msg = "Pendaftaran gagal: " . $e->getMessage();
        file_put_contents(__DIR__ . '/error_log.txt', date('Y-m-d H:i:s') . " | {$msg}\n", FILE_APPEND);
        echo "<script>console.error('Error saat pendaftaran: " . addslashes($msg) . "');</script>";
        die($msg);
    }
}
?>

<div class="container mx-auto px-4 py-8 md:py-12 max-w-6xl">
    <!-- Breadcrumb -->
    <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-sm text-gray-600">
            <li><a href="index.php" class="hover:text-green-700 transition flex items-center">
                    <i class="fas fa-home mr-1"></i> Beranda
                </a></li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li><a href="index.php#events" class="hover:text-green-700 transition">Event</a></li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-green-700 font-medium"><?= htmlspecialchars($workshop['judul']) ?></li>
        </ol>
    </nav>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Main Content -->
        <div class="w-full lg:w-2/3">
            <!-- Updated hero gradient from blue to green theme -->
            <div class="bg-gradient-to-r from-green-700 to-green-900 rounded-2xl shadow-xl overflow-hidden mb-8 relative">
                <div class="absolute inset-0 bg-black/20"></div>
                <div class="relative z-10 p-8 text-white">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
                        <div class="mb-4 md:mb-0">
                            <span
                                class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-medium mb-4 inline-block">
                                <?= $workshop['tipe_event'] == 'berbayar' && $workshop['harga'] > 0 ? 'Berbayar' : 'Gratis' ?>
                            </span>
                            <h1 class="text-3xl md:text-4xl font-bold mb-2"><?= htmlspecialchars($workshop['judul']) ?>
                            </h1>
                            <p class="text-green-100 text-lg">
                                <?= date('l, d F Y', strtotime($workshop['tanggal_waktu'])) ?> •
                                <?= date('H:i', strtotime($workshop['tanggal_waktu'])) ?> WIB</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center">
                            <div class="text-2xl font-bold">
                                <?php
                                if ($workshop['tipe_event'] == 'berbayar' && $workshop['harga'] > 0) {
                                    echo 'Rp ' . number_format($workshop['harga'], 0, ',', '.');
                                } else {
                                    echo 'Gratis';
                                }
                                ?>
                            </div>
                            <div class="text-green-100 text-sm">Biaya Pendaftaran</div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($workshop['poster']): ?>
                    <div class="rounded-2xl shadow-xl overflow-hidden mb-8">
                        <img src="assets/img/posters/<?= htmlspecialchars($workshop['poster']) ?>"
                            alt="<?= htmlspecialchars($workshop['judul']) ?>" class="w-full h-auto object-cover">
                    </div>
            <?php else: ?>
                    <!-- Updated placeholder gradient to green theme -->
                    <div
                        class="w-full h-64 bg-gradient-to-r from-green-700 to-green-900 rounded-2xl shadow-xl mb-8 flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-white text-6xl"></i>
                    </div>
            <?php endif; ?>

            <!-- Updated card icon backgrounds to green theme -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 stagger-animation">
                <div class="bg-white p-6 rounded-xl shadow-md card-hover">
                    <div class="flex items-start">
                        <div class="bg-green-100 p-3 rounded-full mr-4">
                            <i class="fas fa-calendar-alt text-green-700 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-700 mb-1">Tanggal & Waktu</h3>
                            <p class="text-gray-900 font-medium">
                                <?= date('l, d F Y', strtotime($workshop['tanggal_waktu'])) ?>
                            </p>
                            <p class="text-gray-600"><?= date('H:i', strtotime($workshop['tanggal_waktu'])) ?> WIB</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-md card-hover">
                    <div class="flex items-start">
                        <div class="bg-green-100 p-3 rounded-full mr-4">
                            <i class="fas fa-map-marker-alt text-green-700 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-700 mb-1">Lokasi</h3>
                            <p class="text-gray-900 font-medium"><?= htmlspecialchars($workshop['lokasi']) ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-md card-hover flex items-center justify-between">
                    <div class="flex items-start">
                        <div class="bg-green-100 p-3 rounded-full mr-4">
                            <i class="fas fa-ticket-alt text-green-700 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-700 mb-1">Biaya Pendaftaran</h3>
                            <p class="text-gray-900 font-bold text-lg">
                                <?php
                                if ($workshop['tipe_event'] == 'berbayar' && $workshop['harga'] > 0) {
                                    echo 'Rp ' . number_format($workshop['harga'], 0, ',', '.');
                                } else {
                                    echo 'Gratis';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Updated section title icon colors to green -->
            <div class="bg-white p-8 rounded-2xl shadow-xl mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b border-gray-200 flex items-center">
                    <i class="fas fa-info-circle text-green-700 mr-3"></i>
                    Deskripsi Event
                </h2>
                <div class="prose max-w-none text-gray-700 text-lg leading-relaxed">
                    <p><?= nl2br(htmlspecialchars($workshop['deskripsi'])) ?></p>
                </div>
            </div>

            <!-- Updated info icons to yellow theme -->
            <div class="mt-6 bg-white rounded-2xl shadow-lg p-6 card-hover">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-exclamation-circle text-yellow-600 mr-2"></i>
                    Informasi Penting
                </h3>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-yellow-600 mt-1 mr-3"></i>
                        <span class="text-gray-700">Bawa tiket elektronik atau print out saat event</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-yellow-600 mt-1 mr-3"></i>
                        <span class="text-gray-700">Datang 15 menit sebelum acara dimulai</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-yellow-600 mt-1 mr-3"></i>
                        <span class="text-gray-700">Siapkan pertanyaan untuk sesi tanya jawab</span>
                    </li>
                </ul>
            </div>

            <!-- Updated contact card gradient and info to green/yellow theme and Ponpes contact -->
            <div class="mt-6 bg-gradient-to-br from-green-700 to-green-900 rounded-2xl shadow-lg p-6 text-white">
                <h3 class="text-lg font-bold mb-4 flex items-center">
                    <i class="fas fa-headset mr-2"></i>
                    Butuh Bantuan?
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <i class="fas fa-phone mr-3 text-yellow-400"></i>
                        <span>+62 812 3456 7890</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-envelope mr-3 text-yellow-400"></i>
                        <span>info@ponpesalihsanbaron.ac.id</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-clock mr-3 text-yellow-400"></i>
                        <span>Senin - Jumat, 08:00 - 16:00 WIB</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar - Form Pendaftaran -->
        <div class="w-full lg:w-1/3">
            <div class="sticky top-24 bg-white rounded-2xl shadow-xl overflow-hidden">
                <!-- Updated form header gradient to yellow/gold theme -->
                <div
                    class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white p-6 text-center relative overflow-hidden">
                    <div
                        class="absolute top-0 right-0 w-20 h-20 bg-white/10 rounded-full -translate-y-10 translate-x-10">
                    </div>
                    <div
                        class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full translate-y-8 -translate-x-8">
                    </div>
                    <h2 class="text-2xl font-bold mb-2 relative z-10">Daftar Sekarang!</h2>
                    <p class="text-yellow-50 relative z-10">Kuota terbatas untuk event ini</p>
                </div>

                <div class="p-6">
                    <?php if (isset($error)): ?>
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    <p class="font-medium"><?= $error ?></p>
                                </div>
                            </div>
                    <?php endif; ?>

                    <form action="proses_daftar.php?id=<?= $workshop_id ?>" method="POST">
                        <div class="mb-4">
                            <label for="nama" class="block text-gray-700 font-medium mb-2">Nama Lengkap <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="nama_peserta" id="nama" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                required>
                        </div>
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 font-medium mb-2">Email <span
                                    class="text-red-500">*</span></label>
                            <input type="email" name="email_peserta" id="email"
                                class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                        </div>
                        <div class="mb-4">
                            <label for="telepon" class="block text-gray-700 font-medium mb-2">No. Telepon/WhatsApp <span
                                    class="text-red-500">*</span></label>
                            <input type="tel" name="telepon_peserta" id="telepon"
                                class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">Jenis Kelamin</label>
                            <div class="mt-2 flex space-x-4">
                                <label class="flex items-center"><input type="radio" class="form-radio text-green-600" name="jenis_kelamin" value="Laki-laki">
                                    <span class="ml-2">Laki-laki</span></label>
                                <label class="flex items-center"><input type="radio" class="form-radio text-green-600" name="jenis_kelamin" value="Perempuan">
                                    <span class="ml-2">Perempuan</span></label>
                            </div>
                        </div>

                        <hr class="my-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">Informasi Tambahan</h3>

                        <?php foreach ($form_fields as $field): ?>
                                <div class="mb-4">
                                    <label for="field_<?= $field['id'] ?>" class="block text-gray-700 font-medium mb-2">
                                        <?= htmlspecialchars($field['label']) ?>
                                        <?= $field['is_required'] ? '<span class="text-red-500">*</span>' : '' ?>
                                    </label>

                                    <?php if ($field['field_type'] == 'select'): ?>
                                            <select name="jawaban[<?= $field['id'] ?>]" id="field_<?= $field['id'] ?>"
                                                class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" <?= $field['is_required'] ? 'required' : '' ?>>
                                                <option value="" disabled selected>Pilih...</option>
                                                <?php $options = explode(',', $field['options']); ?>
                                                <?php foreach ($options as $option): ?>
                                                        <option value="<?= trim($option) ?>"><?= trim($option) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                    <?php else: ?>
                                            <input type="text" name="jawaban[<?= $field['id'] ?>]" id="field_<?= $field['id'] ?>"
                                                class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" <?= $field['is_required'] ? 'required' : '' ?>>
                                    <?php endif; ?>
                                </div>
                        <?php endforeach; ?>

                        <!-- Updated button gradient to yellow/gold theme -->
                        <button type="submit" class="w-full bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-semibold py-4 px-6 rounded-lg transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center justify-center">
                            <i class="fas fa-ticket-alt mr-2"></i> Daftar Event
                        </button>
                    </form>

                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600">
                            Dengan mendaftar, Anda menyetujui
                            <a href="#" class="text-green-700 hover:underline font-medium">syarat dan ketentuan</a>
                            yang berlaku.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
