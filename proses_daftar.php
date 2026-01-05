<?php
// ========================================================================
// DEBUG MODE (BISA DIMATIKAN SETELAH STABIL)
// ========================================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ========================================================================
// 1. KONEKSI DATABASE
// ========================================================================
require_once 'core/koneksi.php';

// ========================================================================
// 2. VALIDASI REQUEST
// ========================================================================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: ID Event tidak ditemukan.");
}

$workshop_id = (int) $_GET['id'];

// Ambil data statis dari form
$nama_peserta = trim($_POST['nama_peserta'] ?? '');
$email_peserta = trim($_POST['email_peserta'] ?? '');
$jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
$telepon_peserta = $_POST['telepon_peserta'] ?? '';
$jawaban_form = $_POST['jawaban'] ?? [];

// Validasi data wajib
if (empty($nama_peserta) || empty($email_peserta)) {
    die("Error: Nama Lengkap dan Email wajib diisi.");
}

// ========================================================================
// 3. AMBIL DATA WORKSHOP
// ========================================================================
try {
    $stmt_workshop = $pdo->prepare("SELECT tipe_event, harga, judul, tanggal_waktu FROM workshops WHERE id = ?");
    $stmt_workshop->execute([$workshop_id]);
    $workshop = $stmt_workshop->fetch(PDO::FETCH_ASSOC);

    if (!$workshop) {
        die("Error: Event dengan ID {$workshop_id} tidak ditemukan.");
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// ========================================================================
// 4. SIMPAN PENDAFTARAN KE DATABASE
// ========================================================================
$pdo->beginTransaction();

try {
    $kode_unik = "WS-" . $workshop_id . "-" . strtoupper(bin2hex(random_bytes(4)));
    $status_pembayaran = ($workshop['tipe_event'] == 'gratis') ? 'free' : 'pending';

    // Simpan data pendaftaran utama
    $stmt = $pdo->prepare("
        INSERT INTO pendaftaran 
        (workshop_id, nama_peserta, jenis_kelamin, email_peserta, telepon_peserta, kode_unik, status_pembayaran) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$workshop_id, $nama_peserta, $jenis_kelamin, $email_peserta, $telepon_peserta, $kode_unik, $status_pembayaran]);
    $pendaftaran_id = $pdo->lastInsertId();

    // Simpan data tambahan (jawaban dinamis)
    if (!empty($jawaban_form)) {
        $stmt_jawaban = $pdo->prepare("INSERT INTO pendaftaran_data (pendaftaran_id, field_id, value) VALUES (:pendaftaran_id, :field_id, :value)");
        foreach ($jawaban_form as $field_id => $value) {
            $stmt_jawaban->execute([
                ':pendaftaran_id' => $pendaftaran_id,
                ':field_id' => (int) $field_id,
                ':value' => htmlspecialchars(strip_tags($value))
            ]);
        }
    }

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    die("Database Error: Gagal menyimpan data pendaftaran. " . $e->getMessage());
}

// ========================================================================
// 5. UPDATE LAST ACCESSED
// ========================================================================
try {
    $stmt_log = $pdo->prepare("UPDATE pendaftaran SET last_accessed_at = NOW() WHERE id = ?");
    $stmt_log->execute([$pendaftaran_id]);
} catch (PDOException $e) {
    error_log("Gagal update last_accessed_at: " . $e->getMessage());
}

// ========================================================================
// 6. EVENT GRATIS → LANGSUNG SUKSES
// ========================================================================
if ($status_pembayaran == 'free') {
    header("Location: sukses_pendaftaran.php?kode=" . $kode_unik);
    exit();
}

// ========================================================================
// 7. EVENT BERBAYAR → PROSES KE DUITKU
// ========================================================================
$merchantCode = 'D20354';
$apiKey = 'cc7e768f19d886126b3ef8b1babe81b8';
$duitku_endpoint = 'https://passport.duitku.com/webapi/api/merchant/v2/inquiry';

$paymentAmount = (int) $workshop['harga'];
$merchantOrderId = $kode_unik;
$productDetails = 'Tiket untuk event: ' . $workshop['judul'];
$returnUrl = 'https://ukmelrahma.my.id/sukses_pendaftaran.php?kode=' . $merchantOrderId;
$callbackUrl = 'https://ukmelrahma.my.id/callback.php';
$signature = md5($merchantCode . $merchantOrderId . $paymentAmount . $apiKey);
$expiryPeriod = 60; // menit

$params = [
    'merchantCode' => $merchantCode,
    'paymentAmount' => $paymentAmount,
    'merchantOrderId' => $merchantOrderId,
    'productDetails' => $productDetails,
    'email' => $email_peserta,
    'customerVaName' => $nama_peserta,
    'callbackUrl' => $callbackUrl,
    'returnUrl' => $returnUrl,
    'signature' => $signature,
    'paymentMethod' => 'SQ', // Non-Qris? Bisa ganti ke QRIS / VC / BT
    'expiryPeriod' => $expiryPeriod
];

// ========================================================================
// 8. KIRIM REQUEST KE DUITKU
// ========================================================================
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $duitku_endpoint);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ========================================================================
// 9. TANGGAPI HASIL
// ========================================================================
if ($http_status == 200) {
    $result = json_decode($response, true);
    if (isset($result['paymentUrl'])) {
        // Simpan link pembayaran
        $stmt_update = $pdo->prepare("
            UPDATE pendaftaran 
            SET payment_url = ?, payment_expiry = DATE_ADD(NOW(), INTERVAL ? MINUTE)
            WHERE id = ?
        ");
        $stmt_update->execute([$result['paymentUrl'], $expiryPeriod, $pendaftaran_id]);

        header('Location: ' . $result['paymentUrl']);
        exit();
    } else {
        echo "❌ Error: Duitku tidak mengembalikan paymentUrl.<br>";
        var_dump($result);
    }
} else {
    echo "❌ Gagal terhubung ke Duitku (Status: $http_status)<br>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    exit();
}
?>