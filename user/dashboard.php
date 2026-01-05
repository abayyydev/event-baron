<?php
// Mulai sesi (Wajib ada di baris paling atas)
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Panggil koneksi database
// Sesuaikan path ini. Jika dashboard ada di folder 'user/', maka kita perlu mundur satu folder (../) untuk ke 'core/'
require_once __DIR__ . '/../core/koneksi.php';
require_once __DIR__ . '/templates/header.php';

// 2. LOGIC SIMPAN ULASAN (POST)
if (isset($_POST['kirim_ulasan'])) {
    // Pastikan session user_id ada sebelum diakses
    if (!isset($_SESSION['user_id'])) {
        echo "<script>window.location='../login.php';</script>";
        exit;
    }

    $u_workshop_id = $_POST['workshop_id'];
    $u_rating = (int) $_POST['rating'];
    $u_ulasan = trim($_POST['ulasan']);
    $u_user_id = $_SESSION['user_id'];

    if ($u_rating > 0) {
        try {
            // Cek sudah review belum
            $cek = $pdo->prepare("SELECT id FROM workshop_reviews WHERE workshop_id = ? AND user_id = ?");
            $cek->execute([$u_workshop_id, $u_user_id]);

            if ($cek->rowCount() > 0) {
                // Update
                $sql_rev = "UPDATE workshop_reviews SET rating = ?, ulasan = ?, created_at = NOW() WHERE workshop_id = ? AND user_id = ?";
                $stmt_rev = $pdo->prepare($sql_rev);
                $stmt_rev->execute([$u_rating, $u_ulasan, $u_workshop_id, $u_user_id]);
            } else {
                // Insert
                $sql_rev = "INSERT INTO workshop_reviews (workshop_id, user_id, rating, ulasan) VALUES (?, ?, ?, ?)";
                $stmt_rev = $pdo->prepare($sql_rev);
                $stmt_rev->execute([$u_workshop_id, $u_user_id, $u_rating, $u_ulasan]);
            }
            echo "<script>Swal.fire('Terima Kasih!', 'Ulasan Anda berhasil dikirim.', 'success');</script>";
        } catch (Exception $e) {
            echo "<script>Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');</script>";
        }
    }
}

// 3. AMBIL DATA
// Cek email session untuk menghindari error undefined index
$email_peserta = $_SESSION['email'] ?? '';

