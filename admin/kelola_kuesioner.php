<?php
// Pastikan BASE_PATH terdefinisi
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/admin/templates/header.php';
require_once BASE_PATH . '/core/koneksi.php';

$id_workshop = $_GET['id'] ?? 0;

// 1. Ambil Data Event untuk Judul
$stmt = $pdo->prepare("SELECT * FROM workshops WHERE id = ?");
$stmt->execute([$id_workshop]);
$event = $stmt->fetch();

if (!$event) {
    echo "<div class='p-8 text-center'>Event tidak ditemukan.</div>";
    require_once BASE_PATH . '/admin/templates/footer.php';
    exit;
}

// 2. PROSES POST (Simpan/Hapus/Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // A. Update Status Aktif/Tidak
    if (isset($_POST['update_status'])) {
        $status = isset($_POST['is_active']) ? 1 : 0;
        $pdo->prepare("UPDATE workshops SET is_kuesioner_active = ? WHERE id = ?")->execute([$status, $id_workshop]);

        // Refresh data event agar toggle berubah
        $event['is_kuesioner_active'] = $status;
        echo "<script>Swal.fire('Sukses', 'Status kuesioner berhasil diperbarui', 'success');</script>";
    }

    // B. Tambah Pertanyaan Baru (Logika Google Forms)
    if (isset($_POST['add_question'])) {
        $q_text = trim($_POST['question_text']);
        $q_type = $_POST['question_type'];
        $q_opts = null;

        // Logika Penggabungan Opsi (Array to Comma Separated String)
        if ($q_type == 'radio' || $q_type == 'dropdown') {
            if (isset($_POST['dynamic_options']) && is_array($_POST['dynamic_options'])) {
                // Hapus nilai array yang kosong/null
                $clean_opts = array_filter($_POST['dynamic_options'], function ($value) {
                    return !is_null($value) && trim($value) !== '';
                });
                // Gabungkan jadi string: "Opsi A,Opsi B,Opsi C"
                $q_opts = implode(',', $clean_opts);
            }
        }

        if (!empty($q_text)) {
            $sql = "INSERT INTO workshop_questions (workshop_id, question_text, question_type, options) VALUES (?, ?, ?, ?)";
            $stmt_add = $pdo->prepare($sql);
            $stmt_add->execute([$id_workshop, $q_text, $q_type, $q_opts]);
            echo "<script>Swal.fire('Berhasil', 'Pertanyaan ditambahkan', 'success');</script>";
        }
    }

    // C. Hapus Pertanyaan
    if (isset($_POST['delete_question'])) {
        $q_id = $_POST['question_id'];
        $pdo->prepare("DELETE FROM workshop_questions WHERE id = ?")->execute([$q_id]);
    }
}

// 3. Ambil Daftar Pertanyaan Existing
$questions = $pdo->prepare("SELECT * FROM workshop_questions WHERE workshop_id = ? ORDER BY id ASC");
$questions->execute([$id_workshop]);
$list_q = $questions->fetchAll();
?>

