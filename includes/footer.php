</main>

<footer class="bg-white border-t border-slate-200 mt-16">
    <div class="max-w-7xl mx-auto px-4 py-8 text-center text-slate-500 text-sm">
        <p class="font-display text-indigo-700 text-lg mb-1">📚 PerpusDigital</p>
        <p>Sistem Manajemen Perpustakaan Online &copy; <?= date('Y') ?></p>
    </div>
</footer>

<script>
    // Mobile menu toggle
    document.getElementById('mobile-btn')?.addEventListener('click', () => {
        document.getElementById('mobile-menu').classList.toggle('hidden');
    });
</script>
</body>
</html>
