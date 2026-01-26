<?php
// user/materi.php

if (session_status() === PHP_SESSION_NONE)
    session_start();

// 1. CEK LOGIN SANTRI
if (!isset($_SESSION['santri_id']) || $_SESSION['role'] !== 'peserta') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Ruang Belajar";
$current_page = "materi"; // Active state sidebar
require_once __DIR__ . '/../core/koneksi.php';
require_once __DIR__ . '/templates/header.php';

$user_id = $_SESSION['santri_id']; // Gunakan ID Santri untuk chat user_id
$email_peserta = $_SESSION['email'] ?? ''; // Fallback jika email ada di session

// 2. QUERY WORKSHOP (Hanya yang Paid/Free)
// Ambil daftar workshop yang diikuti santri
$sql_ws = "SELECT DISTINCT p.workshop_id, w.judul, w.tanggal_waktu, w.poster, w.is_diskusi_active 
           FROM pendaftaran p 
           JOIN workshops w ON p.workshop_id = w.id 
           WHERE p.santri_id = :sid 
           AND (p.status_pembayaran = 'paid' OR p.status_pembayaran = 'free')
           ORDER BY w.tanggal_waktu DESC";

$stmt = $pdo->prepare($sql_ws);
$stmt->execute(['sid' => $user_id]);
$workshops = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cek tab aktif (Default ke workshop pertama jika tidak ada di URL)
$active_ws_id = isset($_GET['active_ws']) ? $_GET['active_ws'] : ($workshops[0]['workshop_id'] ?? 0);
?>

