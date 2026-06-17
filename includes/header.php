<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Perpustakaan Digital' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .font-display { font-family: 'Playfair Display', serif; }
        .nav-link { @apply text-slate-600 hover:text-indigo-600 font-medium transition-colors; }
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            transition: all 0.2s;
        }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); box-shadow: 0 4px 15px rgba(79,70,229,0.4); }
        .card-book { transition: all 0.25s ease; }
        .card-book:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,0.12); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

<?php
// Pastikan app.php sudah di-load (bisa dipanggil dari file manapun)
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/app.php';
}
?>

<!-- NAVBAR -->
<nav class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="<?= url() ?>" class="flex items-center gap-2">
                <span class="text-2xl">📚</span>
                <span class="font-display text-xl text-indigo-700 font-bold">PerpusDigital</span>
            </a>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center gap-6">
                <a href="<?= url() ?>" class="nav-link">Katalog</a>
                <?php if (isLoggedIn()): ?>
                    <a href="<?= url('pages/riwayat.php') ?>" class="nav-link">Riwayat Saya</a>
                    <a href="<?= url('pages/peminjaman_aktif.php') ?>" class="nav-link">Pinjaman Aktif</a>
                    <div class="flex items-center gap-3 ml-4 pl-4 border-l border-slate-200">
                        <span class="text-sm text-slate-500">Halo, <strong class="text-slate-800"><?= e(currentUser()['nama']) ?></strong></span>
                        <a href="<?= url('pages/logout.php') ?>" class="text-sm text-red-500 hover:text-red-700 font-medium">Keluar</a>
                    </div>
                <?php else: ?>
                    <a href="<?= url('login') ?>" class="nav-link">Masuk</a>
                    <a href="<?= url('register') ?>" class="btn-primary text-white px-4 py-2 rounded-lg text-sm font-semibold">Daftar</a>
                <?php endif; ?>
            </div>

            <!-- Mobile menu button -->
            <button id="mobile-btn" class="md:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden border-t border-slate-100 px-4 py-3 space-y-2 bg-white">
        <a href="<?= url() ?>" class="block py-2 text-slate-700">Katalog</a>
        <?php if (isLoggedIn()): ?>
            <a href="<?= url('pages/riwayat.php') ?>" class="block py-2 text-slate-700">Riwayat Saya</a>
            <a href="<?= url('pages/peminjaman_aktif.php') ?>" class="block py-2 text-slate-700">Pinjaman Aktif</a>
            <a href="<?= url('pages/logout.php') ?>" class="block py-2 text-red-500">Keluar</a>
        <?php else: ?>
            <a href="<?= url('login') ?>" class="block py-2 text-slate-700">Masuk</a>
            <a href="<?= url('register') ?>" class="block py-2 text-indigo-600 font-semibold">Daftar</a>
        <?php endif; ?>
    </div>
</nav>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
<?php $flash = showFlash(); if ($flash) echo $flash; ?>