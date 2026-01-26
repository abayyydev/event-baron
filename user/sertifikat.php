<?php
// user/sertifikat.php

if (session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION['santri_id']) || $_SESSION['role'] !== 'peserta') {
    header("Location: ../login.php");
    exit();
}

require_once '../core/koneksi.php';
$page_title = "E-Sertifikat";
$current_page = "sertifikat";
require_once 'templates/header.php';

$santri_id = $_SESSION['santri_id'];

try {
    // PERBAIKAN: Menggunakan :sid1 dan :sid2 agar tidak error parameter count
    $sql = "SELECT p.id as pendaftaran_id, p.sertifikat_nomor, 
                   w.id as workshop_id, w.judul, w.tanggal_waktu, w.lokasi, w.sertifikat_template, w.is_kuesioner_active,
                   -- Parameter 1: Untuk subquery cek jawaban
                   (SELECT COUNT(*) FROM workshop_answers wa WHERE wa.workshop_id = w.id AND wa.santri_id = :sid1) as is_filled
            FROM pendaftaran p 
            JOIN workshops w ON p.workshop_id = w.id 
            -- Parameter 2: Untuk filter pendaftaran utama
            WHERE p.santri_id = :sid2 
            AND p.status_kehadiran = 'hadir'
            ORDER BY w.tanggal_waktu DESC";

    $stmt = $pdo->prepare($sql);

    // Binding kedua parameter dengan nilai yang sama ($santri_id)
    $stmt->execute([
        'sid1' => $santri_id,
        'sid2' => $santri_id
    ]);

    $sertifikats = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Tampilkan error jika ada (untuk debugging)
    echo "<div class='bg-red-500 text-white p-4'>Error: " . $e->getMessage() . "</div>";
    $sertifikats = [];
}
?>

<div class="min-h-screen bg-slate-50 font-sans pb-20">
    <div class="bg-primary-900 pb-24 pt-10 px-4 rounded-b-[3rem] shadow-xl relative overflow-hidden">
        <div
            class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-primary-800 rounded-full opacity-50 blur-3xl animate-pulse-subtle">
        </div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-gold-500 rounded-full opacity-20 blur-2xl">
        </div>
        <div class="max-w-6xl mx-auto px-4 relative z-10">
            <h1 class="text-3xl md:text-4xl font-extrabold text-white leading-tight">E-Sertifikat Saya</h1>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 -mt-16 relative z-20 space-y-8">
        <?php if (count($sertifikats) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($sertifikats as $row):
                    $is_ready = !empty($row['sertifikat_template']);
                    // Syarat download: (Kuesioner Tidak Aktif) ATAU (Sudah Isi)
                    $can_download = ($row['is_kuesioner_active'] == 0 || $row['is_filled'] > 0);
                    ?>
                    <div
                        class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden hover:shadow-xl transition-all duration-300 group flex flex-col h-full">
                        <div class="h-32 bg-slate-100 relative overflow-hidden flex items-center justify-center">
                            <div
                                class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-sm z-10 text-gold-500 border-4 border-slate-50">
                                <i class="fas fa-certificate text-3xl"></i>
                            </div>
                        </div>

                        <div class="p-6 flex-1 flex flex-col text-center">
                            <h3 class="font-bold text-slate-800 text-lg leading-snug mb-2 line-clamp-2">
                                <?= htmlspecialchars($row['judul']) ?>
                            </h3>
                            <p class="text-xs text-slate-500 mb-4">
                                <?= date('d F Y', strtotime($row['tanggal_waktu'])) ?>
                            </p>

                            <div class="mt-auto">
                                <?php if (!$is_ready): ?>
                                    <button disabled
                                        class="w-full bg-slate-100 text-slate-400 font-bold py-3 rounded-xl cursor-not-allowed text-sm">
                                        <i class="fas fa-clock"></i> Belum Rilis
                                    </button>
                                <?php elseif ($can_download): ?>
                                    <a href="download_sertifikat.php?id=<?= $row['pendaftaran_id'] ?>" target="_blank"
                                        class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-gold-400 to-gold-500 hover:from-gold-500 hover:to-gold-600 text-white font-bold py-3 rounded-xl shadow-md transition-all text-sm">
                                        <i class="fas fa-download"></i> Unduh Sertifikat
                                    </a>
                                <?php else: ?>
                                    <a href="isi_kuesioner.php?id=<?= $row['workshop_id'] ?>"
                                        class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-md transition-all text-sm animate-pulse">
                                        <i class="fas fa-edit"></i> Isi Kuesioner
                                    </a>
                                    <p class="text-[10px] text-slate-500 mt-2">Wajib isi kuesioner untuk unduh.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-3xl p-10 text-center shadow-sm border border-slate-200">
                <p class="text-slate-500">Belum ada sertifikat. Pastikan status Anda "Hadir".</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once 'templates/footer.php'; ?>