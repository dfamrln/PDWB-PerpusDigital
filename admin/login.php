<?php
require_once '../config/database.php';
require_once '../config/app.php';
require_once '../includes/functions.php';

if (isAdmin()) redirect(url('admin/dashboard.php'));

$pageTitle = 'Login Petugas';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM petugas WHERE email = ?");
    $stmt->execute([$email]);
    $petugas = $stmt->fetch();

    if ($petugas && password_verify($pass, $petugas['password'])) {
        $_SESSION['petugas_id'] = $petugas['id'];
        $_SESSION['petugas']    = $petugas;
        redirect(url('admin/dashboard.php'));
    } else {
        $error = 'Email atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Petugas - PerpusDigital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>body { font-family: 'DM Sans', sans-serif; } .font-display { font-family: 'Playfair Display', serif; }</style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <p class="text-4xl mb-2">🔑</p>
            <h1 class="font-display text-2xl font-bold text-white">Panel Petugas</h1>
            <p class="text-slate-400 text-sm">PerpusDigital</p>
        </div>

        <div class="bg-white rounded-2xl p-8 shadow-2xl">
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-5 text-sm">
                    ❌ <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= url('admin/login.php') ?>" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email Petugas</label>
                    <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required autofocus
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" required
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-semibold py-3 rounded-xl transition">
                    Masuk sebagai Petugas
                </button>
            </form>

            <p class="text-center text-xs text-slate-400 mt-4">
                Default: admin@perpus.com / password
            </p>
        </div>

        <p class="text-center mt-4 text-sm text-slate-500">
            <a href="<?= url() ?>" class="hover:text-white transition">← Kembali ke Katalog</a>
        </p>
    </div>
</body>
</html>


