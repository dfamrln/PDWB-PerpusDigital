<?php
require_once '../config/database.php';
require_once '../config/app.php';
require_once '../includes/functions.php';
requireAdmin();

$pageTitle = 'Pengembalian Buku - Admin';
$db = getDB();

// Proses pengembalian
if (isset($_GET['proses'])) {
    $id = (int)$_GET['proses'];
    $stmt = $db->prepare("SELECT p.*, b.id as buku_id FROM peminjaman p JOIN buku b ON p.buku_id = b.id WHERE p.id=? AND p.status='dipinjam'");
    $stmt->execute([$id]);
    $pinjam = $stmt->fetch();

    if ($pinjam) {
        $tgl_kembali = date('Y-m-d');
        $denda = hitungDenda($pinjam['tanggal_jatuh_tempo'], $tgl_kembali);

        $db->prepare("UPDATE peminjaman SET status='dikembalikan', tanggal_kembali=?, denda=? WHERE id=?")
           ->execute([$tgl_kembali, $denda, $id]);

        $db->prepare("UPDATE buku SET stok_tersedia = stok_tersedia + 1 WHERE id=?")
           ->execute([$pinjam['buku_id']]);

        if ($denda > 0) {
            setFlash('warning', 'Buku dikembalikan. Denda keterlambatan: ' . formatRupiah($denda));
        } else {
            setFlash('success', 'Buku berhasil dikembalikan. Tidak ada denda.');
        }
        redirect(url('admin/pengembalian.php'));
    }
}

// Bayar denda
if (isset($_GET['bayar'])) {
    $id = (int)$_GET['bayar'];
    $db->prepare("UPDATE peminjaman SET denda_dibayar=1 WHERE id=?")->execute([$id]);
    setFlash('success', 'Denda telah ditandai sebagai dibayar.');
    redirect(url('admin/pengembalian.php'));
}

// Aktif yang bisa dikembalikan
$aktif = $db->query("
    SELECT p.*, b.judul, b.pengarang, a.nama as anggota_nama
    FROM peminjaman p
    JOIN buku b ON p.buku_id = b.id
    JOIN anggota a ON p.anggota_id = a.id
    WHERE p.status = 'dipinjam'
    ORDER BY p.tanggal_jatuh_tempo ASC
")->fetchAll();

// Riwayat pengembalian
$riwayat = $db->query("
    SELECT p.*, b.judul, a.nama as anggota_nama
    FROM peminjaman p
    JOIN buku b ON p.buku_id = b.id
    JOIN anggota a ON p.anggota_id = a.id
    WHERE p.status = 'dikembalikan'
    ORDER BY p.tanggal_kembali DESC LIMIT 20
")->fetchAll();

include 'header.php';
?>

<h1 class="font-display text-2xl font-bold text-slate-800 mb-6">🔄 Pengembalian Buku</h1>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm mb-8">
    <div class="px-6 py-4 border-b border-slate-100">
        <h2 class="font-semibold text-slate-800">📚 Buku Sedang Dipinjam (<?= count($aktif) ?>)</h2>
    </div>
    <?php if (empty($aktif)): ?>
        <div class="p-8 text-center text-slate-400 text-sm">Tidak ada buku yang sedang dipinjam.</div>
    <?php else: ?>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Buku</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Anggota</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Jatuh Tempo</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Estimasi Denda</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            <?php foreach ($aktif as $p): ?>
                <?php
                $hari_sisa = hariSisaPinjam($p['tanggal_jatuh_tempo']);
                $denda_est = $hari_sisa < 0 ? abs($hari_sisa) * DENDA_PER_HARI : 0;
                $terlambat = $hari_sisa < 0;
                ?>
                <tr class="hover:bg-slate-50 <?= $terlambat ? 'bg-red-50/40' : '' ?>">
                    <td class="px-4 py-3 font-medium text-slate-800"><?= e($p['judul']) ?></td>
                    <td class="px-4 py-3 text-slate-600"><?= e($p['anggota_nama']) ?></td>
                    <td class="px-4 py-3">
                        <span class="<?= $terlambat ? 'text-red-600 font-semibold' : 'text-slate-600' ?>">
                            <?= formatTanggal($p['tanggal_jatuh_tempo']) ?>
                            <?php if ($terlambat): ?>
                                <br><span class="text-xs text-red-500">Terlambat <?= abs($hari_sisa) ?> hari</span>
                            <?php elseif ($hari_sisa <= 2): ?>
                                <br><span class="text-xs text-yellow-600">⚠️ <?= $hari_sisa ?> hari lagi</span>
                            <?php endif; ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <?php if ($denda_est > 0): ?>
                            <span class="text-red-600 font-bold"><?= formatRupiah($denda_est) ?></span>
                        <?php else: ?>
                            <span class="text-green-500 text-xs">Tidak ada</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <a href="?proses=<?= $p['id'] ?>"
                           onclick="return confirm('Catat pengembalian buku ini?<?= $denda_est > 0 ? ' Denda: ' . formatRupiah($denda_est) : '' ?>')"
                           class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                            🔄 Proses Kembali
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<!-- Riwayat Pengembalian -->
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
    <div class="px-6 py-4 border-b border-slate-100">
        <h2 class="font-semibold text-slate-800">📜 Riwayat Pengembalian</h2>
    </div>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-slate-600">Buku</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600">Anggota</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600">Tgl Kembali</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600">Denda</th>
                <th class="px-4 py-3 text-center font-semibold text-slate-600">Pembayaran</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
        <?php foreach ($riwayat as $p): ?>
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-medium text-slate-800 line-clamp-1"><?= e($p['judul']) ?></td>
                <td class="px-4 py-3 text-slate-600"><?= e($p['anggota_nama']) ?></td>
                <td class="px-4 py-3 text-slate-600"><?= formatTanggal($p['tanggal_kembali']) ?></td>
                <td class="px-4 py-3">
                    <?php if ($p['denda'] > 0): ?>
                        <span class="<?= $p['denda_dibayar'] ? 'text-green-600' : 'text-red-600' ?> font-semibold">
                            <?= formatRupiah($p['denda']) ?>
                        </span>
                    <?php else: ?>
                        <span class="text-slate-300">-</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-center">
                    <?php if ($p['denda'] > 0 && !$p['denda_dibayar']): ?>
                        <a href="?bayar=<?= $p['id'] ?>" onclick="return confirm('Tandai denda sebagai dibayar?')"
                           class="bg-green-500 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg hover:bg-green-600 transition">
                            💰 Bayar
                        </a>
                    <?php elseif ($p['denda'] > 0 && $p['denda_dibayar']): ?>
                        <span class="bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-1.5 rounded-full">✅ Lunas</span>
                    <?php else: ?>
                        <span class="text-slate-300 text-xs">—</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<?php include 'footer.php'; ?>