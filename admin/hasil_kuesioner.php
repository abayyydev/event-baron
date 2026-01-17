<?php
// Pastikan BASE_PATH terdefinisi
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/admin/templates/header.php';
require_once BASE_PATH . '/core/koneksi.php';

$id_workshop = $_GET['id'] ?? 0;
$penyelenggara_id = $_SESSION['penyelenggara_id_bersama'];

// 1. Validasi Event & Kepemilikan
$stmt = $pdo->prepare("SELECT * FROM workshops WHERE id = ? AND penyelenggara_id = ?");
$stmt->execute([$id_workshop, $penyelenggara_id]);
$event = $stmt->fetch();

if (!$event) {
    echo "
    <div class='min-h-screen flex items-center justify-center bg-gray-50 font-sans'>
        <div class='text-center p-10 bg-white rounded-3xl shadow-xl border border-gray-100 max-w-md mx-4'>
            <div class='w-20 h-20 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl'>
                <i class='fas fa-lock'></i>
            </div>
            <h2 class='text-2xl font-bold text-gray-800 mb-2'>Akses Ditolak</h2>
            <p class='text-gray-500 mb-8'>Event tidak ditemukan atau Anda tidak memiliki izin untuk melihat hasilnya.</p>
            <a href='kelola_event.php' class='inline-flex items-center justify-center px-6 py-3 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-200 font-bold'>
                <i class='fas fa-arrow-left mr-2'></i> Kembali ke Dashboard
            </a>
        </div>
    </div>";
    require_once BASE_PATH . '/admin/templates/footer.php';
    exit;
}

// 2. Ambil Daftar Pertanyaan (Untuk Header Tabel)
$stmt_q = $pdo->prepare("SELECT * FROM workshop_questions WHERE workshop_id = ? ORDER BY id ASC");
$stmt_q->execute([$id_workshop]);
$questions = $stmt_q->fetchAll(PDO::FETCH_ASSOC);

// 3. Ambil Semua Jawaban + Data User (Untuk Isi Tabel)
$sql_ans = "SELECT a.*, u.nama_lengkap, u.email 
            FROM workshop_answers a 
            JOIN users u ON a.user_id = u.id 
            WHERE a.workshop_id = ? 
            ORDER BY a.created_at DESC";
$stmt_a = $pdo->prepare($sql_ans);
$stmt_a->execute([$id_workshop]);
$raw_answers = $stmt_a->fetchAll(PDO::FETCH_ASSOC);

// 4. Grouping Data per User (Pivot Manual)
$respondents = [];
foreach ($raw_answers as $row) {
    $uid = $row['user_id'];
    if (!isset($respondents[$uid])) {
        $respondents[$uid] = [
            'nama' => $row['nama_lengkap'],
            'email' => $row['email'],
            'waktu' => $row['created_at'],
            'answers' => [] // Array untuk menyimpan jawaban per pertanyaan
        ];
    }
    // Key-nya adalah ID Pertanyaan, Value-nya adalah Jawaban
    $respondents[$uid]['answers'][$row['question_id']] = $row['answer_text'];
}
?>

