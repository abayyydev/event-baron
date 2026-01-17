<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Ruang Belajar";
$current_page = "materi.php";
require_once __DIR__ . '/../core/koneksi.php';
require_once __DIR__ . '/templates/header.php';

$user_id = $_SESSION['user_id'];
$email_peserta = $_SESSION['email'];

// NOTE: Logic kirim pesan PHP dihapus, digantikan AJAX

// 2. QUERY WORKSHOP (Ambil is_diskusi_active)
$sql_ws = "SELECT DISTINCT p.workshop_id, w.judul, w.tanggal_waktu, w.poster, w.is_diskusi_active 
           FROM pendaftaran p 
           JOIN workshops w ON p.workshop_id = w.id 
           WHERE p.email_peserta = :email 
           AND (p.status_pembayaran = 'paid' OR p.status_pembayaran = 'free')
           ORDER BY w.tanggal_waktu DESC";

$stmt = $pdo->prepare($sql_ws);
$stmt->execute(['email' => $email_peserta]);
$workshops = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cek tab aktif
$active_ws_id = isset($_GET['active_ws']) ? $_GET['active_ws'] : ($workshops[0]['workshop_id'] ?? 0);
?>

<div class="min-h-screen bg-gray-50 font-sans pb-20">

    <!-- Hero Section -->
    <div class="bg-emerald-900 relative overflow-hidden pb-24 pt-10 rounded-b-[3rem] shadow-xl">
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-emerald-800 rounded-full opacity-50 blur-3xl">
        </div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-amber-500 rounded-full opacity-20 blur-2xl">
        </div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <span
                        class="text-emerald-200 text-xs font-bold uppercase tracking-widest border border-emerald-700/50 px-2 py-1 rounded-md">LMS
                        Area</span>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-white mt-2 leading-tight">
                        Ruang Belajar
                    </h1>
                    <p class="text-emerald-100/90 mt-2 text-sm md:text-base max-w-lg">
                        Akses materi eksklusif dan berdiskusi langsung dengan pemateri serta peserta lainnya.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 -mt-16 relative z-20 space-y-8">

        <?php if (count($workshops) > 0): ?>

            <!-- Tabs Navigasi Workshop -->
            <div class="flex overflow-x-auto gap-3 pb-4 scrollbar-hide snap-x">
                <?php foreach ($workshops as $ws): ?>
                    <?php $isActive = ($active_ws_id == $ws['workshop_id']); ?>
                    <a href="?active_ws=<?= $ws['workshop_id'] ?>" class="snap-start shrink-0 px-5 py-3 rounded-2xl text-sm font-bold transition-all border flex items-center gap-3 shadow-md 
                       <?= $isActive
                           ? 'bg-white text-emerald-800 border-emerald-500 ring-2 ring-emerald-500/20'
                           : 'bg-white/90 backdrop-blur-sm text-gray-500 border-transparent hover:bg-white' ?>">
                        <?php if ($ws['poster']): ?>
                            <img src="<?= BASE_URL ?>assets/img/posters/<?= htmlspecialchars($ws['poster']) ?>"
                                class="w-8 h-8 rounded-lg object-cover bg-gray-200">
                        <?php else: ?>
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                        <?php endif; ?>
                        <div class="flex flex-col truncate max-w-[150px]">
                            <span class="truncate"><?= htmlspecialchars($ws['judul']) ?></span>
                            <?php if (!$ws['is_diskusi_active']): ?>
                                <span class="text-[10px] text-red-500 flex items-center gap-1 font-normal"><i
                                        class="fas fa-lock"></i> Terkunci</span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php
            // Detail Workshop Aktif
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
                $stmt_d = $pdo->prepare("
                    SELECT d.*, u.nama_lengkap, u.role 
                    FROM workshop_discussions d 
                    JOIN users u ON d.user_id = u.id 
                    WHERE d.workshop_id = ? 
                    ORDER BY d.created_at ASC
                ");
                $stmt_d->execute([$active_ws_id]);
                $chats = $stmt_d->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                    <!-- KOLOM KIRI: INFO & MATERI (4 Grid) -->
                    <div class="lg:col-span-4 space-y-6">

                        <!-- Info Card -->
                        <div class="bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden">
                            <div class="relative h-48 bg-gray-200">
                                <?php if ($current_ws['poster']): ?>
                                    <img src="<?= BASE_URL ?>assets/img/posters/<?= $current_ws['poster'] ?>"
                                        class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center bg-emerald-800 text-white">
                                        <i class="fas fa-image text-4xl opacity-50"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                                <div class="absolute bottom-4 left-5 right-5 text-white">
                                    <h2 class="text-xl font-bold leading-tight mb-1">
                                        <?= htmlspecialchars($current_ws['judul']) ?>
                                    </h2>
                                    <p class="text-xs opacity-90 font-medium flex items-center gap-2">
                                        <i class="far fa-calendar-alt text-amber-400"></i>
                                        <?= date('d F Y', strtotime($current_ws['tanggal_waktu'])) ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Materi List -->
                        <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-6">
                            <h3 class="font-bold text-gray-800 mb-4 flex items-center text-lg">
                                <span
                                    class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center mr-3 text-sm">
                                    <i class="fas fa-folder-open"></i>
                                </span>
                                Materi Workshop
                            </h3>

                            <?php if (count($materi_list) > 0): ?>
                                <div class="space-y-3">
                                    <?php foreach ($materi_list as $materi):
                                        $ext = strtolower(pathinfo($materi['nama_file'], PATHINFO_EXTENSION));
                                        $iconInfo = match ($ext) {
                                            'pdf' => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'icon' => 'fa-file-pdf'],
                                            'ppt', 'pptx' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600', 'icon' => 'fa-file-powerpoint'],
                                            'doc', 'docx' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'icon' => 'fa-file-word'],
                                            'xls', 'xlsx' => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'icon' => 'fa-file-excel'],
                                            default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'icon' => 'fa-file-alt']
                                        };
                                        ?>
                                        <a href="<?= BASE_URL ?>assets/uploads/materi/<?= $materi['nama_file'] ?>" download
                                            class="flex items-center p-3 rounded-2xl border border-gray-100 hover:border-emerald-200 hover:bg-emerald-50/30 transition-all group">
                                            <div
                                                class="w-10 h-10 rounded-xl <?= $iconInfo['bg'] ?> <?= $iconInfo['text'] ?> flex items-center justify-center text-lg flex-shrink-0">
                                                <i class="fas <?= $iconInfo['icon'] ?>"></i>
                                            </div>
                                            <div class="ml-3 flex-grow min-w-0">
                                                <div class="text-sm font-bold text-gray-700 truncate group-hover:text-emerald-700">
                                                    <?= htmlspecialchars($materi['judul_materi']) ?>
                                                </div>
                                                <div class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mt-0.5">
                                                    <?= strtoupper($ext) ?>
                                                </div>
                                            </div>
                                            <div
                                                class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-400 group-hover:text-emerald-600 group-hover:border-emerald-200 transition-colors">
                                                <i class="fas fa-download text-xs"></i>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                                    <i class="fas fa-box-open text-gray-300 text-3xl mb-2"></i>
                                    <p class="text-sm text-gray-500">Belum ada materi diupload.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- KOLOM KANAN: CHAT (8 Grid) -->
                    <div class="lg:col-span-8">
                        <?php if (count($materi_list) > 0): ?>
                            <div
                                class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden flex flex-col h-[700px] relative">

                                <!-- Chat Header -->
                                <div
                                    class="bg-white/80 backdrop-blur-md px-6 py-4 border-b border-gray-100 flex justify-between items-center sticky top-0 z-10">
                                    <h3 class="font-bold text-gray-800 flex items-center text-lg gap-2">
                                        <?php if ($current_ws['is_diskusi_active']): ?>
                                            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                            Diskusi Live
                                        <?php else: ?>
                                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                            Diskusi Dikunci
                                        <?php endif; ?>
                                    </h3>
                                    <div class="text-xs text-gray-500 font-medium bg-gray-100 px-3 py-1 rounded-full">
                                        <span id="chatCount"><?= count($chats) ?></span> Pesan
                                    </div>
                                </div>

                                <!-- Chat Area -->
                                <div class="flex-grow overflow-y-auto p-6 space-y-4 bg-slate-50 scrollbar-thin scrollbar-thumb-gray-200"
                                    id="chatContainer">
                                    <?php if (count($chats) > 0): ?>
                                        <?php foreach ($chats as $chat):
                                            $is_me = ($chat['user_id'] == $user_id);
                                            $is_admin = ($chat['role'] == 'penyelenggara' || $chat['role'] == 'admin');

                                            // Bubble Styles
                                            if ($is_me) {
                                                $wrapperClass = 'justify-end';
                                                $bubbleClass = 'bg-emerald-600 text-white rounded-tr-none shadow-emerald-200';
                                                $metaClass = 'text-emerald-600';
                                                $name = 'Anda';
                                            } elseif ($is_admin) {
                                                $wrapperClass = 'justify-start';
                                                $bubbleClass = 'bg-amber-100 text-amber-900 border border-amber-200 rounded-tl-none';
                                                $metaClass = 'text-amber-600';
                                                $name = htmlspecialchars($chat['nama_lengkap']) . ' <i class="fas fa-check-circle text-amber-500 ml-1" title="Admin"></i>';
                                            } else {
                                                $wrapperClass = 'justify-start';
                                                $bubbleClass = 'bg-white text-gray-700 border border-gray-200 rounded-tl-none';
                                                $metaClass = 'text-gray-500';
                                                $name = htmlspecialchars($chat['nama_lengkap']);
                                            }
                                            ?>
                                            <div class="flex <?= $wrapperClass ?> group animate-fade-in">
                                                <div class="max-w-[85%] sm:max-w-[75%]">
                                                    <div class="flex items-center gap-2 mb-1 px-1 <?= $is_me ? 'flex-row-reverse' : '' ?>">
                                                        <span class="text-xs font-bold <?= $metaClass ?>">
                                                            <?= $name ?>
                                                        </span>
                                                        <span class="text-[10px] text-gray-400">
                                                            <?= date('H:i', strtotime($chat['created_at'])) ?>
                                                        </span>
                                                    </div>
                                                    <div
                                                        class="px-4 py-3 rounded-2xl text-sm leading-relaxed shadow-sm <?= $bubbleClass ?>">
                                                        <?= nl2br(htmlspecialchars($chat['message'])) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div id="emptyChatState"
                                            class="flex flex-col items-center justify-center h-full text-center opacity-60">
                                            <div
                                                class="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-3 shadow-sm border border-gray-100">
                                                <i class="far fa-comments text-3xl text-emerald-300"></i>
                                            </div>
                                            <h4 class="text-gray-600 font-bold">Belum ada diskusi</h4>
                                            <p class="text-sm text-gray-400 mt-1">Jadilah yang pertama bertanya atau menyapa!</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Input Area -->
                                <div class="p-4 bg-white border-t border-gray-100 relative">
                                    <?php if (!$current_ws['is_diskusi_active']): ?>
                                        <div
                                            class="absolute inset-0 bg-white/80 backdrop-blur-sm z-20 flex items-center justify-center rounded-b-3xl">
                                            <div
                                                class="text-gray-500 text-sm font-medium flex items-center gap-2 bg-gray-100 px-4 py-2 rounded-full shadow-sm border border-gray-200">
                                                <i class="fas fa-lock text-red-400"></i> Diskusi telah dikunci oleh penyelenggara.
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <form id="chatForm" class="flex gap-3 relative items-end">
                                        <input type="hidden" name="workshop_id" value="<?= $active_ws_id ?>">
                                        <div class="relative flex-grow">
                                            <textarea name="pesan" required rows="1" id="chatInput"
                                                class="w-full pl-4 pr-12 py-3.5 rounded-2xl border border-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-gray-50 focus:bg-white transition-all resize-none overflow-hidden max-h-32 text-sm text-gray-700"
                                                placeholder="Ketik pesan diskusi..."></textarea>
                                        </div>
                                        <button type="submit" id="btnSend"
                                            class="h-[46px] w-[46px] bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition-all shadow-lg shadow-emerald-200 flex items-center justify-center flex-shrink-0 transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                            <i class="fas fa-paper-plane text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <div
                                class="bg-white rounded-3xl shadow-lg border border-gray-100 p-12 text-center h-full flex flex-col justify-center items-center relative overflow-hidden">
                                <div class="absolute inset-0 bg-gray-50 opacity-50"
                                    style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 20px 20px;">
                                </div>
                                <div class="relative z-10">
                                    <div
                                        class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6 mx-auto border-4 border-white shadow-sm">
                                        <i class="fas fa-lock text-gray-300 text-4xl"></i>
                                    </div>
                                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Akses Belum Dibuka</h3>
                                    <p class="text-gray-500 max-w-md mx-auto leading-relaxed">
                                        Materi dan ruang diskusi untuk event ini belum tersedia. Silakan cek kembali nanti atau
                                        hubungi penyelenggara.
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>

            <?php endif; ?>

        <?php else: ?>

            <div
                class="flex flex-col items-center justify-center py-20 bg-white rounded-3xl shadow-lg border border-gray-100 text-center">
                <div class="w-24 h-24 bg-emerald-50 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-book-reader text-emerald-300 text-4xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800">Belum Ada Kelas</h3>
                <p class="text-gray-500 mt-2 mb-8 max-w-md">Anda belum terdaftar di kelas manapun. Yuk daftar workshop
                    sekarang untuk mulai belajar!</p>
                <a href="dashboard.php"
                    class="bg-emerald-600 text-white px-8 py-3.5 rounded-xl font-bold shadow-lg shadow-emerald-200 hover:bg-emerald-700 transition-transform transform hover:-translate-y-1">
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

            div.innerHTML = `
            <div class="max-w-[85%] sm:max-w-[75%]">
                <div class="flex items-center gap-2 mb-1 px-1 flex-row-reverse">
                    <span class="text-xs font-bold text-emerald-600">Anda</span>
                    <span class="text-[10px] text-gray-400" id="time-${id}">
                        ${timeDisplay}
                    </span>
                </div>
                <div class="px-4 py-3 rounded-2xl text-sm leading-relaxed shadow-md bg-emerald-600 text-white rounded-tr-none shadow-emerald-200">
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
                const innerBubble = bubble.querySelector('.bg-emerald-600');
                innerBubble.classList.remove('bg-emerald-600');
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