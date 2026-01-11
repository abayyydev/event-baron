<?php
// Mulai sesi (Wajib ada di baris paling atas)
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Panggil koneksi database
require_once __DIR__ . '/../core/koneksi.php';
require_once __DIR__ . '/templates/header.php';

// (Bagian Logic Simpan Ulasan LAMA DIHAPUS karena diganti sistem Kuesioner Baru)

// 3. AMBIL DATA
$email_peserta = $_SESSION['email'] ?? '';
$user_id = $_SESSION['user_id']; // Ambil ID user untuk cek kuesioner

if ($email_peserta) {
    // Query Tiket Saya
    $sql_tiket = "SELECT p.*, w.judul, w.tanggal_waktu, w.lokasi, w.poster, w.id as workshop_id 
                  FROM pendaftaran p 
                  JOIN workshops w ON p.workshop_id = w.id 
                  WHERE p.email_peserta = :email 
                  ORDER BY p.created_at DESC";

    if (isset($pdo)) {
        $stmt = $pdo->prepare($sql_tiket);
        $stmt->execute(['email' => $email_peserta]);
        $tiket_saya = $stmt->fetchAll();

        // Query Agenda Terbaru
        $sql_agenda = "SELECT * FROM workshops WHERE tanggal_waktu >= NOW() ORDER BY tanggal_waktu ASC LIMIT 6";
        $stmt_agenda = $pdo->query($sql_agenda);
        $agendas = $stmt_agenda->fetchAll();
    } else {
        die("Koneksi database gagal dimuat.");
    }
} else {
    $tiket_saya = [];
    $agendas = [];
}
?>

