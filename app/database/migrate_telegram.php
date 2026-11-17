<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

try {
    $db = new Database();

    // SQL pembuatan tabel telegram_accounts untuk menghubungkan user SantriFit dengan akun Telegram
    $sql = "CREATE TABLE IF NOT EXISTS `telegram_accounts` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT NOT NULL UNIQUE,
      `telegram_chat_id` VARCHAR(50) NOT NULL UNIQUE,
      `telegram_username` VARCHAR(100) DEFAULT NULL,
      `is_verified` TINYINT(1) NOT NULL DEFAULT 0,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $db->query($sql);
    $db->execute();

    echo "<h3>Migrasi Tabel Telegram Accounts Berhasil!</h3>";
    echo "<p>Tabel <b>telegram_accounts</b> berhasil dibuat di database <b>" . DB_NAME . "</b>.</p>";
} catch (Exception $e) {
    echo "<h3>Migrasi Gagal!</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
