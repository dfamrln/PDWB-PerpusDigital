<!-- HEADER.PHP -->
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard Admin - PerpusDigital' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .font-display { font-family: 'Playfair Display', serif; }
        .sidebar { width: 240px; min-width: 240px; flex-shrink: 0; }
        .sidebar-link {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 16px; border-radius: 10px;
            color: #94a3b8; font-size: 14px; font-weight: 500;
            text-decoration: none; transition: all 0.15s;
            white-space: nowrap;
        }
        .sidebar-link:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .sidebar-link.active { background: rgba(255,255,255,0.15); color: #fff; }
        .sidebar-link .icon { font-size: 16px; flex-shrink: 0; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen">
<div class="flex min-h-screen">

<!-- ══════════ SIDEBAR ══════════ -->
<aside class="sidebar bg-gradient-to-b from-slate-800 to-slate-900 min-h-screen flex flex-col fixed top-0 left-0 z-40">

    <!-- Logo -->
    <div class="px-5 py-5 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-indigo-500 rounded-lg flex items-center justify-center text-lg">📚</div>
            <div>
                <p class="font-display text-white font-bold text-base leading-tight">PerpusDigital</p>
                <p class="text-slate-400 text-xs">Panel Petugas</p>
            </div>
        </div>
    </div>

    <!-- Nav -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-3 mb-3">Menu</p>

        <a href="/perpustakaan/admin/dashboard.php"
           class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
            <span class="icon">📊</span> Dashboard
        </a>
        <a href="/perpustakaan/admin/buku.php"
           class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'buku.php' ? 'active' : '' ?>">
            <span class="icon">📖</span> Kelola Buku
        </a>
        <a href="/perpustakaan/admin/peminjaman.php"
           class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'peminjaman.php' ? 'active' : '' ?>">
            <span class="icon">📋</span> Pengajuan Pinjam
        </a>
        <a href="/perpustakaan/admin/pengembalian.php"
           class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'pengembalian.php' ? 'active' : '' ?>">
            <span class="icon">🔄</span> Pengembalian
        </a>
        <a href="/perpustakaan/admin/anggota.php"
           class="sidebar-link <?= basename($_SERVER['PHP_SELF']) === 'anggota.php' ? 'active' : '' ?>">
            <span class="icon">👥</span> Data Anggota
        </a>
    </nav>

    <!-- User & Logout -->
    <div class="px-3 py-4 border-t border-white/10 space-y-3">
        <div class="flex items-center gap-3 px-3">
            <div class="w-9 h-9 bg-indigo-500 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                <?= strtoupper(substr($_SESSION['petugas']['nama'] ?? 'A', 0, 1)) ?>
            </div>
            <div class="overflow-hidden">
                <p class="text-white text-sm font-semibold truncate"><?= htmlspecialchars($_SESSION['petugas']['nama'] ?? 'Admin') ?></p>
                <p class="text-slate-400 text-xs">Petugas</p>
            </div>
        </div>
        <a href="/perpustakaan/admin/logout.php"
           class="sidebar-link text-red-400 hover:text-red-300 hover:bg-red-500/10">
            <span class="icon">🚪</span> Keluar
        </a>
    </div>
</aside>

<!-- ══════════ MAIN WRAPPER ══════════ -->
<div class="flex-1 flex flex-col" style="margin-left: 240px;">

    <!-- Top bar -->
    <header class="bg-white border-b border-slate-200 px-6 py-3 flex items-center justify-between sticky top-0 z-30">
        <div>
            <p class="text-slate-800 font-semibold text-sm"><?= $pageTitle ?? 'Dashboard' ?></p>
            <p class="text-slate-400 text-xs"><?= date('l, d F Y') ?></p>
        </div>
        <a href="/perpustakaan/index.php" target="_blank"
           class="text-xs text-indigo-600 hover:underline flex items-center gap-1">
            🌐 Lihat Katalog
        </a>
    </header>

    <!-- Flash message + Page content -->
    <main class="flex-1 p-6">
        <?php
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            $colors = [
                'success' => 'bg-green-100 border-green-400 text-green-800',
                'error'   => 'bg-red-100 border-red-400 text-red-800',
                'info'    => 'bg-blue-100 border-blue-400 text-blue-800',
                'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-800',
            ];
            $icons = ['success'=>'✅','error'=>'❌','info'=>'ℹ️','warning'=>'⚠️'];
            $cls   = $colors[$flash['type']] ?? $colors['info'];
            $icon  = $icons[$flash['type']] ?? '';
            echo "<div class=\"{$cls} border px-4 py-3 rounded-lg mb-5 flex items-center gap-2\">
                    <span>{$icon}</span>
                    <span>" . htmlspecialchars($flash['message']) . "</span>
                  </div>";
        }
        ?>