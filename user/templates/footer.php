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