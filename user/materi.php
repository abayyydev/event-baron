<?php
$page_title = "Materi Workshop";
$current_page = "materi";

require_once __DIR__ . '/templates/header.php';

$email_peserta = $_SESSION['email'];

// Query Workshop Lunas/Gratis
$sql_ws = "SELECT p.workshop_id, w.judul, w.tanggal_waktu, w.poster 
           FROM pendaftaran p 
           JOIN workshops w ON p.workshop_id = w.id 
           WHERE p.email_peserta = :email 
           AND (p.status_pembayaran = 'paid' OR p.status_pembayaran = 'free')
           ORDER BY w.tanggal_waktu DESC";

$stmt = $pdo->prepare($sql_ws);
$stmt->execute(['email' => $email_peserta]);
$workshops = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Materi Pembelajaran</h1>
    <p class="text-gray-600 mt-2">Unduh modul dan presentasi dari workshop yang telah Anda ikuti.</p>
</div>

<?php if (count($workshops) > 0): ?>
    <div class="space-y-8">
        <?php foreach ($workshops as $ws): ?>
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">

                <div class="bg-gray-50 p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center gap-4">
                    <div class="w-16 h-16 flex-shrink-0 bg-gray-200 rounded-lg overflow-hidden">
                        <?php if ($ws['poster']): ?>
                            <img src="<?= BASE_URL ?>assets/img/posters/<?= $ws['poster'] ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="flex items-center justify-center h-full text-gray-400"><i class="fas fa-image"></i></div>
                        <?php endif; ?>
                    </div>

                    <div class="flex-grow">
                        <h2 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($ws['judul']) ?></h2>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="far fa-calendar-alt mr-1"></i> <?= date('d F Y', strtotime($ws['tanggal_waktu'])) ?>
                        </p>
                    </div>
                </div>

                <div class="p-6">
                    <?php
                    // Ambil Materi per Workshop
                    $sql_materi = "SELECT * FROM workshop_materials WHERE workshop_id = ? ORDER BY uploaded_at DESC";
                    $stmt_m = $pdo->prepare($sql_materi);
                    $stmt_m->execute([$ws['workshop_id']]);
                    $materi_list = $stmt_m->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <?php if (count($materi_list) > 0): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($materi_list as $materi): ?>
                                <?php
                                $ext = strtolower(pathinfo($materi['nama_file'], PATHINFO_EXTENSION));
                                $icon = 'fa-file-alt';
                                $color = 'text-gray-500';
                                if (in_array($ext, ['pdf'])) {
                                    $icon = 'fa-file-pdf';
                                    $color = 'text-red-500';
                                } elseif (in_array($ext, ['ppt', 'pptx'])) {
                                    $icon = 'fa-file-powerpoint';
                                    $color = 'text-orange-500';
                                } elseif (in_array($ext, ['doc', 'docx'])) {
                                    $icon = 'fa-file-word';
                                    $color = 'text-blue-500';
                                } elseif (in_array($ext, ['zip', 'rar'])) {
                                    $icon = 'fa-file-archive';
                                    $color = 'text-yellow-600';
                                }
                                ?>

                                <div
                                    class="flex items-center p-4 border border-gray-200 rounded-xl hover:shadow-md transition bg-gray-50 hover:bg-white group">
                                    <div
                                        class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm mr-4 group-hover:scale-110 transition">
                                        <i class="fas <?= $icon ?> <?= $color ?> text-2xl"></i>
                                    </div>
                                    <div class="flex-grow min-w-0">
                                        <h4 class="font-bold text-gray-800 truncate"><?= htmlspecialchars($materi['judul_materi']) ?>
                                        </h4>
                                        <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($materi['deskripsi'] ?: '-') ?>
                                        </p>
                                    </div>
                                    <a href="<?= BASE_URL ?>assets/uploads/materi/<?= $materi['nama_file'] ?>" download
                                        class="ml-3 bg-primary hover:bg-green-800 text-white w-10 h-10 rounded-full flex items-center justify-center transition shadow-lg">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-6 bg-yellow-50 rounded-xl border border-yellow-100 border-dashed">
                            <i class="fas fa-box-open text-yellow-400 text-3xl mb-2"></i>
                            <p class="text-yellow-700 font-medium">Belum ada materi yang diunggah.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="text-center py-16 bg-white rounded-2xl shadow-lg border border-gray-100">
        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-book text-gray-400 text-4xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800">Tidak ada materi</h3>
        <p class="text-gray-500 mt-2 mb-6">Anda belum mendaftar workshop apapun.</p>
        <a href="dashboard.php"
            class="bg-primary text-white px-6 py-3 rounded-full font-bold shadow-lg hover:bg-green-800 transition">
            Cari Workshop
        </a>
    </div>
<?php endif; ?>

<?php require_once 'templates/footer.php'; ?>