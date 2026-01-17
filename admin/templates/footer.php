</main>

<footer class="bg-white border-t border-gray-200 mt-auto transition-all duration-300">
    <div class="w-full px-6 py-4">
        <div class="flex flex-col md:flex-row justify-between items-center gap-3">

            <div class="text-sm text-gray-500">
                &copy; <?= date('Y') ?> <span class="font-bold text-primary-700">Pondok Pesantren Al Ihsan Baron</span>.
                All rights reserved.
            </div>

            <div class="flex items-center gap-4 text-xs font-medium text-gray-400">

                <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                <span>v1.0.0</span>
            </div>

        </div>
    </div>
</footer>

</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>

<script src="../assets/js/modal.js" defer></script>

<script
    src="<?= defined('BASE_PATH') ? 'http://localhost/workshop-app-baron/assets/js/script.js' : '../assets/js/script.js' ?>"></script>

<script>
    // Menutup sidebar otomatis saat layar diresize ke desktop
    window.addEventListener('resize', () => {
        const overlay = document.getElementById('mobile-overlay');
        const sidebar = document.getElementById('sidebar');

        if (window.innerWidth >= 1024) {
            if (overlay) overlay.classList.add('hidden');
            if (sidebar) sidebar.classList.remove('-translate-x-full');
        }
    });
</script>

</body>

</html>