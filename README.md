# 📚 PerpusDigital — Panduan Setup & Deploy

## Akun Default
- **Admin/Petugas:** `admin@perpus.com` / `password`
- **Anggota:** Daftar sendiri via halaman Register

---

## Cara Setup Lokal (XAMPP)

1. Salin folder `perpustakaan/` ke `C:/xampp/htdocs/perpustakaan/`
2. Buka phpMyAdmin → Import file `database.sql`
3. Edit `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'perpustakaan_db');
   ```
4. Buka browser: `http://localhost/perpustakaan/`

---

## Cara Deploy ke cPanel

### Langkah 1 — Buat Akun
- Daftar di https://perpus.pdwtiumy.click/.com
- Buat akun hosting baru → catat **FTP host, username, password**
- Catat nama **database** yang dibuat otomatis

### Langkah 2 — Upload Database
- Buka panel control InfinityFree → **phpMyAdmin**
- Pilih database yang tersedia → **Import** → Upload `database.sql`

### Langkah 3 — Edit Konfigurasi
Edit `config/database.php` sesuai kredensial hosting:
```php
define('DB_HOST', 'sql123.infinityfree.com');  // dari panel hosting
define('DB_USER', 'if0_xxxxxxxx');              // dari panel hosting
define('DB_PASS', 'password_anda');
define('DB_NAME', 'if0_xxxxxxxx_perpustakaan'); // dari panel hosting
```

### Langkah 4 — Upload File via FileZilla
1. Download FileZilla (https://filezilla-project.org)
2. Isi: Host, Username, Password dari InfinityFree
3. Upload semua isi folder `perpustakaan/` ke folder `htdocs/` di server
4. Akses via URL domain yang diberikan InfinityFree

---

## Struktur Folder

```
perpustakaan/
├── index.php              ← Katalog buku (publik)
├── login.php              ← Login anggota
├── register.php           ← Registrasi anggota
├── database.sql           ← Import ke MySQL
├── config/
│   └── database.php       ← ⚠️ Edit ini sesuai hosting
├── includes/
│   ├── functions.php      ← Helper functions
│   ├── header.php         ← Template header anggota
│   └── footer.php         ← Template footer
├── pages/
│   ├── ajukan_pinjam.php  ← Form pengajuan pinjam
│   ├── peminjaman_aktif.php
│   ├── riwayat.php
│   └── logout.php
└── admin/
    ├── login.php          ← Login petugas
    ├── dashboard.php      ← Dashboard statistik
    ├── buku.php           ← CRUD buku
    ├── peminjaman.php     ← Setujui/tolak pengajuan
    ├── pengembalian.php   ← Proses kembali + denda
    └── anggota.php        ← Daftar anggota
```

---

## Fitur yang Diimplementasi

| Fitur | Status |
|-------|--------|
| Katalog buku publik | ✅ |
| Pencarian & filter buku | ✅ |
| Registrasi & login anggota | ✅ |
| Pengajuan peminjaman | ✅ |
| Validasi max 3 pinjaman aktif | ✅ |
| Login petugas (admin) | ✅ |
| Dashboard statistik | ✅ |
| CRUD buku (tambah/edit/hapus) | ✅ |
| Setujui/tolak pengajuan | ✅ |
| Proses pengembalian | ✅ |
| Kalkulasi denda otomatis (Rp 1.000/hari) | ✅ |
| Status badge (hijau/merah) | ✅ |
| Notifikasi jatuh tempo | ✅ |
| Riwayat peminjaman anggota | ✅ |
| Manajemen denda & pembayaran | ✅ |
| Responsive layout (mobile) | ✅ |
