<?php
require_once 'core/koneksi.php';
require_once 'templates/header.php';

try {
    // Ambil semua kegiatan/agenda yang akan datang
    $stmt = $pdo->query("SELECT * FROM workshops WHERE tanggal_waktu >= CURDATE() ORDER BY tanggal_waktu ASC");
    $agendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback jika database belum siap
    $agendas = [];
}
?>

<!-- Hero Section -->
<section class="bg-gradient-to-br from-green-800 via-green-700 to-green-900 text-white py-20 relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-20">
        <div class="absolute top-0 left-0 w-full h-full parallax"
            style="background-image: url('assets/img/images/pesantren_bg.jpg'); background-size: cover; background-position: center;">
        </div>
    </div>

    <!-- Floating elements -->
    <div class="absolute top-10 left-10 w-20 h-20 rounded-full bg-yellow-400/20 animate-float"></div>
    <div class="absolute top-1/4 right-20 w-16 h-16 rounded-full bg-green-400/30 animate-float delay-200"></div>
    <div class="absolute bottom-20 left-1/4 w-12 h-12 rounded-full bg-white/10 animate-float delay-300"></div>

    <div class="container mx-auto px-6 flex flex-col-reverse md:flex-row items-center md:space-x-12 relative z-10">
        <div class="md:w-1/2 text-center md:text-left animate-fade-in">
            <h1 class="text-4xl md:text-6xl font-extrabold mb-4 leading-tight">
                Pondok Pesantren<br />
                <span class="text-yellow-400">Al Ihsan Baron</span>
            </h1>
            <p
                class="text-lg md:text-xl mb-8 max-w-xl mx-auto md:mx-0 glass p-4 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20">
                Mencetak generasi yang berkarakter Islami, unggul dalam prestasi, serta memiliki keahlian spesial
                berlandaskan Iman dan Taqwa.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                <a href="#daftar"
                    class="inline-flex items-center bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-orange-500 hover:to-yellow-500 text-white font-semibold py-3 px-8 rounded-lg shadow-lg transition duration-300 transform hover:-translate-y-1">
                    Daftar Sekarang <i class="ml-2 fas fa-user-plus"></i>
                </a>
                <a href="#profil"
                    class="inline-flex items-center border-2 border-white hover:bg-white hover:text-green-800 text-white font-semibold py-3 px-8 rounded-lg transition duration-300">
                    Profil Pesantren
                </a>
            </div>
        </div>
        <div class="md:w-1/2 mb-12 md:mb-0 flex justify-center animate-fade-in delay-100">
            <div class="relative">
                <div
                    class="absolute -inset-4 bg-gradient-to-r from-yellow-400 to-green-500 rounded-full blur-lg opacity-30">
                </div>
                <img src="./assets/img/images/pesantren_bg.webp" alt="Gedung Pesantren Al Ihsan Baron"
                    class="rounded-xl shadow-2xl w-80 h-80 object-cover border-4 border-white relative z-10" />
            </div>
        </div>
    </div>
</section>

