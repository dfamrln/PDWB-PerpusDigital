<?php
// =============================================
//  Konfigurasi Aplikasi - PerpusDigital
// =============================================

// Base URL aplikasi — sesuaikan jika nama folder berbeda
// Contoh: jika folder = perpustakaan → BASE_URL = '/perpustakaan'
// Jika nanti deploy ke root domain → BASE_URL = ''
define('BASE_URL', '/perpustakaan');

// Nama aplikasi
define('APP_NAME', 'PerpusDigital');

// Helper: buat full URL dari path relatif
// Penggunaan: url('login') → '/perpustakaan/login'
//             url('pages/ajukan_pinjam.php') → '/perpustakaan/pages/ajukan_pinjam.php'
function url(string $path = ''): string {
    $path = ltrim($path, '/');
    return BASE_URL . ($path ? '/' . $path : '/');

    
}  