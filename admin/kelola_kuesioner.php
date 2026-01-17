<?php
// Pastikan BASE_PATH terdefinisi
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/admin/templates/header.php';
require_once BASE_PATH . '/core/koneksi.php';

// Ambil ID dari URL
$id_workshop = $_GET['id'] ?? 0;
$penyelenggara_id = $_SESSION['penyelenggara_id_bersama']; // Pastikan session ini ada

// 1. KEAMANAN & VALIDASI EVENT
// Kita tambahkan "AND penyelenggara_id = ?" agar orang lain tidak bisa edit event kita via URL
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
            <p class='text-gray-500 mb-8'>Event tidak ditemukan atau Anda tidak memiliki izin untuk mengelolanya.</p>
            <a href='kelola_event.php' class='inline-flex items-center justify-center px-6 py-3 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-200 font-bold'>
                <i class='fas fa-arrow-left mr-2'></i> Kembali ke Dashboard
            </a>
        </div>
    </div>";
    require_once BASE_PATH . '/admin/templates/footer.php';
    exit;
}

// 2. PROSES LOGIKA (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // A. Update Status Aktif/Tidak
    if (isset($_POST['update_status'])) {
        $status = isset($_POST['is_active']) ? 1 : 0;
        $pdo->prepare("UPDATE workshops SET is_kuesioner_active = ? WHERE id = ?")->execute([$status, $id_workshop]);

        // Refresh variabel event agar toggle visualnya ikut berubah
        $event['is_kuesioner_active'] = $status;

        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Status Diperbarui',
                text: 'Status kuesioner berhasil diubah.',
                timer: 1500,
                showConfirmButton: false,
                confirmButtonColor: '#059669'
            });
        </script>";
    }

    // B. Tambah Pertanyaan Baru
    if (isset($_POST['add_question'])) {
        $q_text = trim($_POST['question_text']);
        $q_type = $_POST['question_type'];
        $q_opts = null;

        // Jika tipe Radio/Dropdown, kita ambil array opsi dinamis
        if ($q_type == 'radio' || $q_type == 'dropdown') {
            if (isset($_POST['dynamic_options']) && is_array($_POST['dynamic_options'])) {
                // Filter nilai kosong dan gabungkan jadi string (pisahkan koma)
                $clean_opts = array_filter($_POST['dynamic_options'], function ($val) {
                    return !is_null($val) && trim($val) !== '';
                });
                $q_opts = implode(',', $clean_opts);
            }
        }

        if (!empty($q_text)) {
            $sql = "INSERT INTO workshop_questions (workshop_id, question_text, question_type, options) VALUES (?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$id_workshop, $q_text, $q_type, $q_opts]);

            echo "<script>Swal.fire({title: 'Berhasil', text: 'Pertanyaan ditambahkan', icon: 'success', confirmButtonColor: '#059669'});</script>";
        }
    }

    // C. Hapus Pertanyaan
    if (isset($_POST['delete_question'])) {
        $q_id = $_POST['question_id'];
        $pdo->prepare("DELETE FROM workshop_questions WHERE id = ?")->execute([$q_id]);
        echo "<script>const Toast = Swal.mixin({toast: true, position: 'top-end', showConfirmButton: false, timer: 3000}); Toast.fire({icon: 'success', title: 'Pertanyaan dihapus'});</script>";
    }
}

// 3. AMBIL DAFTAR PERTANYAAN
$questions = $pdo->prepare("SELECT * FROM workshop_questions WHERE workshop_id = ? ORDER BY id ASC");
$questions->execute([$id_workshop]);
$list_q = $questions->fetchAll();
?>