<!-- About / Sejarah Section -->
<section id="profil" class="py-16 bg-green-50 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-64 h-64 bg-green-200/20 rounded-full -translate-y-32 translate-x-32"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-yellow-200/20 rounded-full translate-y-40 -translate-x-40"></div>

    <div class="container mx-auto px-6 max-w-6xl relative z-10">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-green-800 mb-3">Sejarah & Profil</h2>
            <div class="w-20 h-1 bg-yellow-500 mx-auto mb-6"></div>
            <p class="text-gray-700 max-w-3xl mx-auto text-lg">
                Perjalanan kami dalam mendidik generasi penerus bangsa yang berakhlak mulia dan berwawasan luas.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-start">
            <div class="relative">
                <div
                    class="absolute -inset-4 bg-gradient-to-r from-green-600 to-green-400 rounded-xl blur-lg opacity-20">
                </div>
                <img src="./assets/img/images/WhatsApp-Image-2025-10-07-at-05.41.46_75cc5cf4-scaled.jpg"
                    alt="Kegiatan Santri"
                    class="rounded-xl shadow-lg w-full object-cover relative z-10 h-full min-h-[300px]" />
                <div class="absolute bottom-4 left-4 right-4 bg-white/90 p-4 rounded-lg shadow-md z-20">
                    <p class="text-sm font-semibold text-green-800"> <i class="fas fa-history mr-2"></i> Berdiri sejak
                        tahun 2012</p>
                </div>
            </div>

            <div>
                <h3 class="text-2xl font-bold text-green-900 mb-4">Awal Berdiri</h3>
                <p class="text-gray-700 mb-6 leading-relaxed">
                    Berdirinya Pondok Pesantren Baron Nganjuk tahun 2012 diawali dengan pendirian <strong>SMP Bina Insan
                        Mandiri</strong> dengan jumlah awal 35 santri.
                    Kepercayaan dari walisantri terhadap Pondok Pesantren Baron yang baru berdiri menjadi penyemangat
                    tersendiri bagi pihak pondok untuk membesarkannya
                    menjadi lembaga pendidikan yang mencetak generasi yang berkarakter Islami dan berkeahlian spesial.
                </p>

                <div class="mb-6 bg-white p-6 rounded-xl shadow-md border-l-4 border-yellow-500">
                    <h4 class="text-xl font-semibold text-green-800 mb-2 flex items-center">
                        <i class="fas fa-star mr-2 text-yellow-500"></i> Visi Kami
                    </h4>
                    <p class="text-gray-700 italic">
                        "Terwujudnya generasi berkarakter dan unggul dalam prestasi yang berlandaskan Iman dan Taqwa."
                    </p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-green-600">
                    <h4 class="text-xl font-semibold text-green-800 mb-4 flex items-center">
                        <i class="fas fa-list-ul mr-2 text-green-600"></i> Misi Pondok
                    </h4>
                    <ul class="text-gray-700 space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                            <span>Menumbuhkembangkan karakter warga sekolah yang religius dan cerdas.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                            <span>Mengembangkan pembelajaran aktif, kreatif, inovatif, mengikuti IPTEK.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                            <span>Mengontrol aktivitas harian agar siswa mandiri, disiplin, dan bertanggung
                                jawab.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                            <span>Melakukan pelayanan terbaik untuk membentuk kecakapan kepemimpinan & sosial.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                            <span>Meningkatkan kerjasama dengan lembaga atau instansi tingkat nasional.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Keunggulan / Values Section -->
<section id="keunggulan" class="py-16 bg-white relative overflow-hidden">
    <div class="absolute top-10 right-10 w-40 h-40 bg-yellow-100 rounded-full"></div>
    <div class="absolute bottom-10 left-10 w-32 h-32 bg-green-100 rounded-full"></div>

    <div class="container mx-auto px-6 max-w-6xl relative z-10">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-green-800 mb-3">Nilai-Nilai Utama</h2>
            <div class="w-20 h-1 bg-yellow-500 mx-auto mb-6"></div>
            <p class="text-gray-700 max-w-3xl mx-auto text-lg">
                Pondok Pesantren Al Ihsan Baron berkomitmen menanamkan nilai-nilai luhur kepada setiap santri.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 stagger-animation">
            <!-- Nilai 1 -->
            <div
                class="bg-white rounded-xl shadow-lg p-6 card-hover border-t-4 border-green-600 relative overflow-hidden group hover:-translate-y-2 transition-all duration-300">
                <div
                    class="w-14 h-14 bg-gradient-to-r from-green-600 to-green-800 rounded-full flex items-center justify-center text-white text-2xl mb-4 relative z-10">
                    <i class="fas fa-quran"></i>
                </div>
                <h3 class="text-xl font-bold text-green-900 mb-3 relative z-10">Religius & Berkarakter</h3>
                <p class="text-gray-600 mb-4 relative z-10">
                    Membentuk pribadi yang taat beribadah, berakhlak mulia, dan memegang teguh nilai-nilai Islam dalam
                    kehidupan sehari-hari.
                </p>
            </div>

            <!-- Nilai 2 -->
            <div
                class="bg-white rounded-xl shadow-lg p-6 card-hover border-t-4 border-yellow-500 relative overflow-hidden group hover:-translate-y-2 transition-all duration-300">
                <div
                    class="w-14 h-14 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-full flex items-center justify-center text-white text-2xl mb-4 relative z-10">
                    <i class="fas fa-brain"></i>
                </div>
                <h3 class="text-xl font-bold text-green-900 mb-3 relative z-10">Cerdas & Inovatif</h3>
                <p class="text-gray-600 mb-4 relative z-10">
                    Mengintegrasikan ilmu pengetahuan umum dan teknologi dengan pembelajaran yang aktif dan kreatif
                    sesuai perkembangan zaman.
                </p>
            </div>

            <!-- Nilai 3 -->
            <div
                class="bg-white rounded-xl shadow-lg p-6 card-hover border-t-4 border-green-600 relative overflow-hidden group hover:-translate-y-2 transition-all duration-300">
                <div
                    class="w-14 h-14 bg-gradient-to-r from-green-600 to-teal-600 rounded-full flex items-center justify-center text-white text-2xl mb-4 relative z-10">
                    <i class="fas fa-users-cog"></i>
                </div>
                <h3 class="text-xl font-bold text-green-900 mb-3 relative z-10">Mandiri & Memimpin</h3>
                <p class="text-gray-600 mb-4 relative z-10">
                    Melatih kedisiplinan, tanggung jawab, dan jiwa kepemimpinan melalui pengontrolan aktivitas harian
                    dan kecakapan sosial.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Program Pendidikan / Services -->
