<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

try {
    $db = new Database();
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    
    if (!$sql) {
        die("Gagal membaca file schema.sql");
    }

    // Split queries by semicolon to execute one by one (PDO sometimes struggles with multiple queries at once depending on driver configuration)
    // However, for CREATE statements, multiple queries in one string often works with MySQL.
    $db->query($sql);
    $db->execute();

    echo "<h3>Migrasi Berhasil!</h3>";
    echo "<p>Semua tabel berhasil dibuat di database <b>" . DB_NAME . "</b>.</p>";
} catch (Exception $e) {
    echo "<h3>Migrasi Gagal!</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
