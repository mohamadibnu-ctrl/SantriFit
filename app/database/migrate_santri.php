<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

try {
    $db = new Database();
    
    $sql = "ALTER TABLE santri 
            ADD COLUMN foto VARCHAR(255) DEFAULT NULL,
            ADD COLUMN tempat_lahir VARCHAR(100) DEFAULT NULL,
            ADD COLUMN kamar VARCHAR(50) DEFAULT NULL,
            ADD COLUMN kelas VARCHAR(50) DEFAULT NULL,
            ADD COLUMN no_kontak VARCHAR(20) DEFAULT NULL,
            ADD COLUMN nama_wali VARCHAR(100) DEFAULT NULL,
            ADD COLUMN kontak_wali VARCHAR(20) DEFAULT NULL,
            ADD COLUMN riwayat_penyakit TEXT DEFAULT NULL,
            ADD COLUMN alergi TEXT DEFAULT NULL,
            ADD COLUMN status_aktif ENUM('1', '0') DEFAULT '1'";
            
    $db->query($sql);
    $db->execute();
    
    echo "<h3>Migrasi Santri Berhasil!</h3>";
    echo "<p>Kolom-kolom baru berhasil ditambahkan ke tabel santri.</p>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