<div class="mb-10">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-l-4 border-secondary pl-3">
        <i class="fas fa-ticket-alt text-primary mr-2"></i> Tiket Saya
    </h2>

    <?php if (count($tiket_saya) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($tiket_saya as $row): ?>
                        <?php
                        $is_lunas = ($row['status_pembayaran'] == 'paid' || $row['status_pembayaran'] == 'free');
                        $poster_src = !empty($row['poster']) ? BASE_URL . 'assets/img/posters/' . $row['poster'] : 'https://via.placeholder.com/400x200?text=No+Poster';
                        ?>
                        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition flex flex-col h-full">
                            <div class="flex h-full">
                                <div class="w-1/3 bg-gray-200 relative">
                                    <img src="<?= $poster_src ?>" class="absolute inset-0 w-full h-full object-cover" alt="Poster">
                                </div>
                        
                                <div class="w-2/3 p-4 flex flex-col justify-between">
                                    <div>
                                        <h3 class="font-bold text-gray-800 text-sm mb-1 line-clamp-2">
                                            <?= htmlspecialchars($row['judul']) ?>
                                        </h3>
                                        <p class="text-xs text-gray-500 mb-2">
                                            <i class="far fa-calendar-alt mr-1"></i>
                                            <?= date('d M Y', strtotime($row['tanggal_waktu'])) ?>
                                        </p>
                                        <div class="mb-2">
                                            <?php if ($is_lunas): ?>
                                                    <span class="bg-green-100 text-green-700 text-[10px] px-2 py-1 rounded-full font-bold">Terdaftar</span>
                                            <?php else: ?>
                                                    <span class="bg-yellow-100 text-yellow-700 text-[10px] px-2 py-1 rounded-full font-bold">Pending</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="space-y-2 mt-2">
                                        <div class="flex flex-col gap-2">
                                            <?php if ($is_lunas): ?>
                                                    <a href="cetak_tiket.php?id=<?= $row['id'] ?>" target="_blank"
                                                        class="w-full bg-primary text-white text-xs text-center py-2 rounded hover:bg-green-800 transition">
                                                        <i class="fas fa-qrcode"></i> Tiket
                                                    </a>

                                                    <?php
                                                    // 2. LOGIKA KUESIONER vs SERTIFIKAT
                                                    if ($row['status_kehadiran'] == 'hadir') {

                                                        // A. Cek Status Kuesioner di Database
                                                        $stmt_cek_k = $pdo->prepare("SELECT is_kuesioner_active FROM workshops WHERE id = ?");
                                                        $stmt_cek_k->execute([$row['workshop_id']]);
                                                        $kuesioner_active = $stmt_cek_k->fetchColumn();

                                                        // B. Cek Apakah User Sudah Mengisi
                                                        $stmt_cek_ans = $pdo->prepare("SELECT COUNT(*) FROM workshop_answers WHERE workshop_id = ? AND user_id = ?");
                                                        $stmt_cek_ans->execute([$row['workshop_id'], $user_id]);
                                                        $sudah_isi = $stmt_cek_ans->fetchColumn() > 0;

                                                        // C. Tentukan Tombol Mana yang Muncul
                                                        if ($kuesioner_active == 1 && !$sudah_isi) {
                                                            // Kasus: Kuesioner Aktif & Belum Isi -> Wajib Isi Dulu
                                                            ?>
                                                                    <a href="isi_kuesioner.php?id=<?= $row['workshop_id'] ?>"
                                                                        class="w-full bg-purple-600 text-white text-xs text-center py-2 rounded hover:bg-purple-700 transition animate-pulse font-bold">
                                                                        <i class="fas fa-poll-h mr-1"></i> Isi Kuesioner (Wajib)
                                                                    </a>
                                                                    <?php
                                                        } else {
                                                            // Kasus: Kuesioner Tidak Aktif ATAU Sudah Isi -> Boleh Download
                                                            ?>
                                                                    <a href="download_sertifikat.php?id=<?= $row['id'] ?>"
                                                                        class="w-full bg-secondary text-white text-xs text-center py-2 rounded hover:bg-yellow-600 transition">
                                                                        <i class="fas fa-certificate mr-1"></i> Download Sertifikat
                                                                    </a>
                                                                    <?php
                                                        }

                                                    } else {
                                                        // Kasus: Belum Hadir
                                                        echo '<span class="text-[10px] text-gray-400 text-center italic border border-gray-200 rounded py-1 bg-gray-50">Hadir dulu untuk sertifikat</span>';
                                                    }
                                                    ?>

                                            <?php elseif ($row['payment_url']): ?>
                                                    <a href="<?= $row['payment_url'] ?>" target="_blank"
                                                        class="w-full bg-yellow-500 text-white text-xs text-center py-2 rounded hover:bg-yellow-600 transition">
                                                        Bayar Sekarang
                                                    </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                <?php endforeach; ?>
            </div>
    <?php else: ?>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded text-blue-700 text-sm">
                <p>Anda belum terdaftar di event manapun. Silakan lihat agenda terbaru di bawah.</p>
            </div>
    <?php endif; ?>
</div>

<hr class="my-10 border-gray-200">

<div>
    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-l-4 border-primary pl-3">
        <i class="fas fa-calendar-check text-secondary mr-2"></i> Agenda & Event Terbaru
    </h2>

    <?php if (!empty($agendas)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($agendas as $agenda): ?>
                        <article class="rounded-xl shadow-lg overflow-hidden bg-white hover:shadow-xl transition-shadow duration-300 border border-gray-100 flex flex-col h-full group">
                            <div class="h-48 bg-gray-200 relative overflow-hidden">
                                <?php if (!empty($agenda['poster'])): ?>
                                        <img src="<?= BASE_URL ?>assets/img/posters/<?= htmlspecialchars($agenda['poster']) ?>"
                                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center bg-primary text-white">
                                            <i class="fas fa-calendar-alt text-4xl"></i>
                                        </div>
                                <?php endif; ?>

                                <div class="absolute top-4 right-4 bg-secondary text-white text-xs font-bold py-1 px-3 rounded-full shadow-md">
                                    <?= ($agenda['tipe_event'] == 'berbayar') ? 'Rp ' . number_format($agenda['harga'], 0, ',', '.') : 'Gratis' ?>
                                </div>
                            </div>

                            <div class="p-6 flex flex-col flex-grow">
                                <h3 class="text-lg font-bold text-gray-800 mb-2 line-clamp-2 min-h-[3.5rem]">
                                    <?= htmlspecialchars($agenda['judul']) ?>
                                </h3>

                                <div class="flex items-center text-gray-500 text-sm mb-3">
                                    <i class="far fa-clock mr-2 text-secondary"></i>
                                    <?= date('d F Y • H:i', strtotime($agenda['tanggal_waktu'])) ?> WIB
                                </div>

                                <p class="text-gray-600 mb-4 line-clamp-3 text-sm flex-grow">
                                    <?= htmlspecialchars(substr($agenda['deskripsi'], 0, 100)) ?>...
                                </p>

                                <a href="<?= BASE_URL ?>user/daftar_event.php?id=<?= $agenda['id'] ?>"
                                    class="block w-full text-center bg-primary hover:bg-green-800 text-white font-semibold py-2.5 px-4 rounded-lg transition-colors duration-300 shadow-md mt-auto">
                                    <i class="fas fa-user-plus mr-2"></i> Daftar Event
                                </a>
                            </div>
                        </article>
                <?php endforeach; ?>
            </div>
    <?php else: ?>
            <div class="text-center py-10 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                <i class="fas fa-calendar-times text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-500">Belum ada agenda terdekat.</p>
            </div>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer.php'; ?>