-- =========================================
-- SISTEM MANAJEMEN PERPUSTAKAAN ONLINE
-- Database Schema (Direct to Active DB)
-- =========================================

-- Tabel Anggota
CREATE TABLE anggota (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_telp VARCHAR(20),
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Petugas
CREATE TABLE petugas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Buku
CREATE TABLE buku (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    pengarang VARCHAR(100) NOT NULL,
    penerbit VARCHAR(100),
    tahun_terbit YEAR,
    kategori VARCHAR(50),
    isbn VARCHAR(20),
    stok INT DEFAULT 1,
    stok_tersedia INT DEFAULT 1,
    deskripsi TEXT,
    cover_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Peminjaman
CREATE TABLE peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anggota_id INT NOT NULL,
    buku_id INT NOT NULL,
    tanggal_pinjam DATE,
    tanggal_jatuh_tempo DATE,
    tanggal_kembali DATE NULL,
    status ENUM('menunggu', 'dipinjam', 'dikembalikan', 'ditolak') DEFAULT 'menunggu',
    denda INT DEFAULT 0,
    denda_dibayar TINYINT DEFAULT 0,
    catatan_petugas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE,
    FOREIGN KEY (buku_id) REFERENCES buku(id) ON DELETE CASCADE
);

-- =========================================
-- DATA AWAL (SEED DATA)
-- =========================================

-- Petugas default (password: admin123)
INSERT INTO petugas (nama, email, password) VALUES
('Admin Perpustakaan', 'admin@perpus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Buku sample
INSERT INTO buku (judul, pengarang, penerbit, tahun_terbit, kategori, stok, stok_tersedia, deskripsi) VALUES
('Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 'Novel', 3, 3, 'Kisah inspiratif tentang semangat belajar anak-anak di Belitung.'),
('Bumi Manusia', 'Pramoedya Ananta Toer', 'Lentera Dipantara', 1980, 'Novel', 2, 2, 'Novel sejarah tentang perjuangan di masa kolonial Belanda.'),
('Atomic Habits', 'James Clear', 'Penguin Books', 2018, 'Self-Help', 2, 2, 'Panduan praktis membangun kebiasaan baik dan menghilangkan kebiasaan buruk.'),
('Sapiens', 'Yuval Noah Harari', 'Harper Collins', 2011, 'Sejarah', 1, 1, 'Sejarah singkat umat manusia dari zaman batu hingga modern.'),
('The Psychology of Money', 'Morgan Housel', 'Harriman House', 2020, 'Keuangan', 2, 2, 'Pelajaran timeless tentang kekayaan, keserakahan, dan kebahagiaan.'),
('Dilan 1990', 'Pidi Baiq', 'Pastel Books', 2014, 'Novel', 3, 3, 'Kisah cinta remaja di Bandung tahun 1990-an.'),
('Rich Dad Poor Dad', 'Robert Kiyosaki', 'Warner Books', 1997, 'Keuangan', 2, 2, 'Pelajaran tentang uang yang tidak diajarkan di sekolah.'),
('Clean Code', 'Robert C. Martin', 'Prentice Hall', 2008, 'Teknologi', 1, 1, 'Panduan menulis kode yang bersih dan mudah dipahami.'),
('Harry Potter and the Sorcerers Stone', 'J.K. Rowling', 'Bloomsbury', 1997, 'Fiksi', 2, 2, 'Petualangan seorang penyihir muda di sekolah Hogwarts.'),
('Filosofi Teras', 'Henry Manampiring', 'Kompas', 2018, 'Filsafat', 2, 2, 'Filsafat Stoa untuk menghadapi tantangan hidup modern.');