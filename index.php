<?php
require_once 'config/database.php';
require_once 'config/app.php';        // ← tambahkan ini
require_once 'includes/functions.php';

$pageTitle = 'Katalog Buku - PerpusDigital';

$db = getDB();

// Filter & Search
$search = trim($_GET['q'] ?? '');
$kategori = trim($_GET['kategori'] ?? '');
$status_filter = trim($_GET['status'] ?? '');

$where = [];
$params = [];

if ($search) {
    $where[] = "(b.judul LIKE ? OR b.pengarang LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($kategori) {
    $where[] = "b.kategori = ?";
    $params[] = $kategori;
}
if ($status_filter === 'tersedia') {
    $where[] = "b.stok_tersedia > 0";
} elseif ($status_filter === 'dipinjam') {
    $where[] = "b.stok_tersedia = 0";
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$stmt = $db->prepare("SELECT * FROM buku b $whereSQL ORDER BY b.judul ASC");
$stmt->execute($params);
$bukus = $stmt->fetchAll();

// Kategori list
$cats = $db->query("SELECT DISTINCT kategori FROM buku ORDER BY kategori")->fetchAll(PDO::FETCH_COLUMN);

include 'includes/header.php';
?>

<!-- HERO -->
<div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl p-8 md:p-12 mb-10 text-white relative overflow-hidden">
    <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,<svg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"><g fill=\"white\" fill-opacity=\"1\"><rect x=\"0\" y=\"0\" width=\"4\" height=\"4\"/></g></svg>'); background-size: 20px 20px;"></div>
    <div class="relative z-10">
        <h1 class="font-display text-3xl md:text-4xl font-bold mb-2">Perpustakaan Digital</h1>
        <p class="text-indigo-200 text-lg mb-6">Temukan dan pinjam buku favoritmu dengan mudah</p>

        <!-- Search Bar -->
        <form method="GET" action="<?= url() ?>" class="flex gap-2 flex-col sm:flex-row">
            <input type="hidden" name="kategori" value="<?= e($kategori) ?>">
            <input type="hidden" name="status" value="<?= e($status_filter) ?>">
            <input
                type="text" name="q" value="<?= e($search) ?>"
                placeholder="🔍 Cari judul atau pengarang..."
                class="flex-1 px-4 py-3 rounded-xl text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-white shadow"
            >
            <button type="submit" class="bg-white text-indigo-700 font-semibold px-6 py-3 rounded-xl hover:bg-indigo-50 transition shadow">
                Cari
            </button>
        </form>
    </div>
</div>

<!-- FILTER ROW -->
<div class="flex flex-wrap gap-2 mb-6">
    <span class="text-sm text-slate-500 self-center font-medium mr-2">Filter:</span>
    <a href="<?= url() ?><?= $search ? '?q=' . urlencode($search) : '' ?>"
       class="px-3 py-1.5 rounded-full text-sm font-medium border <?= !$kategori && !$status_filter ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-300' ?>">
        Semua
    </a>

    <?php foreach ($cats as $cat): ?>
        <a href="?<?= http_build_query(array_filter(['q' => $search, 'kategori' => $cat, 'status' => $status_filter])) ?>"
           class="px-3 py-1.5 rounded-full text-sm font-medium border <?= $kategori === $cat ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-300' ?>">
            <?= e($cat) ?>
        </a>
    <?php endforeach; ?>

    <div class="ml-auto flex gap-2">
        <a href="?<?= http_build_query(array_filter(['q' => $search, 'kategori' => $kategori, 'status' => 'tersedia'])) ?>"
           class="px-3 py-1.5 rounded-full text-sm font-medium border <?= $status_filter === 'tersedia' ? 'bg-green-500 text-white border-green-500' : 'bg-white text-slate-600 border-slate-200 hover:border-green-300' ?>">
            ✅ Tersedia
        </a>
        <a href="?<?= http_build_query(array_filter(['q' => $search, 'kategori' => $kategori, 'status' => 'dipinjam'])) ?>"
           class="px-3 py-1.5 rounded-full text-sm font-medium border <?= $status_filter === 'dipinjam' ? 'bg-red-500 text-white border-red-500' : 'bg-white text-slate-600 border-slate-200 hover:border-red-300' ?>">
            🔴 Dipinjam
        </a>
    </div>
</div>

<!-- RESULT INFO -->
<p class="text-sm text-slate-500 mb-6">
    Menampilkan <strong><?= count($bukus) ?></strong> buku
    <?= $search ? "untuk pencarian \"<strong>" . e($search) . "</strong>\"" : '' ?>
</p>

<!-- BOOK GRID -->
<?php if (empty($bukus)): ?>
    <div class="text-center py-20 text-slate-400">
        <div class="text-6xl mb-4">📭</div>
        <h3 class="text-xl font-semibold mb-2 text-slate-600">Buku tidak ditemukan</h3>
        <p>Coba kata kunci atau filter yang berbeda</p>
        <a href="<?= url() ?>" class="mt-4 inline-block text-indigo-600 hover:underline">← Lihat semua buku</a>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($bukus as $buku): ?>
            <?php $tersedia = $buku['stok_tersedia'] > 0; ?>
            <div class="card-book bg-white rounded-2xl overflow-hidden border border-slate-100 shadow-sm flex flex-col">
                <!-- Cover placeholder -->
                <div class="h-40 flex items-center justify-center text-6xl font-display"
                     style="background: linear-gradient(135deg, <?= ['#e0e7ff,#c7d2fe', '#fce7f3,#fbcfe8', '#d1fae5,#a7f3d0', '#fef3c7,#fde68a', '#ede9fe,#ddd6fe'][crc32($buku['kategori']) % 5] ?>)">
                    <?= ['Novel' => '📖', 'Self-Help' => '🌱', 'Sejarah' => '🏛️', 'Keuangan' => '💰', 'Teknologi' => '💻', 'Filsafat' => '🧠', 'Fiksi' => '✨'][$buku['kategori']] ?? '📚' ?>
                </div>

                <div class="p-4 flex flex-col flex-1">
                    <!-- Status Badge -->
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full <?= $tersedia ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' ?>">
                            <?= $tersedia ? '● Tersedia' : '● Dipinjam' ?>
                        </span>
                        <span class="text-xs text-slate-400"><?= e($buku['kategori']) ?></span>
                    </div>

                    <h3 class="font-semibold text-slate-800 text-sm leading-tight mb-1 line-clamp-2"><?= e($buku['judul']) ?></h3>
                    <p class="text-xs text-slate-500 mb-1"><?= e($buku['pengarang']) ?></p>
                    <p class="text-xs text-slate-400 mb-3"><?= e($buku['tahun_terbit']) ?></p>

                    <?php if ($buku['deskripsi']): ?>
                        <p class="text-xs text-slate-500 mb-3 line-clamp-2 flex-1"><?= e($buku['deskripsi']) ?></p>
                    <?php endif; ?>

                    <div class="mt-auto">
                        <?php if ($tersedia): ?>
                            <?php if (isLoggedIn()): ?>
                                <a href="<?= url('pages/ajukan_pinjam.php') ?>?buku_id=<?= $buku['id'] ?>"
                                   class="btn-primary w-full text-white text-sm font-bold py-2.5 px-4 rounded-xl text-center block">
                                    📤 Ajukan Pinjam
                                </a>
                            <?php else: ?>
                                <a href="<?= url('login') ?>" class="btn-primary w-full text-white text-sm font-bold py-2.5 px-4 rounded-xl text-center block">
                                    🔐 Login untuk Pinjam
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <button disabled class="w-full bg-slate-100 text-slate-400 text-sm font-medium py-2.5 px-4 rounded-xl cursor-not-allowed">
                                Sedang Dipinjam
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>