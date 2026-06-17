<?php
require_once '../config/database.php';
require_once '../config/app.php';
require_once '../includes/functions.php';

requireLogin();
$pageTitle = 'Riwayat Peminjaman - PerpusDigital';
$db = getDB();
$anggota_id = $_SESSION['anggota_id'];

$stmt = $db->prepare("
    SELECT p.*, b.judul, b.pengarang, b.kategori
    FROM peminjaman p
    JOIN buku b ON p.buku_id = b.id
    WHERE p.anggota_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$anggota_id]);
$riwayat = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="max-w-3xl mx-auto">
    <h1 class="font-display text-2xl font-bold text-slate-800 mb-6">📜 Riwayat Peminjaman</h1>

    <?php if (empty($riwayat)): ?>
        <div class="bg-white rounded-2xl border border-slate-100 p-12 text-center text-slate-400">
            <div class="text-5xl mb-3">📭</div>
            <p>Belum ada riwayat peminjaman.</p>
            <a href="<?= url() ?>" class="text-indigo-600 hover:underline text-sm mt-2 block">Mulai pinjam buku →</a>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Buku</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 hidden sm:table-cell">Tgl Pinjam</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 hidden sm:table-cell">Tgl Kembali</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Denda</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($riwayat as $r): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-800 line-clamp-1"><?= e($r['judul']) ?></p>
                                <p class="text-xs text-slate-400"><?= e($r['pengarang']) ?></p>
                            </td>
                            <td class="px-4 py-3 text-slate-600 hidden sm:table-cell">
                                <?= formatTanggal($r['tanggal_pinjam']) ?>
                            </td>
                            <td class="px-4 py-3 text-slate-600 hidden sm:table-cell">
                                <?= formatTanggal($r['tanggal_kembali']) ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php
                                $badges = [
                                    'menunggu'     => 'bg-yellow-100 text-yellow-700',
                                    'dipinjam'     => 'bg-blue-100 text-blue-700',
                                    'dikembalikan' => 'bg-green-100 text-green-700',
                                    'ditolak'      => 'bg-red-100 text-red-700',
                                ];
                                $labels = ['menunggu' => '⏳ Menunggu', 'dipinjam' => '📚 Dipinjam', 'dikembalikan' => '✅ Kembali', 'ditolak' => '❌ Ditolak'];
                                $cls = $badges[$r['status']] ?? '';
                                ?>
                                <span class="<?= $cls ?> text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
                                    <?= $labels[$r['status']] ?? $r['status'] ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <?php if ($r['denda'] > 0): ?>
                                    <span class="<?= $r['denda_dibayar'] ? 'text-green-600' : 'text-red-600' ?> font-medium">
                                        <?= formatRupiah($r['denda']) ?>
                                        <?= $r['denda_dibayar'] ? '✓' : '' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-300">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>