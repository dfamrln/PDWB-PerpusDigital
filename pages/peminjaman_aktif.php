<?php
require_once '../config/database.php';
require_once '../config/app.php';
require_once '../includes/functions.php';

requireLogin();
$pageTitle = 'Pinjaman Aktif - PerpusDigital';
$db = getDB();
$anggota_id = $_SESSION['anggota_id'];

$stmt = $db->prepare("
    SELECT p.*, b.judul, b.pengarang, b.kategori
    FROM peminjaman p
    JOIN buku b ON p.buku_id = b.id
    WHERE p.anggota_id = ? AND p.status IN ('menunggu','dipinjam')
    ORDER BY p.created_at DESC
");
$stmt->execute([$anggota_id]);
$pinjamans = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="max-w-3xl mx-auto">
    <h1 class="font-display text-2xl font-bold text-slate-800 mb-6">📋 Pinjaman Aktif</h1>

    <?php if (empty($pinjamans)): ?>
        <div class="bg-white rounded-2xl border border-slate-100 p-12 text-center text-slate-400">
            <div class="text-5xl mb-3">📭</div>
            <p class="text-lg font-medium text-slate-600 mb-2">Tidak ada pinjaman aktif</p>
            <a href="<?= url() ?>" class="text-indigo-600 hover:underline text-sm">Jelajahi katalog buku →</a>
        </div>
    <?php else: ?>
        <div class="space-y-4">
        <?php foreach ($pinjamans as $p): ?>
            <?php
            $hariSisa = $p['tanggal_jatuh_tempo'] ? hariSisaPinjam($p['tanggal_jatuh_tempo']) : null;
            $dendaEstimasi = $p['tanggal_jatuh_tempo'] && $hariSisa < 0 ? abs($hariSisa) * DENDA_PER_HARI : 0;
            ?>
            <div class="bg-white rounded-2xl border <?= $hariSisa !== null && $hariSisa < 0 ? 'border-red-200' : ($hariSisa !== null && $hariSisa <= 2 ? 'border-yellow-200' : 'border-slate-100') ?> shadow-sm p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex gap-4 flex-1">
                        <div class="text-3xl mt-1">
                            <?= ['Novel' => '📖', 'Self-Help' => '🌱', 'Sejarah' => '🏛️', 'Keuangan' => '💰', 'Teknologi' => '💻', 'Filsafat' => '🧠', 'Fiksi' => '✨'][$p['kategori']] ?? '📚' ?>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800"><?= e($p['judul']) ?></h3>
                            <p class="text-sm text-slate-500"><?= e($p['pengarang']) ?></p>
                        </div>
                    </div>
                    <div>
                        <?php if ($p['status'] === 'menunggu'): ?>
                            <span class="bg-yellow-100 text-yellow-700 text-xs font-semibold px-3 py-1 rounded-full">⏳ Menunggu</span>
                        <?php else: ?>
                            <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full">📚 Dipinjam</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($p['status'] === 'dipinjam'): ?>
                    <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-400">Tgl Pinjam</p>
                            <p class="font-medium text-slate-700"><?= formatTanggal($p['tanggal_pinjam']) ?></p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-400">Jatuh Tempo</p>
                            <p class="font-medium text-slate-700"><?= formatTanggal($p['tanggal_jatuh_tempo']) ?></p>
                        </div>
                        <div class="<?= $hariSisa < 0 ? 'bg-red-50' : ($hariSisa <= 2 ? 'bg-yellow-50' : 'bg-green-50') ?> rounded-lg p-3">
                            <p class="text-xs text-slate-400">Sisa Waktu</p>
                            <p class="font-bold <?= $hariSisa < 0 ? 'text-red-600' : ($hariSisa <= 2 ? 'text-yellow-600' : 'text-green-600') ?>">
                                <?php if ($hariSisa < 0): ?>
                                    ⚠️ Terlambat <?= abs($hariSisa) ?> hari
                                <?php elseif ($hariSisa === 0): ?>
                                    🔴 Hari ini!
                                <?php elseif ($hariSisa <= 2): ?>
                                    ⚠️ <?= $hariSisa ?> hari lagi
                                <?php else: ?>
                                    ✅ <?= $hariSisa ?> hari lagi
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <?php if ($dendaEstimasi > 0): ?>
                        <div class="mt-3 bg-red-50 border border-red-200 rounded-lg px-4 py-2 text-sm text-red-700">
                            💸 Estimasi denda saat ini: <strong><?= formatRupiah($dendaEstimasi) ?></strong> (bertambah <?= formatRupiah(DENDA_PER_HARI) ?>/hari)
                        </div>
                    <?php elseif ($hariSisa !== null && $hariSisa <= 2 && $hariSisa >= 0): ?>
                        <div class="mt-3 bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-2 text-sm text-yellow-700">
                            ⚠️ Segera kembalikan! Jatuh tempo dalam <?= $hariSisa ?> hari.
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-xs text-slate-400 mt-3">Pengajuan sedang menunggu persetujuan petugas perpustakaan.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>