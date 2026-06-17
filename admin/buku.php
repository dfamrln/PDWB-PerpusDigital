<?php
require_once '../config/database.php';
require_once '../config/app.php';
require_once '../includes/functions.php';
requireAdmin();

$pageTitle = 'Kelola Buku - Admin';
$db = getDB();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah' || $action === 'edit') {
        $judul       = trim($_POST['judul'] ?? '');
        $pengarang   = trim($_POST['pengarang'] ?? '');
        $penerbit    = trim($_POST['penerbit'] ?? '');
        $tahun       = (int)($_POST['tahun_terbit'] ?? 0);
        $kategori    = trim($_POST['kategori'] ?? '');
        $isbn        = trim($_POST['isbn'] ?? '');
        $stok        = max(1, (int)($_POST['stok'] ?? 1));
        $deskripsi   = trim($_POST['deskripsi'] ?? '');

        if ($action === 'tambah') {
            $db->prepare("INSERT INTO buku (judul, pengarang, penerbit, tahun_terbit, kategori, isbn, stok, stok_tersedia, deskripsi) VALUES (?,?,?,?,?,?,?,?,?)")
               ->execute([$judul, $pengarang, $penerbit, $tahun ?: null, $kategori, $isbn, $stok, $stok, $deskripsi]);
            setFlash('success', 'Buku berhasil ditambahkan.');
        } else {
            $id = (int)$_POST['id'];
            $db->prepare("UPDATE buku SET judul=?, pengarang=?, penerbit=?, tahun_terbit=?, kategori=?, isbn=?, stok=?, deskripsi=? WHERE id=?")
               ->execute([$judul, $pengarang, $penerbit, $tahun ?: null, $kategori, $isbn, $stok, $deskripsi, $id]);
            setFlash('success', 'Buku berhasil diperbarui.');
        }
    }

    if ($_POST['action'] === 'hapus') {
        $id = (int)$_POST['id'];
        $db->prepare("DELETE FROM buku WHERE id=?")->execute([$id]);
        setFlash('success', 'Buku berhasil dihapus.');
    }

    redirect(url('admin/buku.php'));
}

// Edit mode
$editBuku = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM buku WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editBuku = $stmt->fetch();
}

// List buku
$search = trim($_GET['q'] ?? '');
$where = $search ? "WHERE judul LIKE ? OR pengarang LIKE ?" : '';
$params = $search ? ["%$search%", "%$search%"] : [];
$stmt = $db->prepare("SELECT * FROM buku $where ORDER BY judul ASC");
$stmt->execute($params);
$bukus = $stmt->fetchAll();

$kategoriList = ['Novel', 'Self-Help', 'Sejarah', 'Keuangan', 'Teknologi', 'Filsafat', 'Fiksi', 'Lainnya'];

include 'header.php';
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="font-display text-2xl font-bold text-slate-800">📖 Kelola Buku</h1>
    <button onclick="document.getElementById('modal-tambah').classList.remove('hidden')"
            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition">
        + Tambah Buku
    </button>
</div>

<!-- Search -->
<form method="GET" action="<?= url('admin/buku.php') ?>" class="flex gap-2 mb-6">
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="🔍 Cari judul atau pengarang..."
        class="flex-1 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
    <button type="submit" class="bg-slate-700 text-white px-4 py-2 rounded-xl text-sm font-semibold">Cari</button>
    <?php if ($search): ?>
        <a href="<?= url('admin/buku.php') ?>" class="bg-slate-100 text-slate-600 px-4 py-2 rounded-xl text-sm">Reset</a>
    <?php endif; ?>
</form>

<!-- Tabel Buku -->
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-slate-600">Judul / Pengarang</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600 hidden md:table-cell">Kategori</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600 hidden lg:table-cell">Tahun</th>
                <th class="px-4 py-3 text-center font-semibold text-slate-600">Stok</th>
                <th class="px-4 py-3 text-center font-semibold text-slate-600">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
        <?php foreach ($bukus as $b): ?>
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3">
                    <p class="font-medium text-slate-800"><?= e($b['judul']) ?></p>
                    <p class="text-xs text-slate-400"><?= e($b['pengarang']) ?></p>
                </td>
                <td class="px-4 py-3 text-slate-600 hidden md:table-cell"><?= e($b['kategori']) ?></td>
                <td class="px-4 py-3 text-slate-500 hidden lg:table-cell"><?= $b['tahun_terbit'] ?? '-' ?></td>
                <td class="px-4 py-3 text-center">
                    <span class="font-bold <?= $b['stok_tersedia'] > 0 ? 'text-green-600' : 'text-red-500' ?>">
                        <?= $b['stok_tersedia'] ?>/<?= $b['stok'] ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <div class="flex gap-2 justify-center">
                        <a href="<?= url('admin/buku.php') ?>?edit=<?= $b['id'] ?>"
                           class="bg-indigo-50 text-indigo-600 hover:bg-indigo-100 text-xs font-medium px-3 py-1.5 rounded-lg transition">Edit</a>
                        <form method="POST" onsubmit="return confirm('Hapus buku ini?')">
                            <input type="hidden" name="action" value="hapus">
                            <input type="hidden" name="id" value="<?= $b['id'] ?>">
                            <button type="submit" class="bg-red-50 text-red-600 hover:bg-red-100 text-xs font-medium px-3 py-1.5 rounded-lg transition">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- MODAL TAMBAH -->
<div id="modal-tambah" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">➕ Tambah Buku</h2>
            <button onclick="document.getElementById('modal-tambah').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form method="POST" action="<?= url('admin/buku.php') ?>" class="p-6 space-y-4">
            <input type="hidden" name="action" value="tambah">
            <?php include '_form_buku.php'; ?>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-xl transition">Simpan</button>
                <button type="button" onclick="document.getElementById('modal-tambah').classList.add('hidden')" class="flex-1 bg-slate-100 text-slate-600 font-semibold py-2.5 rounded-xl">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT -->
<?php if ($editBuku): ?>
<div id="modal-edit" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">✏️ Edit Buku</h2>
            <a href="<?= url('admin/buku.php') ?>" class="text-slate-400 hover:text-slate-600">✕</a>
        </div>
        <form method="POST" action="<?= url('admin/buku.php') ?>" class="p-6 space-y-4">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $editBuku['id'] ?>">
            <?php include '_form_buku.php'; ?>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-xl transition">Update</button>
                <a href="<?= url('admin/buku.php') ?>" class="flex-1 bg-slate-100 text-slate-600 font-semibold py-2.5 rounded-xl text-center">Batal</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>