if ($email_peserta) {
    // Query Tiket Saya
    $sql_tiket = "SELECT p.*, w.judul, w.tanggal_waktu, w.lokasi, w.poster, w.id as workshop_id 
                  FROM pendaftaran p 
                  JOIN workshops w ON p.workshop_id = w.id 
                  WHERE p.email_peserta = :email 
                  ORDER BY p.created_at DESC";

    // Pastikan $pdo sudah terdefinisi dari header.php -> koneksi.php
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
<style>
    .rate {
        float: left;
        height: 46px;
        padding: 0 10px;
    }

    .rate:not(:checked)>input {
        position: absolute;
        top: -9999px;
    }

    .rate:not(:checked)>label {
        float: right;
        width: 1em;
        overflow: hidden;
        white-space: nowrap;
        cursor: pointer;
        font-size: 30px;
        color: #ccc;
    }

    .rate:not(:checked)>label:before {
        content: '★ ';
    }

    .rate>input:checked~label {
        color: #ffc700;
    }

    .rate:not(:checked)>label:hover,
    .rate:not(:checked)>label:hover~label {
        color: #deb217;
    }

    .rate>input:checked+label:hover,
    .rate>input:checked+label:hover~label,
    .rate>input:checked~label:hover,
    .rate>input:checked~label:hover~label,
    .rate>label:hover~input:checked~label {
        color: #c59b08;
    }
</style>

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
                <div
                    class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition flex flex-col h-full">
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
                                        <span
                                            class="bg-green-100 text-green-700 text-[10px] px-2 py-1 rounded-full font-bold">Terdaftar</span>
                                    <?php else: ?>
                                        <span
                                            class="bg-yellow-100 text-yellow-700 text-[10px] px-2 py-1 rounded-full font-bold">Pending</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="space-y-2 mt-2">
                                <div class="flex gap-2">
                                    <?php if ($is_lunas): ?>
                                        <a href="cetak_tiket.php?id=<?= $row['id'] ?>" target="_blank"
                                            class="flex-1 bg-primary text-white text-xs text-center py-1.5 rounded hover:bg-green-800 transition">
                                            <i class="fas fa-qrcode"></i> Tiket
                                        </a>
                                        <?php if ($row['status_kehadiran'] == 'hadir'): ?>
                                            <a href="download_sertifikat.php?id=<?= $row['id'] ?>"
                                                class="flex-1 bg-secondary text-white text-xs text-center py-1.5 rounded hover:bg-yellow-600 transition">
                                                <i class="fas fa-certificate"></i> Sertifikat
                                            </a>
                                        <?php endif; ?>
                                    <?php elseif ($row['payment_url']): ?>
                                        <a href="<?= $row['payment_url'] ?>" target="_blank"
                                            class="flex-1 bg-yellow-500 text-white text-xs text-center py-1.5 rounded hover:bg-yellow-600 transition">
                                            Bayar
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <?php if (strtotime($row['tanggal_waktu']) < time() || $row['status_kehadiran'] == 'hadir'): ?>
                                    <button
                                        onclick="bukaModalUlasan(<?= $row['workshop_id'] ?>, '<?= htmlspecialchars($row['judul']) ?>')"
                                        class="w-full text-center border border-gray-300 text-gray-600 hover:text-primary hover:border-primary text-xs py-1.5 rounded transition bg-gray-50 hover:bg-white flex items-center justify-center">
                                        <i class="far fa-comment-dots mr-1"></i> Beri Ulasan
                                    </button>
                                <?php endif; ?>
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
                <article
                    class="rounded-xl shadow-lg overflow-hidden bg-white hover:shadow-xl transition-shadow duration-300 border border-gray-100 flex flex-col h-full group">
                    <div class="h-48 bg-gray-200 relative overflow-hidden">
                        <?php if (!empty($agenda['poster'])): ?>
                            <img src="<?= BASE_URL ?>assets/img/posters/<?= htmlspecialchars($agenda['poster']) ?>"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-primary text-white">
                                <i class="fas fa-calendar-alt text-4xl"></i>
                            </div>
                        <?php endif; ?>

                        <div
                            class="absolute top-4 right-4 bg-secondary text-white text-xs font-bold py-1 px-3 rounded-full shadow-md">
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

<div id="modalUlasan" class="fixed inset-0 z-[999] hidden" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" onclick="tutupModalUlasan()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-primary px-4 py-4 sm:px-6 flex justify-between items-center">
                    <h3 class="text-lg font-bold leading-6 text-white" id="modal-judul-event">Review Event</h3>
                    <button onclick="tutupModalUlasan()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form action="" method="POST">
                    <div class="px-4 py-5 sm:p-6">
                        <input type="hidden" name="workshop_id" id="input_workshop_id">
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-2">Seberapa puas Anda dengan event ini?</p>
                            <div class="flex justify-center mb-4">
                                <div class="rate">
                                    <input type="radio" id="star5" name="rating" value="5" required /><label
                                        for="star5">5 stars</label>
                                    <input type="radio" id="star4" name="rating" value="4" /><label for="star4">4
                                        stars</label>
                                    <input type="radio" id="star3" name="rating" value="3" /><label for="star3">3
                                        stars</label>
                                    <input type="radio" id="star2" name="rating" value="2" /><label for="star2">2
                                        stars</label>
                                    <input type="radio" id="star1" name="rating" value="1" /><label for="star1">1
                                        star</label>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label for="ulasan"
                                    class="block text-sm font-medium text-gray-700 text-left mb-1">Masukan Anda
                                    (Opsional)</label>
                                <textarea name="ulasan" rows="3"
                                    class="w-full rounded-md border border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm p-2 bg-gray-50"
                                    placeholder="Tulis kritik & saran..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit" name="kirim_ulasan"
                            class="inline-flex w-full justify-center rounded-md border border-transparent bg-secondary px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-yellow-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Kirim
                            Ulasan</button>
                        <button type="button" onclick="tutupModalUlasan()"
                            class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function bukaModalUlasan(id, judul) {
        document.getElementById('input_workshop_id').value = id;
        document.getElementById('modal-judul-event').innerText = judul;
        document.getElementById('modalUlasan').classList.remove('hidden');
    }
    function tutupModalUlasan() {
        document.getElementById('modalUlasan').classList.add('hidden');
    }
</script>

<?php require_once 'templates/footer.php'; ?>