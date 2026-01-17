<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
require_once BASE_PATH . '/core/koneksi.php';
$page_title = 'Kelola Font Sertifikat';
$current_page = 'kelola_font';
require_once BASE_PATH . '/admin/templates/header.php';

// Scan Folder Font
$font_dir = BASE_PATH . '/assets/fonts/';
$fonts = [];
if (is_dir($font_dir)) {
    $files = glob($font_dir . "*.{ttf,otf}", GLOB_BRACE);
    if ($files) {
        foreach ($files as $file) {
            $fonts[] = basename($file);
        }
    }
}
?>

<style>
    <?php foreach ($fonts as $font):
        $name = pathinfo($font, PATHINFO_FILENAME);
        ?>
        @font-face {
            font-family: '<?= $name ?>';
            src: url('../assets/fonts/<?= $font ?>');
        }

        .font-<?= $name ?> {
            font-family: '<?= $name ?>', sans-serif;
        }

    <?php endforeach; ?>
</style>

<div class="min-h-screen bg-gray-50 font-sans pb-32">
    
    <!-- Hero Header Section -->
    <div class="bg-emerald-900 pb-20 pt-10 px-4 rounded-b-[3rem] shadow-xl relative overflow-hidden">
        <!-- Elemen Dekoratif Background -->
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-emerald-800 rounded-full opacity-50 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-amber-500 rounded-full opacity-20 blur-2xl"></div>

        <div class="max-w-6xl mx-auto relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-emerald-200 text-xs font-bold uppercase tracking-widest border border-emerald-700/50 px-2 py-1 rounded-md">Manajemen Aset</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight leading-tight">
                    Kelola Font
                </h1>
                <p class="text-emerald-100/80 mt-2 text-sm md:text-base max-w-xl">
                    Upload font (.ttf/.otf) kustom untuk mempercantik sertifikat event Anda.
                </p>
            </div>
            
            <form id="uploadFontForm" class="flex gap-2 w-full md:w-auto bg-white/10 p-2 rounded-xl backdrop-blur-sm border border-white/10">
                <input type="hidden" name="action" value="upload">
                <div class="relative flex-1 md:w-64">
                    <input type="file" name="font_file" accept=".ttf,.otf" required
                        class="block w-full text-sm text-emerald-100 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500 cursor-pointer focus:outline-none">
                </div>
                <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-4 rounded-lg shadow-lg shadow-amber-500/30 transition-all transform hover:-translate-y-0.5 flex items-center justify-center">
                    <i class="fas fa-cloud-upload-alt text-lg"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content Container (Naik ke atas menutupi header) -->
    <div class="max-w-6xl mx-auto px-4 -mt-12 relative z-20">
        
        <!-- Tester Input Card -->
        <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-6 mb-8">
            <label class="block text-xs font-bold text-gray-500 uppercase mb-2 ml-1">Live Preview Tester</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-emerald-500">
                    <i class="fas fa-pen-nib"></i>
                </span>
                <input type="text" id="fontTesterInput" value="Sertifikat Penghargaan 2025"
                    class="w-full pl-11 pr-4 py-4 text-lg border border-gray-200 bg-gray-50 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all shadow-inner text-gray-800 placeholder-gray-400"
                    placeholder="Ketik teks di sini untuk mencoba semua font...">
            </div>
        </div>

        <!-- Fonts Grid -->
        <?php if (count($fonts) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($fonts as $font):
                        $name = pathinfo($font, PATHINFO_FILENAME);
                        $ext = pathinfo($font, PATHINFO_EXTENSION);
                        $is_default = ($font == 'Poppins-SemiBold.ttf'); // Sesuaikan dengan font default Anda
                        ?>
                            <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden group relative hover:-translate-y-1">
                        
                                <!-- Preview Area -->
                                <div class="h-40 flex items-center justify-center p-6 bg-gradient-to-br from-gray-50 to-gray-100 border-b border-gray-100 overflow-hidden text-center relative group-hover:from-emerald-50/30 group-hover:to-emerald-50/10 transition-colors">
                                    <!-- Background Text Pattern (Optional) -->
                                    <div class="absolute inset-0 opacity-[0.03] text-6xl font-bold select-none pointer-events-none flex items-center justify-center overflow-hidden">
                                        Aa
                                    </div>
                            
                                    <h3 class="text-3xl font-<?= $name ?> text-gray-800 font-preview-target break-words w-full leading-tight drop-shadow-sm">
                                        Sertifikat Penghargaan 2025
                                    </h3>
                                </div>

                                <!-- Card Footer -->
                                <div class="p-5 flex justify-between items-center bg-white">
                                    <div class="flex-1 min-w-0 mr-4">
                                        <h4 class="font-bold text-gray-800 truncate text-base" title="<?= $name ?>"><?= $name ?></h4>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded uppercase font-bold border border-gray-200"><?= strtoupper($ext) ?></span>
                                        </div>
                                    </div>

                                    <?php if ($is_default): ?>
                                            <span class="text-xs bg-amber-100 text-amber-700 px-3 py-1.5 rounded-lg font-bold border border-amber-200 shadow-sm">
                                                <i class="fas fa-star mr-1"></i> Default
                                            </span>
                                    <?php else: ?>
                                            <button class="hapus-font-btn w-10 h-10 rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center shadow-sm hover:shadow-md"
                                                data-filename="<?= $font ?>" title="Hapus Font">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                    <?php endforeach; ?>
                </div>
        <?php else: ?>
                <div class="flex flex-col items-center justify-center py-20 bg-white rounded-3xl border-2 border-dashed border-gray-200">
                    <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6 text-gray-300">
                        <i class="fas fa-font text-5xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-700">Belum ada font tambahan</h3>
                    <p class="text-gray-500 mt-2 text-sm max-w-xs text-center">Upload file .ttf atau .otf pertama Anda melalui form di atas.</p>
                </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Live Preview Tester
    document.getElementById('fontTesterInput').addEventListener('input', function () {
        const text = this.value || 'Sertifikat Penghargaan 2025';
        document.querySelectorAll('.font-preview-target').forEach(el => {
            el.textContent = text;
        });
    });

    // Upload Form Handling (Contoh Sederhana)
    const uploadForm = document.getElementById('uploadFontForm');
    if(uploadForm){
        uploadForm.addEventListener('submit', function(e) {
            // Logika submit form (Anda bisa menambahkan AJAX di sini jika mau)
            // e.preventDefault(); 
            // alert('Upload logic goes here');
        });
    }

    // Hapus Font Handling
    document.querySelectorAll('.hapus-font-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filename = this.getAttribute('data-filename');
            Swal.fire({
                title: 'Hapus Font?',
                text: `Anda akan menghapus font "${filename}".`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#d1d5db',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-lg px-5',
                    cancelButton: 'rounded-lg px-5 text-gray-600 bg-gray-100'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Buat form dinamis untuk submit POST request
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = ''; // Submit ke halaman ini sendiri
                    
                    const inputAction = document.createElement('input');
                    inputAction.type = 'hidden';
                    inputAction.name = 'action';
                    inputAction.value = 'delete';
                    
                    const inputFile = document.createElement('input');
                    inputFile.type = 'hidden';
                    inputFile.name = 'font_file';
                    inputFile.value = filename;

                    form.appendChild(inputAction);
                    form.appendChild(inputFile);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
</script>

<?php
// Simple PHP Logic for Upload/Delete (Example)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['font_file'])) {
        $fileToDelete = BASE_PATH . '/assets/fonts/' . basename($_POST['font_file']);
        if (file_exists($fileToDelete) && unlink($fileToDelete)) {
            echo "<script>Swal.fire('Terhapus!', 'Font berhasil dihapus.', 'success').then(() => window.location.href='kelola_font.php');</script>";
        } else {
            echo "<script>Swal.fire('Gagal!', 'Gagal menghapus font.', 'error');</script>";
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'upload' && isset($_FILES['font_file'])) {
        $target_dir = BASE_PATH . '/assets/fonts/';
        if (!is_dir($target_dir))
            mkdir($target_dir, 0755, true);

        $target_file = $target_dir . basename($_FILES["font_file"]["name"]);
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if ($fileType != "ttf" && $fileType != "otf") {
            echo "<script>Swal.fire('Error!', 'Hanya file .ttf & .otf yang diperbolehkan.', 'error');</script>";
        } else {
            if (move_uploaded_file($_FILES["font_file"]["tmp_name"], $target_file)) {
                echo "<script>Swal.fire('Berhasil!', 'Font berhasil diupload.', 'success').then(() => window.location.href='kelola_font.php');</script>";
            } else {
                echo "<script>Swal.fire('Error!', 'Terjadi kesalahan saat mengupload file.', 'error');</script>";
            }
        }
    }
}

require_once BASE_PATH . '/admin/templates/footer.php';
?>