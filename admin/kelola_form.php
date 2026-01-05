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

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50/30">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6 mb-8">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center">
                <div class="mb-6 lg:mb-0">
                    <div class="flex items-center space-x-3 mb-4">
                        <div
                            class="w-12 h-12 bg-gradient-to-r from-amber-500 to-orange-500 rounded-xl flex items-center justify-center">
                            <i class="fas fa-list-alt text-white text-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-800">Kelola Form Pendaftaran</h1>
                            <p class="text-slate-600">Atur pertanyaan dan field untuk form pendaftaran event</p>
                        </div>
                    </div>

                    <!-- Workshop Info -->
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                        <div class="flex items-center space-x-3">
                            <?php if ($workshop['poster']): ?>
                                <img src="../assets/img/posters/<?= htmlspecialchars($workshop['poster']) ?>" alt="Poster"
                                    class="w-16 h-12 object-cover rounded-lg border border-slate-300">
                            <?php else: ?>
                                <div
                                    class="w-16 h-12 bg-slate-200 rounded-lg flex items-center justify-center border border-slate-300">
                                    <i class="fas fa-calendar text-slate-400"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h3 class="font-semibold text-slate-800"><?= htmlspecialchars($workshop['judul']) ?>
                                </h3>
                                <p class="text-sm text-slate-600">
                                    <i class="fas fa-calendar-alt mr-1 text-amber-500"></i>
                                    <?= date('d M Y, H:i', strtotime($workshop['tanggal_waktu'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="kelola_event.php"
                        class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium py-3 px-6 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg flex items-center justify-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                    <button data-modal-target="#fieldModal" id="btnTambahField"
                        data-workshop-id="<?= $workshop['id'] ?>"
                        class="bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center">
                        <i class="fas fa-plus mr-3"></i>
                        Tambah Pertanyaan
                    </button>
                </div>
            </div>
        </div>

        <!-- Form Fields Section -->
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-slate-800 to-slate-900 text-white px-6 py-4">
                <div class="grid grid-cols-12 gap-4 items-center">
                    <div class="col-span-5">
                        <span class="font-semibold">Pertanyaan / Field</span>
                    </div>
                    <div class="col-span-3">
                        <span class="font-semibold">Tipe Field</span>
                    </div>
                    <div class="col-span-2 text-center">
                        <span class="font-semibold">Status</span>
                    </div>
                    <div class="col-span-2 text-center">
                        <span class="font-semibold">Aksi</span>
                    </div>
                </div>
            </div>

            <!-- Fields List -->
            <div class="divide-y divide-slate-200">
                <?php if (empty($fields)): ?>
                    <!-- Empty State -->
                    <div class="px-6 py-12 text-center">
                        <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-question-circle text-slate-400 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-600 mb-2">Belum Ada Pertanyaan</h3>
                        <p class="text-slate-500 mb-6">Mulai dengan menambahkan pertanyaan untuk form pendaftaran event ini
                        </p>
                        <button data-modal-target="#fieldModal" id="btnTambahFieldEmpty"
                            data-workshop-id="<?= $workshop['id'] ?>"
                            class="bg-amber-500 hover:bg-amber-600 text-white font-medium py-2 px-6 rounded-xl transition-colors inline-flex items-center">
                            <i class="fas fa-plus mr-2"></i> Tambah Pertanyaan Pertama
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($fields as $field): ?>
                        <div class="px-6 py-4 hover:bg-slate-50/50 transition-colors group">
                            <div class="grid grid-cols-12 gap-4 items-center">
                                <!-- Field Label & Info -->
                                <div class="col-span-5">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0 border border-blue-200">
                                            <?php
                                            $fieldIcons = [
                                                'text' => 'fas fa-font',
                                                'email' => 'fas fa-envelope',
                                                'tel' => 'fas fa-phone',
                                                'textarea' => 'fas fa-align-left',
                                                'select' => 'fas fa-list',
                                                'radio' => 'fas fa-dot-circle'
                                            ];
                                            $fieldIcon = $fieldIcons[$field['field_type']] ?? 'fas fa-question';
                                            ?>
                                            <i class="<?= $fieldIcon ?> text-blue-600 text-sm"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h4
                                                class="font-semibold text-slate-800 group-hover:text-amber-600 transition-colors truncate">
                                                <?= htmlspecialchars($field['label']) ?>
                                            </h4>
                                            <?php if ($field['placeholder']): ?>
                                                <p class="text-sm text-slate-500 truncate">
                                                    Placeholder: <?= htmlspecialchars($field['placeholder']) ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($field['options']): ?>
                                                <p class="text-xs text-slate-400 truncate">
                                                    Opsi: <?= htmlspecialchars($field['options']) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Field Type -->
                                <div class="col-span-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                        <?= $field['field_type'] === 'select' || $field['field_type'] === 'radio'
                                            ? 'bg-purple-100 text-purple-800 border border-purple-200'
                                            : 'bg-blue-100 text-blue-800 border border-blue-200' ?>">
                                        <i class="fas fa-tag mr-2 text-xs"></i>
                                        <?= ucfirst(htmlspecialchars($field['field_type'])) ?>
                                    </span>
                                </div>

                                <!-- Status -->
                                <div class="col-span-2 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                        <?= $field['is_required']
                                            ? 'bg-red-100 text-red-800 border border-red-200'
                                            : 'bg-slate-100 text-slate-800 border border-slate-200' ?>">
                                        <i
                                            class="fas fa-<?= $field['is_required'] ? 'exclamation-circle' : 'circle' ?> mr-2 text-xs"></i>
                                        <?= $field['is_required'] ? 'Wajib' : 'Opsional' ?>
                                    </span>
                                </div>

                                <!-- Actions -->
                                <div class="col-span-2">
                                    <div class="flex items-center justify-center space-x-2">
                                        <!-- Edit Button -->
                                        <button
                                            class="btn-edit-field w-10 h-10 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 border border-blue-200"
                                            title="Edit Field" data-field-id="<?= $field['id'] ?>"
                                            data-workshop-id="<?= $field['workshop_id'] ?>"
                                            data-label="<?= htmlspecialchars($field['label']) ?>"
                                            data-field-type="<?= $field['field_type'] ?>"
                                            data-options="<?= htmlspecialchars($field['options']) ?>"
                                            data-is-required="<?= $field['is_required'] ?>"
                                            data-placeholder="<?= htmlspecialchars($field['placeholder']) ?>">
                                            <i class="fas fa-pencil-alt text-sm"></i>
                                        </button>

                                        <!-- Delete Button -->
                                        <button
                                            class="btn-hapus-field w-10 h-10 bg-red-100 hover:bg-red-200 text-red-600 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110 border border-red-200"
                                            title="Hapus Field" data-field-id="<?= $field['id'] ?>"
                                            data-label="<?= htmlspecialchars($field['label']) ?>">
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Stats & Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <!-- Field Statistics -->
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center">
                    <i class="fas fa-chart-bar text-amber-500 mr-3"></i>
                    Statistik Field
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600">Total Pertanyaan</span>
                        <span class="font-semibold text-slate-800"><?= count($fields) ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600">Field Wajib</span>
                        <span class="font-semibold text-red-600">
                            <?= count(array_filter($fields, function ($f) {
                                return $f['is_required'];
                            })) ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600">Field Opsional</span>
                        <span class="font-semibold text-slate-600">
                            <?= count(array_filter($fields, function ($f) {
                                return !$f['is_required'];
                            })) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Field Types -->
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center">
                    <i class="fas fa-shapes text-blue-500 mr-3"></i>
                    Tipe Field
                </h3>
                <div class="space-y-2">
                    <?php
                    $fieldTypes = [];
                    foreach ($fields as $field) {
                        $fieldTypes[$field['field_type']] = ($fieldTypes[$field['field_type']] ?? 0) + 1;
                    }
                    ?>
                    <?php foreach ($fieldTypes as $type => $count): ?>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600 capitalize"><?= $type ?></span>
                            <span class="font-semibold text-slate-800"><?= $count ?></span>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($fieldTypes)): ?>
                        <p class="text-slate-500 text-sm text-center py-2">Belum ada field</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tips & Info -->
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl shadow-lg border border-amber-200 p-6">
                <h3 class="text-lg font-semibold text-amber-800 mb-4 flex items-center">
                    <i class="fas fa-lightbulb text-amber-600 mr-3"></i>
                    Tips Form
                </h3>
                <ul class="space-y-2 text-sm text-amber-700">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle mt-1 mr-2 text-amber-500 text-xs"></i>
                        <span>Gunakan field wajib untuk informasi penting</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle mt-1 mr-2 text-amber-500 text-xs"></i>
                        <span>Pilih tipe field yang sesuai dengan data yang dibutuhkan</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle mt-1 mr-2 text-amber-500 text-xs"></i>
                        <span>Urutkan field dari yang paling penting</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
// Panggil file modal
require_once '../admin/templates/modal_fields_form.php';
require_once '../admin/templates/footer.php';
?>