<div class="min-h-screen bg-gray-50 font-sans pb-32">
    
    <!-- Hero Header Section -->
    <div class="bg-emerald-900 pb-32 pt-10 px-4 rounded-b-[3rem] shadow-xl relative overflow-hidden">
        <!-- Elemen Dekoratif Background -->
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-emerald-800 rounded-full opacity-50 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-amber-500 rounded-full opacity-20 blur-2xl"></div>

        <div class="max-w-7xl mx-auto relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <a href="kelola_event.php" class="text-emerald-200 hover:text-white transition-all bg-white/10 hover:bg-white/20 p-2 rounded-full backdrop-blur-sm group">
                        <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                    </a>
                    <span class="text-emerald-200 text-xs font-bold uppercase tracking-widest border border-emerald-700/50 px-2 py-1 rounded-md">Laporan Event</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight leading-tight">
                    Hasil Kuesioner
                </h1>
                <p class="text-emerald-100/80 mt-2 text-sm md:text-base max-w-2xl">
                    Rekapitulasi jawaban peserta untuk event: <span class="font-bold text-white"><?= htmlspecialchars($event['judul']) ?></span>
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="kelola_kuesioner.php?id=<?= $id_workshop ?>"
                    class="bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center">
                    <i class="fas fa-edit mr-2"></i> Edit Pertanyaan
                </a>
                <button onclick="window.print()"
                    class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-lg shadow-amber-500/30 flex items-center transform hover:-translate-y-0.5">
                    <i class="fas fa-print mr-2"></i> Cetak / PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content (Overlapping) -->
    <div class="max-w-7xl mx-auto px-4 -mt-20 relative z-20 space-y-8">
        
        <!-- Stats Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Card 1: Total Responden -->
            <div class="bg-white p-6 rounded-3xl shadow-lg border border-gray-100 flex items-center gap-5 hover:shadow-xl transition-shadow duration-300">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-100 to-indigo-200 text-indigo-600 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Total Responden</p>
                    <p class="text-3xl font-extrabold text-gray-800">
                        <?= count($respondents) ?>
                    </p>
                </div>
            </div>

            <!-- Card 2: Status Kuesioner -->
            <div class="bg-white p-6 rounded-3xl shadow-lg border border-gray-100 flex items-center gap-5 hover:shadow-xl transition-shadow duration-300">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-100 to-emerald-200 text-emerald-600 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fas <?= $event['is_kuesioner_active'] ? 'fa-check-circle' : 'fa-pause-circle' ?>"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Status Kuesioner</p>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full <?= $event['is_kuesioner_active'] ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' ?>"></span>
                        <p class="text-lg font-bold text-gray-800">
                            <?= $event['is_kuesioner_active'] ? 'Sedang Aktif' : 'Non-Aktif' ?>
                        </p>
                    </div>
                </div>
            </div>

             <!-- Card 3: Info Tambahan (Misal Total Pertanyaan) -->
             <div class="bg-white p-6 rounded-3xl shadow-lg border border-gray-100 flex items-center gap-5 hover:shadow-xl transition-shadow duration-300">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-100 to-amber-200 text-amber-600 flex items-center justify-center text-2xl shadow-sm">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Total Pertanyaan</p>
                    <p class="text-3xl font-extrabold text-gray-800">
                        <?= count($questions) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="bg-gray-50/50 px-8 py-6 border-b border-gray-100 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg font-bold">
                        <i class="fas fa-table"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg">Data Jawaban</h3>
                        <p class="text-xs text-gray-500">Rincian jawaban per responden</p>
                    </div>
                </div>
            </div>

            <?php if (empty($questions)): ?>
                    <div class="p-16 text-center">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-gray-200">
                            <i class="fas fa-clipboard-question text-3xl text-gray-300"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">Belum Ada Pertanyaan</h3>
                        <p class="text-gray-500 text-sm mt-2 max-w-sm mx-auto">
                            Anda belum membuat pertanyaan untuk event ini. Silakan tambahkan pertanyaan terlebih dahulu.
                        </p>
                        <a href="kelola_kuesioner.php?id=<?= $id_workshop ?>" class="mt-6 inline-block px-6 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition">
                            Buat Pertanyaan
                        </a>
                    </div>
            <?php elseif (empty($respondents)): ?>
                    <div class="p-16 text-center">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-gray-200">
                            <i class="fas fa-user-clock text-3xl text-gray-300"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">Belum Ada Responden</h3>
                        <p class="text-gray-500 text-sm mt-2 max-w-sm mx-auto">
                            Belum ada peserta yang mengisi kuesioner ini. Data akan muncul di sini setelah ada yang mengisi.
                        </p>
                    </div>
            <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50/80 text-gray-600 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-5 font-extrabold uppercase tracking-wider text-xs w-16 text-center rounded-tl-2xl">No</th>
                                    <th class="px-6 py-5 font-extrabold uppercase tracking-wider text-xs min-w-[250px]">Responden</th>
                                    <?php foreach ($questions as $index => $q): ?>
                                            <th class="px-6 py-5 font-bold text-xs min-w-[200px] border-l border-gray-100 align-top group">
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-[10px] text-gray-400 font-normal uppercase tracking-wide">Tanya <?= $index + 1 ?> (<?= ucfirst($q['question_type']) ?>)</span>
                                                    <span class="text-gray-800 line-clamp-2" title="<?= htmlspecialchars($q['question_text']) ?>">
                                                        <?= htmlspecialchars($q['question_text']) ?>
                                                    </span>
                                                </div>
                                            </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php $no = 1;
                                foreach ($respondents as $uid => $data): ?>
                                        <tr class="hover:bg-emerald-50/30 transition-colors">
                                            <td class="px-6 py-5 text-gray-500 text-center font-medium">
                                                <?= $no++ ?>
                                            </td>
                                            <td class="px-6 py-5">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-500 font-bold text-sm">
                                                        <?= strtoupper(substr($data['nama'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <p class="font-bold text-gray-800 text-base">
                                                            <?= htmlspecialchars($data['nama']) ?>
                                                        </p>
                                                        <p class="text-xs text-gray-500 mb-1">
                                                            <?= htmlspecialchars($data['email']) ?>
                                                        </p>
                                                        <p class="text-[10px] inline-flex items-center gap-1 text-gray-400 bg-gray-50 px-2 py-0.5 rounded-full border border-gray-200">
                                                            <i class="far fa-clock"></i>
                                                            <?= date('d M Y, H:i', strtotime($data['waktu'])) ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <?php foreach ($questions as $q): ?>
                                                    <td class="px-6 py-5 border-l border-gray-50 align-top">
                                                        <?php
                                                        $ans = $data['answers'][$q['id']] ?? null;

                                                        if (is_null($ans) || $ans === '') {
                                                            echo '<span class="text-gray-300 italic text-xs">Tidak menjawab</span>';
                                                        } elseif ($q['question_type'] == 'rating') {
                                                            // Tampilan khusus rating (bintang)
                                                            echo '<div class="flex items-center gap-1.5">';
                                                            echo '<span class="text-amber-400 text-sm"><i class="fas fa-star"></i></span>';
                                                            echo '<span class="font-bold text-gray-700">' . $ans . '</span>';
                                                            echo '<span class="text-xs text-gray-400">/ 5</span>';
                                                            echo '</div>';
                                                        } else {
                                                            // Tampilan text biasa
                                                            echo '<span class="text-gray-700 leading-relaxed block max-w-xs">' . nl2br(htmlspecialchars($ans)) . '</span>';
                                                        }
                                                        ?>
                                                    </td>
                                            <?php endforeach; ?>
                                        </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Print Styles (Hanya aktif saat print) -->
<style>
    @media print {
        body { background: white; }
        .min-h-screen { padding: 0; }
        /* Hide UI elements */
        .shadow-xl, .shadow-lg, .shadow-sm { box-shadow: none !important; border: 1px solid #ddd !important; }
        button, a[href^="kelola_event"], a[href^="kelola_kuesioner"] { display: none !important; }
        /* Adjust layout for A4 */
        .max-w-7xl { max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
        .bg-emerald-900 { background: white !important; padding: 20px !important; color: black !important; border-bottom: 2px solid #000; border-radius: 0 !important; }
        .text-white, .text-emerald-200, .text-emerald-100\/80 { color: black !important; }
        .bg-white { background: transparent !important; }
        .grid { display: flex; flex-wrap: wrap; gap: 20px; }
        .-mt-20 { margin-top: 0 !important; }
        table { font-size: 10pt; width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000 !important; padding: 5px !important; }
        /* Sembunyikan dekorasi */
        .absolute { display: none !important; }
    }
</style>

<?php require_once BASE_PATH . '/admin/templates/footer.php'; ?>