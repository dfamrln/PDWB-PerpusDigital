<?php
require_once '../config/database.php';
require_once '../config/app.php';
require_once '../includes/functions.php';
requireAdmin();

$pageTitle = 'Data Anggota - Admin';
$db = getDB();

$anggota = $db->query("
    SELECT a.*,
        COUNT(CASE WHEN p.status='dipinjam' THEN 1 END) as pinjam_aktif,
        COUNT(p.id) as total_pinjam
    FROM anggota a
    LEFT JOIN peminjaman p ON a.id = p.anggota_id
    GROUP BY a.id
    ORDER BY a.created_at DESC
")->fetchAll();

include 'header.php';
?>

<h1 class="font-display text-2xl font-bold text-slate-800 mb-6">👥 Data Anggota</h1>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-slate-600">Nama</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600 hidden md:table-cell">Email</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600 hidden lg:table-cell">No. Telp</th>
                <th class="px-4 py-3 text-center font-semibold text-slate-600">Pinjam Aktif</th>
                <th class="px-4 py-3 text-center font-semibold text-slate-600">Total Pinjam</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600 hidden lg:table-cell">Bergabung</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
        <?php foreach ($anggota as $a): ?>
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold text-sm">
                            <?= strtoupper(substr($a['nama'], 0, 1)) ?>
                        </div>
                        <span class="font-medium text-slate-800"><?= e($a['nama']) ?></span>
                    </div>
                </td>
                <td class="px-4 py-3 text-slate-600 hidden md:table-cell"><?= e($a['email']) ?></td>
                <td class="px-4 py-3 text-slate-500 hidden lg:table-cell"><?= e($a['no_telp']) ?: '-' ?></td>
                <td class="px-4 py-3 text-center">
                    <span class="font-bold <?= $a['pinjam_aktif'] > 0 ? 'text-blue-600' : 'text-slate-400' ?>">
                        <?= $a['pinjam_aktif'] ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-center text-slate-600"><?= $a['total_pinjam'] ?></td>
                <td class="px-4 py-3 text-slate-400 text-xs hidden lg:table-cell"><?= formatTanggal($a['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>