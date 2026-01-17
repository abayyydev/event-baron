<?php
$page_title = 'Kelola Form Pendaftaran';
$current_page = 'kelola_event';
require_once '../admin/templates/header.php';
require_once '../core/koneksi.php';

// Pastikan ada parameter ID di URL
if (!isset($_GET['id'])) {
    die("Workshop ID tidak ditemukan di URL!");
}
$workshop_id = $_GET['id'];

// Ambil data workshop utama
$stmt_workshop = $pdo->prepare("SELECT * FROM workshops WHERE id = ?");
$stmt_workshop->execute([$workshop_id]);
$workshop = $stmt_workshop->fetch(PDO::FETCH_ASSOC);

if (!$workshop) {
    die("Workshop tidak ditemukan!");
}

// Ambil semua form fields yang terkait dengan workshop ini
$stmt_fields = $pdo->prepare("SELECT * FROM form_fields WHERE workshop_id = ? ORDER BY urutan ASC, id ASC");
$stmt_fields->execute([$workshop_id]);
$fields = $stmt_fields->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="min-h-screen bg-gray-50 font-sans pb-32">
    
    <!-- Hero Header Section -->
    <div class="bg-emerald-900 pb-20 pt-10 px-4 rounded-b-[3rem] shadow-xl relative overflow-hidden">
        <!-- Elemen Dekoratif Background -->
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-emerald-800 rounded-full opacity-50 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-amber-500 rounded-full opacity-20 blur-2xl"></div>

        <div class="max-w-6xl mx-auto relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <a href="kelola_event.php" class="text-emerald-200 hover:text-white transition-all bg-white/10 hover:bg-white/20 p-2 rounded-full backdrop-blur-sm group">
                        <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                    </a>
                    <span class="text-emerald-200 text-xs font-bold uppercase tracking-widest border border-emerald-700/50 px-2 py-1 rounded-md">Manajemen Form</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight leading-tight">
                    Kelola Form Pendaftaran
                </h1>
                <p class="text-emerald-100/80 mt-2 text-sm md:text-base max-w-xl">
                    Atur pertanyaan dan isian data yang dibutuhkan untuk event: <span class="font-bold text-white"><?= htmlspecialchars($workshop['judul']) ?></span>
                </p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <button type="button" id="btnTambahField" data-workshop-id="<?= $workshop['id'] ?>"
                    class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-amber-500/30 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Pertanyaan
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content Container (Naik ke atas menutupi header) -->
    <div class="max-w-6xl mx-auto px-4 -mt-12 relative z-20">
        
        <!-- Info Card -->
        <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-6 mb-8 flex flex-col md:flex-row items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-100 to-indigo-200 text-indigo-600 flex items-center justify-center text-xl shadow-sm flex-shrink-0">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-bold text-gray-800">Preview Event</h3>
                <div class="flex flex-wrap gap-4 mt-1 text-sm text-gray-600">
                    <span class="flex items-center"><i class="fas fa-calendar-alt mr-2 text-amber-500"></i> <?= date('d M Y, H:i', strtotime($workshop['tanggal_waktu'])) ?></span>
                    <span class="flex items-center"><i class="fas fa-map-marker-alt mr-2 text-emerald-500"></i> <?= htmlspecialchars($workshop['lokasi']) ?></span>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="bg-gray-50/50 px-6 py-5 border-b border-gray-100">
                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-list-ul text-emerald-600"></i> Daftar Pertanyaan
                </h3>
            </div>

            <div class="divide-y divide-gray-100">
                <?php if (empty($fields)): ?>
                        <div class="p-12 text-center">
                            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-gray-200">
                                <i class="fas fa-clipboard-question text-3xl text-gray-300"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800">Belum Ada Pertanyaan</h3>
                            <p class="text-gray-500 text-sm mt-2 max-w-sm mx-auto">
                                Form pendaftaran masih kosong. Mulai tambahkan pertanyaan untuk calon peserta.
                            </p>
                            <button type="button" id="btnTambahFieldEmpty" data-workshop-id="<?= $workshop['id'] ?>"
                                class="mt-6 inline-flex items-center justify-center px-6 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-200 font-bold text-sm">
                                <i class="fas fa-plus mr-2"></i> Tambah Pertanyaan Pertama
                            </button>
                        </div>
                <?php else: ?>
                        <?php foreach ($fields as $field): ?>
                                <div class="px-6 py-5 hover:bg-emerald-50/30 transition-colors group">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                                        <!-- Col 1: Icon & Label -->
                                        <div class="md:col-span-5 flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center shadow-sm border border-blue-100 flex-shrink-0">
                                                <?php
                                                $icons = [
                                                    'text' => 'fa-font',
                                                    'email' => 'fa-envelope',
                                                    'tel' => 'fa-phone',
                                                    'textarea' => 'fa-align-left',
                                                    'select' => 'fa-list-ul',
                                                    'radio' => 'fa-dot-circle'
                                                ];
                                                $iconClass = $icons[$field['field_type']] ?? 'fa-question';
                                                ?>
                                                <i class="fas <?= $iconClass ?>"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <h4 class="font-bold text-gray-800 text-sm truncate group-hover:text-emerald-700 transition-colors">
                                                    <?= htmlspecialchars($field['label']) ?>
                                                </h4>
                                                <?php if ($field['placeholder']): ?>
                                                        <p class="text-xs text-gray-400 truncate mt-0.5">Example: <?= htmlspecialchars($field['placeholder']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Col 2: Type -->
                                        <div class="md:col-span-3">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider
                                        <?= ($field['field_type'] === 'select' || $field['field_type'] === 'radio') ? 'bg-purple-50 text-purple-700 border border-purple-100' : 'bg-blue-50 text-blue-700 border border-blue-100' ?>">
                                                <?= $field['field_type'] ?>
                                            </span>
                                        </div>

                                        <!-- Col 3: Status -->
                                        <div class="md:col-span-2 text-center md:text-left">
                                            <?php if ($field['is_required']): ?>
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-600 border border-red-100">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Wajib
                                                    </span>
                                            <?php else: ?>
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500 border border-gray-200">
                                                        Opsional
                                                    </span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Col 4: Actions -->
                                        <div class="md:col-span-2 flex justify-end gap-2">
                                            <button type="button" 
                                                class="btn-edit-field w-9 h-9 rounded-lg bg-white border border-gray-200 text-gray-500 hover:text-emerald-600 hover:border-emerald-200 hover:shadow-md transition-all flex items-center justify-center"
                                                title="Edit Field" 
                                                data-field-id="<?= $field['id'] ?>"
                                                data-workshop-id="<?= $field['workshop_id'] ?>"
                                                data-label="<?= htmlspecialchars($field['label']) ?>"
                                                data-field-type="<?= $field['field_type'] ?>"
                                                data-options="<?= htmlspecialchars($field['options']) ?>"
                                                data-is-required="<?= $field['is_required'] ?>"
                                                data-placeholder="<?= htmlspecialchars($field['placeholder']) ?>">
                                                <i class="fas fa-pencil-alt text-sm"></i>
                                            </button>

                                            <button type="button" 
                                                class="btn-hapus-field w-9 h-9 rounded-lg bg-white border border-gray-200 text-gray-500 hover:text-red-600 hover:border-red-200 hover:shadow-md transition-all flex items-center justify-center"
                                                title="Hapus Field" 
                                                data-field-id="<?= $field['id'] ?>"
                                                data-label="<?= htmlspecialchars($field['label']) ?>">
                                                <i class="fas fa-trash-alt text-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                        <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase">Total Field</p>
                    <p class="text-2xl font-extrabold text-gray-800"><?= count($fields) ?></p>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-red-50 text-red-600 flex items-center justify-center text-xl">
                    <i class="fas fa-asterisk"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase">Field Wajib</p>
                    <p class="text-2xl font-extrabold text-gray-800"><?= count(array_filter($fields, fn($f) => $f['is_required'])) ?></p>
                </div>
            </div>

            <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl shadow-lg border border-amber-100 p-6">
                <h3 class="text-sm font-bold text-amber-800 mb-2 flex items-center">
                    <i class="fas fa-lightbulb text-amber-500 mr-2"></i> Tips Form Efektif
                </h3>
                <ul class="text-xs text-amber-700 space-y-1.5 list-disc list-inside opacity-90">
                    <li>Gunakan placeholder yang jelas.</li>
                    <li>Batasi field wajib agar user tidak malas mengisi.</li>
                    <li>Gunakan dropdown untuk pilihan yang banyak.</li>
                </ul>
            </div>
        </div>

    </div>
</div>

<?php
// --- PENTING: INCLUDE MODAL HARUS DISINI (SEBELUM SCRIPT) ---
require_once '../admin/templates/modal_fields_form.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- Variabel DOM ---
    const modal = document.getElementById('fieldModal');
    const modalContent = document.getElementById('modalContent');
    const overlay = document.getElementById('modalOverlay');
    const form = document.getElementById('fieldForm');
    
    // Container Opsi Dinamis
    const optionsContainer = document.getElementById('optionsContainer');
    const dynamicOptionsList = document.getElementById('dynamicOptionsList');
    const typeSelect = document.getElementById('formFieldType');

    // --- 1. Fungsi Buka/Tutup Modal ---
    window.openModal = function(isEdit = false) {
        if (!modal || !overlay) return;
        
        overlay.classList.remove('hidden');
        modal.classList.remove('hidden');

        setTimeout(() => {
            overlay.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);

        document.getElementById('modalTitle').innerText = isEdit ? 'Edit Pertanyaan' : 'Buat Pertanyaan Baru';
    }

    window.closeModal = function() {
        if (!modal) return;
        overlay.classList.add('opacity-0');
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            overlay.classList.add('hidden');
            modal.classList.add('hidden');
            if(form) form.reset();
            if(dynamicOptionsList) dynamicOptionsList.innerHTML = '';
            if(optionsContainer) optionsContainer.classList.add('hidden');
        }, 300);
    }

    // --- 2. LOGIKA OPSI DINAMIS (ADD/REMOVE ROW) ---
    window.addOptionRow = function(value = '') {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 animate-fadeIn mb-2'; 
        
        div.innerHTML = `
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-circle text-[6px] text-emerald-300"></i>
                </div>
                <input type="text" class="dynamic-option-input w-full pl-8 pr-10 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all bg-gray-50 focus:bg-white" 
                       placeholder="Nama Opsi (Contoh: Sesi Pagi)" value="${value}">
            </div>
            <button type="button" onclick="this.parentElement.remove()" 
                    class="w-10 h-10 flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors border border-transparent hover:border-red-100" title="Hapus Opsi">
                <i class="fas fa-trash-alt"></i>
            </button>
        `;
        
        dynamicOptionsList.appendChild(div);
        
        if(value === '') {
            const inputs = div.querySelectorAll('input');
            if(inputs.length > 0) inputs[0].focus();
        }
    }

    if (typeSelect) {
        typeSelect.addEventListener('change', function () {
            const val = this.value;
            if (val === 'select' || val === 'radio') {
                optionsContainer.classList.remove('hidden');
                if(dynamicOptionsList.children.length === 0) {
                    addOptionRow();
                }
            } else {
                optionsContainer.classList.add('hidden');
                dynamicOptionsList.innerHTML = '';
            }
        });
    }

    // --- 3. Tombol Tambah ---
    function handleTambah(e) {
        e.preventDefault();
        document.getElementById('formAction').value = 'tambah';
        document.getElementById('formWorkshopId').value = this.dataset.workshopId;
        document.getElementById('formFieldId').value = '';

        if (typeSelect) {
            typeSelect.value = 'text';
            typeSelect.dispatchEvent(new Event('change'));
        }
        openModal(false);
    }

    const btnTambah = document.getElementById('btnTambahField');
    const btnTambahEmpty = document.getElementById('btnTambahFieldEmpty');
    
    if (btnTambah) btnTambah.addEventListener('click', handleTambah);
    if (btnTambahEmpty) btnTambahEmpty.addEventListener('click', handleTambah);

    // --- 4. Tombol Edit ---
    document.body.addEventListener('click', function(e) {
        const btnEdit = e.target.closest('.btn-edit-field');
        if (btnEdit) {
            const data = btnEdit.dataset;

            document.getElementById('formAction').value = 'edit';
            document.getElementById('formWorkshopId').value = data.workshopId;
            document.getElementById('formFieldId').value = data.fieldId;
            document.getElementById('formLabel').value = data.label;
            document.getElementById('formPlaceholder').value = data.placeholder;
            document.getElementById('formIsRequired').checked = (data.isRequired === "1");

            // Load Opsi Lama
            dynamicOptionsList.innerHTML = '';
            
            if (typeSelect) {
                typeSelect.value = data.fieldType;
                typeSelect.dispatchEvent(new Event('change')); 
            }

            if ((data.fieldType === 'select' || data.fieldType === 'radio') && data.options) {
                const optionsArray = data.options.split(',');
                optionsArray.forEach(opt => {
                    addOptionRow(opt.trim());
                });
            } else if (data.fieldType === 'select' || data.fieldType === 'radio') {
                addOptionRow();
            }

            openModal(true);
        }
    });

    // --- 5. Submit Form ---
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            
            // Gabungkan Opsi
            const optionInputs = document.querySelectorAll('.dynamic-option-input');
            let collectedOptions = [];
            
            optionInputs.forEach(input => {
                const val = input.value.trim();
                if(val !== '') {
                    collectedOptions.push(val.replace(/,/g, '')); 
                }
            });

            document.getElementById('finalOptionsInput').value = collectedOptions.join(',');

            // Kirim Data
            const formData = new FormData(this);

            Swal.fire({
                title: 'Menyimpan...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            fetch('crud_form_fields.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    Swal.fire({
                        icon: 'success', 
                        title: 'Berhasil!', 
                        text: result.message,
                        showConfirmButton: false, 
                        timer: 1500,
                        confirmButtonColor: '#059669'
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!', 
                        text: result.message, 
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!', 
                    text: 'Terjadi kesalahan sistem.', 
                    confirmButtonColor: '#ef4444'
                });
            });
        });
    }

    // --- 6. Hapus Data ---
    document.body.addEventListener('click', function(e) {
        const btnHapus = e.target.closest('.btn-hapus-field');
        if (btnHapus) {
            const id = btnHapus.dataset.fieldId;
            const label = btnHapus.dataset.label;

            Swal.fire({
                title: 'Hapus Pertanyaan?',
                text: `Anda yakin ingin menghapus "${label}"? Data yang sudah diisi peserta mungkin akan hilang.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#d1d5db',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'rounded-3xl',
                    confirmButton: 'rounded-xl px-5',
                    cancelButton: 'rounded-xl px-5'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'hapus');
                    formData.append('field_id', id);

                    fetch('crud_form_fields.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!', 
                                text: data.message, 
                                showConfirmButton: false,
                                timer: 1500,
                                confirmButtonColor: '#059669'
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Gagal!', data.message, 'error');
                        }
                    });
                }
            });
        }
    });

    // Close on Overlay Click
    if (overlay) overlay.addEventListener('click', closeModal);
});
</script>

<?php
// Include Footer di paling akhir
require_once '../admin/templates/footer.php';
?>