CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_role` VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS `user_roles` (
  `user_id` INT NOT NULL,
  `role_id` INT NOT NULL,
  PRIMARY KEY (`user_id`, `role_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `santri` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nis` VARCHAR(20) NOT NULL UNIQUE,
  `nama` VARCHAR(100) NOT NULL,
  `foto` VARCHAR(255) NULL,
  `tempat_lahir` VARCHAR(100),
  `tanggal_lahir` DATE NOT NULL,
  `jenis_kelamin` ENUM('L', 'P') NOT NULL,
  `alamat` TEXT,
  `user_id` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS `dokter` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `no_izin_praktek` VARCHAR(50),
  `spesialisasi` VARCHAR(100),
  `user_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `perawat` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `nip` VARCHAR(50),
  `user_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `pemeriksaan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `santri_id` INT NOT NULL,
  `petugas_id` INT NOT NULL COMMENT 'User ID of perawat/pengurus/dokter',
  `tanggal_pemeriksaan` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `keluhan_utama` TEXT NOT NULL,
  `tekanan_darah` VARCHAR(20),
  `suhu_tubuh` DECIMAL(4,2),
  `berat_badan` DECIMAL(5,2),
  `tinggi_badan` DECIMAL(5,2),
  `hasil_pemeriksaan` TEXT,
  `catatan_petugas` TEXT,
  `rekomendasi_ai` TEXT,
  `status` VARCHAR(50) DEFAULT 'Menunggu Dokter',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`santri_id`) REFERENCES `santri`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`petugas_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `gejala` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_gejala` VARCHAR(100) NOT NULL UNIQUE,
  `deskripsi` TEXT,
  `status_aktif` BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS `pemeriksaan_gejala` (
  `pemeriksaan_id` INT NOT NULL,
  `gejala_id` INT NOT NULL,
  PRIMARY KEY (`pemeriksaan_id`, `gejala_id`),
  FOREIGN KEY (`pemeriksaan_id`) REFERENCES `pemeriksaan`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`gejala_id`) REFERENCES `gejala`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `rekomendasi_dokter` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pemeriksaan_id` INT NOT NULL,
  `dokter_id` INT NOT NULL,
  `diagnosis_medis` TEXT NOT NULL,
  `catatan_saran` TEXT,
  `status` ENUM('Draft', 'Final') DEFAULT 'Draft',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`pemeriksaan_id`) REFERENCES `pemeriksaan`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`dokter_id`) REFERENCES `dokter`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `obat` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_obat` VARCHAR(50) NULL,
  `nama_obat` VARCHAR(100) NOT NULL,
  `satuan` VARCHAR(50),
  `stok` INT DEFAULT 0,
  `deskripsi` TEXT,
  `status_aktif` BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS `resep_obat` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `rekomendasi_id` INT NOT NULL,
  `obat_id` INT NOT NULL,
  `dosis` VARCHAR(50) NOT NULL,
  `aturan_minum` VARCHAR(100) NOT NULL,
  `jumlah` INT NOT NULL,
  `durasi_hari` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`rekomendasi_id`) REFERENCES `rekomendasi_dokter`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`obat_id`) REFERENCES `obat`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `pemberian_obat` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `resep_id` INT NOT NULL,
  `perawat_id` INT NOT NULL,
  `waktu_diberikan` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `catatan` TEXT,
  FOREIGN KEY (`resep_id`) REFERENCES `resep_obat`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`perawat_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `riwayat_kesehatan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `santri_id` INT NOT NULL,
  `nama_penyakit` VARCHAR(100) NOT NULL,
  `status` ENUM('Aktif', 'Sembuh') DEFAULT 'Aktif',
  `tanggal_tercatat` DATE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`santri_id`) REFERENCES `santri`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `aksi` VARCHAR(50) NOT NULL,
  `tabel_terdampak` VARCHAR(50) NOT NULL,
  `record_id` INT,
  `detail_perubahan` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
);
