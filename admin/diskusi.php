<?php
session_start();

$page_title = 'Ruang Diskusi';
$current_page = 'diskusi';

// Cek Sesi Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penyelenggara') {
    header("Location: ../login.php");
    exit();
}
require_once __DIR__ . '/../core/koneksi.php';
require_once __DIR__ . '/templates/header.php';

$user_id = $_SESSION['user_id'];
$id_penyelenggara = $_SESSION['penyelenggara_id_bersama'] ?? $user_id;

// 1. PROSES TOGGLE STATUS DISKUSI (Enable/Disable)
if (isset($_POST['toggle_diskusi'])) {
    $ws_id = $_POST['workshop_id'];
    $current_status = $_POST['current_status'];
    $new_status = ($current_status == 1) ? 0 : 1;

    $stmt_update = $pdo->prepare("UPDATE workshops SET is_diskusi_active = ? WHERE id = ?");
    $stmt_update->execute([$new_status, $ws_id]);

    echo "<script>window.location.href='diskusi.php?active_ws=$ws_id';</script>";
    exit;
}

// NOTE: Logic kirim pesan PHP DIHAPUS karena sudah digantikan AJAX di file ajax_chat_send.php
// agar tidak terjadi konflik double submit.

// 2. QUERY WORKSHOP KHUSUS ADMIN
$sql_ws = "SELECT id as workshop_id, judul, tanggal_waktu, poster, is_diskusi_active 
           FROM workshops 
           WHERE penyelenggara_id = :id_penyelenggara 
           ORDER BY tanggal_waktu DESC";

$stmt = $pdo->prepare($sql_ws);
$stmt->execute(['id_penyelenggara' => $id_penyelenggara]);
$workshops = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cek tab aktif
$active_ws_id = isset($_GET['active_ws']) ? $_GET['active_ws'] : ($workshops[0]['workshop_id'] ?? 0);
?>

