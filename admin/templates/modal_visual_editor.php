<style>
    <?php
    $font_dir_css = BASE_PATH . '/assets/fonts/';
    if (is_dir($font_dir_css)) {
        $font_files = glob($font_dir_css . "*.{ttf,otf}", GLOB_BRACE);
        if ($font_files) {
            foreach ($font_files as $file) {
                $filename = basename($file); // misal: GreatVibes.ttf
                $font_family = pathinfo($filename, PATHINFO_FILENAME); // misal: GreatVibes
                // Buat rule CSS
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
</style>


<div id="visualEditorModal"
    class="modal fixed inset-0 flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300">
    <div
        class="bg-gray-100 rounded-lg shadow-xl w-11/12 max-w-4xl h-5/6 flex flex-col p-4 relative transform -translate-y-10 transition-transform duration-300">

        <button data-close-button
            class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 text-2xl z-20">&times;</button>
        <h2 class="text-xl font-bold mb-2 text-gray-800 text-center">Visual Certificate Editor</h2>

        <div class="flex-grow flex space-x-4 overflow-hidden">
            <div class="w-3/4 h-full flex items-center justify-center bg-gray-300 overflow-hidden rounded">
                <div id="certificate-preview" class="relative shadow-lg"
                    style="width: 800px; height: 565px; background-size: contain; background-repeat: no-repeat; background-position: center;">
                    <div id="drag-nama"
                        class="draggable absolute cursor-move border border-dashed border-blue-500 p-2 whitespace-nowrap"
                        style="left: 100px; top: 250px;">[Nama Peserta]</div>
                    <div id="drag-nomor"
                        class="draggable absolute cursor-move border border-dashed border-red-500 p-2 whitespace-nowrap"
                        style="left: 100px; top: 350px;">[Nomor Sertifikat]</div>
                </div>
            </div>

            <div class="w-1/4 h-full bg-white p-4 rounded shadow">
                <h3 class="font-semibold mb-4">Pengaturan Teks</h3>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-blue-700">Nama Peserta</label>
                    <div class="flex items-center space-x-2 mt-1">
                        <label for="fs-nama" class="text-xs">Size:</label>
                        <input type="number" id="fs-nama" class="w-full p-1 border border-gray-300 rounded text-sm"
                            value="120">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-red-700">Nomor Sertifikat</label>
                    <div class="flex items-center space-x-2 mt-1">
                        <label for="fs-nomor" class="text-xs">Size:</label>
                        <input type="number" id="fs-nomor" class="w-full p-1 border border-gray-300 rounded text-sm"
                            value="40">
                    </div>
                </div>

                <div class="absolute bottom-4 right-4 w-1/4 pr-8">
                    <button id="save-positions-btn"
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                        Simpan Posisi & Ukuran
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #drag-nama,
    #drag-nomor {
        background-color: rgba(255, 255, 255, 0.6);
        font-weight: bold;
        color: black;
    }
</style>