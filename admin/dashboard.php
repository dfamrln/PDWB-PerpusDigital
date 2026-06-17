<!-- DASHBOARD.PHP -->
<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireAdmin();

$pageTitle = 'Dashboard - Admin PerpusDigital';
$db = getDB();

$stats = [
    'total_buku'      => $db->query("SELECT COUNT(*) FROM buku")->fetchColumn(),
    'total_anggota'   => $db->query("SELECT COUNT(*) FROM anggota")->fetchColumn(),
    'total_dipinjam'  => $db->query("SELECT COUNT(*) FROM peminjaman WHERE status='dipinjam'")->fetchColumn(),
    'total_menunggu'  => $db->query("SELECT COUNT(*) FROM peminjaman WHERE status='menunggu'")->fetchColumn(),
    'total_terlambat' => $db->query("SELECT COUNT(*) FROM peminjaman WHERE status='dipinjam' AND tanggal_jatuh_tempo < CURDATE()")->fetchColumn(),
    'total_denda'     => $db->query("SELECT COALESCE(SUM(denda),0) FROM peminjaman WHERE denda_dibayar=0 AND denda>0")->fetchColumn(),
];

$pengajuan = $db->query("
    SELECT p.*, b.judul, a.nama as anggota_nama
    FROM peminjaman p
    JOIN buku b ON p.buku_id = b.id
    JOIN anggota a ON p.anggota_id = a.id
    WHERE p.status = 'menunggu'
    ORDER BY p.created_at DESC LIMIT 5
")->fetchAll();

$terlambat_list = $db->query("
    SELECT p.*, b.judul, a.nama as anggota_nama,
           DATEDIFF(CURDATE(), p.tanggal_jatuh_tempo) as hari_terlambat
    FROM peminjaman p
    JOIN buku b ON p.buku_id = b.id
    JOIN anggota a ON p.anggota_id = a.id
    WHERE p.status = 'dipinjam' AND p.tanggal_jatuh_tempo < CURDATE()
    ORDER BY hari_terlambat DESC LIMIT 5
")->fetchAll();

include 'header.php';
?>

<!-- PAGE TITLE -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Dashboard</h1>
    <p class="text-slate-500 text-sm mt-1">Selamat datang, <strong><?= e(currentAdmin()['nama']) ?></strong>!</p>
</div>

<!-- STAT CARDS -->
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
    <?php
    $cards = [
        ['📖', 'Total Buku',          $stats['total_buku'],                    'text-indigo-600', 'bg-indigo-50'],
        ['👥', 'Anggota',             $stats['total_anggota'],                 'text-purple-600', 'bg-purple-50'],
        ['📚', 'Sedang Dipinjam',     $stats['total_dipinjam'],                'text-blue-600',   'bg-blue-50'],
        ['⏳', 'Menunggu Persetujuan',$stats['total_menunggu'],                'text-yellow-600', 'bg-yellow-50'],
        ['⚠️', 'Terlambat',           $stats['total_terlambat'],               'text-orange-600', 'bg-orange-50'],
        ['💸', 'Denda Belum Dibayar', formatRupiah($stats['total_denda']),     'text-red-600',    'bg-red-50'],
    ];
    foreach ($cards as [$icon, $label, $val, $textCls, $bgCls]):
    ?>
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col gap-3">
        <div class="w-10 h-10 <?= $bgCls ?> rounded-xl flex items-center justify-center text-xl">
            <?= $icon ?>
        </div>
        <div>
            <p class="font-bold text-2xl <?= $textCls ?>"><?= $val ?></p>
            <p class="text-xs text-slate-500 mt-0.5 leading-snug"><?= $label ?></p>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

    <!-- PENGAJUAN BARU -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">⏳ Pengajuan Menunggu</h2>
            <a href="/perpustakaan/admin/peminjaman.php" class="text-xs text-indigo-600 hover:underline font-medium">Lihat Semua →</a>
        </div>

        <?php if (empty($pengajuan)): ?>
            <div class="p-10 text-center text-slate-400 text-sm">
                <p class="text-3xl mb-2">📭</p>
                Tidak ada pengajuan baru.
            </div>
        <?php else: ?>
            <div class="divide-y divide-slate-100">
            <?php foreach ($pengajuan as $p): ?>
                <div class="px-6 py-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 bg-indigo-100 rounded-xl flex items-center justify-center text-lg flex-shrink-0">📖</div>
                        <div class="min-w-0">
                            <p class="font-medium text-slate-800 text-sm truncate"><?= e($p['judul']) ?></p>
                            <p class="text-xs text-slate-500">oleh <strong><?= e($p['anggota_nama']) ?></strong> · <?= formatTanggal($p['created_at']) ?></p>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <a href="/perpustakaan/admin/peminjaman.php?approve=<?= $p['id'] ?>"
                           onclick="return confirm('Setujui peminjaman ini?')"
                           class="bg-green-500 hover:bg-green-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                            ✅ Setujui
                        </a>
                        <a href="/perpustakaan/admin/peminjaman.php?reject=<?= $p['id'] ?>"
                           onclick="return confirm('Tolak pengajuan ini?')"
                           class="bg-red-100 hover:bg-red-200 text-red-600 text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                            ❌ Tolak
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- BUKU TERLAMBAT -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">⚠️ Buku Terlambat Dikembalikan</h2>
            <a href="/perpustakaan/admin/pengembalian.php" class="text-xs text-indigo-600 hover:underline font-medium">Proses →</a>
        </div>

        <?php if (empty($terlambat_list)): ?>
            <div class="p-10 text-center text-slate-400 text-sm">
                <p class="text-3xl mb-2">✅</p>
                Tidak ada buku yang terlambat.
            </div>
        <?php else: ?>
            <div class="divide-y divide-slate-100">
            <?php foreach ($terlambat_list as $t): ?>
                <div class="px-6 py-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 bg-red-100 rounded-xl flex items-center justify-center text-lg flex-shrink-0">📕</div>
                        <div class="min-w-0">
                            <p class="font-medium text-slate-800 text-sm truncate"><?= e($t['judul']) ?></p>
                            <p class="text-xs text-slate-500"><?= e($t['anggota_nama']) ?></p>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-red-600 font-bold text-sm"><?= $t['hari_terlambat'] ?> hari</p>
                        <p class="text-xs text-red-400"><?= formatRupiah($t['hari_terlambat'] * 1000) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include 'footer.php'; ?>