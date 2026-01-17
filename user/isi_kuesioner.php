<?php
session_start();
require_once __DIR__ . '/../core/koneksi.php';

$page_title = "Isi Kuesioner";
$current_page = "dashboard";

// Gunakan template header agar style konsisten
require_once 'templates/header.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    echo "<script>window.location='dashboard.php';</script>";
    exit;
}

$workshop_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// 1. Cek Event
$stmt = $pdo->prepare("SELECT * FROM workshops WHERE id = ?");
$stmt->execute([$workshop_id]);
$event = $stmt->fetch();
if (!$event) {
    echo "<div class='text-center py-20 text-gray-500'>Event tidak ditemukan.</div>";
    require_once 'templates/footer.php';
    exit;
}

// 2. Cek Apakah Sudah Pernah Mengisi? (Mencegah Double Submit)
$stmt_check = $pdo->prepare("SELECT COUNT(*) FROM workshop_answers WHERE workshop_id = ? AND user_id = ?");
$stmt_check->execute([$workshop_id, $user_id]);
if ($stmt_check->fetchColumn() > 0) {
    // FIX: Load library SweetAlert sebelum script dijalankan
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Sudah Diisi',
                text: 'Anda sudah mengisi kuesioner untuk event ini.',
                icon: 'info',
                confirmButtonColor: '#10b981'
            }).then(() => {
                window.location = 'dashboard.php';
            });
        });
    </script>";
    exit;
}

// 3. Ambil Pertanyaan
$stmt_q = $pdo->prepare("SELECT * FROM workshop_questions WHERE workshop_id = ? ORDER BY id ASC");
$stmt_q->execute([$workshop_id]);
$questions = $stmt_q->fetchAll();

// 4. PROSES SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        $answers = $_POST['answer'] ?? [];

        foreach ($answers as $q_id => $ans_text) {
            // Jika array (multiselect), gabungkan string
            if (is_array($ans_text))
                $ans_text = implode(', ', $ans_text);

            $sql = "INSERT INTO workshop_answers (workshop_id, user_id, question_id, answer_text) VALUES (?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$workshop_id, $user_id, $q_id, $ans_text]);
        }

        $pdo->commit();
        // FIX: Load library SweetAlert sebelum script dijalankan
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Terima Kasih!',
                    text: 'Jawaban Anda telah tersimpan. Sertifikat kini dapat diunduh.',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    window.location='dashboard.php';
                });
            });
        </script>";
    } catch (Exception $e) {
        $pdo->rollBack();
        // FIX: Load library SweetAlert untuk error handling juga
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire('Error', 'Gagal menyimpan jawaban: " . addslashes($e->getMessage()) . "', 'error');
            });
        </script>";
    }
}
?>

<style>
    /* Custom Rating Star Style */
    .rating-group {
        display: inline-flex;
        flex-direction: row-reverse;
        gap: 0.5rem;
    }

    .rating-group input {
        display: none;
    }

    .rating-group label {
        font-size: 2.5rem;
        color: #e2e8f0;
        /* Slate-200 */
        cursor: pointer;
        transition: color 0.2s, transform 0.1s;
    }

    /* Hover & Checked Effects */
    .rating-group input:checked~label,
    .rating-group label:hover,
    .rating-group label:hover~label {
        color: #fbbf24;
        /* Amber-400 */
        filter: drop-shadow(0 0 2px rgba(251, 191, 36, 0.5));
    }

    .rating-group label:active {
        transform: scale(0.9);
    }
</style>

