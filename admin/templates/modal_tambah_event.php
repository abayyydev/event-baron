<div id="addEventModal"
    class="modal fixed inset-0 flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300">
    <div
        class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto p-6 relative transform -translate-y-10 transition-transform duration-300">

        <button data-close-button
            class="absolute top-3 right-4 text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
        <h2 class="text-xl font-bold mb-4 text-gray-800 text-center">Formulir Event Baru</h2>

        <form id="addEventForm" action="crud_event.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="tambah">

            <div class="mb-3">
                <label for="judul_add" class="block text-sm font-medium text-gray-700 mb-1">Judul Event</label>
                <input type="text" name="judul" id="judul_add" class="w-full p-2 border border-gray-300 rounded text-sm"
                    required>
            </div>

            <div class="mb-3">
                <label for="deskripsi_add" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi_add" rows="3"
                    class="w-full p-2 border border-gray-300 rounded text-sm" required></textarea>
            </div>

            <div class="mb-3">
                <label for="poster_add" class="block text-sm font-medium text-gray-700 mb-1">Poster Event</label>
                <input type="file" name="poster" id="poster_add"
                    class="w-full p-2 border border-gray-300 rounded text-sm" accept="image/*" required>
            </div>

            <div class="mb-3">
                <label for="tanggal_waktu_add" class="block text-sm font-medium text-gray-700 mb-1">Tanggal &
                    Waktu</label>
                <input type="datetime-local" name="tanggal_waktu" id="tanggal_waktu_add"
                    class="w-full p-2 border border-gray-300 rounded text-sm" required>
            </div>

            <div class="mb-3">
                <label for="lokasi_add" class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                <input type="text" name="lokasi" id="lokasi_add"
                    class="w-full p-2 border border-gray-300 rounded text-sm" required>
            </div>

            <!-- Tambahan: Tipe Event dan Harga -->
            <div class="mb-3">
                <label for="tipe_event" class="block text-sm font-medium text-gray-700 mb-1">Tipe Event</label>
                <select id="tipe_event" name="tipe_event" class="w-full p-2 border border-gray-300 rounded text-sm"
                    required>
                    <option value="gratis">Gratis</option>
                    <option value="berbayar">Berbayar</option>
                </select>
            </div>

            <div class="mb-3" id="container_harga" style="display:none;">
                <label for="harga" class="block text-sm font-medium text-gray-700 mb-1">Harga Tiket (Rp)</label>
                <input type="number" id="harga" name="harga" class="w-full p-2 border border-gray-300 rounded text-sm"
                    placeholder="Contoh: 50000">
            </div>

            <div class="border-t border-gray-200 pt-4 mt-4">
                <h3 class="text-base font-medium text-gray-900 mb-2 text-center">Pengaturan Sertifikat (Opsional)</h3>
                <div class="mb-3">
                    <label for="sertifikat_template_add" class="block text-sm font-medium text-gray-700 mb-1">Upload
                        Template (A4)</label>
                    <input type="file" name="sertifikat_template" id="sertifikat_template_add"
                        class="w-full p-2 border border-gray-300 rounded text-sm" accept="image/jpeg, image/png">
                    <p class="text-xs text-gray-500 mt-1">Bisa diatur nanti saat Edit Event.</p>
                </div>
                <div class="mb-3">
                    <label for="sertifikat_prefix_add" class="block text-sm font-medium text-gray-700 mb-1">Awalan Nomor
                        Sertifikat</label>
                    <input type="text" name="sertifikat_prefix" id="sertifikat_prefix_add"
                        class="w-full p-2 border border-gray-300 rounded text-sm" placeholder="Contoh: 001/EVT/IX/2025">
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="button" data-close-button
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-1.5 px-3 rounded mr-2 text-sm">Batal</button>
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-1.5 px-3 rounded text-sm">Simpan</button>
            </div>
        </form>

        <script>
            document.getElementById('tipe_event').addEventListener('change', function () {
                const containerHarga = document.getElementById('container_harga');
                if (this.value === 'berbayar') {
                    containerHarga.style.display = 'block';
                    document.getElementById('harga').setAttribute('required', 'required');
                } else {
                    containerHarga.style.display = 'none';
                    document.getElementById('harga').removeAttribute('required');
                }
            });
        </script>

    </div>
</div>

<div id="overlay"
    class="overlay fixed inset-0 bg-black z-40 opacity-0 pointer-events-none transition-opacity duration-300"></div>

<style>
    .modal.active {
        opacity: 1;
        pointer-events: auto;
    }

    .modal.active .transform {
        transform: translateY(0);
    }

    .overlay.active {
        opacity: 0.5;
        pointer-events: auto;
    }
</style>