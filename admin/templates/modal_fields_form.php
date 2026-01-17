<!-- Modal Overlay -->
<div id="modalOverlay" class="fixed inset-0 bg-emerald-950/40 z-40 hidden transition-opacity duration-300 backdrop-blur-sm">
</div>

<!-- Modal Container -->
<div id="fieldModal" class="fixed inset-0 flex items-center justify-center z-50 hidden pointer-events-none">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all scale-95 opacity-0 pointer-events-auto border border-white/20"
        id="modalContent">

        <!-- Modal Header -->
        <div class="px-8 py-5 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-10">
            <div>
                <h2 class="text-xl font-extrabold text-gray-800 tracking-tight" id="modalTitle">Tambah Pertanyaan</h2>
                <p class="text-xs text-gray-500 mt-0.5">Konfigurasi field form pendaftaran</p>
            </div>
            <button type="button" onclick="closeModal()" class="w-8 h-8 rounded-full bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors flex items-center justify-center">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Modal Body / Form -->
        <form id="fieldForm" class="p-8 space-y-6">
            <input type="hidden" name="action" id="formAction" value="tambah">
            <input type="hidden" name="workshop_id" id="formWorkshopId">
            <input type="hidden" name="field_id" id="formFieldId">

            <!-- Hidden input untuk menampung gabungan opsi -->
            <input type="hidden" name="options" id="finalOptionsInput">

            <!-- Label Input -->
            <div class="space-y-2">
                <label class="block text-sm font-bold text-gray-700">
                    Pertanyaan / Label <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="text" name="label" id="formLabel" placeholder="Contoh: Ukuran Baju"
                        class="w-full pl-4 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all bg-gray-50 focus:bg-white text-gray-800 placeholder-gray-400"
                        required>
                </div>
            </div>

            <!-- Tipe Jawaban -->
            <div class="space-y-2">
                <label class="block text-sm font-bold text-gray-700">
                    Tipe Jawaban <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select name="field_type" id="formFieldType"
                        class="w-full pl-4 pr-10 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all bg-gray-50 focus:bg-white text-gray-800 appearance-none cursor-pointer">
                        <option value="text">Teks Singkat</option>
                        <option value="textarea">Teks Panjang (Paragraf)</option>
                        <option value="email">Email</option>
                        <option value="tel">Nomor Telepon</option>
                        <option value="select">Pilihan Ganda (Dropdown)</option>
                        <option value="radio">Pilihan (Radio Button)</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-emerald-500">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- Container Opsi Dinamis -->
            <div id="optionsContainer" class="hidden bg-emerald-50/50 p-5 rounded-2xl border border-emerald-100 animate-fadeIn">
                <div class="flex items-center justify-between mb-3">
                    <label class="block text-sm font-bold text-emerald-800">Daftar Pilihan Jawaban</label>
                    <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-md font-bold uppercase tracking-wider">Opsi</span>
                </div>

                <div id="dynamicOptionsList" class="space-y-2 mb-4">
                    <!-- Item akan ditambahkan via JS -->
                </div>

                <button type="button" onclick="addOptionRow()"
                    class="w-full py-2.5 rounded-xl border-2 border-dashed border-emerald-300 text-emerald-600 hover:bg-emerald-50 hover:border-emerald-400 hover:text-emerald-700 transition-all text-sm font-bold flex items-center justify-center gap-2">
                    <i class="fas fa-plus-circle"></i> Tambah Pilihan Lain
                </button>
            </div>

            <!-- Placeholder -->
            <div class="space-y-2">
                <label class="block text-sm font-bold text-gray-700">
                    Placeholder / Bantuan <span class="text-gray-400 font-normal text-xs">(Opsional)</span>
                </label>
                <input type="text" name="placeholder" id="formPlaceholder" placeholder="Teks bantuan di dalam input..."
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all bg-gray-50 focus:bg-white text-gray-800 placeholder-gray-400">
            </div>

            <!-- Required Checkbox -->
            <div class="flex items-center p-4 bg-gray-50 rounded-xl border border-gray-100 cursor-pointer hover:bg-gray-100 transition-colors" onclick="document.getElementById('formIsRequired').click()">
                <div class="flex items-center h-5">
                    <input type="checkbox" name="is_required" id="formIsRequired" value="1"
                        class="w-5 h-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 cursor-pointer accent-emerald-600">
                </div>
                <div class="ml-3 text-sm">
                    <label for="formIsRequired" class="font-bold text-gray-700 cursor-pointer">Wajib Diisi</label>
                    <p class="text-xs text-gray-500">Peserta tidak bisa melanjutkan jika field ini kosong.</p>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="pt-6 flex justify-end gap-3 border-t border-gray-100">
                <button type="button" onclick="closeModal()"
                    class="px-6 py-3 bg-white border border-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-50 hover:text-gray-800 transition-all text-sm">
                    Batal
                </button>
                <button type="submit"
                    class="px-8 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 transition-all transform hover:-translate-y-0.5 text-sm flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out forwards;
    }
</style>