<!-- Container Utama -->
<div class="min-h-screen bg-gray-50 font-sans pb-32">

    <!-- Hero Header Section (Matching form_event.php) -->
    <div class="bg-emerald-900 pb-24 pt-10 px-4 rounded-b-[3rem] shadow-xl relative overflow-hidden">
        <!-- Elemen Dekoratif Background -->
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-emerald-800 rounded-full opacity-50 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-amber-500 rounded-full opacity-20 blur-2xl"></div>

        <div class="max-w-6xl mx-auto relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <a href="kelola_event.php" class="text-emerald-200 hover:text-white transition-all bg-white/10 hover:bg-white/20 p-2 rounded-full backdrop-blur-sm group">
                            <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                        </a>
                        <span class="text-emerald-200 text-xs font-bold uppercase tracking-widest border border-emerald-700/50 px-2 py-1 rounded-md">Manajemen Kuesioner</span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight leading-tight">
                        Atur Kuesioner
                    </h1>
                    <p class="text-emerald-100/80 mt-2 text-sm md:text-base max-w-2xl">
                        Event: <span class="font-bold text-white"><?= htmlspecialchars($event['judul']) ?></span>
                    </p>
                </div>

                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-xl p-3 px-5 flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-[10px] text-emerald-200 uppercase font-bold">Total Pertanyaan</p>
                        <p class="text-white font-bold text-2xl"><?= count($list_q) ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-amber-500 flex items-center justify-center text-white shadow-lg shadow-amber-500/30 text-xl">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Container (Overlapping Header) -->
    <div class="max-w-6xl mx-auto px-4 -mt-16 relative z-20">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- LEFT COLUMN: Controls & Add Form -->
            <div class="lg:col-span-1 space-y-6">

                <!-- 1. Status Card -->
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden relative group hover:shadow-2xl transition-all duration-300">
                    <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <i class="fas fa-power-off"></i>
                        </div>
                        <h3 class="font-bold text-gray-800">Status Kuesioner</h3>
                    </div>
                    
                    <div class="p-6">
                        <p class="text-xs text-gray-500 mb-4 leading-relaxed">
                            Jika aktif, peserta <span class="text-red-500 font-bold">wajib</span> mengisi kuesioner ini sebelum bisa mengunduh sertifikat mereka.
                        </p>

                        <form method="POST">
                            <label class="flex items-center justify-between cursor-pointer group/toggle p-3 rounded-xl border border-gray-200 hover:border-emerald-200 hover:bg-emerald-50/30 transition-all bg-white">
                                <span class="text-sm font-bold text-gray-700">Aktifkan Kuesioner</span>
                                <div class="relative">
                                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" <?= $event['is_kuesioner_active'] ? 'checked' : '' ?>>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </div>
                            </label>
                            
                            <button type="submit" name="update_status"
                                class="mt-4 w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold py-2.5 rounded-xl shadow-lg shadow-emerald-200 transition-all transform hover:-translate-y-0.5">
                                Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>

                <!-- 2. Add Question Form Card -->
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden relative group hover:shadow-2xl transition-all duration-300 sticky top-6">
                    <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h3 class="font-bold text-gray-800">Buat Pertanyaan</h3>
                    </div>

                    <div class="p-6">
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Pertanyaan</label>
                                <textarea name="question_text" rows="3" required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-200 focus:border-emerald-500 outline-none text-sm placeholder-gray-400 bg-gray-50 focus:bg-white transition-all resize-none"
                                    placeholder="Tulis pertanyaan Anda di sini..."></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Tipe Jawaban</label>
                                <div class="relative">
                                    <select name="question_type" id="typeSelect" onchange="toggleOptions()"
                                        class="w-full pl-4 pr-10 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-200 focus:border-emerald-500 outline-none text-sm bg-gray-50 focus:bg-white appearance-none transition-all cursor-pointer font-medium text-gray-700">
                                        <option value="text">✍️ Teks Singkat</option>
                                        <option value="textarea">📝 Paragraf Panjang</option>
                                        <option value="rating">⭐ Rating Bintang (1-5)</option>
                                        <option value="radio">🔘 Pilihan Ganda (Radio)</option>
                                        <option value="dropdown">🔽 Dropdown Menu</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <div id="optionsContainer" class="hidden animate-fade-in space-y-3 pt-3 border-t border-dashed border-gray-200">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Opsi Jawaban</label>
                                <div id="dynamicInputs" class="space-y-2"></div>

                                <button type="button" onclick="addOptionInput()"
                                    class="text-xs text-emerald-600 hover:text-emerald-800 font-bold flex items-center gap-1.5 mt-2 py-2 px-3 hover:bg-emerald-50 rounded-lg transition-all w-full justify-center border border-dashed border-emerald-200">
                                    <i class="fas fa-plus-circle"></i> Tambah Opsi Lain
                                </button>
                            </div>

                            <button type="submit" name="add_question"
                                class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 rounded-xl shadow-lg shadow-slate-200 transition-all transform hover:-translate-y-0.5 mt-2 flex items-center justify-center gap-2">
                                <i class="fas fa-save"></i> Tambahkan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Question List -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden min-h-[500px]">
                    <div class="bg-gray-50/50 px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-100 to-indigo-200 text-indigo-700 flex items-center justify-center text-lg font-bold shadow-sm">
                                <i class="fas fa-list-ol"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800">Daftar Pertanyaan</h3>
                                <p class="text-xs text-gray-500">Preview kuesioner yang akan dilihat peserta.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 md:p-8 space-y-4">
                        <?php if (count($list_q) > 0): ?>
                            <?php foreach ($list_q as $index => $q): ?>
                                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-emerald-100 transition-all group relative">
                                    
                                    <!-- Header Pertanyaan -->
                                    <div class="flex justify-between items-start mb-3">
                                        <div class="flex items-center gap-2">
                                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">
                                                <?= $index + 1 ?>
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-500 border border-gray-200">
                                                <?= strtoupper($q['question_type']) ?>
                                            </span>
                                        </div>

                                        <form method="POST" onsubmit="return confirm('Hapus pertanyaan ini?');">
                                            <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                                            <button type="submit" name="delete_question"
                                                class="text-gray-300 hover:text-red-500 w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-50 transition-all"
                                                title="Hapus Pertanyaan">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Isi Pertanyaan -->
                                    <h4 class="text-lg font-bold text-gray-800 mb-4 leading-snug pl-8">
                                        <?= htmlspecialchars($q['question_text']) ?>
                                    </h4>

                                    <!-- Preview Input -->
                                    <div class="pl-8">
                                        <?php if (($q['question_type'] == 'radio' || $q['question_type'] == 'dropdown') && !empty($q['options'])): ?>
                                            <div class="space-y-2">
                                                <?php $opts = explode(',', $q['options']); foreach ($opts as $opt): ?>
                                                    <div class="flex items-center gap-3 p-2 rounded-lg border border-gray-100 bg-gray-50/50">
                                                        <i class="far <?= $q['question_type'] == 'radio' ? 'fa-circle' : 'fa-square' ?> text-emerald-400"></i>
                                                        <span class="text-sm text-gray-600 font-medium"><?= htmlspecialchars(trim($opt)) ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        
                                        <?php elseif ($q['question_type'] == 'rating'): ?>
                                            <div class="flex gap-1 text-amber-400 text-lg">
                                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star text-gray-200"></i>
                                            </div>
                                        
                                        <?php elseif ($q['question_type'] == 'textarea'): ?>
                                            <div class="w-full h-20 bg-gray-50 border border-dashed border-gray-300 rounded-xl flex items-center justify-center text-gray-400 text-sm">
                                                Area Teks Panjang
                                            </div>
                                        
                                        <?php else: ?>
                                            <div class="w-full h-10 bg-gray-50 border-b-2 border-gray-200 rounded-t-lg flex items-center px-3 text-gray-400 text-sm">
                                                Input Teks Singkat
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="flex flex-col items-center justify-center py-20 text-center">
                                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-4 border-2 border-dashed border-gray-200">
                                    <i class="fas fa-clipboard-question text-4xl text-gray-300"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800">Belum Ada Pertanyaan</h3>
                                <p class="text-gray-500 text-sm max-w-sm mt-2 leading-relaxed">
                                    Kuesioner ini masih kosong. Gunakan form di sebelah kiri (atau atas pada mobile) untuk mulai menambahkan pertanyaan.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // 1. Toggle Tampilan Container Opsi
    function toggleOptions() {
        const type = document.getElementById('typeSelect').value;
        const container = document.getElementById('optionsContainer');
        const inputsDiv = document.getElementById('dynamicInputs');

        if (type === 'radio' || type === 'dropdown') {
            container.classList.remove('hidden');
            // Jika kosong, tambah 2 input otomatis
            if (inputsDiv.children.length === 0) {
                addOptionInput();
                addOptionInput();
            }
        } else {
            container.classList.add('hidden');
            inputsDiv.innerHTML = '';
        }
    }

    // 2. Tambah Input Baru
    function addOptionInput() {
        const inputsDiv = document.getElementById('dynamicInputs');
        const wrapper = document.createElement('div');
        wrapper.className = "flex items-center gap-2 animate-fade-in group";

        wrapper.innerHTML = `
            <div class="w-2 h-2 rounded-full bg-emerald-200 flex-shrink-0"></div>
            <input type="text" name="dynamic_options[]" placeholder="Opsi jawaban..." required
                class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-200 outline-none transition bg-white">
            <button type="button" onclick="removeOption(this)" 
                class="text-gray-300 hover:text-red-500 p-1.5 rounded-full hover:bg-red-50 transition opacity-0 group-hover:opacity-100" title="Hapus Opsi">
                <i class="fas fa-times"></i>
            </button>
        `;

        inputsDiv.appendChild(wrapper);

        // Auto focus
        const input = wrapper.querySelector('input');
        if (input) input.focus();
    }

    // 3. Hapus Input
    function removeOption(btn) {
        const inputsDiv = document.getElementById('dynamicInputs');
        // Pastikan minimal ada 1 opsi jika ingin menghapus (UX choice)
        // Atau biarkan kosong
        if (inputsDiv.children.length > 1) {
            btn.parentElement.remove();
        } else {
            // Jika tinggal satu, kosongkan valuenya
            const input = btn.parentElement.querySelector('input');
            input.value = '';
            input.focus();
        }
    }
</script>

<style>
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out forwards;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<?php require_once BASE_PATH . '/admin/templates/footer.php'; ?>