<style>
    <?php
    // Load font agar terbaca di visual editor
    $font_dir_css = BASE_PATH . '/assets/fonts/';
    if (is_dir($font_dir_css)) {
        $font_files = glob($font_dir_css . "*.{ttf,otf}", GLOB_BRACE);
        if ($font_files) {
            foreach ($font_files as $file) {
                $filename = basename($file);
                $font_family = pathinfo($filename, PATHINFO_FILENAME);
                echo "
                @font-face {
                    font-family: '{$font_family}';
                    src: url('../assets/fonts/{$filename}');
                }
                ";
            }
        }
    }
    ?>

    /* Style untuk elemen draggable */
    .draggable-item {
        position: absolute;
        cursor: move;
        border: 2px dashed transparent;
        padding: 4px 8px;
        white-space: nowrap;
        user-select: none;
        transition: all 0.2s ease;
        line-height: 1;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);

        /* PERBAIKAN PENTING: Set titik tumpu di tengah secara default */
        transform: translate(-50%, -50%);
        transform-origin: center center;
    }

    .draggable-item:hover {
        border-color: rgba(16, 185, 129, 0.5);
        background-color: rgba(16, 185, 129, 0.05);
        /* PERBAIKAN: Gabungkan translate dan scale agar tidak lompat */
        transform: translate(-50%, -50%) scale(1.02);
    }

    .draggable-item.active {
        border-color: #059669;
        background-color: rgba(5, 150, 105, 0.1);
        z-index: 10;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        /* PERBAIKAN: Tetap jaga translate saat active */
        transform: translate(-50%, -50%) scale(1.05);
    }

    #certificate-preview {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        position: relative;
        background-color: #fff;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        /* Default size, akan diupdate JS */
        width: 800px;
        height: 565px;
        border-radius: 8px;
        overflow: hidden;
    }

    /* Custom Range Slider Styling */
    input[type=range] {
        height: 6px;
        border-radius: 5px;
        background: #e2e8f0;
        outline: none;
    }

    input[type=range]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #10b981;
        cursor: pointer;
        transition: background .15s ease-in-out;
    }

    input[type=range]::-webkit-slider-thumb:hover {
        background: #059669;
    }
</style>

