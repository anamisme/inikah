-- ============================================
-- DATABASE iNikah - KUA Karangdadap
-- Jalankan SQL ini di phpMyAdmin
-- ============================================

CREATE TABLE IF NOT EXISTS `notifikasi` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `judul` VARCHAR(255) NOT NULL,
    `pesan` TEXT NOT NULL,
    `tanggal` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `banner` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `judul` VARCHAR(255) NOT NULL,
    `tag` VARCHAR(50) DEFAULT 'INFO',
    `link` VARCHAR(500) DEFAULT NULL,
    `gambar` VARCHAR(500) DEFAULT NULL,
    `warna` VARCHAR(200) DEFAULT NULL,
    `aktif` TINYINT(1) DEFAULT 1,
    `tanggal` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `sertifikat` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(255) NOT NULL,
    `link` VARCHAR(500) NOT NULL,
    `tanggal` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `jadwal` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tanggal_akad` DATE NOT NULL,
    `waktu` VARCHAR(20) DEFAULT NULL,
    `nama_pria` VARCHAR(255) NOT NULL,
    `nama_wanita` VARCHAR(255) NOT NULL,
    `desa` VARCHAR(255) DEFAULT NULL,
    `petugas` VARCHAR(255) DEFAULT NULL,
    `keterangan` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `jawaban_test` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tipe` ENUM('pretest', 'posttest') NOT NULL,
    `nama` VARCHAR(255) NOT NULL,
    `nik` VARCHAR(20) DEFAULT NULL,
    `no_hp` VARCHAR(20) DEFAULT NULL,
    `jawaban` JSON NOT NULL,
    `skor` INT DEFAULT NULL,
    `tanggal` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Index untuk pencarian cepat
CREATE INDEX idx_sertifikat_nama ON `sertifikat` (`nama`);
CREATE INDEX idx_jawaban_nama ON `jawaban_test` (`nama`, `tipe`);
CREATE INDEX idx_jadwal_tanggal ON `jadwal` (`tanggal_akad`);
