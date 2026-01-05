<div id="fieldModal"
    class="modal fixed inset-0 flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300">
    <div
        class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto p-6 relative transform -translate-y-10 transition-transform duration-300">
        <button data-close-button
            class="absolute top-3 right-4 text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
        <h2 class="text-xl font-bold mb-4 text-gray-800 text-center" id="modalTitle">Formulir Pertanyaan</h2>

        <form id="fieldForm" method="POST">
            <input type="hidden" name="action" id="formAction">
            <input type="hidden" name="workshop_id" id="formWorkshopId">
            <input type="hidden" name="field_id" id="formFieldId">

            <div class="mb-3">
                <label for="formLabel" class="block text-sm font-medium text-gray-700 mb-1">Label Pertanyaan</label>
                <input type="text" name="label" id="formLabel" placeholder="Contoh: Ukuran Kaos"
                    class="w-full p-2 border border-gray-300 rounded text-sm" required>
            </div>

            <div class="mb-3">
                <label for="formFieldType" class="block text-sm font-medium text-gray-700 mb-1">Tipe Field</label>
                <select name="field_type" id="formFieldType" class="w-full p-2 border border-gray-300 rounded text-sm">
                    <option value="text">Teks Singkat</option>
                    <option value="email">Email</option>
                    <option value="tel">Nomor Telepon</option>
                    <option value="textarea">Teks Panjang</option>
                    <option value="select">Pilihan (Dropdown)</option>
                    <option value="radio">Pilihan (Radio Button)</option>
                </select>
            </div>

            <div class="mb-3 hidden" id="formOptionsContainer">
                <label for="formOptions" class="block text-sm font-medium text-gray-700 mb-1">Pilihan Jawaban</label>
                <textarea name="options" id="formOptions"
                    placeholder="Pisahkan setiap pilihan dengan koma. Contoh: S,M,L,XL"
                    class="w-full p-2 border border-gray-300 rounded text-sm"></textarea>
            </div>

            <div class="mb-3">
                <label for="formPlaceholder" class="block text-sm font-medium text-gray-700 mb-1">Placeholder
                    (Opsional)</label>
                <input type="text" name="placeholder" id="formPlaceholder" placeholder="Teks bantuan di dalam input"
                    class="w-full p-2 border border-gray-300 rounded text-sm">
            </div>

            <div class="mb-4">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="is_required" id="formIsRequired" value="1" checked
                        class="form-checkbox h-4 w-4 text-indigo-600">
                    <span class="text-sm text-gray-700">Wajib diisi</span>
                </label>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="button" data-close-button
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-1.5 px-3 rounded mr-2 text-sm">Batal</button>
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-1.5 px-3 rounded text-sm">Simpan</button>
            </div>
        </form>
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

    .hidden {
        display: none;
    }
</style>