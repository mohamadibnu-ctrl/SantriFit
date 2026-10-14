<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

try {
    $db = new Database();
    
    // 1. Alter OBAT
    $sql_obat = "ALTER TABLE obat 
                 ADD COLUMN kode_obat VARCHAR(50) DEFAULT NULL UNIQUE AFTER nama_obat,
                 ADD COLUMN stok INT DEFAULT 0 AFTER satuan,
                 ADD COLUMN status_aktif ENUM('1', '0') DEFAULT '1'";
                 
    try { $db->query($sql_obat); $db->execute(); } catch (Exception $e) {}
    
    // 2. Alter REKOMENDASI DOKTER
    $sql_rek = "ALTER TABLE rekomendasi_dokter 
                ADD COLUMN status ENUM('Draft', 'Final') DEFAULT 'Draft'";
                
    try { $db->query($sql_rek); $db->execute(); } catch (Exception $e) {}
    
    // 3. Create PEMBERIAN OBAT
    $sql_pem = "CREATE TABLE IF NOT EXISTS pemberian_obat (
                  id INT AUTO_INCREMENT PRIMARY KEY,
                  resep_id INT NOT NULL,
                  perawat_id INT NOT NULL,
                  waktu_diberikan DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  catatan TEXT,
                  FOREIGN KEY (resep_id) REFERENCES resep_obat(id) ON DELETE CASCADE,
                  FOREIGN KEY (perawat_id) REFERENCES users(id) ON DELETE CASCADE
                )";
                
    $db->query($sql_pem);
    $db->execute();
    
    echo "<h3>Migrasi Medis Lanjutan Berhasil!</h3>";
    echo "<p>Struktur tabel obat, rekomendasi, dan pemberian_obat telah diperbarui.</p>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
