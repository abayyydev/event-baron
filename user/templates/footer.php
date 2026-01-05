</main>
        </div>
        <div id="mobile-sidebar"
            class="fixed inset-0 z-50 transform -translate-x-full transition-transform duration-300 md:hidden">
            <div class="absolute inset-0 bg-gray-900 opacity-50" id="mobile-sidebar-backdrop"></div>

            <aside class="relative w-64 h-full bg-primary text-white shadow-xl flex flex-col">
                <div class="h-16 flex items-center justify-between px-4 border-b border-green-800 bg-dark">
                    <div class="font-bold text-lg"><i class="fas fa-mosque mr-2 text-secondary"></i> MENU</div>
                    <button id="close-sidebar-btn" class="text-white focus:outline-none">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1">
                    <a href="dashboard.php"
                        class="block px-4 py-3 rounded text-sm font-medium hover:bg-green-700 <?= $current_page == 'dashboard' ? 'bg-green-700 border-l-4 border-secondary' : '' ?>">
                        <i class="fas fa-home w-6"></i> Dashboard
                    </a>
                    <a href="riwayat_transaksi.php"
                        class="block px-4 py-3 rounded text-sm font-medium hover:bg-green-700 <?= $current_page == 'transaksi' ? 'bg-green-700 border-l-4 border-secondary' : '' ?>">
                        <i class="fas fa-receipt w-6"></i> Transaksi
                    </a>
                    <a href="materi.php"
                        class="block px-4 py-3 rounded text-sm font-medium hover:bg-green-700 <?= $current_page == 'materi' ? 'bg-green-700 border-l-4 border-secondary' : '' ?>">
                        <i class="fas fa-book-open w-6"></i> Materi
                    </a>
                    <a href="profil.php"
                        class="block px-4 py-3 rounded text-sm font-medium hover:bg-green-700 <?= $current_page == 'profil' ? 'bg-green-700 border-l-4 border-secondary' : '' ?>">
                        <i class="fas fa-user-cog w-6"></i> Profil
                    </a>
                    <a href="<?= BASE_URL ?>logout"
                        class="block px-4 py-3 rounded text-sm font-medium text-red-200 hover:bg-red-900/50 mt-4">
                        <i class="fas fa-sign-out-alt w-6"></i> Keluar
                    </a>
                </nav>
            </aside>
        </div>

    </div>

    <script>
        const mobileBtn = document.getElementById('mobile-menu-btn');
        const closeBtn = document.getElementById('close-sidebar-btn');
        const sidebar = document.getElementById('mobile-sidebar');
        const backdrop = document.getElementById('mobile-sidebar-backdrop');

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
        }

        mobileBtn.addEventListener('click', toggleSidebar);
        closeBtn.addEventListener('click', toggleSidebar);
        backdrop.addEventListener('click', toggleSidebar);
    </script>

</body>

</html>