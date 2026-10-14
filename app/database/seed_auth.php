<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

try {
    $db = new Database();
    
    // 1. Create Santri User
    $password = password_hash('password123', PASSWORD_DEFAULT);
    $db->query("INSERT IGNORE INTO users (username, password) VALUES ('santri_budi', :password)");
    $db->bind('password', $password);
    $db->execute();
    
    $db->query("SELECT id FROM users WHERE username = 'santri_budi'");
    $userId = $db->single()['id'] ?? null;
    
    if ($userId) {
        // Add Santri Role
        $db->query("SELECT id FROM roles WHERE nama_role = 'Santri'");
        $roleId = $db->single()['id'] ?? null;
        if ($roleId) {
            $db->query("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (:uid, :rid)");
            $db->bind('uid', $userId);
            $db->bind('rid', $roleId);
            $db->execute();
        }
        
        // Link to existing Santri
        $db->query("UPDATE santri SET user_id = :uid WHERE nis = 'NIS001'");
        $db->bind('uid', $userId);
        $db->execute();
        
        echo "<h3>Seed User Santri Berhasil!</h3>";
        echo "<p>User 'santri_budi' (password: password123) berhasil dibuat dan dilink ke Budi Santoso (NIS001).</p>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
