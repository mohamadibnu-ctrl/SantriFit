<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

try {
    $db = new Database();
    
    // Tambah kolom
    $sql1 = "ALTER TABLE pemeriksaan 
             ADD COLUMN hasil_pemeriksaan TEXT DEFAULT NULL,
             ADD COLUMN catatan_petugas TEXT DEFAULT NULL";
    
    // Ubah status ENUM
    $sql2 = "ALTER TABLE pemeriksaan 
             MODIFY COLUMN status ENUM('Menunggu Dokter', 'Sedang Ditangani', 'Selesai', 'Perlu Tindak Lanjut') DEFAULT 'Menunggu Dokter'";
             
    try {
        $db->query($sql1);
        $db->execute();
    } catch (Exception $e) {
        // Abaikan jika kolom sudah ada
    }
    
    try {
        $db->query($sql2);
        $db->execute();
    } catch (Exception $e) {
        echo "Gagal mengubah ENUM: " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>Migrasi Pemeriksaan Berhasil!</h3>";
    echo "<p>Struktur tabel pemeriksaan telah diperbarui.</p>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