<div class="min-h-screen bg-slate-50 py-8 px-4">
    <div class="max-w-4xl mx-auto">
        
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <a href="kelola_event.php" class="text-slate-500 hover:text-slate-700 mb-2 inline-flex items-center gap-2 text-sm font-medium transition-colors">
                    <i class="fas fa-arrow-left"></i> Kembali ke Kelola Event
                </a>
                <h1 class="text-2xl font-bold text-slate-800">Atur Kuesioner</h1>
                <p class="text-slate-600">Event: <span class="font-semibold text-blue-600"><?= htmlspecialchars($event['judul']) ?></span></p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border border-slate-200">
            <form method="POST" class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-lg text-slate-800">Wajibkan Kuesioner?</h3>
                    <p class="text-sm text-slate-500">Jika aktif, peserta <b>wajib</b> mengisi kuesioner ini sebelum bisa mengunduh sertifikat.</p>
                </div>
                <div class="flex items-center gap-4 bg-slate-50 p-2 rounded-lg border border-slate-100">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer" <?= $event['is_kuesioner_active'] ? 'checked' : '' ?>>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ml-3 text-sm font-medium text-slate-700 min-w-[70px]"><?= $event['is_kuesioner_active'] ? 'Aktif' : 'Non-Aktif' ?></span>
                    </label>
                    <button type="submit" name="update_status" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition shadow-md shadow-blue-200">
                        Simpan
                    </button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            
            <?php if (count($list_q) > 0): ?>
                    <?php foreach ($list_q as $index => $q): ?>
                            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition group relative">
                                <div class="absolute top-5 right-5 flex gap-2">
                                    <form method="POST" onsubmit="return confirm('Yakin ingin menghapus pertanyaan ini?');">
                                        <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                                        <button type="submit" name="delete_question" class="text-slate-400 hover:text-red-500 transition p-1" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>

                                <div class="pr-10">
                                    <span class="inline-block px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-500 mb-2">
                                        <?= $q['question_type'] ?>
                                    </span>
                                    <h4 class="text-lg font-semibold text-slate-800"><?= htmlspecialchars($q['question_text']) ?></h4>
                            
                                    <?php if (($q['question_type'] == 'radio' || $q['question_type'] == 'dropdown') && !empty($q['options'])): ?>
                                            <div class="mt-3 pl-4 border-l-2 border-slate-200">
                                                <p class="text-xs text-slate-400 mb-1">Pilihan Jawaban:</p>
                                                <ul class="list-disc list-inside text-sm text-slate-600 space-y-1">
                                                    <?php
                                                    $opts = explode(',', $q['options']);
                                                    foreach ($opts as $opt) {
                                                        echo "<li>" . htmlspecialchars(trim($opt)) . "</li>";
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                    <?php endforeach; ?>
            <?php else: ?>
                    <div class="text-center py-12 bg-white rounded-xl border border-dashed border-slate-300">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400">
                            <i class="fas fa-clipboard-list text-2xl"></i>
                        </div>
                        <p class="text-slate-500">Belum ada pertanyaan kuesioner.</p>
                        <p class="text-sm text-slate-400">Silakan tambahkan pertanyaan di bawah ini.</p>
                    </div>
            <?php endif; ?>

            <div class="bg-blue-50/50 p-6 rounded-xl border border-blue-100 shadow-sm">
                <h4 class="font-bold text-blue-800 mb-6 flex items-center gap-2 text-lg border-b border-blue-100 pb-4">
                    <i class="fas fa-plus-circle"></i> Tambah Pertanyaan Baru
                </h4>
                
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="md:col-span-3 space-y-2">
                            <label class="block text-sm font-bold text-slate-700">Teks Pertanyaan</label>
                            <input type="text" name="question_text" required 
                                placeholder="Contoh: Bagaimana pendapat Anda tentang materi narasumber?" 
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
                        </div>
                        
                        <div class="md:col-span-1 space-y-2">
                            <label class="block text-sm font-bold text-slate-700">Tipe Jawaban</label>
                            <select name="question_type" id="typeSelect" onchange="toggleOptions()" 
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-white cursor-pointer">
                                <option value="text">Teks Singkat</option>
                                <option value="textarea">Paragraf Panjang</option>
                                <option value="rating">Rating Bintang (1-5)</option>
                                <option value="radio">Pilihan Ganda (Radio)</option>
                                <option value="dropdown">Dropdown Menu</option>
                            </select>
                        </div>
                    </div>

                    <div id="optionsContainer" class="hidden bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
                        <label class="block text-sm font-bold text-slate-700 mb-3">Pilihan Jawaban</label>
                        
                        <div id="dynamicInputs" class="space-y-3 mb-3">
                            </div>

                        <button type="button" onclick="addOptionInput()" 
                            class="text-sm text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-2 py-2 px-1 rounded hover:bg-blue-50 transition w-fit">
                            <i class="fas fa-plus"></i> Tambah Opsi Lain
                        </button>
                    </div>

                    <div class="text-right pt-4">
                        <button type="submit" name="add_question" 
                            class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transform active:scale-95 transition-all">
                            Simpan Pertanyaan
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    // 1. Fungsi Toggle Tampilan Container Opsi
    function toggleOptions() {
        const type = document.getElementById('typeSelect').value;
        const container = document.getElementById('optionsContainer');
        const inputsDiv = document.getElementById('dynamicInputs');
        
        // Tampilkan hanya jika tipe radio atau dropdown
        if (type === 'radio' || type === 'dropdown') {
            container.classList.remove('hidden');
            container.classList.add('animate-fade-in');
            
            // Jika belum ada input sama sekali, tambahkan 1 otomatis
            if (inputsDiv.children.length === 0) {
                addOptionInput();
            }
        } else {
            container.classList.add('hidden');
            container.classList.remove('animate-fade-in');
            // Kita tidak menghapus innerHTML agar jika user salah klik ganti tipe, datanya tidak hilang, 
            // tapi secara visual tersembunyi. (PHP akan mengabaikannya jika tipe bukan radio/dropdown)
        }
    }

    // 2. Fungsi Menambahkan Baris Input Baru
    function addOptionInput() {
        const inputsDiv = document.getElementById('dynamicInputs');
        
        // Buat element wrapper
        const wrapper = document.createElement('div');
        wrapper.className = "flex items-center gap-3 animate-fade-in group";
        
        // HTML untuk input + tombol hapus
        wrapper.innerHTML = `
            <i class="far fa-circle text-slate-300 text-sm"></i>
            <input type="text" name="dynamic_options[]" placeholder="Tulis opsi jawaban..." required
                class="flex-1 px-4 py-2 border-b border-slate-300 focus:border-blue-500 outline-none bg-transparent transition-colors placeholder-slate-400 text-slate-700">
            <button type="button" onclick="removeOption(this)" 
                class="text-slate-300 hover:text-red-500 p-2 transition-colors" title="Hapus opsi ini">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        inputsDiv.appendChild(wrapper);
        
        // Auto focus ke input yang baru dibuat
        const newInput = wrapper.querySelector('input');
        if(newInput) newInput.focus();
    }

    // 3. Fungsi Menghapus Baris Input
    function removeOption(button) {
        const inputsDiv = document.getElementById('dynamicInputs');
        // Jangan biarkan menghapus jika itu satu-satunya input (opsional, tapi UX bagus)
        if (inputsDiv.children.length > 1) {
            button.parentElement.remove();
        } else {
            // Jika tinggal satu, cukup kosongkan nilainya
            const input = button.parentElement.querySelector('input');
            input.value = '';
            input.focus();
            
            // Optional: Beri feedback visual/alert kecil
            const Toast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 1500
            });
            Toast.fire({ icon: 'info', title: 'Minimal satu opsi diperlukan' });
        }
    }
</script>

<style>
    /* Animasi halus saat muncul */
    .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { 
        from { opacity: 0; transform: translateY(-10px); } 
        to { opacity: 1; transform: translateY(0); } 
    }
</style>

<?php require_once BASE_PATH . '/admin/templates/footer.php'; ?>