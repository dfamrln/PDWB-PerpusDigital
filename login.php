<?php
require_once 'config/database.php';
require_once 'config/app.php';
require_once 'includes/functions.php';

if (isLoggedIn()) redirect(url());

$pageTitle = 'Masuk - PerpusDigital';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email && $pass) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM anggota WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['anggota_id'] = $user['id'];
            $_SESSION['anggota']    = $user;
            setFlash('success', 'Selamat datang, ' . $user['nama'] . '!');
            redirect(url());
        } else {
            $error = 'Email atau password salah.';
        }
    } else {
        $error = 'Harap isi semua kolom.';
    }
}

include 'includes/header.php';
?>

<div class="max-w-md mx-auto">
    <div class="text-center mb-8">
        <div class="text-5xl mb-3">🔐</div>
        <h1 class="font-display text-2xl font-bold text-slate-800">Masuk ke Akun</h1>
        <p class="text-slate-500 mt-1">Selamat datang kembali!</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-5 text-sm">
                ❌ <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('login') ?>" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required autofocus
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <button type="submit" class="btn-primary w-full text-white font-semibold py-3 rounded-xl">
                Masuk
            </button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-6">
            Belum punya akun? <a href="<?= url('register') ?>" class="text-indigo-600 font-medium hover:underline">Daftar gratis</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>