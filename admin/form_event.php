<?php
// Fix Error: Define BASE_PATH jika belum ada
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/core/koneksi.php';

// Cek Mode (Tambah atau Edit)
$is_edit = isset($_GET['id']);
$page_title = $is_edit ? 'Edit Event' : 'Tambah Event Baru';
$current_page = 'kelola_event';

$event = []; // Array kosong untuk data event

// Jika Edit, Ambil Data dari Database
if ($is_edit) {
    $id = (int) $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM workshops WHERE id = ?");
    $stmt->execute([$id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        header("Location: kelola_event.php");
        exit;
    }
}

require_once BASE_PATH . '/admin/templates/header.php';
?>

<div class="min-h-screen bg-slate-50 py-2">
    <div class="max-w-4xl mx-auto px-2 sm:px-4 lg:px-4">

        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-800"><?= $page_title ?></h1>
                <p class="text-slate-600 mt-1">Lengkapi formulir di bawah ini dengan data event yang valid.</p>
            </div>
            <a href="kelola_event.php"
                class="text-slate-500 hover:text-slate-700 font-medium flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>

        <form id="eventFormPage" enctype="multipart/form-data">

            <input type="hidden" name="action" value="<?= $is_edit ? 'edit' : 'tambah' ?>">
            <?php if ($is_edit): ?>
                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                <input type="hidden" name="poster_lama" value="<?= htmlspecialchars($event['poster'] ?? '') ?>">
                <input type="hidden" id="sertifikat_template_lama_edit" name="sertifikat_template_lama"
                    value="<?= htmlspecialchars($event['sertifikat_template'] ?? '') ?>">

                <input type="hidden" id="sertifikat_nama_x_percent_edit" name="sertifikat_nama_x_percent"
                    value="<?= $event['sertifikat_nama_x_percent'] ?? 50 ?>">
                <input type="hidden" id="sertifikat_nama_y_percent_edit" name="sertifikat_nama_y_percent"
                    value="<?= $event['sertifikat_nama_y_percent'] ?? 50 ?>">
                <input type="hidden" id="sertifikat_nomor_x_percent_edit" name="sertifikat_nomor_x_percent"
                    value="<?= $event['sertifikat_nomor_x_percent'] ?? 50 ?>">
                <input type="hidden" id="sertifikat_nomor_y_percent_edit" name="sertifikat_nomor_y_percent"
                    value="<?= $event['sertifikat_nomor_y_percent'] ?? 60 ?>">
                <input type="hidden" id="sertifikat_nama_fs_edit" name="sertifikat_nama_fs"
                    value="<?= $event['sertifikat_nama_fs'] ?? 120 ?>">
                <input type="hidden" id="sertifikat_nomor_fs_edit" name="sertifikat_nomor_fs"
                    value="<?= $event['sertifikat_nomor_fs'] ?? 40 ?>">
                <input type="hidden" id="sertifikat_orientasi_edit" name="sertifikat_orientasi"
                    value="<?= $event['sertifikat_orientasi'] ?? 'portrait' ?>">
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center">
                    <div
                        class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mr-3 font-bold">
                        1</div>
                    <h3 class="text-lg font-bold text-slate-800">Informasi Event</h3>
                </div>

                <div class="p-6 md:p-8 space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Judul Event <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="judul" required value="<?= htmlspecialchars($event['judul'] ?? '') ?>"
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 transition-all placeholder-slate-400">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi Lengkap</label>
                        <textarea name="deskripsi" rows="5"
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 transition-all placeholder-slate-400"><?= htmlspecialchars($event['deskripsi'] ?? '') ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Waktu Mulai <span
                                    class="text-red-500">*</span></label>
                            <input type="datetime-local" name="tanggal_waktu" required
                                value="<?= isset($event['tanggal_waktu']) ? date('Y-m-d\TH:i', strtotime($event['tanggal_waktu'])) : '' ?>"
                                class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Lokasi <span
                                    class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i
                                        class="fas fa-map-marker-alt"></i></span>
                                <input type="text" name="lokasi" required
                                    value="<?= htmlspecialchars($event['lokasi'] ?? '') ?>"
                                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 transition-all">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Visibilitas Event</label>
                        <div class="flex gap-4">
                            <label
                                class="flex items-center cursor-pointer p-3 border rounded-xl hover:bg-slate-50 transition w-full">
                                <input type="radio" name="visibilitas" value="public" class="w-5 h-5 text-blue-600"
                                    <?= ($event['visibilitas'] ?? 'public') == 'public' ? 'checked' : '' ?>>
                                <div class="ml-3">
                                    <span class="block text-sm font-bold text-slate-700">Publik</span>
                                    <span class="block text-xs text-slate-500">Tampil di Landing Page & Semua
                                        Orang</span>
                                </div>
                            </label>
                            <label
                                class="flex items-center cursor-pointer p-3 border rounded-xl hover:bg-slate-50 transition w-full">
                                <input type="radio" name="visibilitas" value="internal" class="w-5 h-5 text-red-600"
                                    <?= ($event['visibilitas'] ?? '') == 'internal' ? 'checked' : '' ?>>
                                <div class="ml-3">
                                    <span class="block text-sm font-bold text-slate-700">Internal Pondok</span>
                                    <span class="block text-xs text-slate-500">Hanya untuk Santri (Hidden di Landing
                                        Page)</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <hr class="border-slate-100 my-4">

                    <div>
                        <label class="flex items-center cursor-pointer mb-4 w-fit">
                            <div class="relative">
                                <input type="checkbox" id="toggle_denda" name="aktifkan_denda" class="sr-only"
                                    <?= (!empty($event['nominal_denda']) && $event['nominal_denda'] > 0) ? 'checked' : '' ?>>
                                <div
                                    class="w-10 h-6 bg-slate-200 rounded-full shadow-inner toggle-bg transition-colors">
                                </div>
                                <div class="dot absolute w-4 h-4 bg-white rounded-full shadow left-1 top-1 transition">
                                </div>
                            </div>
                            <div class="ml-3 text-sm font-semibold text-slate-700">
                                Aktifkan Fitur Check-out & Denda
                            </div>
                        </label>

                        <div id="container_denda"
                            class="hidden bg-red-50 border border-red-100 rounded-xl p-5 space-y-4">
                            <div class="flex items-center gap-2 text-red-800 mb-2">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span class="text-sm font-bold">Pengaturan Batas Waktu</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Batas Waktu
                                        Check-out</label>
                                    <input type="datetime-local" name="jam_selesai"
                                        value="<?= isset($event['jam_selesai']) ? date('Y-m-d\TH:i', strtotime($event['jam_selesai'])) : '' ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-red-500 transition-all bg-white">
                                    <p class="text-[10px] text-slate-500 mt-1">Lewat dari jam ini dianggap terlambat.
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nominal Denda
                                        (Rp)</label>
                                    <div class="relative">
                                        <span
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center text-red-500 font-bold">Rp</span>
                                        <input type="number" name="nominal_denda"
                                            value="<?= $event['nominal_denda'] ?? 0 ?>"
                                            class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-red-500 transition-all bg-white"
                                            placeholder="0">
                                    </div>
                                    <p class="text-[10px] text-slate-500 mt-1">Denda yang harus dibayar peserta.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center">
                    <div
                        class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mr-3 font-bold">
                        2</div>
                    <h3 class="text-lg font-bold text-slate-800">Pengaturan Tiket</h3>
                </div>

                <div class="p-6 md:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tipe Event</label>
                            <select name="tipe_event" id="tipe_event_page"
                                class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-2 focus:ring-amber-500">
                                <option value="gratis" <?= ($event['tipe_event'] ?? '') == 'gratis' ? 'selected' : '' ?>>
                                    Gratis</option>
                                <option value="berbayar" <?= ($event['tipe_event'] ?? '') == 'berbayar' ? 'selected' : '' ?>>Berbayar</option>
                            </select>
                        </div>

                        <div id="container_harga_page"
                            class="<?= ($event['tipe_event'] ?? '') == 'berbayar' ? '' : 'hidden' ?>">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Harga Tiket (Rp)</label>
                            <div class="relative">
                                <span
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 font-bold">Rp</span>
                                <input type="number" name="harga" value="<?= $event['harga'] ?? 0 ?>"
                                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 transition-all"
                                    placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center">
                    <div
                        class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center mr-3 font-bold">
                        3</div>
                    <h3 class="text-lg font-bold text-slate-800">Media & Aset</h3>
                </div>

                <div class="p-6 md:p-8 space-y-8">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Poster Event</label>
                            <p class="text-xs text-slate-500 mb-4">Upload gambar (JPG/PNG). Ukuran disarankan rasio 16:9
                                atau A4.</p>
                        </div>
                        <div class="md:col-span-2">
                            <?php if ($is_edit && !empty($event['poster'])): ?>
                                <div
                                    class="flex items-center gap-4 mb-3 p-3 bg-slate-50 rounded-lg border border-slate-200">
                                    <img src="../assets/img/posters/<?= htmlspecialchars($event['poster']) ?>"
                                        class="h-20 w-auto rounded object-cover shadow-sm">
                                    <div class="text-xs text-slate-500">Poster saat ini terpasang.</div>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="poster" accept="image/*"
                                class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 transition-all">
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Template Sertifikat</label>
                                <p class="text-xs text-slate-500 mt-1">Gunakan gambar kosong (JPG/PNG). Nama & Nomor
                                    akan digenerate otomatis.</p>
                            </div>
                            <?php if ($is_edit): ?>
                                <?php if (!empty($event['sertifikat_template'])): ?>
                                    <span
                                        class="text-xs bg-green-100 text-green-700 px-3 py-1.5 rounded-full font-bold border border-green-200 flex items-center">
                                        <i class="fas fa-check-circle mr-1"></i> Template Aktif
                                    </span>
                                <?php else: ?>
                                    <span
                                        class="text-xs bg-red-100 text-red-700 px-3 py-1.5 rounded-full font-bold border border-red-200 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i> Belum Ada
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <div class="bg-blue-50 p-6 rounded-xl border border-blue-100">
                            <input type="file" name="sertifikat_template" accept="image/*"
                                class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-white file:text-blue-700 hover:file:bg-blue-100 mb-6 transition-all border border-blue-200 rounded-lg bg-white">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Jenis
                                            Font</label>
                                        <select name="sertifikat_font" id="sertifikat_font_edit"
                                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500">
                                            <?php
                                            $font_dir = BASE_PATH . '/assets/fonts/';
                                            $available_fonts = [];
                                            if (is_dir($font_dir)) {
                                                $files = glob($font_dir . "*.{ttf,otf}", GLOB_BRACE);
                                                if ($files) {
                                                    foreach ($files as $file) {
                                                        $filename = basename($file);
                                                        $font_name = pathinfo($filename, PATHINFO_FILENAME);
                                                        $available_fonts[$filename] = $font_name;
                                                    }
                                                }
                                            }
                                            $current_font = $event['sertifikat_font'] ?? 'Poppins-SemiBold.ttf';
                                            if (!empty($available_fonts)):
                                                foreach ($available_fonts as $file => $name):
                                                    $selected = ($current_font == $file) ? 'selected' : '';
                                                    ?>
                                                    <option value="<?= $file ?>" <?= $selected ?>><?= $name ?></option>
                                                    <?php
                                                endforeach;
                                            else:
                                                ?>
                                                <option value="">Default (Poppins)</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Orientasi
                                            Kertas</label>
                                        <select name="sertifikat_orientasi" id="sertifikat_orientasi_edit"
                                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500">
                                            <?php $orientasi = $event['sertifikat_orientasi'] ?? 'portrait'; ?>
                                            <option value="portrait" <?= $orientasi == 'portrait' ? 'selected' : '' ?>>
                                                Portrait (Tegak)</option>
                                            <option value="landscape" <?= $orientasi == 'landscape' ? 'selected' : '' ?>>
                                                Landscape (Mendatar)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nomor
                                            Awal</label>
                                        <input type="number" name="sertifikat_nomor_awal"
                                            value="<?= htmlspecialchars($event['sertifikat_nomor_awal'] ?? '1') ?>"
                                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500"
                                            placeholder="1">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Prefix /
                                            Format</label>
                                        <input type="text" name="sertifikat_prefix"
                                            value="<?= htmlspecialchars($event['sertifikat_prefix'] ?? '') ?>"
                                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500"
                                            placeholder="/SRT/2025">
                                    </div>
                                </div>
                            </div>

                            <?php if ($is_edit): ?>
                                <div class="mt-6 pt-6 border-t border-blue-200">
                                    <button type="button" id="open-visual-editor-btn"
                                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg transition transform hover:-translate-y-0.5 flex items-center justify-center">
                                        <i class="fas fa-magic mr-2"></i> Buka Editor Tata Letak (Drag & Drop)
                                    </button>
                                    <p class="text-center text-xs text-blue-700 mt-2">Atur posisi nama dan nomor secara
                                        visual.</p>
                                </div>
                            <?php else: ?>
                                <div
                                    class="mt-6 p-3 bg-yellow-50 text-yellow-800 text-xs text-center rounded border border-yellow-200">
                                    Simpan event terlebih dahulu untuk membuka Editor Visual.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

            <div
                class="sticky bottom-4 z-10 flex justify-end gap-4 bg-white/90 backdrop-blur p-4 rounded-2xl shadow-2xl border border-slate-200">
                <a href="kelola_event.php"
                    class="px-6 py-3 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition-all">
                    Batal
                </a>
                <button type="submit"
                    class="px-8 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold shadow-lg hover:shadow-indigo-500/30 hover:-translate-y-1 transition-all flex items-center">
                    <i class="fas fa-save mr-2"></i> <?= $is_edit ? 'Simpan Perubahan' : 'Buat Event' ?>
                </button>
            </div>

        </form>
    </div>
</div>

<script>
    // Logic Show/Hide Harga
    document.getElementById('tipe_event_page').addEventListener('change', function () {
        const hargaContainer = document.getElementById('container_harga_page');
        hargaContainer.classList.toggle('hidden', this.value !== 'berbayar');
    });
</script>

<style>
    /* Custom Toggle Switch CSS */
    input:checked~.toggle-bg {
        background-color: #ef4444;
    }

    /* Red color for active */
    input:checked~.dot {
        transform: translateX(100%);
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Logic Toggle Denda
        const toggleDenda = document.getElementById('toggle_denda');
        const containerDenda = document.getElementById('container_denda');

        function cekToggle() {
            if (toggleDenda.checked) {
                containerDenda.classList.remove('hidden');
            } else {
                containerDenda.classList.add('hidden');
                // Reset nilai jika dimatikan (opsional)
                // containerDenda.querySelectorAll('input').forEach(input => input.value = '');
            }
        }

        if (toggleDenda) {
            toggleDenda.addEventListener('change', cekToggle);
            cekToggle(); // Cek status awal (saat edit)
        }
    });
</script>

<?php
if ($is_edit) {
    require_once BASE_PATH . '/admin/templates/modal_visual_editor.php';
}

require_once BASE_PATH . '/admin/templates/footer.php';
?>