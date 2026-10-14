<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

try {
    $db = new Database();

    // 1. Tambah Roles
    $roles = ['Super User', 'Pengurus', 'Perawat', 'Dokter', 'Santri'];
    foreach ($roles as $role) {
        $db->query("INSERT IGNORE INTO roles (nama_role) VALUES (:role)");
        $db->bind('role', $role);
        $db->execute();
    }
    
    // 2. Tambah User (Admin, Dokter, Perawat)
    $users = [
        ['admin', 'Super User'],
        ['dokter_ahmad', 'Dokter'],
        ['perawat_siti', 'Perawat']
    ];
    $password = password_hash('password123', PASSWORD_DEFAULT);
    
    foreach ($users as $u) {
        $db->query("INSERT IGNORE INTO users (username, password) VALUES (:username, :password)");
        $db->bind('username', $u[0]);
        $db->bind('password', $password);
        $db->execute();
        
        $userId = $db->lastInsertId();
        if ($userId > 0) {
            $db->query("SELECT id FROM roles WHERE nama_role = :role");
            $db->bind('role', $u[1]);
            $roleData = $db->single();
            if ($roleData) {
                $db->query("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (:uid, :rid)");
                $db->bind('uid', $userId);
                $db->bind('rid', $roleData['id']);
                $db->execute();
            }
        }
    }

    // 3. Tambah Profil Dokter & Perawat
    $db->query("SELECT id FROM users WHERE username = 'dokter_ahmad'");
    $dokterUserId = $db->single()['id'] ?? null;
    if ($dokterUserId) {
        $db->query("INSERT INTO dokter (nama, no_izin_praktek, spesialisasi, user_id) VALUES ('dr. Ahmad', 'SIP-12345', 'Umum', :uid)");
        $db->bind('uid', $dokterUserId);
        $db->execute();
    }

    $db->query("SELECT id FROM users WHERE username = 'perawat_siti'");
    $perawatUserId = $db->single()['id'] ?? null;
    if ($perawatUserId) {
        $db->query("INSERT INTO perawat (nama, nip, user_id) VALUES ('Siti', 'NIP-67890', :uid)");
        $db->bind('uid', $perawatUserId);
        $db->execute();
    }

    // 4. Tambah Santri
    $santri = [
        ['NIS001', 'Budi Santoso', '2010-05-12', 'L', 'Jl. Mawar No. 1'],
        ['NIS002', 'Aisyah', '2011-08-20', 'P', 'Jl. Melati No. 2']
    ];
    foreach ($santri as $s) {
        $db->query("INSERT INTO santri (nis, nama, tanggal_lahir, jenis_kelamin, alamat) VALUES (:nis, :nama, :tgl, :jk, :alamat)");
        $db->bind('nis', $s[0]);
        $db->bind('nama', $s[1]);
        $db->bind('tgl', $s[2]);
        $db->bind('jk', $s[3]);
        $db->bind('alamat', $s[4]);
        $db->execute();
    }

    // 5. Tambah Gejala & Obat
    $gejala = ['Demam', 'Batuk', 'Pilek', 'Pusing', 'Sakit Perut'];
    foreach ($gejala as $g) {
        $db->query("INSERT IGNORE INTO gejala (nama_gejala, deskripsi) VALUES (:nama, :desc)");
        $db->bind('nama', $g);
        $db->bind('desc', 'Gejala ' . $g);
        $db->execute();
    }

    $obat = [
        ['Paracetamol 500mg', 'Tablet'],
        ['Amoxicillin 500mg', 'Tablet'],
        ['OBH Combi', 'Botol']
    ];
    foreach ($obat as $o) {
        $db->query("INSERT IGNORE INTO obat (nama_obat, satuan) VALUES (:nama, :satuan)");
        $db->bind('nama', $o[0]);
        $db->bind('satuan', $o[1]);
        $db->execute();
    }

    echo "<h3>Seeding Berhasil!</h3>";
    echo "<p>Data awal telah dimasukkan ke database.</p>";
} catch (Exception $e) {
    echo "<h3>Seeding Gagal!</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