<section id="pendidikan" class="py-16 bg-gradient-to-br from-gray-50 to-green-50 relative overflow-hidden">
    <div class="container mx-auto px-6 max-w-6xl relative z-10">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-green-800 mb-3">Jenjang Pendidikan</h2>
            <div class="w-20 h-1 bg-yellow-500 mx-auto mb-6"></div>
            <p class="text-gray-700 max-w-3xl mx-auto text-lg">
                Pondok Pesantren Al Ihsan Baron menyelenggarakan pendidikan formal dari tingkat SD hingga SMA yang
                terintegrasi dengan kurikulum kepesantrenan.
            </p>
        </div>

        <!-- Updated to show SD, SMP, SMA cards in 3-column grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 stagger-animation">
            <!-- SD Card -->
            <div
                class="bg-white rounded-xl shadow-lg overflow-hidden card-hover group hover:-translate-y-2 transition-all duration-300">
                <div
                    class="bg-gradient-to-br from-red-500 to-red-600 h-32 flex items-center justify-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-black/10 group-hover:bg-black/5 transition-colors"></div>
                    <i class="fas fa-child text-6xl text-white relative z-10"></i>
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-green-900 mb-3">Tingkat SD</h3>
                    <p class="text-gray-600 mb-4 leading-relaxed">
                        Membangun fondasi aqidah yang lurus, akhlak mulia, dan kecintaan pada Al-Quran sejak usia dini.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-700 mb-4">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-red-500 mr-2 mt-0.5"></i>
                            <span>Tahfidz Al-Quran</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-red-500 mr-2 mt-0.5"></i>
                            <span>Pendidikan Karakter Islami</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-red-500 mr-2 mt-0.5"></i>
                            <span>Pembelajaran Menyenangkan</span>
                        </li>
                    </ul>
                    <span class="inline-block bg-red-100 text-red-800 text-xs font-semibold px-3 py-1 rounded-full">
                        Full Day
                    </span>
                </div>
            </div>

            <!-- SMP Card -->
            <div
                class="bg-white rounded-xl shadow-lg overflow-hidden card-hover group hover:-translate-y-2 transition-all duration-300">
                <div
                    class="bg-gradient-to-br from-blue-600 to-blue-700 h-32 flex items-center justify-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-black/10 group-hover:bg-black/5 transition-colors"></div>
                    <i class="fas fa-user-graduate text-6xl text-white relative z-10"></i>
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-green-900 mb-3">Tingkat SMP</h3>
                    <p class="text-gray-600 mb-4 leading-relaxed">
                        <strong>Bina Insan Mandiri.</strong> Membentuk kemandirian, kedisiplinan, dan dasar kepemimpinan
                        yang kuat.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-700 mb-4">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-600 mr-2 mt-0.5"></i>
                            <span>Kurikulum Terpadu</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-600 mr-2 mt-0.5"></i>
                            <span>Life Skills & Kewirausahaan</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-600 mr-2 mt-0.5"></i>
                            <span>Pembinaan Karakter Pemimpin</span>
                        </li>
                    </ul>
                    <span class="inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">
                        Boarding School
                    </span>
                </div>
            </div>

            <!-- SMA Card -->
            <div
                class="bg-white rounded-xl shadow-lg overflow-hidden card-hover group hover:-translate-y-2 transition-all duration-300">
                <div
                    class="bg-gradient-to-br from-gray-700 to-gray-800 h-32 flex items-center justify-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-black/10 group-hover:bg-black/5 transition-colors"></div>
                    <i class="fas fa-university text-6xl text-white relative z-10"></i>
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-green-900 mb-3">Tingkat SMA</h3>
                    <p class="text-gray-600 mb-4 leading-relaxed">
                        Persiapan matang menuju jenjang pendidikan tinggi dengan bekal ilmu agama dan umum yang
                        seimbang.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-700 mb-4">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-gray-700 mr-2 mt-0.5"></i>
                            <span>Persiapan Universitas</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-gray-700 mr-2 mt-0.5"></i>
                            <span>Ilmu Syariah & Umum</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-gray-700 mr-2 mt-0.5"></i>
                            <span>Kepemimpinan Lanjut</span>
                        </li>
                    </ul>
                    <span class="inline-block bg-gray-100 text-gray-800 text-xs font-semibold px-3 py-1 rounded-full">
                        Boarding School
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Agenda Terdekat Section (Dynamic PHP) -->
<section id="agenda" class="py-16 bg-white border-t border-gray-200">
    <div class="container mx-auto px-6 max-w-7xl relative z-10">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-green-800 mb-3">Agenda & Event Pesantren</h2>
            <div class="w-20 h-1 bg-yellow-500 mx-auto mb-6"></div>
            <p class="text-gray-700 max-w-3xl mx-auto text-lg">
                Ikuti berbagai kegiatan bermanfaat yang diselenggarakan oleh Pondok Pesantren Al Ihsan Baron.
                <br><span class="text-sm text-green-600 font-semibold">(Terbuka untuk Umum & Wali Santri)</span>
            </p>
        </div>

        <?php
        // Hitung dulu ada berapa event public (untuk handling tampilan kosong)
        $public_count = 0;
        if (!empty($agendas)) {
            foreach ($agendas as $a) {
                if (($a['visibilitas'] ?? 'public') == 'public')
                    $public_count++;
            }
        }
        ?>

        <?php if ($public_count > 0): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($agendas as $agenda): ?>
                    <?php
                    // LOGIKA UTAMA: LEWATI JIKA EVENT INTERNAL
                    // Jika datanya 'internal', langsung skip ke loop berikutnya (hidden)
                    if (isset($agenda['visibilitas']) && $agenda['visibilitas'] == 'internal') {
                        continue;
                    }
                    ?>

                    <article
                        class="rounded-xl shadow-lg overflow-hidden bg-white hover:shadow-xl transition-shadow duration-300 border border-gray-100 flex flex-col h-full">
                        <div class="h-48 bg-gray-200 relative overflow-hidden group flex-shrink-0">
                            <?php if (!empty($agenda['poster'])): ?>
                                <img src="assets/img/posters/<?= htmlspecialchars($agenda['poster']) ?>"
                                    alt="<?= htmlspecialchars($agenda['judul'] ?? 'Agenda') ?>"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center bg-green-700 text-white">
                                    <i class="fas fa-calendar-alt text-4xl"></i>
                                </div>
                            <?php endif; ?>

                            <div
                                class="absolute top-4 right-4 bg-yellow-500 text-white text-xs font-bold py-1 px-3 rounded-full shadow-md z-10">
                                Open Registration
                            </div>

                            <div
                                class="absolute top-4 left-4 bg-blue-600/90 backdrop-blur-sm text-white text-xs font-bold py-1 px-3 rounded-full shadow-md z-10">
                                <i class="fas fa-globe-asia mr-1"></i> Umum
                            </div>
                        </div>

                        <div class="p-6 flex flex-col flex-1">
                            <h3 class="text-xl font-bold text-gray-800 mb-2 line-clamp-2">
                                <?= htmlspecialchars($agenda['judul'] ?? 'Agenda Kegiatan') ?>
                            </h3>

                            <div class="flex items-center text-gray-500 text-sm mb-4">
                                <i class="far fa-clock mr-2 text-yellow-600"></i>
                                <?= isset($agenda['tanggal_waktu']) ? date('d F Y, H:i', strtotime($agenda['tanggal_waktu'])) . ' WIB' : 'Segera' ?>
                            </div>

                            <p class="text-gray-600 mb-6 line-clamp-3 text-sm flex-1">
                                <?= htmlspecialchars(substr($agenda['deskripsi'] ?? 'Kegiatan rutin pesantren untuk meningkatkan kualitas santri.', 0, 120)) ?>...
                            </p>

                            <a href="detail_workshop.php?id=<?= $agenda['id'] ?? '#' ?>"
                                class="block w-full text-center bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-4 rounded-lg transition-colors duration-300 shadow-md hover:shadow-lg mt-auto">
                                <i class="fas fa-ticket-alt mr-2"></i> Daftar Sekarang
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-300 max-w-2xl mx-auto">
                <div class="w-20 h-20 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-calendar-day text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-700 mb-2">Belum Ada Agenda Umum</h3>
                <p class="text-gray-500 max-w-md mx-auto">
                    Saat ini belum ada event yang dibuka untuk umum. Pantau terus halaman ini atau sosial media kami untuk
                    update terbaru.
                </p>
            </div>
        <?php endif; ?>

        <div
            class="mt-16 bg-gradient-to-r from-green-800 via-green-700 to-green-900 rounded-2xl shadow-2xl overflow-hidden relative">
            <div
                class="absolute top-0 right-0 w-96 h-96 bg-yellow-500/10 rounded-full -translate-y-1/2 translate-x-1/3 blur-3xl">
            </div>
            <div
                class="absolute bottom-0 left-0 w-80 h-80 bg-green-500/10 rounded-full translate-y-1/2 -translate-x-1/3 blur-3xl">
            </div>

            <div class="relative z-10 p-8 md:p-12">
                <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="md:w-2/3 text-center md:text-left">
                        <div
                            class="inline-block bg-yellow-500/20 text-yellow-300 text-xs font-bold py-1 px-3 rounded-full mb-4">
                            <i class="fas fa-info-circle mr-1"></i> Informasi Santri
                        </div>
                        <h3 class="text-2xl md:text-3xl font-bold text-white mb-3">
                            Wali Santri atau Pengurus?
                        </h3>
                        <p class="text-green-100 text-base md:text-lg leading-relaxed">
                            Untuk melihat <b>Agenda Internal Khusus Santri</b>, silakan login menggunakan akun santri
                            atau akun pengurus pesantren.
                        </p>
                    </div>

                    <div class="md:w-1/3 flex flex-col gap-3 w-full md:items-end">
                        <a href="login.php"
                            class="inline-flex items-center justify-center bg-yellow-500 hover:bg-yellow-400 text-green-900 font-bold py-3 px-8 rounded-xl shadow-xl transition-all duration-300 transform hover:scale-105 w-full md:w-auto">
                            <i class="fas fa-sign-in-alt mr-2"></i> Login Akun
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-20 bg-green-900 text-white relative overflow-hidden">
    <div class="absolute inset-0 bg-[url('assets/img/pattern.png')] opacity-10"></div>
    <div class="container mx-auto px-6 text-center relative z-10">
        <h2 class="text-3xl md:text-5xl font-bold mb-6">Siap Menjadi Bagian dari Kami?</h2>
        <p class="text-xl text-green-100 mb-10 max-w-2xl mx-auto">
            Mari bergabung bersama keluarga besar Pondok Pesantren Al Ihsan Baron dan wujudkan generasi Islami yang
            gemilang.
        </p>
        <a href="#daftar"
            class="inline-block bg-yellow-500 hover:bg-yellow-400 text-green-900 font-bold py-4 px-10 rounded-full shadow-lg transition-transform transform hover:scale-105">
            Daftar Sekarang
        </a>
    </div>
</section>

<?php require_once 'templates/footer.php'; ?>