<div class="min-h-screen bg-gray-50 font-sans pb-20">

    <!-- Hero Section -->
    <div class="bg-emerald-900 pb-24 pt-10 px-4 rounded-b-[3rem] shadow-xl relative overflow-hidden">
        <!-- Dekorasi Background -->
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-emerald-800 rounded-full opacity-50 blur-3xl">
        </div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-amber-500 rounded-full opacity-20 blur-2xl">
        </div>

        <div class="max-w-4xl mx-auto px-6 relative z-10 text-center">
            <span
                class="text-emerald-200 text-xs font-bold uppercase tracking-widest border border-emerald-700/50 px-3 py-1 rounded-full bg-emerald-900/50 backdrop-blur">
                Feedback Event
            </span>
            <h1 class="text-2xl md:text-4xl font-extrabold text-white mt-4 leading-tight">
                <?= htmlspecialchars($event['judul']) ?>
            </h1>
            <p class="text-emerald-100/90 mt-2 text-sm md:text-base max-w-xl mx-auto">
                Masukan Anda sangat berharga bagi kami. Mohon isi kuesioner ini untuk meningkatkan kualitas event
                mendatang dan <b>membuka akses sertifikat</b>.
            </p>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="max-w-3xl mx-auto px-4 -mt-16 relative z-20">

        <form method="POST" class="space-y-6">

            <?php if (count($questions) > 0): ?>
                <?php foreach ($questions as $index => $q): ?>
                    <div
                        class="bg-white rounded-3xl shadow-lg border border-gray-100 p-6 md:p-8 relative group hover:shadow-xl transition-all duration-300">
                        <!-- Number Badge -->
                        <div
                            class="absolute -left-3 -top-3 w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 text-white flex items-center justify-center font-bold shadow-lg border-4 border-gray-50 z-10">
                            <?= $index + 1 ?>
                        </div>

                        <label class="block text-lg font-bold text-gray-800 mb-4 ml-2">
                            <?= htmlspecialchars($q['question_text']) ?>
                            <span class="text-red-500 text-sm align-top">*</span>
                        </label>

                        <!-- Tipe: TEXT -->
                        <?php if ($q['question_type'] === 'text'): ?>
                            <input type="text" name="answer[<?= $q['id'] ?>]" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all outline-none"
                                placeholder="Tulis jawaban singkat Anda...">

                            <!-- Tipe: TEXTAREA -->
                        <?php elseif ($q['question_type'] === 'textarea'): ?>
                            <textarea name="answer[<?= $q['id'] ?>]" rows="4" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all outline-none resize-none"
                                placeholder="Ceritakan pendapat Anda secara detail..."></textarea>

                            <!-- Tipe: RATING -->
                        <?php elseif ($q['question_type'] === 'rating'): ?>
                            <div
                                class="flex flex-col items-center justify-center py-4 bg-gray-50/50 rounded-2xl border border-dashed border-gray-200">
                                <div class="rating-group">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="q<?= $q['id'] ?>-s<?= $i ?>" name="answer[<?= $q['id'] ?>]"
                                            value="<?= $i ?>" required>
                                        <label for="q<?= $q['id'] ?>-s<?= $i ?>" title="<?= $i ?> Bintang">
                                            <i class="fas fa-star"></i>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                                <div
                                    class="flex justify-between w-full max-w-xs px-4 mt-2 text-xs text-gray-400 font-medium uppercase tracking-wide">
                                    <span>Buruk</span>
                                    <span>Sempurna</span>
                                </div>
                            </div>

                            <!-- Tipe: RADIO -->
                        <?php elseif ($q['question_type'] === 'radio'):
                            $opts = explode(',', $q['options']); ?>
                            <div class="space-y-3">
                                <?php foreach ($opts as $idx => $opt):
                                    $opt = trim($opt); ?>
                                    <label
                                        class="flex items-center p-4 border border-gray-200 rounded-xl cursor-pointer hover:bg-emerald-50/30 hover:border-emerald-200 transition-all group/opt">
                                        <div class="relative flex items-center">
                                            <input type="radio" name="answer[<?= $q['id'] ?>]" value="<?= htmlspecialchars($opt) ?>"
                                                required class="peer sr-only">
                                            <div
                                                class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-emerald-500 peer-checked:bg-emerald-500 transition-all relative">
                                                <div
                                                    class="absolute inset-0 m-auto w-2 h-2 rounded-full bg-white transform scale-0 peer-checked:scale-100 transition-transform">
                                                </div>
                                            </div>
                                        </div>
                                        <span
                                            class="ml-3 text-gray-600 font-medium group-hover/opt:text-emerald-700 transition-colors"><?= htmlspecialchars($opt) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <!-- Tipe: DROPDOWN -->
                        <?php elseif ($q['question_type'] === 'dropdown'):
                            $opts = explode(',', $q['options']); ?>
                            <div class="relative">
                                <select name="answer[<?= $q['id'] ?>]" required
                                    class="w-full px-4 py-3 pr-10 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all outline-none appearance-none cursor-pointer text-gray-700">
                                    <option value="" disabled selected>-- Pilih salah satu --</option>
                                    <?php foreach ($opts as $opt): ?>
                                        <option value="<?= htmlspecialchars(trim($opt)) ?>"><?= htmlspecialchars(trim($opt)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-emerald-500">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>

                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4">
                    <a href="dashboard.php"
                        class="w-full sm:w-auto px-6 py-3 rounded-xl border border-gray-300 text-gray-600 font-bold hover:bg-gray-50 transition text-center">
                        Batal
                    </a>
                    <button type="submit"
                        class="w-full sm:w-auto px-10 py-3.5 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white font-bold shadow-lg shadow-emerald-500/30 transform hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i> Kirim Jawaban
                    </button>
                </div>

            <?php else: ?>
                <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-clipboard-check text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Kuesioner Belum Tersedia</h3>
                    <p class="text-gray-500 mt-2 mb-6">Penyelenggara belum membuat pertanyaan untuk event ini.</p>
                    <a href="dashboard.php"
                        class="inline-block px-6 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition">
                        Kembali ke Dashboard
                    </a>
                </div>
            <?php endif; ?>

        </form>
    </div>

    <div class="text-center mt-12 text-gray-400 text-xs pb-4">
        &copy; <?= date('Y') ?> Pondok Pesantren Al Ihsan Baron - All Rights Reserved.
    </div>

</div>

<!-- SweetAlert2 (Sudah include di header biasanya, tapi jaga-jaga) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php require_once 'templates/footer.php'; ?>