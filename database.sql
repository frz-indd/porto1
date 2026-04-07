-- Database Sistem Pengaduan Masyarakat
CREATE DATABASE IF NOT EXISTS pengaduan_masyarakat;
USE pengaduan_masyarakat;

-- Tabel Users (Admin)
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    nama_lengkap VARCHAR(100),
    role ENUM('admin', 'staff') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Kategori Pengaduan
CREATE TABLE IF NOT EXISTS kategori_pengaduan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Pengaduan Utama
CREATE TABLE IF NOT EXISTS pengaduan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    no_laporan VARCHAR(20) UNIQUE NOT NULL,
    nama_pelapor VARCHAR(100) NOT NULL,
    email_pelapor VARCHAR(100) NOT NULL,
    telepon_pelapor VARCHAR(15) NOT NULL,
    alamat_pelapor TEXT NOT NULL,
    kategori_id INT NOT NULL,
    judul_pengaduan VARCHAR(200) NOT NULL,
    isi_pengaduan LONGTEXT NOT NULL,
    lokasi_kejadian VARCHAR(255),
    tanggal_kejadian DATE NOT NULL,
    status ENUM('diterima', 'sedang_diproses', 'ditangguhkan', 'selesai', 'ditolak') DEFAULT 'diterima',
    prioritas ENUM('rendah', 'sedang', 'tinggi', 'urgen') DEFAULT 'sedang',
    tindakan_lanjut TEXT,
    respons_admin TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori_pengaduan(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Bukti Pendukung (File/Gambar)
CREATE TABLE IF NOT EXISTS bukti_pendukung (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pengaduan_id INT NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    tipe_file VARCHAR(50),
    ukuran_file INT,
    path_file VARCHAR(255) NOT NULL,
    keterangan VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pengaduan_id) REFERENCES pengaduan(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Tracking/Progress Pengaduan
CREATE TABLE IF NOT EXISTS tracking_pengaduan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pengaduan_id INT NOT NULL,
    status_baru VARCHAR(50) NOT NULL,
    keterangan TEXT,
    admin_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pengaduan_id) REFERENCES pengaduan(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Admin Default
INSERT INTO users (username, password, email, nama_lengkap, role) VALUES 
('admin', sha2('admin123', 256), 'admin@pengaduan.local', 'Administrator', 'admin');

-- Insert Kategori Contoh
INSERT INTO kategori_pengaduan (nama_kategori, deskripsi) VALUES 
('Layanan Publik', 'Pengaduan tentang pelayanan publik yang tidak memuaskan'),
('Infrastruktur', 'Pengaduan tentang jalan, jembatan, dan bangunan umum yang rusak'),
('Lingkungan', 'Pengaduan tentang polusi, kebersihan, dan lingkungan hidup'),
('Keamanan', 'Pengaduan tentang keamanan dan ketertiban masyarakat'),
('Pendidikan', 'Pengaduan tentang sistem pendidikan dan sekolah'),
('Kesehatan', 'Pengaduan tentang pelayanan kesehatan'),
('Korupsi', 'Pengaduan tentang tindakan korupsi dan penyalahgunaan wewenang'),
('Lainnya', 'Kategori pengaduan lainnya yang tidak termasuk di atas');

-- Tabel untuk Lampiran/Evidence Details
CREATE TABLE IF NOT EXISTS evidence_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bukti_id INT NOT NULL,
    action VARCHAR(50),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bukti_id) REFERENCES bukti_pendukung(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_pengaduan_status ON pengaduan(status);
CREATE INDEX idx_pengaduan_created ON pengaduan(created_at);
CREATE INDEX idx_pengaduan_kategori ON pengaduan(kategori_id);
CREATE INDEX idx_tracking_pengaduan ON tracking_pengaduan(pengaduan_id);
CREATE INDEX idx_bukti_pengaduan ON bukti_pendukung(pengaduan_id);
