<?php
// Internal API endpoint for SantriFit Chatbot Database Context
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/Notification_model.php';

try {
    $db = new Database();

    // 1. Santri
    $db->query("SELECT id, nis, nama, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat FROM santri ORDER BY id ASC");
    $santri = $db->resultSet();

    // 2. Dokter
    $db->query("SELECT id, nama, spesialisasi, no_izin_praktek FROM dokter ORDER BY id ASC");
    $dokter = $db->resultSet();

    // 3. Perawat
    $db->query("SELECT id, nama, nip FROM perawat ORDER BY id ASC");
    $perawat = $db->resultSet();

    // 4. Obat
    $db->query("SELECT id, kode_obat, nama_obat, satuan, stok, deskripsi FROM obat WHERE status_aktif = 1 OR status_aktif IS NULL ORDER BY nama_obat ASC");
    $obat = $db->resultSet();

    // 5. Pemeriksaan Terkini
    $db->query("SELECT p.id, s.nama AS nama_santri, s.nis, p.tanggal_pemeriksaan, p.keluhan_utama, p.hasil_pemeriksaan, p.status, p.suhu_tubuh, p.tekanan_darah, p.berat_badan, p.tinggi_badan 
                FROM pemeriksaan p 
                JOIN santri s ON p.santri_id = s.id 
                ORDER BY p.id DESC LIMIT 15");
    $pemeriksaan = $db->resultSet();

    // 6. Riwayat Kesehatan
    $db->query("SELECT rk.id, s.nama AS nama_santri, s.nis, rk.nama_penyakit, rk.status, rk.tanggal_tercatat 
                FROM riwayat_kesehatan rk 
                JOIN santri s ON rk.santri_id = s.id 
                ORDER BY rk.id DESC LIMIT 15");
    $riwayat = $db->resultSet();

    // 7. Rekomendasi Dokter & Diagnosis
    $db->query("SELECT rd.id, s.nama AS nama_santri, d.nama AS nama_dokter, rd.diagnosis_medis, rd.catatan_saran, rd.status, rd.created_at
                FROM rekomendasi_dokter rd
                JOIN pemeriksaan p ON rd.pemeriksaan_id = p.id
                JOIN santri s ON p.santri_id = s.id
                LEFT JOIN dokter d ON rd.dokter_id = d.id
                ORDER BY rd.id DESC LIMIT 10");
    $rekomendasi = $db->resultSet();

    // 8. Pemberian Obat
    $db->query("SELECT po.id, s.nama AS nama_santri, o.nama_obat, ro.dosis, ro.aturan_minum, po.waktu_diberikan, pr.nama AS nama_perawat, po.catatan
                FROM pemberian_obat po
                JOIN resep_obat ro ON po.resep_id = ro.id
                JOIN obat o ON ro.obat_id = o.id
                JOIN rekomendasi_dokter rd ON ro.rekomendasi_id = rd.id
                JOIN pemeriksaan p ON rd.pemeriksaan_id = p.id
                JOIN santri s ON p.santri_id = s.id
                LEFT JOIN perawat pr ON po.perawat_id = pr.id
                ORDER BY po.id DESC LIMIT 10");
    $pemberian = $db->resultSet();

    // 8.5. Gejala Terdaftar
    $db->query("SELECT id, nama_gejala, deskripsi FROM gejala WHERE status_aktif = 1 ORDER BY nama_gejala ASC");
    $gejala = $db->resultSet();

    // 8.6. Resep Obat Terkini
    $db->query("SELECT ro.id, ro.rekomendasi_id, o.nama_obat, ro.dosis, ro.aturan_minum, ro.jumlah 
                FROM resep_obat ro 
                JOIN obat o ON ro.obat_id = o.id 
                ORDER BY ro.id DESC LIMIT 15");
    $resep = $db->resultSet();

    // 9. Notifikasi Sesuai Role
    $userId = $_GET['user_id'] ?? null;
    $roleParam = $_GET['role'] ?? 'Super User';
    $roles = is_array($roleParam) ? $roleParam : explode(',', $roleParam);
    $notifModel = new Notification_model();
    $notifications = $notifModel->getNotifications($userId, $roles, 'all', 10);
    $unreadCount = $notifModel->getUnreadCount($userId, $roles);

    // 9.5. Pengguna (User)
    $db->query("SELECT u.id, u.username, GROUP_CONCAT(r.nama_role SEPARATOR ', ') AS roles 
                FROM users u 
                LEFT JOIN user_roles ur ON u.id = ur.user_id 
                LEFT JOIN roles r ON ur.role_id = r.id 
                GROUP BY u.id, u.username 
                ORDER BY u.id ASC");
    $users = $db->resultSet();

    echo json_encode([
        'success' => true,
        'database_name' => DB_NAME,
        'summary' => [
            'total_santri' => count($santri),
            'total_santri_aktif' => count($santri),
            'total_dokter' => count($dokter),
            'total_perawat' => count($perawat),
            'total_obat' => count($obat),
            'total_user' => count($users),
            'total_pemeriksaan' => count($pemeriksaan),
            'total_gejala' => count($gejala),
            'total_resep' => count($resep),
            'total_riwayat' => count($riwayat),
            'total_rekomendasi' => count($rekomendasi),
            'total_pemberian' => count($pemberian),
            'total_unread_notif' => $unreadCount
        ],
        'data' => [
            'santri' => $santri,
            'dokter' => $dokter,
            'perawat' => $perawat,
            'obat' => $obat,
            'users' => $users,
            'pemeriksaan' => $pemeriksaan,
            'gejala' => $gejala,
            'resep_obat' => $resep,
            'riwayat' => $riwayat,
            'rekomendasi' => $rekomendasi,
            'pemberian_obat' => $pemberian,
            'notifications' => $notifications
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}