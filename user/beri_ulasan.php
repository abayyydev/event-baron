<?php
// File: user/beri_ulasan.php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once '../core/koneksi.php';

// 1. CEK LOGIN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'peserta') {
    header("Location: " . BASE_URL . "login1");
    exit;
}

$user_id = $_SESSION['user_id'];
$workshop_id = $_GET['id'] ?? 0;

// 2. VALIDASI AKSES
// Cek apakah user benar-benar terdaftar di workshop ini
$stmt_cek = $pdo->prepare("SELECT id FROM pendaftaran WHERE workshop_id = ? AND email_peserta = ?");
$stmt_cek->execute([$workshop_id, $_SESSION['email']]);
if ($stmt_cek->rowCount() == 0) {
    die("Anda tidak terdaftar di event ini.");
}

// 3. PROSES SIMPAN ULASAN
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = (int) $_POST['rating'];
    $ulasan = trim($_POST['ulasan']);

    if ($rating < 1 || $rating > 5) {
        $message = "Mohon pilih bintang 1 sampai 5.";
    } else {
        try {
            // Cek apakah sudah pernah review (Insert or Update)
            $cek_review = $pdo->prepare("SELECT id FROM workshop_reviews WHERE workshop_id = ? AND user_id = ?");
            $cek_review->execute([$workshop_id, $user_id]);

            if ($cek_review->rowCount() > 0) {
                // Update
                $sql = "UPDATE workshop_reviews SET rating = ?, ulasan = ?, created_at = NOW() WHERE workshop_id = ? AND user_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$rating, $ulasan, $workshop_id, $user_id]);
            } else {
                // Insert Baru
                $sql = "INSERT INTO workshop_reviews (workshop_id, user_id, rating, ulasan) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$workshop_id, $user_id, $rating, $ulasan]);
            }

            // Redirect balik ke dashboard dengan pesan sukses
            echo "<script>alert('Terima kasih atas ulasan Anda!'); window.location='dashboard.php';</script>";
            exit;

        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// 4. AMBIL DATA WORKSHOP & ULASAN LAMA (JIKA ADA)
$stmt_ws = $pdo->prepare("SELECT judul, poster FROM workshops WHERE id = ?");
$stmt_ws->execute([$workshop_id]);
$ws = $stmt_ws->fetch();

$stmt_old = $pdo->prepare("SELECT * FROM workshop_reviews WHERE workshop_id = ? AND user_id = ?");
$stmt_old->execute([$workshop_id, $user_id]);
$review_lama = $stmt_old->fetch();

// Default values
$rating_val = $review_lama['rating'] ?? 0;
$ulasan_val = $review_lama['ulasan'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beri Ulasan - <?= htmlspecialchars($ws['judul']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Styling Bintang Rating */
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
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">

        <div class="bg-[#166534] p-6 text-white text-center relative">
            <a href="dashboard.php" class="absolute left-4 top-4 text-white hover:text-gray-200"><i
                    class="fas fa-times text-xl"></i></a>
            <h2 class="text-xl font-bold">Bagaimana Eventnya?</h2>
            <p class="text-sm text-green-100 opacity-90 mt-1">Masukan Anda sangat berarti bagi kami.</p>
        </div>

        <div class="p-8">
            <div class="flex items-center gap-4 mb-6">
                <?php if ($ws['poster']): ?>
                    <img src="<?= BASE_URL ?>assets/img/posters/<?= htmlspecialchars($ws['poster']) ?>"
                        class="w-16 h-16 rounded-lg object-cover shadow-sm">
                <?php else: ?>
                    <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center text-gray-400"><i
                            class="fas fa-image"></i></div>
                <?php endif; ?>
                <div>
                    <h3 class="font-bold text-gray-800 line-clamp-2"><?= htmlspecialchars($ws['judul']) ?></h3>
                    <span class="text-xs text-gray-500">Ponpes Al Ihsan Baron</span>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm"><?= $message ?></div>
            <?php endif; ?>

            <form action="" method="POST">

                <div class="mb-6 text-center">
                    <p class="text-gray-600 text-sm mb-2 font-semibold">Berikan Rating</p>
                    <div class="flex justify-center">
                        <div class="rate">
                            <input type="radio" id="star5" name="rating" value="5" <?= $rating_val == 5 ? 'checked' : '' ?>
                                required />
                            <label for="star5" title="Sempurna">5 stars</label>

                            <input type="radio" id="star4" name="rating" value="4" <?= $rating_val == 4 ? 'checked' : '' ?> />
                            <label for="star4" title="Bagus">4 stars</label>

                            <input type="radio" id="star3" name="rating" value="3" <?= $rating_val == 3 ? 'checked' : '' ?> />
                            <label for="star3" title="Cukup">3 stars</label>

                            <input type="radio" id="star2" name="rating" value="2" <?= $rating_val == 2 ? 'checked' : '' ?> />
                            <label for="star2" title="Kurang">2 stars</label>

                            <input type="radio" id="star1" name="rating" value="1" <?= $rating_val == 1 ? 'checked' : '' ?> />
                            <label for="star1" title="Buruk">1 star</label>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Ceritakan pengalamanmu
                        (Opsional)</label>
                    <textarea name="ulasan" rows="4"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#D4AF37] focus:border-transparent transition resize-none bg-gray-50"
                        placeholder="Apa yang paling berkesan? Apa yang perlu ditingkatkan?"><?= htmlspecialchars($ulasan_val) ?></textarea>
                </div>

                <div class="flex gap-3">
                    <a href="dashboard.php"
                        class="flex-1 py-3 px-4 border border-gray-300 rounded-xl text-center text-gray-600 font-semibold hover:bg-gray-50 transition">
                        Nanti Saja
                    </a>
                    <button type="submit"
                        class="flex-1 py-3 px-4 bg-[#D4AF37] hover:bg-[#b89528] text-white rounded-xl font-bold shadow-lg transition transform hover:-translate-y-0.5">
                        Kirim Ulasan
                    </button>
                </div>

            </form>
        </div>
    </div>

</body>

</html>