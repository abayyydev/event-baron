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

<div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">Kelola Font</h1>
                <p class="text-slate-600 mt-1">Upload font (.ttf/.otf) untuk mempercantik sertifikat event Anda.</p>
            </div>

            <form id="uploadFontForm" class="flex gap-2 w-full md:w-auto">
                <input type="hidden" name="action" value="upload">
                <input type="file" name="font_file" accept=".ttf,.otf" required
                    class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 bg-white border border-slate-300 rounded-xl cursor-pointer">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-xl shadow-md transition whitespace-nowrap">
                    <i class="fas fa-cloud-upload-alt mr-2"></i> Upload
                </button>
            </form>
        </div>

        <div class="mb-8 bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Tester Text Preview</label>
            <input type="text" id="fontTesterInput" value="Sertifikat Penghargaan 2025"
                class="w-full px-4 py-3 text-lg border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 transition"
                placeholder="Ketik teks untuk mencoba font...">
        </div>

        <?php if (count($fonts) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($fonts as $font):
                    $name = pathinfo($font, PATHINFO_FILENAME);
                    $ext = pathinfo($font, PATHINFO_EXTENSION);
                    $is_default = ($font == 'Poppins-SemiBold.ttf');
                    ?>
                    <div
                        class="bg-white rounded-2xl shadow-sm hover:shadow-md transition border border-slate-200 overflow-hidden group relative">
                        <div
                            class="h-32 flex items-center justify-center p-4 bg-slate-50 border-b border-slate-100 overflow-hidden text-center">
                            <h3 class="text-3xl font-<?= $name ?> text-slate-800 font-preview-target break-words w-full">
                                Sertifikat Penghargaan 2025
                            </h3>
                        </div>

                        <div class="p-4 flex justify-between items-center">
                            <div>
                                <h4 class="font-bold text-slate-700 truncate max-w-[150px]" title="<?= $name ?>"><?= $name ?>
                                </h4>
                                <span
                                    class="text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded uppercase font-bold"><?= $ext ?></span>
                            </div>

                            <?php if ($is_default): ?>
                                <span class="text-xs bg-amber-100 text-amber-700 px-3 py-1 rounded-full font-bold">Default</span>
                            <?php else: ?>
                                <button
                                    class="hapus-font-btn w-9 h-9 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition flex items-center justify-center"
                                    data-filename="<?= $font ?>">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                <i class="fas fa-font text-5xl text-slate-300 mb-4"></i>
                <h3 class="text-xl font-bold text-slate-600">Belum ada font tambahan</h3>
                <p class="text-slate-500 mt-2">Upload file .ttf atau .otf pertama Anda.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    // Live Preview Tester
    document.getElementById('fontTesterInput').addEventListener('input', function () {
        const text = this.value || 'Contoh Teks';
        document.querySelectorAll('.font-preview-target').forEach(el => {
            el.textContent = text;
        });
    });
</script>

<?php require_once BASE_PATH . '/admin/templates/footer.php'; ?>