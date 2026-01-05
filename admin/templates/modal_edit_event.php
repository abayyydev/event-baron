<?php
// Script kecil untuk scan semua font yang tersedia di folder /assets/fonts/
$font_dir = realpath(__DIR__ . '/../../assets/fonts');
$available_fonts = [];
if ($font_dir && is_dir($font_dir)) {
    $files = scandir($font_dir);
    foreach ($files as $file) {
        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'ttf') {
            $available_fonts[] = $file;
        }
    }
}
?>
<div id="editEventModal"
    class="modal fixed inset-0 flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300">
    <div
        class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto p-6 relative transform -translate-y-10 transition-transform duration-300">

        <button data-close-button
            class="absolute top-3 right-4 text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
        <h2 class="text-xl font-bold mb-4 text-gray-800 text-center">Edit Event</h2>

        <form id="editEventForm" action="crud_event.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="event_id" id="event_id_edit">
            <input type="hidden" name="poster_lama" id="poster_lama_edit">
            <input type="hidden" name="sertifikat_template_lama" id="sertifikat_template_lama_edit">

            <input type="hidden" name="sertifikat_nama_fs" id="sertifikat_nama_fs_edit">
            <input type="hidden" name="sertifikat_nama_y_percent" id="sertifikat_nama_y_percent_edit">
            <input type="hidden" name="sertifikat_nama_x_percent" id="sertifikat_nama_x_percent_edit">
            <input type="hidden" name="sertifikat_nomor_fs" id="sertifikat_nomor_fs_edit">
            <input type="hidden" name="sertifikat_nomor_y_percent" id="sertifikat_nomor_y_percent_edit">
            <input type="hidden" name="sertifikat_nomor_x_percent" id="sertifikat_nomor_x_percent_edit">

            <div class="mb-3">
                <label for="judul_edit" class="block text-sm font-medium text-gray-700 mb-1">Judul Event</label>
                <input type="text" name="judul" id="judul_edit"
                    class="w-full p-2 border border-gray-300 rounded text-sm" required>
            </div>

            <div class="mb-3">
                <label for="deskripsi_edit" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi_edit" rows="3"
                    class="w-full p-2 border border-gray-300 rounded text-sm" required></textarea>
            </div>

            <div class="mb-3">
                <label for="poster_edit" class="block text-sm font-medium text-gray-700 mb-1">Ganti Poster
                    (Opsional)</label>
                <input type="file" name="poster" id="poster_edit"
                    class="w-full p-2 border border-gray-300 rounded text-sm" accept="image/*">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="mb-3">
                    <label for="tanggal_waktu_edit" class="block text-sm font-medium text-gray-700 mb-1">Tanggal &
                        Waktu</label>
                    <input type="datetime-local" name="tanggal_waktu" id="tanggal_waktu_edit"
                        class="w-full p-2 border border-gray-300 rounded text-sm" required>
                </div>
                <div class="mb-3">
                    <label for="lokasi_edit" class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                    <input type="text" name="lokasi" id="lokasi_edit"
                        class="w-full p-2 border border-gray-300 rounded text-sm" required>
                </div>
            </div>

            <!-- Tambahan: Tipe Event & Harga Tiket -->
            <div class="mb-3">
                <label for="tipe_event_edit" class="block text-sm font-medium text-gray-700 mb-1">Tipe Event</label>
                <select id="tipe_event_edit" name="tipe_event" class="w-full p-2 border border-gray-300 rounded text-sm"
                    required>
                    <option value="gratis">Gratis</option>
                    <option value="berbayar">Berbayar</option>
                </select>
            </div>

            <div class="mb-3" id="container_harga_edit" style="display:none;">
                <label for="harga_edit" class="block text-sm font-medium text-gray-700 mb-1">Harga Tiket (Rp)</label>
                <input type="number" id="harga_edit" name="harga"
                    class="w-full p-2 border border-gray-300 rounded text-sm" placeholder="Contoh: 50000">
            </div>

            <div class="border-t border-gray-200 pt-4 mt-4">
                <h3 class="text-base font-medium text-gray-900 mb-2 text-center">Pengaturan Sertifikat</h3>
                <div class="mb-3">
                    <label for="sertifikat_template_edit"
                        class="block text-sm font-medium text-gray-700 mb-1">Upload/Ganti Template (A4)</label>
                    <input type="file" name="sertifikat_template" id="sertifikat_template_edit"
                        class="w-full p-2 border border-gray-300 rounded text-sm" accept="image/jpeg, image/png">
                    <div id="status_template_edit" class="text-xs text-gray-500 mt-1"></div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-3">
                        <label for="sertifikat_nomor_awal_edit" class="block text-sm font-medium text-gray-700 mb-1">No.
                            Awal Sertifikat</label>
                        <input type="number" name="sertifikat_nomor_awal" id="sertifikat_nomor_awal_edit"
                            class="w-full p-2 border border-gray-300 rounded text-sm" placeholder="Contoh: 1" value="1">
                    </div>
                    <div class="mb-3">
                        <label for="sertifikat_font_edit" class="block text-sm font-medium text-gray-700 mb-1">Pilih
                            Font</label>
                        <select name="sertifikat_font" id="sertifikat_font_edit"
                            class="w-full p-2 border border-gray-300 rounded text-sm">
                            <?php foreach ($available_fonts as $font): ?>
                                <option value="<?= htmlspecialchars($font) ?>"><?= htmlspecialchars($font) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="sertifikat_prefix_edit" class="block text-sm font-medium text-gray-700 mb-1">Teks
                        Lanjutan (Setelah Nomor)</label>
                    <input type="text" name="sertifikat_prefix" id="sertifikat_prefix_edit"
                        class="w-full p-2 border border-gray-300 rounded text-sm" placeholder="Contoh: SRT/BEM/IX/2025">
                    <p class="text-xs text-gray-500 mt-1">Hasil: [Nomor]/[Teks Lanjutan], contoh: 001/SRT/BEM/IX/2025
                    </p>
                </div>

                <div class="mb-3">
                    <label for="sertifikat_orientasi_edit"
                        class="block text-sm font-medium text-gray-700 mb-1">Orientasi Sertifikat</label>
                    <select name="sertifikat_orientasi" id="sertifikat_orientasi_edit"
                        class="w-full p-2 border border-gray-300 rounded text-sm">
                        <option value="portrait">Portrait (Berdiri)</option>
                        <option value="landscape">Landscape (Mendatar)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Posisi Teks Nama & Nomor</label>
                    <button type="button" id="open-visual-editor-btn"
                        class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-medium py-2 px-3 rounded text-sm">
                        Atur Posisi Teks Secara Visual
                    </button>
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="button" data-close-button
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-1.5 px-3 rounded mr-2 text-sm">Batal</button>
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-1.5 px-3 rounded text-sm">Update</button>
            </div>
        </form>

        <script>
            const tipeEventEdit = document.getElementById('tipe_event_edit');
            const containerHargaEdit = document.getElementById('container_harga_edit');
            const hargaEdit = document.getElementById('harga_edit');

            tipeEventEdit.addEventListener('change', function () {
                if (this.value === 'berbayar') {
                    containerHargaEdit.style.display = 'block';
                    hargaEdit.setAttribute('required', 'required');
                } else {
                    containerHargaEdit.style.display = 'none';
                    hargaEdit.removeAttribute('required');
                    hargaEdit.value = '';
                }
            });

            // Saat form dibuka untuk edit, tampilkan harga jika tipe = berbayar
            function setEditEventData(data) {
                document.getElementById('tipe_event_edit').value = data.tipe_event;
                document.getElementById('harga_edit').value = data.harga || '';
                if (data.tipe_event === 'berbayar') {
                    containerHargaEdit.style.display = 'block';
                    hargaEdit.setAttribute('required', 'required');
                } else {
                    containerHargaEdit.style.display = 'none';
                    hargaEdit.removeAttribute('required');
                }
            }
        </script>

    </div>
</div>