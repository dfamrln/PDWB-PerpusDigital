<?php
require_once '../config/database.php';
require_once '../config/app.php';
require_once '../includes/functions.php';

requireLogin();

$pageTitle = 'Ajukan Peminjaman - PerpusDigital';
$db = getDB();

$buku_id = (int)($_GET['buku_id'] ?? 0);
if (!$buku_id) { setFlash('error', 'Buku tidak valid.'); redirect(url()); }

$buku = $db->prepare("SELECT * FROM buku WHERE id = ?");
$buku->execute([$buku_id]);
$buku = $buku->fetch();

if (!$buku) { setFlash('error', 'Buku tidak ditemukan.'); redirect(url()); }
if ($buku['stok_tersedia'] <= 0) { setFlash('error', 'Buku sedang tidak tersedia.'); redirect(url()); }

$anggota_id = $_SESSION['anggota_id'];
$aktif = countAktifPinjaman($anggota_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($aktif >= MAX_PINJAM) {
        setFlash('error', 'Kamu sudah mencapai batas maksimal ' . MAX_PINJAM . ' peminjaman aktif.');
        redirect(url('pages/peminjaman_aktif.php'));
    }

    $cek = $db->prepare("SELECT id FROM peminjaman WHERE anggota_id = ? AND buku_id = ? AND status IN ('menunggu','dipinjam')");
    $cek->execute([$anggota_id, $buku_id]);
    if ($cek->fetch()) {
        setFlash('warning', 'Kamu sudah memiliki pengajuan aktif untuk buku ini.');
        redirect(url());
    }

    $ins = $db->prepare("INSERT INTO peminjaman (anggota_id, buku_id, status) VALUES (?, ?, 'menunggu')");
    $ins->execute([$anggota_id, $buku_id]);

    setFlash('success', 'Pengajuan peminjaman berhasil dikirim! Tunggu persetujuan petugas.');
    redirect(url('pages/peminjaman_aktif.php'));
}

include '../includes/header.php';
?>

<div class="max-w-lg mx-auto">
    <a href="<?= url() ?>" class="inline-flex items-center gap-1 text-slate-500 hover:text-indigo-600 text-sm mb-6">
        ← Kembali ke Katalog
    </a>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <h1 class="font-display text-2xl font-bold text-slate-800 mb-6">📤 Ajukan Peminjaman</h1>

        <!-- Info Buku -->
        <div class="bg-indigo-50 rounded-xl p-4 mb-6 flex gap-4">
            <div class="text-4xl">
                <?= ['Novel' => '📖', 'Self-Help' => '🌱', 'Sejarah' => '🏛️', 'Keuangan' => '💰', 'Teknologi' => '💻', 'Filsafat' => '🧠', 'Fiksi' => '✨'][$buku['kategori']] ?? '📚' ?>
            </div>
            <div>
                <h2 class="font-bold text-slate-800"><?= e($buku['judul']) ?></h2>
                <p class="text-sm text-slate-500"><?= e($buku['pengarang']) ?> · <?= e($buku['tahun_terbit']) ?></p>
                <p class="text-xs text-slate-400 mt-1"><?= e($buku['kategori']) ?></p>
            </div>
        </div>

        <!-- Info Peminjaman -->
        <div class="bg-slate-50 rounded-xl p-4 mb-6 space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-500">Masa Pinjam</span>
                <strong class="text-slate-800"><?= LAMA_PINJAM ?> hari</strong>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Denda Keterlambatan</span>
                <strong class="text-slate-800"><?= formatRupiah(DENDA_PER_HARI) ?>/hari</strong>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Pinjaman Aktif Kamu</span>
                <strong class="<?= $aktif >= MAX_PINJAM ? 'text-red-600' : 'text-green-600' ?>"><?= $aktif ?>/<?= MAX_PINJAM ?></strong>
            </div>
        </div>

        <?php if ($aktif >= MAX_PINJAM): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-4">
                ❌ Kamu sudah mencapai batas maksimal <strong><?= MAX_PINJAM ?> pinjaman aktif</strong>. Kembalikan buku dulu sebelum meminjam lagi.
            </div>
            <a href="<?= url('pages/peminjaman_aktif.php') ?>" class="btn-primary w-full text-white font-semibold py-3 rounded-xl text-center block">
                Lihat Pinjaman Aktif
            </a>
        <?php else: ?>
            <form method="POST" action="<?= url('pages/ajukan_pinjam.php') ?>?buku_id=<?= $buku_id ?>">
                <p class="text-sm text-slate-600 mb-5">
                    Dengan menekan tombol di bawah, kamu setuju untuk mengembalikan buku dalam waktu <strong><?= LAMA_PINJAM ?> hari</strong> setelah disetujui petugas.
                </p>
                <button type="submit" class="btn-primary w-full text-white font-bold py-3 rounded-xl text-lg">
                    📤 Kirim Pengajuan Pinjam
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>