<?php
// admin/form_event.php

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/core/koneksi.php';

// Cek Mode (Tambah atau Edit)
$is_edit = isset($_GET['id']);
$page_title = $is_edit ? 'Edit Event' : 'Buat Event Baru';
$current_page = 'kelola_event';
$event = [];

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

<div class="min-h-screen bg-gray-50 font-sans pb-32">
    <div class="bg-emerald-900 pb-20 pt-10 px-4 rounded-b-[3rem] shadow-xl relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-emerald-800 rounded-full opacity-50 blur-3xl">
        </div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-amber-500 rounded-full opacity-20 blur-2xl">
        </div>

        <div
            class="max-w-5xl mx-auto relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <a href="kelola_event.php"
                        class="text-emerald-200 hover:text-white transition-all bg-white/10 hover:bg-white/20 p-2 rounded-full backdrop-blur-sm group">
                        <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                    </a>
                    <span
                        class="text-emerald-200 text-xs font-bold uppercase tracking-widest border border-emerald-700/50 px-2 py-1 rounded-md">Manajemen
                        Event</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight leading-tight">
                    <?= $page_title ?>
                </h1>
                <p class="text-emerald-100/80 mt-2 text-sm md:text-base max-w-xl">Lengkapi formulir di bawah ini.</p>
            </div>
            <?php if ($is_edit): ?>
                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-xl p-3 flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-[10px] text-emerald-200 uppercase font-bold">Status Event</p>
                        <p class="text-white font-semibold text-sm">Sedang Diedit</p>
                    </div>
                    <div
                        class="w-10 h-10 rounded-full bg-amber-500 flex items-center justify-center text-white shadow-lg shadow-amber-500/30">
                        <i class="fas fa-pen"></i>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 -mt-12 relative z-20">
        <form id="eventFormPage" enctype="multipart/form-data" class="space-y-8">
            <input type="hidden" name="action" value="<?= $is_edit ? 'edit' : 'tambah' ?>">

            <?php if ($is_edit): ?>
                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                <input type="hidden" name="poster_lama" value="<?= htmlspecialchars($event['poster'] ?? '') ?>">
                <input type="hidden" id="sertifikat_template_lama_edit" name="sertifikat_template_lama"
                    value="<?= htmlspecialchars($event['sertifikat_template'] ?? '') ?>">
                <input type="hidden" id="sertifikat_nama_x_percent_edit" name="sertifikat_nama_x_percent"
                    value="<?= $event['sertifikat_nama_x_percent'] ?? '50.00' ?>">
                <input type="hidden" id="sertifikat_nama_y_percent_edit" name="sertifikat_nama_y_percent"
                    value="<?= $event['sertifikat_nama_y_percent'] ?? '50.00' ?>">
                <input type="hidden" id="sertifikat_nomor_x_percent_edit" name="sertifikat_nomor_x_percent"
                    value="<?= $event['sertifikat_nomor_x_percent'] ?? '50.00' ?>">
                <input type="hidden" id="sertifikat_nomor_y_percent_edit" name="sertifikat_nomor_y_percent"
                    value="<?= $event['sertifikat_nomor_y_percent'] ?? '60.00' ?>">
                <input type="hidden" id="sertifikat_nama_fs_edit" name="sertifikat_nama_fs"
                    value="<?= $event['sertifikat_nama_fs'] ?? 120 ?>">
                <input type="hidden" id="sertifikat_nomor_fs_edit" name="sertifikat_nomor_fs"
                    value="<?= $event['sertifikat_nomor_fs'] ?? 40 ?>">
                <input type="hidden" id="sertifikat_orientasi_edit" name="sertifikat_orientasi"
                    value="<?= $event['sertifikat_orientasi'] ?? 'portrait' ?>">
            <?php endif; ?>

            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gray-50/50 px-6 py-5 border-b border-gray-100 flex items-center gap-4">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-100 to-emerald-200 text-emerald-700 flex items-center justify-center text-lg font-bold shadow-sm">
                        1</div>
                    <h3 class="text-lg font-bold text-gray-800">Informasi Utama</h3>
                </div>
                <div class="p-6 md:p-8 space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Judul Event <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="judul" required value="<?= htmlspecialchars($event['judul'] ?? '') ?>"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Deskripsi</label>
                        <textarea name="deskripsi" rows="5"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all"><?= htmlspecialchars($event['deskripsi'] ?? '') ?></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Waktu Mulai <span
                                    class="text-red-500">*</span></label>
                            <input type="datetime-local" name="tanggal_waktu" required
                                value="<?= isset($event['tanggal_waktu']) ? date('Y-m-d\TH:i', strtotime($event['tanggal_waktu'])) : '' ?>"
                                class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Lokasi <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="lokasi" required
                                value="<?= htmlspecialchars($event['lokasi'] ?? '') ?>"
                                class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-emerald-500 transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-3">Visibilitas</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="cursor-pointer relative">
                                <input type="radio" name="visibilitas" value="public" class="peer sr-only"
                                    <?= ($event['visibilitas'] ?? 'public') == 'public' ? 'checked' : '' ?>>
                                <div
                                    class="p-4 rounded-xl border-2 border-gray-100 bg-white hover:bg-gray-50 peer-checked:border-emerald-500 peer-checked:bg-emerald-50/30 transition-all">
                                    <span class="block text-sm font-bold text-gray-800">Publik</span>
                                    <span class="block text-xs text-gray-500">Tampil untuk semua orang.</span>
                                </div>
                            </label>
                            <label class="cursor-pointer relative">
                                <input type="radio" name="visibilitas" value="internal" class="peer sr-only"
                                    <?= ($event['visibilitas'] ?? '') == 'internal' ? 'checked' : '' ?>>
                                <div
                                    class="p-4 rounded-xl border-2 border-gray-100 bg-white hover:bg-gray-50 peer-checked:border-amber-500 peer-checked:bg-amber-50/30 transition-all">
                                    <span class="block text-sm font-bold text-gray-800">Internal</span>
                                    <span class="block text-xs text-gray-500">Hanya user login.</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="border-t border-gray-100 my-4"></div>
                    <div>
                        <label class="flex items-center cursor-pointer mb-4 w-fit group">
                            <div class="relative">
                                <input type="checkbox" id="toggle_denda" name="aktifkan_denda" class="sr-only peer"
                                    <?= (!empty($event['nominal_denda']) && $event['nominal_denda'] > 0) ? 'checked' : '' ?>>
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500">
                                </div>
                            </div>
                            <span class="ml-3 text-sm font-bold text-gray-700">Aktifkan Denda</span>
                        </label>
                        <div id="container_denda" class="hidden bg-red-50/50 border border-red-100 rounded-2xl p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Batas Check-out</label>
                                    <input type="datetime-local" name="jam_selesai"
                                        value="<?= isset($event['jam_selesai']) ? date('Y-m-d\TH:i', strtotime($event['jam_selesai'])) : '' ?>"
                                        class="w-full px-4 py-3 rounded-xl border-red-200">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Nominal (Rp)</label>
                                    <input type="number" name="nominal_denda"
                                        value="<?= $event['nominal_denda'] ?? 0 ?>"
                                        class="w-full px-4 py-3 rounded-xl border-red-200" placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gray-50/50 px-6 py-5 border-b border-gray-100 flex items-center gap-4">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-100 to-amber-200 text-amber-700 flex items-center justify-center text-lg font-bold shadow-sm">
                        2</div>
                    <h3 class="text-lg font-bold text-gray-800">Tiket & Harga</h3>
                </div>
                <div class="p-6 md:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Jenis Event</label>
                            <select name="tipe_event" id="tipe_event_page"
                                class="w-full px-4 py-3 rounded-xl border-gray-200 bg-gray-50">
                                <option value="gratis" <?= ($event['tipe_event'] ?? '') == 'gratis' ? 'selected' : '' ?>>
                                    Gratis</option>
                                <option value="berbayar" <?= ($event['tipe_event'] ?? '') == 'berbayar' ? 'selected' : '' ?>>Berbayar</option>
                            </select>
                        </div>
                        <div id="container_harga_page"
                            class="<?= ($event['tipe_event'] ?? '') == 'berbayar' ? '' : 'hidden' ?>">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Harga Tiket (Rp)</label>
                            <input type="number" name="harga" value="<?= $event['harga'] ?? 0 ?>"
                                class="w-full px-4 py-3 rounded-xl border-amber-200 bg-white" placeholder="0">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gray-50/50 px-6 py-5 border-b border-gray-100 flex items-center gap-4">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-100 to-indigo-200 text-indigo-700 flex items-center justify-center text-lg font-bold shadow-sm">
                        3</div>
                    <h3 class="text-lg font-bold text-gray-800">Media & Sertifikat</h3>
                </div>
                <div class="p-6 md:p-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Poster Event</label>
                            <p class="text-xs text-gray-500">Format: JPG/PNG. Maks 2MB.</p>
                        </div>
                        <div class="md:col-span-2">
                            <?php if ($is_edit && !empty($event['poster'])): ?>
                                <div
                                    class="flex items-center gap-4 mb-3 p-3 bg-indigo-50/50 rounded-xl border border-indigo-100">
                                    <img src="../assets/img/posters/<?= htmlspecialchars($event['poster']) ?>"
                                        class="h-20 w-auto rounded-lg object-cover">
                                    <div class="text-xs text-indigo-600 font-medium">Poster aktif.</div>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="poster" accept="image/*"
                                class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:bg-indigo-50 file:text-indigo-700 font-semibold border border-gray-300 rounded-xl">
                        </div>
                    </div>

                    <div class="border-t border-gray-100"></div>

                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <label class="block text-sm font-bold text-gray-700">Template Sertifikat</label>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="bg-indigo-50/50 p-6 rounded-2xl border border-indigo-100">
                                <input type="file" name="sertifikat_template" id="input_sertifikat_img" accept="image/*"
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:bg-white file:text-indigo-700 mb-6 border border-indigo-200 rounded-xl bg-white">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Font</label>
                                        <select name="sertifikat_font" id="sertifikat_font_edit"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm">
                                            <?php
                                            $font_dir = BASE_PATH . '/assets/fonts/';
                                            $available_fonts = [];
                                            if (is_dir($font_dir)) {
                                                $files = glob($font_dir . "*.{ttf,otf}", GLOB_BRACE);
                                                if ($files) {
                                                    foreach ($files as $file) {
                                                        $available_fonts[basename($file)] = pathinfo(basename($file), PATHINFO_FILENAME);
                                                    }
                                                }
                                            }
                                            $current_font = $event['sertifikat_font'] ?? 'Poppins-SemiBold.ttf';
                                            if (!empty($available_fonts)):
                                                foreach ($available_fonts as $file => $name):
                                                    $selected = ($current_font == $file) ? 'selected' : '';
                                                    echo "<option value='$file' $selected>$name</option>";
                                                endforeach;
                                            else:
                                                echo "<option value=''>Default (Poppins)</option>";
                                            endif;
                                            ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nomor
                                            Awal</label>
                                        <input type="number" name="sertifikat_nomor_awal"
                                            value="<?= htmlspecialchars($event['sertifikat_nomor_awal'] ?? '1') ?>"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm">
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Format
                                            Nomor</label>
                                        <input type="text" name="sertifikat_prefix"
                                            value="<?= htmlspecialchars($event['sertifikat_prefix'] ?? '/SRT/2025') ?>"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm">
                                    </div>
                                </div>
                                <?php if ($is_edit): ?>
                                    <button type="button" id="open-visual-editor-btn"
                                        class="mt-6 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition-all text-sm">
                                        <i class="fas fa-edit mr-2"></i> Edit Posisi (Advanced)
                                    </button>
                                <?php endif; ?>
                            </div>

                            <div class="flex flex-col">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Live Preview (A4
                                    Statis)</label>

                                <div class="relative w-full border-2 border-dashed border-gray-300 bg-gray-200 rounded-xl overflow-hidden flex items-center justify-center"
                                    style="height: 300px;" id="preview-outer-container">

                                    <?php
                                    $template_db = $event['sertifikat_template'] ?? '';
                                    $has_template = !empty($template_db);
                                    $img_src = $has_template ? '../assets/img/sertifikat_templates/' . $template_db : '';
                                    ?>

                                    <div id="preview-placeholder"
                                        class="text-center p-4 text-gray-400 <?= $has_template ? 'hidden' : '' ?> absolute z-10">
                                        <i class="fas fa-image text-4xl mb-3 opacity-50"></i>
                                        <p class="text-sm font-medium">Upload template untuk preview</p>
                                    </div>

                                    <div id="preview-scaler"
                                        style="transform-origin: center center; transition: transform 0.2s;">

                                        <div id="cert-virtual-paper"
                                            class="relative shadow-lg bg-white <?= $has_template ? '' : 'hidden' ?>"
                                            style="width: 800px; height: 565px; overflow: hidden;">

                                            <img id="cert-preview-img" src="<?= $img_src ?>"
                                                class="w-full h-full object-contain"
                                                onerror="document.getElementById('cert-virtual-paper').classList.add('hidden'); document.getElementById('preview-placeholder').classList.remove('hidden');">

                                            <div id="preview-label-nama"
                                                class="absolute whitespace-nowrap font-bold text-gray-900 border border-dashed border-blue-500 bg-white/30"
                                                style="transform: translate(-50%, -50%);">
                                                [Nama Peserta]
                                            </div>

                                            <div id="preview-label-nomor"
                                                class="absolute whitespace-nowrap text-gray-700 border border-dashed border-red-500 bg-white/30"
                                                style="transform: translate(-50%, -50%);">
                                                No. 001/SRT/2025
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <p class="text-xs text-gray-400 mt-2 text-center">
                                    *Preview ini di-scale down dari ukuran asli A4. Hasil cetak akan presisi.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fixed bottom-0 inset-x-0 bg-white/80 backdrop-blur-lg border-t border-gray-200 p-4 z-40">
                <div class="max-w-5xl mx-auto flex justify-end gap-4">
                    <a href="kelola_event.php"
                        class="px-6 py-3 rounded-xl border border-gray-300 text-gray-600 font-bold hover:bg-gray-50">Batal</a>
                    <button type="submit"
                        class="px-8 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 text-white font-bold shadow-lg shadow-emerald-500/30">
                        <i class="fas fa-save mr-2"></i> <?= $is_edit ? 'Simpan Perubahan' : 'Publikasikan' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // 1. Logic Tipe Event
    document.getElementById('tipe_event_page').addEventListener('change', function () {
        const div = document.getElementById('container_harga_page');
        this.value === 'berbayar' ? div.classList.remove('hidden') : div.classList.add('hidden');
    });

    // 2. Logic Toggle Denda
    const toggleDenda = document.getElementById('toggle_denda');
    const containerDenda = document.getElementById('container_denda');
    if (toggleDenda) {
        toggleDenda.addEventListener('change', () => {
            toggleDenda.checked ? containerDenda.classList.remove('hidden') : containerDenda.classList.add('hidden');
        });
        if (toggleDenda.checked) containerDenda.classList.remove('hidden');
    }

    // 3. Logic Submit (AJAX) - PERBAIKAN UTAMA
    document.getElementById('eventFormPage').addEventListener('submit', function (e) {
        e.preventDefault();

        // Validasi
        const judul = document.querySelector('input[name="judul"]').value;
        if (!judul) { Swal.fire('Error', 'Judul Event wajib diisi!', 'error'); return; }

        Swal.fire({
            title: 'Menyimpan...',
            text: 'Mohon tunggu sebentar.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const formData = new FormData(this);

        fetch('crud_event.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.text()) // Ambil text dulu, jangan langsung JSON
            .then(text => {
                try {
                    const data = JSON.parse(text); // Coba parse
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => { window.location.href = 'kelola_event.php'; });
                    } else {
                        Swal.fire('Gagal!', data.message, 'error');
                    }
                } catch (err) {
                    console.error("Error Parsing JSON:", text);
                    Swal.fire('Error System', 'Respon server tidak valid. Cek console.', 'error');
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                Swal.fire('Error', 'Gagal menghubungi server.', 'error');
            });
    });

    // 4. Logic Live Preview (Update Posisi)
    // GANTI FUNGSI updateMiniPreview YANG LAMA DENGAN INI

    // REPLACE FUNGSI updateMiniPreview() LAMA DENGAN INI

    // REPLACE FUNGSI updateMiniPreview() LAMA DENGAN INI

    function updateMiniPreview() {
        // Elemen
        const labelNama = document.getElementById('preview-label-nama');
        const labelNomor = document.getElementById('preview-label-nomor');
        const paper = document.getElementById('cert-virtual-paper');
        const scaler = document.getElementById('preview-scaler');
        const outerContainer = document.getElementById('preview-outer-container');

        // Input Values
        const namaX = parseFloat(document.getElementById('sertifikat_nama_x_percent_edit')?.value) || 50;
        const namaY = parseFloat(document.getElementById('sertifikat_nama_y_percent_edit')?.value) || 50;
        const namaFs = parseFloat(document.getElementById('sertifikat_nama_fs_edit')?.value) || 30;

        const nomorX = parseFloat(document.getElementById('sertifikat_nomor_x_percent_edit')?.value) || 50;
        const nomorY = parseFloat(document.getElementById('sertifikat_nomor_y_percent_edit')?.value) || 60;
        const nomorFs = parseFloat(document.getElementById('sertifikat_nomor_fs_edit')?.value) || 20;

        const orientasi = document.getElementById('sertifikat_orientasi_edit')?.value || 'portrait';

        // --- 1. SET UKURAN KERTAS (FIXED PIXEL) ---
        // Ukuran ini HARUS SAMA PERSIS dengan yang ada di Modal Editor (modal_visual_editor.php)
        let baseWidth = 800;
        let baseHeight = 565; // Landscape default

        if (orientasi === 'portrait') {
            baseWidth = 565;
            baseHeight = 800;
        }

        // Terapkan ukuran ke "Kertas Virtual"
        paper.style.width = baseWidth + 'px';
        paper.style.height = baseHeight + 'px';

        // --- 2. HITUNG SCALE FACTOR (ZOOM OUT) ---
        // Kita cek lebar container luar, lalu kita hitung butuh di-shrink berapa persen
        const availableWidth = outerContainer.clientWidth - 40; // Kurangi padding dikit biar aman
        const availableHeight = outerContainer.clientHeight - 20;

        // Cari scale yang paling pas (fit to width atau fit to height)
        const scaleX = availableWidth / baseWidth;
        const scaleY = availableHeight / baseHeight;
        const scale = Math.min(scaleX, scaleY); // Pilih yang paling kecil agar muat

        // Terapkan Zoom ke Scaler
        scaler.style.transform = `scale(${scale})`;

        // --- 3. POSISIKAN TEKS (MENGGUNAKAN PERSEN) ---
        // Karena kertasnya ukurannya SUDAH BESAR (800px) tapi hanya di-zoom out,
        // kita TIDAK PERLU mengecilkan font secara manual pake rumus ribet.
        // Cukup pakai ukuran font asli (misal 47px). Browser yang akan mengecilkannya via transform scale.

        if (labelNama) {
            labelNama.style.left = namaX + '%';
            labelNama.style.top = namaY + '%';
            labelNama.style.fontSize = namaFs + 'px'; // Gunakan ukuran ASLI
        }

        if (labelNomor) {
            labelNomor.style.left = nomorX + '%';
            labelNomor.style.top = nomorY + '%';
            labelNomor.style.fontSize = nomorFs + 'px'; // Gunakan ukuran ASLI
        }
    }

    // Event Listeners
    window.addEventListener('resize', updateMiniPreview);
    document.addEventListener("DOMContentLoaded", () => {
        // Tunggu sebentar agar layout render dulu baru hitung size
        setTimeout(updateMiniPreview, 100);

        const img = document.getElementById('cert-preview-img');
        if (img) img.onload = updateMiniPreview;
    });

    // 5. Logic Image Preview
    const inputImg = document.getElementById('input_sertifikat_img');
    const previewImg = document.getElementById('cert-preview-img');
    const previewBox = document.getElementById('cert-preview-box');
    const placeholder = document.getElementById('preview-placeholder');

    if (inputImg) {
        inputImg.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (evt) {
                    previewImg.src = evt.target.result;
                    previewBox.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Modal Handlers (Jika file modal_visual_editor.php di-include)
    const openBtn = document.getElementById('open-visual-editor-btn');
    const modal = document.getElementById('visualEditorModal');
    const closeBtn = document.getElementById('closeVisualEditor');
    const saveBtn = document.getElementById('saveVisualEditor');

    if (openBtn && modal) {
        openBtn.addEventListener('click', () => {
            modal.classList.remove('hidden');
            if (typeof initVisualEditor === 'function') initVisualEditor();
        });
        closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
        saveBtn.addEventListener('click', () => {
            setTimeout(updateMiniPreview, 100); // Update preview kecil setelah simpan
        });
    }

    // Jalankan update posisi saat halaman diload (untuk mode edit)
    document.addEventListener("DOMContentLoaded", updateMiniPreview);

</script>

<?php
if ($is_edit) {
    // Pastikan file ini ada di path yang benar
    require_once BASE_PATH . '/admin/templates/modal_visual_editor.php';
}
require_once BASE_PATH . '/admin/templates/footer.php';
?>