<div id="visualEditorModal"
    class="fixed inset-0 flex items-center justify-center z-[999] hidden transition-all duration-300 ease-in-out">

    <div class="absolute inset-0 bg-emerald-950/60 backdrop-blur-sm transition-opacity" id="modalBackdrop"></div>

    <div class="bg-white rounded-3xl shadow-2xl w-[95%] max-w-6xl h-[90vh] flex flex-col relative transform transition-all duration-300 scale-95 opacity-0 border border-white/20"
        id="modalContent">

        <div class="flex justify-between items-center px-8 py-5 border-b border-gray-100 bg-white rounded-t-3xl">
            <div>
                <h2 class="text-xl font-extrabold text-gray-800 flex items-center gap-2 tracking-tight">
                    <span
                        class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white w-8 h-8 rounded-lg flex items-center justify-center text-sm shadow-md">
                        <i class="fas fa-magic"></i>
                    </span>
                    Visual Editor Sertifikat
                </h2>
                <p class="text-xs text-gray-500 mt-1 ml-10">Geser elemen Nama dan Nomor ke posisi yang diinginkan.</p>
            </div>
            <button type="button" id="closeVisualEditor"
                class="w-10 h-10 rounded-full bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors flex items-center justify-center">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="flex-grow flex overflow-hidden">

            <div class="flex-grow bg-slate-100/50 flex items-center justify-center p-8 overflow-auto relative">
                <div class="absolute inset-0 opacity-5 pointer-events-none"
                    style="background-image: radial-gradient(#10b981 1px, transparent 1px); background-size: 20px 20px;">
                </div>

                <div id="certificate-preview">
                    <div id="drag-nama" class="draggable-item text-slate-800 font-bold" data-target="nama">
                        [Nama Peserta]
                    </div>
                    <div id="drag-nomor" class="draggable-item text-slate-600 font-bold" data-target="nomor">
                        001/SRT/2025
                    </div>
                </div>

                <div
                    class="absolute bottom-6 left-6 bg-white/90 backdrop-blur-md px-4 py-2 rounded-xl text-xs font-bold text-gray-600 shadow-lg border border-white/50 flex items-center gap-2">
                    <i class="fas fa-ruler-combined text-emerald-500"></i> Mode: Persentase (Responsif)
                </div>
            </div>

            <div class="w-80 bg-white border-l border-gray-100 flex flex-col shadow-xl z-10">
                <div class="p-6 space-y-6 overflow-y-auto flex-grow scrollbar-thin">

                    <div
                        class="bg-slate-50 p-5 rounded-2xl border border-slate-100 hover:border-emerald-200 transition-colors group">
                        <label
                            class="flex items-center gap-2 text-sm font-bold text-slate-700 pb-2 border-b border-slate-200 mb-4">
                            <span
                                class="w-6 h-6 rounded bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">
                                <i class="fas fa-user"></i>
                            </span>
                            Nama Peserta
                        </label>
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="text-xs text-gray-500 font-bold uppercase tracking-wider">Ukuran
                                    Font</label>
                                <span
                                    class="text-xs text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded">px</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <input type="range" id="input-fs-nama-range" min="10" max="200"
                                    class="flex-grow accent-emerald-500">
                                <input type="number" id="input-fs-nama"
                                    class="w-16 p-2 text-center border border-gray-200 bg-white rounded-lg text-sm font-bold text-gray-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                                    value="40">
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-slate-50 p-5 rounded-2xl border border-slate-100 hover:border-amber-200 transition-colors group">
                        <label
                            class="flex items-center gap-2 text-sm font-bold text-slate-700 pb-2 border-b border-slate-200 mb-4">
                            <span
                                class="w-6 h-6 rounded bg-amber-100 text-amber-600 flex items-center justify-center text-xs">
                                <i class="fas fa-id-card"></i>
                            </span>
                            Nomor Sertifikat
                        </label>
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="text-xs text-gray-500 font-bold uppercase tracking-wider">Ukuran
                                    Font</label>
                                <span class="text-xs text-amber-600 font-bold bg-amber-50 px-2 py-0.5 rounded">px</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <input type="range" id="input-fs-nomor-range" min="10" max="200"
                                    class="flex-grow accent-amber-500">
                                <input type="number" id="input-fs-nomor"
                                    class="w-16 p-2 text-center border border-gray-200 bg-white rounded-lg text-sm font-bold text-gray-700 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"
                                    value="20">
                            </div>
                        </div>
                    </div>

                    <div class="px-2">
                        <label
                            class="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">
                            Font Terpilih
                        </label>
                        <div id="current-font-display"
                            class="px-4 py-3 bg-indigo-50/50 rounded-xl border border-indigo-100 text-sm text-indigo-900 font-medium truncate flex items-center justify-between">
                            <span>Default</span>
                            <i class="fas fa-lock text-xs text-indigo-300"></i>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-2 leading-relaxed">
                            *Jenis font mengikuti pengaturan di halaman form utama.
                        </p>
                    </div>

                </div>

                <div class="p-6 border-t border-gray-100 bg-gray-50">
                    <button id="btn-save-visual"
                        class="w-full bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-emerald-500/30 transform hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-check-circle"></i> Simpan Posisi
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // --- 1. VARIABEL & SELEKTOR ---
        const modal = document.getElementById('visualEditorModal');
        const modalContent = document.getElementById('modalContent');
        const openBtn = document.getElementById('open-visual-editor-btn');
        const closeBtn = document.getElementById('closeVisualEditor');
        const saveBtn = document.getElementById('btn-save-visual');

        const previewContainer = document.getElementById('certificate-preview');
        const dragNama = document.getElementById('drag-nama');
        const dragNomor = document.getElementById('drag-nomor');

        // Inputs di Sidebar Modal
        const inputFsNama = document.getElementById('input-fs-nama');
        const inputFsNamaRange = document.getElementById('input-fs-nama-range');
        const inputFsNomor = document.getElementById('input-fs-nomor');
        const inputFsNomorRange = document.getElementById('input-fs-nomor-range');
        const fontDisplay = document.getElementById('current-font-display').querySelector('span');

        // Referensi Input Hidden di Form Utama
        const mainFormInputs = {
            xNama: document.getElementById('sertifikat_nama_x_percent_edit'),
            yNama: document.getElementById('sertifikat_nama_y_percent_edit'),
            fsNama: document.getElementById('sertifikat_nama_fs_edit'),
            xNomor: document.getElementById('sertifikat_nomor_x_percent_edit'),
            yNomor: document.getElementById('sertifikat_nomor_y_percent_edit'),
            fsNomor: document.getElementById('sertifikat_nomor_fs_edit'),
            font: document.getElementById('sertifikat_font_edit'),
            orientasi: document.getElementById('sertifikat_orientasi_edit'),
            templateLama: document.getElementById('sertifikat_template_lama_edit'),
            fileInput: document.querySelector('input[name="sertifikat_template"]')
        };

        // --- 2. FUNGSI BUKA/TUTUP MODAL ---

        function openModal() {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
            initVisualData();
        }

        function closeModal() {
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);

        modal.addEventListener('click', (e) => {
            if (e.target === document.getElementById('modalBackdrop')) closeModal();
        });

        // --- 3. FUNGSI INITIALISASI DATA ---
        function initVisualData() {
            // A. Orientasi
            const orientasi = mainFormInputs.orientasi ? mainFormInputs.orientasi.value : 'portrait';
            if (orientasi === 'landscape') {
                previewContainer.style.width = '800px';
                previewContainer.style.height = '565px';
            } else {
                previewContainer.style.width = '565px';
                previewContainer.style.height = '800px';
            }

            // B. Load Background
            const file = mainFormInputs.fileInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => { previewContainer.style.backgroundImage = `url('${e.target.result}')`; };
                reader.readAsDataURL(file);
            } else if (mainFormInputs.templateLama && mainFormInputs.templateLama.value) {
                // Perbaiki path di sini jika perlu (menggunakan ../)
                previewContainer.style.backgroundImage = `url('../assets/img/sertifikat_templates/${mainFormInputs.templateLama.value}')`;
            } else {
                previewContainer.style.backgroundColor = "#f1f5f9";
                previewContainer.style.backgroundImage = "none";
            }

            // C. Load Posisi
            applyStyle(dragNama, mainFormInputs.xNama.value, mainFormInputs.yNama.value, mainFormInputs.fsNama.value);
            inputFsNama.value = mainFormInputs.fsNama.value;
            inputFsNamaRange.value = mainFormInputs.fsNama.value;

            applyStyle(dragNomor, mainFormInputs.xNomor.value, mainFormInputs.yNomor.value, mainFormInputs.fsNomor.value);
            inputFsNomor.value = mainFormInputs.fsNomor.value;
            inputFsNomorRange.value = mainFormInputs.fsNomor.value;

            // D. Load Font
            if (mainFormInputs.font) {
                const selectedOpt = mainFormInputs.font.options[mainFormInputs.font.selectedIndex];
                const fontName = selectedOpt ? selectedOpt.text : 'Default';
                const fontVal = mainFormInputs.font.value;
                fontDisplay.innerText = fontName;
                const cssFontName = fontVal.replace(/\.[^/.]+$/, "");
                dragNama.style.fontFamily = cssFontName;
                dragNomor.style.fontFamily = cssFontName;
            }
        }

        function applyStyle(el, x, y, fs) {
            el.style.left = x + '%';
            el.style.top = y + '%';
            el.style.fontSize = fs + 'px';
            // Transform sudah dihandle di CSS, tapi untuk inisialisasi awal via JS tidak masalah
            // CSS punya prioritas saat hover/active
        }

        // --- 4. LOGIKA DRAG & DROP ---
        let activeDragItem = null;

        [dragNama, dragNomor].forEach(item => {
            item.addEventListener('mousedown', dragStart);
        });

        function dragStart(e) {
            activeDragItem = this;
            activeDragItem.classList.add('active');
            document.addEventListener('mousemove', dragMove);
            document.addEventListener('mouseup', dragEnd);
        }

        function dragMove(e) {
            if (!activeDragItem) return;
            e.preventDefault();
            const containerRect = previewContainer.getBoundingClientRect();
            let cursorX = e.clientX - containerRect.left;
            let cursorY = e.clientY - containerRect.top;

            // Batasi area drag
            cursorX = Math.max(0, Math.min(cursorX, containerRect.width));
            cursorY = Math.max(0, Math.min(cursorY, containerRect.height));

            const percentX = (cursorX / containerRect.width) * 100;
            const percentY = (cursorY / containerRect.height) * 100;

            activeDragItem.style.left = percentX + '%';
            activeDragItem.style.top = percentY + '%';
        }

        function dragEnd() {
            if (activeDragItem) {
                activeDragItem.classList.remove('active');
                activeDragItem = null;
            }
            document.removeEventListener('mousemove', dragMove);
            document.removeEventListener('mouseup', dragEnd);
        }

        // --- 5. LOGIKA RESIZE FONT ---
        function syncFontInput(range, number, element) {
            range.addEventListener('input', () => {
                number.value = range.value;
                element.style.fontSize = range.value + 'px';
            });
            number.addEventListener('input', () => {
                range.value = number.value;
                element.style.fontSize = number.value + 'px';
            });
        }
        syncFontInput(inputFsNamaRange, inputFsNama, dragNama);
        syncFontInput(inputFsNomorRange, inputFsNomor, dragNomor);

        // --- 6. SIMPAN DATA ---
        saveBtn.addEventListener('click', function () {
            // A. Simpan ke Input Hidden Form Utama
            mainFormInputs.xNama.value = parseFloat(dragNama.style.left);
            mainFormInputs.yNama.value = parseFloat(dragNama.style.top);
            mainFormInputs.fsNama.value = inputFsNama.value;

            mainFormInputs.xNomor.value = parseFloat(dragNomor.style.left);
            mainFormInputs.yNomor.value = parseFloat(dragNomor.style.top);
            mainFormInputs.fsNomor.value = inputFsNomor.value;

            // B. TRIGGER UPDATE MINI PREVIEW DI FORM UTAMA
            if (typeof window.updateMiniPreview === 'function') {
                window.updateMiniPreview();
            }

            closeModal();

            // C. Notifikasi
            Swal.fire({
                icon: 'success',
                title: 'Posisi Disimpan!',
                text: 'Preview halaman utama telah diperbarui.',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false,
                toast: true,
                position: 'top-end',
                background: '#ffffff',
                iconColor: '#10b981'
            });
        });
    });
</script>