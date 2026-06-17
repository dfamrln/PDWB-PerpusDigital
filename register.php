<?php
require_once 'config/database.php';
require_once 'config/app.php';
require_once 'includes/functions.php';

if (isLoggedIn()) redirect(url());

$pageTitle = 'Daftar Akun - PerpusDigital';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama   = trim($_POST['nama'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $pass   = $_POST['password'] ?? '';
    $pass2  = $_POST['password2'] ?? '';
    $telp   = trim($_POST['no_telp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if (!$nama) $errors[] = 'Nama wajib diisi.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
    if (strlen($pass) < 6) $errors[] = 'Password minimal 6 karakter.';
    if ($pass !== $pass2) $errors[] = 'Konfirmasi password tidak cocok.';

    if (empty($errors)) {
        $db = getDB();
        $cek = $db->prepare("SELECT id FROM anggota WHERE email = ?");
        $cek->execute([$email]);
        if ($cek->fetch()) {
            $errors[] = 'Email sudah terdaftar.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $ins = $db->prepare("INSERT INTO anggota (nama, email, password, no_telp, alamat) VALUES (?,?,?,?,?)");
            $ins->execute([$nama, $email, $hash, $telp, $alamat]);
            setFlash('success', 'Pendaftaran berhasil! Silakan masuk.');
            redirect(url('login'));
        }
    }
}

include 'includes/header.php';
?>

<div class="max-w-md mx-auto">
    <div class="text-center mb-8">
        <div class="text-5xl mb-3">📝</div>
        <h1 class="font-display text-2xl font-bold text-slate-800">Buat Akun Baru</h1>
        <p class="text-slate-500 mt-1">Bergabung dan mulai meminjam buku</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <?php if ($errors): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                <strong>Oops!</strong>
                <ul class="mt-1 list-disc list-inside">
                    <?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('register') ?>" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap *</label>
                <input type="text" name="nama" value="<?= e($_POST['nama'] ?? '') ?>" required
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email *</label>
                <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">No. Telepon</label>
                <input type="text" name="no_telp" value="<?= e($_POST['no_telp'] ?? '') ?>"
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password *</label>
                <input type="password" name="password" required minlength="6"
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <p class="text-xs text-slate-400 mt-1">Minimal 6 karakter</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Password *</label>
                <input type="password" name="password2" required
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <button type="submit" class="btn-primary w-full text-white font-semibold py-3 rounded-xl mt-2">
                Buat Akun
            </button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-6">
            Sudah punya akun? <a href="<?= url('login') ?>" class="text-indigo-600 font-medium hover:underline">Masuk di sini</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>