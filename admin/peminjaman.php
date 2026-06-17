<?php
require_once '../config/database.php';
require_once '../config/app.php';
require_once '../includes/functions.php';
requireAdmin();

$pageTitle = 'Pengajuan Pinjam - Admin';
$db = getDB();

// Setujui
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $p = $db->prepare("SELECT * FROM peminjaman WHERE id=? AND status='menunggu'");
    $p->execute([$id]);
    $pinjam = $p->fetch();

    if ($pinjam) {
        $tgl_pinjam = date('Y-m-d');
        $tgl_tempo  = date('Y-m-d', strtotime("+". LAMA_PINJAM . " days"));

        $db->prepare("UPDATE peminjaman SET status='dipinjam', tanggal_pinjam=?, tanggal_jatuh_tempo=? WHERE id=?")
           ->execute([$tgl_pinjam, $tgl_tempo, $id]);

        $db->prepare("UPDATE buku SET stok_tersedia = stok_tersedia - 1 WHERE id=? AND stok_tersedia > 0")
           ->execute([$pinjam['buku_id']]);

        setFlash('success', 'Peminjaman disetujui. Jatuh tempo: ' . formatTanggal($tgl_tempo));
    }
    redirect(url('admin/peminjaman.php'));
}

// Tolak
if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];
    $db->prepare("UPDATE peminjaman SET status='ditolak' WHERE id=? AND status='menunggu'")->execute([$id]);
    setFlash('info', 'Pengajuan ditolak.');
    redirect(url('admin/peminjaman.php'));
}

$tab = $_GET['tab'] ?? 'menunggu';
$validTabs = ['menunggu', 'dipinjam', 'semua'];
if (!in_array($tab, $validTabs)) $tab = 'menunggu';

$where = $tab !== 'semua' ? "WHERE p.status = '$tab'" : '';
$pinjamans = $db->query("
    SELECT p.*, b.judul, b.pengarang, a.nama as anggota_nama, a.email as anggota_email
    FROM peminjaman p
    JOIN buku b ON p.buku_id = b.id
    JOIN anggota a ON p.anggota_id = a.id
    $where
    ORDER BY p.created_at DESC
")->fetchAll();

include 'header.php';
?>

<h1 class="font-display text-2xl font-bold text-slate-800 mb-6">📋 Manajemen Peminjaman</h1>

<!-- Tabs -->
<div class="flex gap-2 mb-6 border-b border-slate-200">
    <?php foreach (['menunggu' => '⏳ Menunggu', 'dipinjam' => '📚 Aktif', 'semua' => '📜 Semua'] as $key => $label): ?>
        <a href="?tab=<?= $key ?>"
           class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition <?= $tab === $key ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' ?>">
            <?= $label ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if (empty($pinjamans)): ?>
    <div class="bg-white rounded-2xl p-12 text-center text-slate-400 border border-slate-100">Tidak ada data.</div>
<?php else: ?>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Buku</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Anggota</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 hidden lg:table-cell">Tgl Pinjam</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 hidden lg:table-cell">Jatuh Tempo</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600">Status</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            <?php foreach ($pinjamans as $p): ?>
                <?php
                $terlambat = $p['status'] === 'dipinjam' && $p['tanggal_jatuh_tempo'] && strtotime($p['tanggal_jatuh_tempo']) < time();
                ?>
                <tr class="hover:bg-slate-50 <?= $terlambat ? 'bg-red-50/30' : '' ?>">
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-800 line-clamp-1"><?= e($p['judul']) ?></p>
                        <p class="text-xs text-slate-400"><?= e($p['pengarang']) ?></p>
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-700"><?= e($p['anggota_nama']) ?></p>
                        <p class="text-xs text-slate-400"><?= e($p['anggota_email']) ?></p>
                    </td>
                    <td class="px-4 py-3 text-slate-600 hidden lg:table-cell"><?= formatTanggal($p['tanggal_pinjam']) ?></td>
                    <td class="px-4 py-3 hidden lg:table-cell">
                        <span class="<?= $terlambat ? 'text-red-600 font-semibold' : 'text-slate-600' ?>">
                            <?= formatTanggal($p['tanggal_jatuh_tempo']) ?>
                            <?= $terlambat ? '⚠️' : '' ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <?php
                        $bs = ['menunggu' => 'bg-yellow-100 text-yellow-700', 'dipinjam' => 'bg-blue-100 text-blue-700', 'dikembalikan' => 'bg-green-100 text-green-700', 'ditolak' => 'bg-red-100 text-red-700'];
                        ?>
                        <span class="<?= $bs[$p['status']] ?> text-xs font-semibold px-2 py-1 rounded-full"><?= ucfirst($p['status']) ?></span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-1 justify-center">
                            <?php if ($p['status'] === 'menunggu'): ?>
                                <a href="?approve=<?= $p['id'] ?>" onclick="return confirm('Setujui?')"
                                   class="bg-green-500 text-white text-xs font-medium px-2.5 py-1.5 rounded-lg hover:bg-green-600 transition">✅ Setujui</a>
                                <a href="?reject=<?= $p['id'] ?>" onclick="return confirm('Tolak?')"
                                   class="bg-red-500 text-white text-xs font-medium px-2.5 py-1.5 rounded-lg hover:bg-red-600 transition">❌ Tolak</a>
                            <?php elseif ($p['status'] === 'dipinjam'): ?>
                                <a href="<?= url('admin/pengembalian.php') ?>?proses=<?= $p['id'] ?>"
                                   class="bg-indigo-500 text-white text-xs font-medium px-2.5 py-1.5 rounded-lg hover:bg-indigo-600 transition">🔄 Kembalikan</a>
                            <?php else: ?>
                                <span class="text-xs text-slate-300">—</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>