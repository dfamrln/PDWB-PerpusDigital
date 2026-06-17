<?php
// =========================================
// KONFIGURASI DATABASE
// Ganti sesuai hosting Anda
// =========================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Ganti dengan username DB hosting
define('DB_PASS', '');            // Ganti dengan password DB hosting
define('DB_NAME', 'perpustakaan_db');

define('DENDA_PER_HARI', 1000);   // Rp 1.000 per hari
define('MAX_PINJAM', 3);          // Maksimal 3 buku aktif
define('LAMA_PINJAM', 7);         // 7 hari masa pinjam

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:2rem;background:#fee2e2;color:#991b1b;border-radius:8px;margin:2rem;">
                <h2>❌ Koneksi Database Gagal</h2>
                <p>Pastikan konfigurasi di <code>config/database.php</code> sudah benar.</p>
                <p><small>' . htmlspecialchars($e->getMessage()) . '</small></p>
            </div>');
        }
    }
    return $pdo;
}