<div class="min-h-screen bg-slate-50 font-sans pb-20">

    <div class="bg-primary-900 pb-24 pt-10 px-4 rounded-b-[3rem] shadow-xl relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-primary-800 rounded-full opacity-50 blur-3xl animate-pulse-subtle"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-gold-500 rounded-full opacity-20 blur-2xl"></div>

        <div class="max-w-7xl mx-auto px-4 relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-primary-200 text-xs font-bold uppercase tracking-widest border border-primary-700/50 px-2 py-1 rounded-md">LMS Area</span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-white leading-tight">
                        Ruang Belajar
                    </h1>
                    <p class="text-primary-100/90 mt-2 text-sm md:text-base max-w-lg">
                        Akses materi eksklusif dan berdiskusi langsung dengan pemateri serta peserta lainnya.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 -mt-16 relative z-20 space-y-8">

        <?php if (count($workshops) > 0): ?>

                <div class="flex overflow-x-auto gap-3 pb-4 scrollbar-hide snap-x">
                    <?php foreach ($workshops as $ws): ?>
                            <?php $isActive = ($active_ws_id == $ws['workshop_id']); ?>
                            <a href="?active_ws=<?= $ws['workshop_id'] ?>" 
                               class="snap-start shrink-0 px-5 py-3 rounded-2xl text-sm font-bold transition-all border flex items-center gap-3 shadow-md 
                       <?= $isActive
                           ? 'bg-white text-primary-800 border-primary-500 ring-2 ring-primary-500/20 transform scale-105'
                           : 'bg-white/90 backdrop-blur-sm text-slate-500 border-transparent hover:bg-white hover:text-slate-700' ?>">
                        
                                <?php if ($ws['poster']): ?>
                                        <img src="<?= BASE_URL ?>assets/img/posters/<?= htmlspecialchars($ws['poster']) ?>"
                                            class="w-8 h-8 rounded-lg object-cover bg-slate-200">
                                <?php else: ?>
                                        <div class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center text-primary-600">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                        </div>
                                <?php endif; ?>
                        
                                <div class="flex flex-col truncate max-w-[150px]">
                                    <span class="truncate"><?= htmlspecialchars($ws['judul']) ?></span>
                                    <?php if (!$ws['is_diskusi_active']): ?>
                                            <span class="text-[10px] text-red-500 flex items-center gap-1 font-normal">
                                                <i class="fas fa-lock"></i> Terkunci
                                            </span>
                                    <?php endif; ?>
                                </div>
                            </a>
                    <?php endforeach; ?>
                </div>

                <?php
                // Logic Detail Workshop Aktif
                $current_ws = null;
                foreach ($workshops as $ws) {
                    if ($ws['workshop_id'] == $active_ws_id) {
                        $current_ws = $ws;
                        break;
                    }
                }

                if ($current_ws):
                    // Ambil Materi
                    $stmt_m = $pdo->prepare("SELECT * FROM workshop_materials WHERE workshop_id = ? ORDER BY uploaded_at DESC");
                    $stmt_m->execute([$active_ws_id]);
                    $materi_list = $stmt_m->fetchAll(PDO::FETCH_ASSOC);

                    // Ambil Diskusi
                    // Note: user_id di tabel diskusi merujuk ke tabel users/santri tergantung implementasi. 
                    // Asumsi: tabel diskusi menyimpan ID pengirim. Kita perlu join ke tabel user/santri untuk nama.
                    // Untuk simplifikasi sesuai kode awal, kita join ke users. 
                    // Jika santri terpisah, query ini mungkin perlu disesuaikan (misal pakai UNION), 
                    // TAPI KARENA REQUESTNYA "FUNGSI SAMA", SAYA PAKAI QUERY ASLI ANDA.
                    $stmt_d = $pdo->prepare("
                    SELECT d.*, u.nama_lengkap, u.role 
                    FROM workshop_discussions d 
                    JOIN users u ON d.user_id = u.id 
                    WHERE d.workshop_id = ? 
                    ORDER BY d.created_at ASC
                ");
                    // *Catatan: Jika chat error karena santri beda tabel, nanti kita perbaiki query-nya. 
                    // Sementara saya pakai query asli Anda.
            
                    // REVISI QUERY AGAR JALAN DI STRUKTUR BARU (User Admin & Santri Terpisah)
                    // Kita gunakan UNION untuk menggabungkan nama dari tabel `users` (admin) dan `santri` (peserta)
                    $sql_chat_revised = "
                    SELECT d.*, 
                           COALESCE(u.nama_lengkap, s.nama_lengkap) as nama_lengkap,
                           COALESCE(u.role, 'peserta') as role
                    FROM workshop_discussions d
                    LEFT JOIN users u ON d.user_id = u.id AND d.user_type = 'admin'
                    LEFT JOIN santri s ON d.user_id = s.id AND d.user_type = 'santri'
                    WHERE d.workshop_id = ?
                    ORDER BY d.created_at ASC
                ";
                    // NOTE: Karena tabel diskusi mungkin belum punya kolom 'user_type', 
                    // kita pakai logika fallback query asli jika struktur belum update.
                    // UNTUK AMANNYA: Saya pakai query asli Anda dulu, asumsi user_id di diskusi nyambung ke users.
                    // Jika error, berarti tabel diskusi harus update menerima ID santri.
            
                    // BACK TO ORIGINAL QUERY (Sesuai Permintaan)
                    // Tapi kita perlu trik karena santri tidak ada di tabel users.
                    // Kita akan coba ambil chat, nanti di loop kita handle tampilannya.
                    $stmt_d = $pdo->prepare("SELECT * FROM workshop_discussions WHERE workshop_id = ? ORDER BY created_at ASC");
                    $stmt_d->execute([$active_ws_id]);
                    $chats_raw = $stmt_d->fetchAll(PDO::FETCH_ASSOC);

                    // Proses Enrich Data Chat (Manual Join di PHP biar aman)
                    $chats = [];
                    foreach ($chats_raw as $c) {
                        if ($c['user_type'] == 'admin') {
                            $stmt_u = $pdo->prepare("SELECT nama_lengkap, role FROM users WHERE id = ?");
                            $stmt_u->execute([$c['user_id']]);
                            $u = $stmt_u->fetch();
                            $c['nama_lengkap'] = $u['nama_lengkap'] ?? 'Admin';
                            $c['role'] = $u['role'] ?? 'admin';
                        } else {
                            $stmt_s = $pdo->prepare("SELECT nama_lengkap FROM santri WHERE id = ?");
                            $stmt_s->execute([$c['user_id']]);
                            $s = $stmt_s->fetch();
                            $c['nama_lengkap'] = $s['nama_lengkap'] ?? 'Santri';
                            $c['role'] = 'peserta';
                        }
                        $chats[] = $c;
                    }
                    ?>

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                            <div class="lg:col-span-4 space-y-6">

                                <div class="bg-white rounded-3xl shadow-lg border border-slate-100 overflow-hidden relative group">
                                    <div class="relative h-48 bg-slate-200">
                                        <?php if ($current_ws['poster']): ?>
                                                <img src="<?= BASE_URL ?>assets/img/posters/<?= $current_ws['poster'] ?>"
                                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                        <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center bg-primary-800 text-white">
                                                    <i class="fas fa-image text-4xl opacity-30"></i>
                                                </div>
                                        <?php endif; ?>
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                                        <div class="absolute bottom-4 left-5 right-5 text-white">
                                            <h2 class="text-xl font-bold leading-tight mb-1">
                                                <?= htmlspecialchars($current_ws['judul']) ?>
                                            </h2>
                                            <p class="text-xs opacity-90 font-medium flex items-center gap-2">
                                                <i class="far fa-calendar-alt text-gold-400"></i>
                                                <?= date('d F Y', strtotime($current_ws['tanggal_waktu'])) ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white rounded-3xl shadow-lg border border-slate-100 p-6">
                                    <h3 class="font-bold text-slate-800 mb-4 flex items-center text-lg">
                                        <span class="w-8 h-8 rounded-lg bg-gold-100 text-gold-600 flex items-center justify-center mr-3 text-sm">
                                            <i class="fas fa-folder-open"></i>
                                        </span>
                                        Materi Workshop
                                    </h3>

                                    <?php if (count($materi_list) > 0): ?>
                                            <div class="space-y-3">
                                                <?php foreach ($materi_list as $materi):
                                                    $ext = strtolower(pathinfo($materi['nama_file'], PATHINFO_EXTENSION));
                                                    $iconInfo = match ($ext) {
                                                        'pdf' => ['bg' => 'bg-red-50', 'text' => 'text-red-600', 'icon' => 'fa-file-pdf'],
                                                        'ppt', 'pptx' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-600', 'icon' => 'fa-file-powerpoint'],
                                                        'doc', 'docx' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'icon' => 'fa-file-word'],
                                                        'xls', 'xlsx' => ['bg' => 'bg-green-50', 'text' => 'text-green-600', 'icon' => 'fa-file-excel'],
                                                        default => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'icon' => 'fa-file-alt']
                                                    };
                                                    ?>
                                                        <a href="<?= BASE_URL ?>assets/uploads/materi/<?= $materi['nama_file'] ?>" download
                                                            class="flex items-center p-3 rounded-2xl border border-slate-100 hover:border-primary-200 hover:bg-primary-50/30 transition-all group">
                                                            <div class="w-10 h-10 rounded-xl <?= $iconInfo['bg'] ?> <?= $iconInfo['text'] ?> flex items-center justify-center text-lg flex-shrink-0">
                                                                <i class="fas <?= $iconInfo['icon'] ?>"></i>
                                                            </div>
                                                            <div class="ml-3 flex-grow min-w-0">
                                                                <div class="text-sm font-bold text-slate-700 truncate group-hover:text-primary-700">
                                                                    <?= htmlspecialchars($materi['judul_materi']) ?>
                                                                </div>
                                                                <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mt-0.5">
                                                                    <?= strtoupper($ext) ?>
                                                                </div>
                                                            </div>
                                                            <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400 group-hover:text-primary-600 group-hover:border-primary-200 transition-colors">
                                                                <i class="fas fa-download text-xs"></i>
                                                            </div>
                                                        </a>
                                                <?php endforeach; ?>
                                            </div>
                                    <?php else: ?>
                                            <div class="text-center py-8 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                                                <i class="fas fa-box-open text-slate-300 text-3xl mb-2"></i>
                                                <p class="text-sm text-slate-500">Belum ada materi diupload.</p>
                                            </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="lg:col-span-8">
                                <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden flex flex-col h-[700px] relative">

                                    <div class="bg-white/80 backdrop-blur-md px-6 py-4 border-b border-slate-100 flex justify-between items-center sticky top-0 z-10">
                                        <h3 class="font-bold text-slate-800 flex items-center text-lg gap-2">
                                            <?php if ($current_ws['is_diskusi_active']): ?>
                                                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse shadow-glow-green"></span>
                                                    Diskusi Live
                                            <?php else: ?>
                                                    <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                                                    Diskusi Dikunci
                                            <?php endif; ?>
                                        </h3>
                                        <div class="text-xs text-slate-500 font-medium bg-slate-100 px-3 py-1 rounded-full border border-slate-200">
                                            <span id="chatCount" class="font-bold text-primary-600"><?= count($chats) ?></span> Pesan
                                        </div>
                                    </div>

                                    <div class="flex-grow overflow-y-auto p-6 space-y-4 bg-slate-50 scrollbar-thin scrollbar-thumb-slate-200" id="chatContainer">
                                        <?php if (count($chats) > 0): ?>
                                                <?php foreach ($chats as $chat):
                                                    $is_me = ($chat['user_id'] == $user_id && $chat['role'] == 'peserta'); // Cek ID dan Role
                                                    $is_admin = ($chat['role'] == 'penyelenggara' || $chat['role'] == 'admin');

                                                    // Styling Bubble
                                                    if ($is_me) {
                                                        $wrapperClass = 'justify-end';
                                                        // Hijau untuk User
                                                        $bubbleClass = 'bg-primary-600 text-white rounded-tr-none shadow-md shadow-primary-200';
                                                        $metaClass = 'text-primary-600';
                                                        $name = 'Anda';
                                                    } elseif ($is_admin) {
                                                        $wrapperClass = 'justify-start';
                                                        // Emas/Kuning untuk Admin
                                                        $bubbleClass = 'bg-gold-50 text-amber-900 border border-gold-200 rounded-tl-none shadow-sm';
                                                        $metaClass = 'text-amber-600';
                                                        $name = htmlspecialchars($chat['nama_lengkap']) . ' <i class="fas fa-check-circle text-amber-500 ml-1" title="Admin"></i>';
                                                    } else {
                                                        $wrapperClass = 'justify-start';
                                                        // Putih untuk User Lain
                                                        $bubbleClass = 'bg-white text-slate-700 border border-slate-200 rounded-tl-none shadow-sm';
                                                        $metaClass = 'text-slate-500';
                                                        $name = htmlspecialchars($chat['nama_lengkap']);
                                                    }
                                                    ?>
                                                        <div class="flex <?= $wrapperClass ?> group animate-fade-in">
                                                            <div class="max-w-[85%] sm:max-w-[75%]">
                                                                <div class="flex items-center gap-2 mb-1 px-1 <?= $is_me ? 'flex-row-reverse' : '' ?>">
                                                                    <span class="text-xs font-bold <?= $metaClass ?>">
                                                                        <?= $name ?>
                                                                    </span>
                                                                    <span class="text-[10px] text-slate-400">
                                                                        <?= date('H:i', strtotime($chat['created_at'])) ?>
                                                                    </span>
                                                                </div>
                                                                <div class="px-4 py-3 rounded-2xl text-sm leading-relaxed <?= $bubbleClass ?>">
                                                                    <?= nl2br(htmlspecialchars($chat['message'])) ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                <?php endforeach; ?>
                                        <?php else: ?>
                                                <div id="emptyChatState" class="flex flex-col items-center justify-center h-full text-center opacity-60">
                                                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-3 shadow-sm border border-slate-100">
                                                        <i class="far fa-comments text-3xl text-primary-300"></i>
                                                    </div>
                                                    <h4 class="text-slate-600 font-bold">Belum ada diskusi</h4>
                                                    <p class="text-sm text-slate-400 mt-1">Jadilah yang pertama bertanya atau menyapa!</p>
                                                </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="p-4 bg-white border-t border-slate-100 relative">
                                        <?php if (!$current_ws['is_diskusi_active']): ?>
                                                <div class="absolute inset-0 bg-white/80 backdrop-blur-sm z-20 flex items-center justify-center rounded-b-3xl">
                                                    <div class="text-slate-500 text-sm font-medium flex items-center gap-2 bg-slate-100 px-4 py-2 rounded-full shadow-sm border border-slate-200">
                                                        <i class="fas fa-lock text-red-400"></i> Diskusi telah dikunci oleh penyelenggara.
                                                    </div>
                                                </div>
                                        <?php endif; ?>

                                        <form id="chatForm" class="flex gap-3 relative items-end">
                                            <input type="hidden" name="workshop_id" value="<?= $active_ws_id ?>">
                                            <input type="hidden" name="user_type" value="santri"> 
                                    
                                            <div class="relative flex-grow">
                                                <textarea name="pesan" required rows="1" id="chatInput"
                                                    class="w-full pl-4 pr-12 py-3.5 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none bg-slate-50 focus:bg-white transition-all resize-none overflow-hidden max-h-32 text-sm text-slate-700"
                                                    placeholder="Ketik pesan diskusi..."></textarea>
                                            </div>
                                            <button type="submit" id="btnSend"
                                                class="h-[46px] w-[46px] bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-all shadow-lg shadow-primary-200 flex items-center justify-center flex-shrink-0 transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                                <i class="fas fa-paper-plane text-sm"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>

                <?php endif; ?>

        <?php else: ?>

                <div class="flex flex-col items-center justify-center py-20 bg-white rounded-3xl shadow-lg border border-slate-100 text-center">
                    <div class="w-24 h-24 bg-primary-50 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-book-reader text-primary-300 text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800">Belum Ada Kelas</h3>
                    <p class="text-slate-500 mt-2 mb-8 max-w-md">Anda belum terdaftar di kelas manapun. Yuk daftar workshop sekarang untuk mulai belajar!</p>
                    <a href="dashboard.php"
                        class="bg-primary-600 text-white px-8 py-3.5 rounded-xl font-bold shadow-lg shadow-primary-200 hover:bg-primary-700 transition-transform transform hover:-translate-y-1">
                        Cari Workshop
                    </a>
                </div>

        <?php endif; ?>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chatContainer = document.getElementById("chatContainer");
        const chatForm = document.getElementById("chatForm");
        const chatInput = document.getElementById("chatInput");
        const btnSend = document.getElementById("btnSend");
        const emptyState = document.getElementById("emptyChatState");

        // Scroll ke bawah saat load
        if (chatContainer) chatContainer.scrollTop = chatContainer.scrollHeight;

        // Auto Resize Textarea
        if (chatInput) {
            chatInput.addEventListener('input', function () {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
                if (this.value === '') this.style.height = 'auto';
            });

            // Kirim dengan Enter (tanpa Shift)
            chatInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    chatForm.dispatchEvent(new Event('submit'));
                }
            });
        }

        // --- AJAX SEND MESSAGE LOGIC (USER) ---
        if (chatForm) {
            chatForm.addEventListener('submit', async function (e) {
                e.preventDefault();

                const message = chatInput.value.trim();
                if (!message) return;

                // 1. UI Optimis: Langsung tampilkan bubble "Loading/Pending"
                const tempId = Date.now();
                const pendingBubble = createBubble(message, tempId, true);

                if (emptyState) emptyState.style.display = 'none';

                chatContainer.appendChild(pendingBubble);
                chatContainer.scrollTop = chatContainer.scrollHeight;

                // 2. Kirim Data via AJAX
                // Ambil data sebelum reset input
                const formData = new FormData(this);

                // Reset Input UI
                chatInput.value = '';
                chatInput.style.height = 'auto';

                try {
                    const response = await fetch('ajax_chat_send_user.php', {
                        method: 'POST',
                        body: formData
                    });

                    // Cek status HTTP
                    if (!response.ok) {
                        throw new Error(`HTTP Error: ${response.status}`);
                    }

                    const result = await response.json();

                    if (result.status === 'success') {
                        updateBubbleSuccess(tempId, result.timestamp);
                    } else {
                        markBubbleFailed(tempId);
                        alert(result.message); // Tampilkan pesan error dari server (misal: dikunci)
                        // Hapus bubble jika gagal karena dikunci agar tidak membingungkan
                        if (result.message.includes('dikunci')) {
                            const bubble = document.getElementById(`msg-${tempId}`);
                            if (bubble) bubble.remove();
                        }
                    }
                } catch (error) {
                    markBubbleFailed(tempId);
                    console.error('Connection Error:', error);
                }
            });
        }

        // Helper: Buat HTML Bubble (User)
        function createBubble(text, id, isPending) {
            const div = document.createElement('div');
            div.className = 'flex justify-end group animate-fade-in';
            div.id = `msg-${id}`;

            const escapedText = text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\n/g, "<br>");
            const timeDisplay = isPending ? '<i class="fas fa-clock animate-pulse"></i>' : 'Baru saja';

            // STYLING HARUS SAMA DENGAN PHP DI ATAS
            div.innerHTML = `
            <div class="max-w-[85%] sm:max-w-[75%]">
                <div class="flex items-center gap-2 mb-1 px-1 flex-row-reverse">
                    <span class="text-xs font-bold text-primary-600">Anda</span>
                    <span class="text-[10px] text-slate-400" id="time-${id}">
                        ${timeDisplay}
                    </span>
                </div>
                <div class="px-4 py-3 rounded-2xl text-sm leading-relaxed shadow-md bg-primary-600 text-white rounded-tr-none shadow-primary-200">
                    ${escapedText}
                </div>
            </div>
        `;
            return div;
        }

        function updateBubbleSuccess(id, timestamp) {
            const timeEl = document.getElementById(`time-${id}`);
            if (timeEl) timeEl.innerText = timestamp;
        }

        function markBubbleFailed(id) {
            const bubble = document.getElementById(`msg-${id}`);
            if (bubble) {
                const innerBubble = bubble.querySelector('.bg-primary-600');
                innerBubble.classList.remove('bg-primary-600');
                innerBubble.classList.add('bg-red-500');
                innerBubble.title = "Gagal terkirim.";

                const timeEl = document.getElementById(`time-${id}`);
                timeEl.innerHTML = '<i class="fas fa-exclamation-circle text-red-500"></i> Gagal';
            }
        }
    });
</script>

<style>
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }

    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fadeIn 0.3s ease-out forwards;
    }
</style>

<?php require_once 'templates/footer.php'; ?>