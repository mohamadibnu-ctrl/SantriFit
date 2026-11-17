<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/core/Database.php';

try {
    $db = new Database();

    $sql = "CREATE TABLE IF NOT EXISTS `telegram_link_tokens` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT NOT NULL,
      `token` VARCHAR(32) NOT NULL UNIQUE,
      `expires_at` DATETIME NOT NULL,
      `used_at` DATETIME NULL DEFAULT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX `idx_token_lookup` (`token`),
      INDEX `idx_user_lookup` (`user_id`),
      FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->query($sql);
    $db->execute();

    echo "Tabel telegram_link_tokens berhasil dibuat atau sudah ada.\n";
} catch (Exception $e) {
    echo "Migrasi gagal: " . $e->getMessage() . "\n";
}