<div class="min-h-screen bg-gray-50 font-sans pb-20">

    <!-- Hero Header -->
    <div class="bg-emerald-900 pb-20 pt-10 px-4 rounded-b-[3rem] shadow-xl relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-emerald-800 rounded-full opacity-50 blur-3xl">
        </div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-amber-500 rounded-full opacity-20 blur-2xl">
        </div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <span
                        class="text-emerald-200 text-xs font-bold uppercase tracking-widest border border-emerald-700/50 px-2 py-1 rounded-md">Moderasi</span>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-white mt-2 leading-tight">
                        Ruang Diskusi
                    </h1>
                    <p class="text-emerald-100/90 mt-2 text-sm md:text-base max-w-lg">
                        Pantau pertanyaan peserta dan kelola interaksi di setiap workshop Anda.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 -mt-16 relative z-20 space-y-8">

        <?php if (count($workshops) > 0): ?>

            <!-- Navigation Tabs -->
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
                // Ambil Diskusi
                $stmt_d = $pdo->prepare("
                    SELECT d.*, u.nama_lengkap, u.role, u.foto_profil 
                    FROM workshop_discussions d 
                    JOIN users u ON d.user_id = u.id 
                    WHERE d.workshop_id = ? 
                    ORDER BY d.created_at ASC
                ");
                $stmt_d->execute([$active_ws_id]);
                $chats = $stmt_d->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <div
                    class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden flex flex-col h-[700px] relative">

                    <!-- Chat Header -->
                    <div
                        class="bg-white/80 backdrop-blur-md px-6 py-4 border-b border-gray-100 flex justify-between items-center sticky top-0 z-10">
                        <div>
                            <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                                <?= htmlspecialchars($current_ws['judul']) ?>
                                <?php if ($current_ws['is_diskusi_active']): ?>
                                    <span
                                        class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full border border-emerald-200">Aktif</span>
                                <?php else: ?>
                                    <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full border border-red-200"><i
                                            class="fas fa-lock mr-1"></i> Terkunci</span>
                                <?php endif; ?>
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5 flex items-center gap-2">
                                <i class="fas fa-comments text-amber-500"></i> <span id="chatCount"><?= count($chats) ?></span>
                                Pesan
                            </p>
                        </div>

                        <!-- Toggle Button Form -->
                        <form method="POST">
                            <input type="hidden" name="workshop_id" value="<?= $active_ws_id ?>">
                            <input type="hidden" name="current_status" value="<?= $current_ws['is_diskusi_active'] ?>">
                            <button type="submit" name="toggle_diskusi"
                                class="px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2 
                                <?= $current_ws['is_diskusi_active']
                                    ? 'bg-red-50 text-red-600 hover:bg-red-100 border border-red-200'
                                    : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200' ?>">
                                <?php if ($current_ws['is_diskusi_active']): ?>
                                    <i class="fas fa-lock"></i> Kunci Diskusi
                                <?php else: ?>
                                    <i class="fas fa-lock-open"></i> Buka Diskusi
                                <?php endif; ?>
                            </button>
                        </form>
                    </div>

                    <!-- Chat Area -->
                    <div class="flex-grow overflow-y-auto p-6 space-y-4 bg-slate-50 scrollbar-thin scrollbar-thumb-gray-200"
                        id="chatContainer">
                        <?php if (count($chats) > 0): ?>
                            <?php foreach ($chats as $chat):
                                $is_me = ($chat['user_id'] == $user_id);
                                $is_participant = ($chat['role'] == 'peserta');

                                // Bubble Styles
                                if ($is_me) {
                                    $wrapperClass = 'justify-end';
                                    $bubbleClass = 'bg-emerald-600 text-white rounded-tr-none shadow-emerald-200';
                                    $metaClass = 'text-emerald-600';
                                    $name = 'Anda (Admin)';
                                } elseif ($is_participant) {
                                    $wrapperClass = 'justify-start';
                                    $bubbleClass = 'bg-white text-gray-700 border border-gray-200 rounded-tl-none';
                                    $metaClass = 'text-gray-500';
                                    $name = htmlspecialchars($chat['nama_lengkap']);
                                } else {
                                    $wrapperClass = 'justify-start';
                                    $bubbleClass = 'bg-amber-100 text-amber-900 border border-amber-200 rounded-tl-none';
                                    $metaClass = 'text-amber-600';
                                    $name = htmlspecialchars($chat['nama_lengkap']) . ' <i class="fas fa-check-circle text-amber-500 ml-1"></i>';
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
                                        <div class="px-4 py-3 rounded-2xl text-sm leading-relaxed shadow-sm <?= $bubbleClass ?>">
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
                                <p class="text-sm text-gray-400 mt-1">Sapa peserta Anda untuk memulai interaksi!</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Input Area (Fixed: Admin Selalu Bisa Kirim) -->
                    <div class="bg-white border-t border-gray-100 relative">

                        <?php if (!$current_ws['is_diskusi_active']): ?>
                            <!-- Banner Info - Tidak memblokir input -->
                            <div class="bg-red-50 px-4 py-2 flex justify-between items-center border-b border-red-100">
                                <div class="text-xs font-bold text-red-600 flex items-center gap-2">
                                    <i class="fas fa-lock"></i>
                                    Diskusi dikunci untuk peserta.
                                </div>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="workshop_id" value="<?= $active_ws_id ?>">
                                    <input type="hidden" name="current_status" value="0">
                                    <button type="submit" name="toggle_diskusi"
                                        class="text-xs text-red-700 hover:text-red-900 underline font-bold">Buka Diskusi</button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <div class="p-4">
                            <!-- ID chatForm ditambahkan untuk JS AJAX -->
                            <form id="chatForm" class="flex gap-3 relative items-end">
                                <input type="hidden" name="workshop_id" value="<?= $active_ws_id ?>">
                                <div class="relative flex-grow">
                                    <textarea name="pesan" required rows="1" id="chatInput"
                                        class="w-full pl-4 pr-12 py-3.5 rounded-2xl border border-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-gray-50 focus:bg-white transition-all resize-none overflow-hidden max-h-32 text-sm text-gray-700"
                                        placeholder="<?= $current_ws['is_diskusi_active'] ? 'Ketik pesan sebagai Admin...' : 'Anda tetap bisa mengirim pesan (Privilege Admin)...' ?>"></textarea>
                                </div>
                                <button type="submit" id="btnSend"
                                    class="h-[46px] w-[46px] bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition-all shadow-lg shadow-emerald-200 flex items-center justify-center flex-shrink-0 transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-paper-plane text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                </div>

            <?php endif; ?>

        <?php else: ?>

            <div
                class="flex flex-col items-center justify-center py-20 bg-white rounded-3xl shadow-lg border border-gray-100 text-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-chalkboard text-gray-300 text-4xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-700">Belum Ada Workshop</h3>
                <p class="text-gray-500 mt-2 mb-6 max-w-md">Anda belum membuat workshop atau event apapun.</p>
                <a href="form_event.php"
                    class="bg-emerald-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-emerald-700 transition">
                    Buat Event Baru
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

        // --- AJAX SEND MESSAGE LOGIC ---
        if (chatForm) {
            chatForm.addEventListener('submit', async function (e) {
                e.preventDefault();

                const message = chatInput.value.trim();
                if (!message) return;

                // 1. UI Optimis: Langsung tampilkan bubble "Loading/Pending"
                const tempId = Date.now(); // ID sementara
                const pendingBubble = createBubble(message, tempId, true);

                // Hapus empty state jika ada
                if (emptyState) emptyState.style.display = 'none';

                chatContainer.appendChild(pendingBubble);
                chatContainer.scrollTop = chatContainer.scrollHeight;

                // 2. Kirim Data ke Server via AJAX
                // FIX: Ambil data form SEBELUM mengosongkan input
                const formData = new FormData(this);

                // Reset Input UI
                chatInput.value = '';
                chatInput.style.height = 'auto';

                try {
                    const response = await fetch('ajax_chat_send.php', {
                        method: 'POST',
                        body: formData
                    });

                    // Cek jika response bukan 200 OK
                    if (!response.ok) {
                        throw new Error(`HTTP Error: ${response.status}`);
                    }

                    const result = await response.json();

                    if (result.status === 'success') {
                        // 3. Update Bubble jadi Sukses (Ganti Icon Jam jadi Centang/Jam Server)
                        updateBubbleSuccess(tempId, result.timestamp);
                    } else {
                        // Gagal server side
                        markBubbleFailed(tempId);
                        console.error('Server Error:', result.message);
                        if (result.message.includes('kosong')) {
                            alert('Pesan tidak terkirim: Data kosong.');
                        }
                    }
                } catch (error) {
                    // Gagal koneksi / File tidak ditemukan
                    markBubbleFailed(tempId);
                    console.error('Connection Error:', error);
                    if (error.message.includes('404')) {
                        alert('File admin/ajax_chat_send.php belum ditemukan! Mohon buat file tersebut terlebih dahulu.');
                    }
                }
            });
        }

        // Helper: Buat HTML Bubble (Mirip PHP)
        function createBubble(text, id, isPending) {
            const div = document.createElement('div');
            div.className = 'flex justify-end group animate-fade-in';
            div.id = `msg-${id}`;

            // Escape HTML untuk keamanan
            const escapedText = text.replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;")
                .replace(/\n/g, "<br>");

            const timeDisplay = isPending ? '<i class="fas fa-clock animate-pulse"></i>' : 'Baru saja';

            div.innerHTML = `
            <div class="max-w-[85%] sm:max-w-[75%]">
                <div class="flex items-center gap-2 mb-1 px-1 flex-row-reverse">
                    <span class="text-xs font-bold text-emerald-600">Anda (Admin)</span>
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

        // Helper: Update status jadi sukses
        function updateBubbleSuccess(id, timestamp) {
            const timeEl = document.getElementById(`time-${id}`);
            if (timeEl) {
                timeEl.innerText = timestamp;
            }
        }

        // Helper: Tandai gagal
        function markBubbleFailed(id) {
            const bubble = document.getElementById(`msg-${id}`);
            if (bubble) {
                const innerBubble = bubble.querySelector('.bg-emerald-600');
                innerBubble.classList.remove('bg-emerald-600');
                innerBubble.classList.add('bg-red-500'); // Merah tanda error
                innerBubble.title = "Gagal terkirim. Klik untuk coba lagi (Reload)";

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

<?php require_once __DIR__ . '/templates/footer.php'; ?>