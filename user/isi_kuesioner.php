<?php
session_start();
require_once __DIR__ . '/../core/koneksi.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$workshop_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Cek Event
$stmt = $pdo->prepare("SELECT * FROM workshops WHERE id = ?");
$stmt->execute([$workshop_id]);
$event = $stmt->fetch();
if (!$event) die("Event tidak ditemukan.");

// Ambil Pertanyaan
$stmt_q = $pdo->prepare("SELECT * FROM workshop_questions WHERE workshop_id = ?");
$stmt_q->execute([$workshop_id]);
$questions = $stmt_q->fetchAll();

// PROSES SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        $answers = $_POST['answer'] ?? []; 
        
        foreach ($answers as $q_id => $ans_text) {
            // Jika array (kasus multiple select masa depan), gabungkan string
            if (is_array($ans_text)) $ans_text = implode(', ', $ans_text);
            
            $sql = "INSERT INTO workshop_answers (workshop_id, user_id, question_id, answer_text) VALUES (?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$workshop_id, $user_id, $q_id, $ans_text]);
        }
        
        $pdo->commit();
        echo "<script>
            alert('Terima kasih! Jawaban Anda telah tersimpan.'); 
            window.location='dashboard.php';
        </script>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuesioner - <?= htmlspecialchars($event['judul']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom Rating Star Style */
        .rating-group { display: inline-flex; flex-direction: row-reverse; }
        .rating-group input { display: none; }
        .rating-group label { 
            font-size: 2rem; color: #cbd5e1; cursor: pointer; transition: 0.2s; padding: 0 2px;
        }
        .rating-group input:checked ~ label, 
        .rating-group label:hover, 
        .rating-group label:hover ~ label { color: #f59e0b; }
        
        /* Custom Radio Style */
        .radio-custom:checked + div { border-color: #3b82f6; background-color: #eff6ff; }
        .radio-custom:checked + div .radio-dot { transform: scale(1); }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8 px-4">

    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg border-t-8 border-blue-600 overflow-hidden mb-6">
            <div class="p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($event['judul']) ?></h1>
                <p class="text-gray-600">Mohon isi kuesioner ini dengan jujur untuk mengunduh sertifikat.</p>
                <div class="mt-4 text-sm text-red-500 font-medium">* Wajib diisi</div>
            </div>
        </div>

        <form method="POST" class="space-y-4">
            <?php foreach ($questions as $q): ?>
                <div class="bg-white rounded-xl shadow p-6 border border-gray-200 transition hover:shadow-md">
                    <label class="block text-lg font-medium text-gray-800 mb-4">
                        <?= htmlspecialchars($q['question_text']) ?> <span class="text-red-500">*</span>
                    </label>

                    <?php if ($q['question_type'] === 'text'): ?>
                        <input type="text" name="answer[<?= $q['id'] ?>]" required 
                            class="w-full border-b border-gray-300 focus:border-blue-600 focus:outline-none py-2 px-1 bg-transparent transition-colors placeholder-gray-400" placeholder="Jawaban Anda">
                    
                    <?php elseif ($q['question_type'] === 'textarea'): ?>
                        <textarea name="answer[<?= $q['id'] ?>]" rows="3" required 
                            class="w-full border-b border-gray-300 focus:border-blue-600 focus:outline-none py-2 px-1 bg-transparent transition-colors placeholder-gray-400 resize-none" placeholder="Jawaban Anda"></textarea>
                    
                    <?php elseif ($q['question_type'] === 'rating'): ?>
                        <div class="rating-group justify-end">
                            <?php for($i=5; $i>=1; $i--): ?>
                                <input type="radio" id="q<?= $q['id'] ?>-s<?= $i ?>" name="answer[<?= $q['id'] ?>]" value="<?= $i ?>" required>
                                <label for="q<?= $q['id'] ?>-s<?= $i ?>" title="<?= $i ?> Bintang"><i class="fas fa-star"></i></label>
                            <?php endfor; ?>
                        </div>
                    
                    <?php elseif ($q['question_type'] === 'radio'): 
                        $opts = explode(',', $q['options']); ?>
                        <div class="space-y-2">
                            <?php foreach($opts as $idx => $opt): $opt = trim($opt); ?>
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" name="answer[<?= $q['id'] ?>]" value="<?= htmlspecialchars($opt) ?>" required class="radio-custom sr-only">
                                    <div class="w-5 h-5 border-2 border-gray-400 rounded-full flex items-center justify-center mr-3 group-hover:border-blue-400 transition">
                                        <div class="radio-dot w-2.5 h-2.5 bg-blue-600 rounded-full transform scale-0 transition-transform"></div>
                                    </div>
                                    <span class="text-gray-700"><?= htmlspecialchars($opt) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                    <?php elseif ($q['question_type'] === 'dropdown'): 
                        $opts = explode(',', $q['options']); ?>
                        <div class="relative">
                            <select name="answer[<?= $q['id'] ?>]" required 
                                class="block w-full px-4 py-3 pr-8 text-gray-700 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none cursor-pointer">
                                <option value="" disabled selected>Pilih salah satu...</option>
                                <?php foreach($opts as $opt): ?>
                                    <option value="<?= htmlspecialchars(trim($opt)) ?>"><?= htmlspecialchars(trim($opt)) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>

                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="flex justify-between items-center pt-6">
                <a href="dashboard.php" class="text-gray-500 hover:text-gray-800 text-sm font-medium">Batal</a>
                <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-blue-700 shadow-lg transform active:scale-95 transition">
                    Kirim Jawaban
                </button>
            </div>
        </form>
    </div>
    
    <div class="text-center mt-8 text-gray-400 text-xs pb-4">
        &copy; <?= date('Y') ?> Workshop App. Form generated automatically.
    </div>

</body>
</html>