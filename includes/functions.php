<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Auth Helpers ───────────────────────────────────────────

function isLoggedIn() {
    return isset($_SESSION['anggota_id']);
}

function isAdmin() {
    return isset($_SESSION['petugas_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function currentUser() {
    return $_SESSION['anggota'] ?? null;
}

function currentAdmin() {
    return $_SESSION['petugas'] ?? null;
}

// ─── Flash Message ──────────────────────────────────────────

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function showFlash() {
    $flash = getFlash();
    if (!$flash) return '';
    $colors = [
        'success' => 'bg-green-100 border-green-400 text-green-800',
        'error'   => 'bg-red-100 border-red-400 text-red-800',
        'info'    => 'bg-blue-100 border-blue-400 text-blue-800',
        'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-800',
    ];
    $icons = ['success' => '✅', 'error' => '❌', 'info' => 'ℹ️', 'warning' => '⚠️'];
    $cls = $colors[$flash['type']] ?? $colors['info'];
    $icon = $icons[$flash['type']] ?? '';
    return "<div class=\"{$cls} border px-4 py-3 rounded-lg mb-4 flex items-center gap-2\">
        <span>{$icon}</span><span>" . htmlspecialchars($flash['message']) . "</span>
    </div>";
}

// ─── Utility ────────────────────────────────────────────────

function hitungDenda($tanggal_jatuh_tempo, $tanggal_kembali = null) {
    $jatuh = new DateTime($tanggal_jatuh_tempo);
    $kembali = $tanggal_kembali ? new DateTime($tanggal_kembali) : new DateTime();
    $selisih = $jatuh->diff($kembali);
    if ($kembali > $jatuh) {
        return $selisih->days * DENDA_PER_HARI;
    }
    return 0;
}

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatTanggal($date) {
    if (!$date) return '-';
    return date('d M Y', strtotime($date));
}

function hariSisaPinjam($tanggal_jatuh_tempo) {
    $jatuh = new DateTime($tanggal_jatuh_tempo);
    $sekarang = new DateTime();
    $diff = $sekarang->diff($jatuh);
    if ($sekarang > $jatuh) return -$diff->days;
    return $diff->days;
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function countAktifPinjaman($anggota_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM peminjaman WHERE anggota_id = ? AND status = 'dipinjam'");
    $stmt->execute([$anggota_id]);
    return (int)$stmt->fetchColumn();
}
