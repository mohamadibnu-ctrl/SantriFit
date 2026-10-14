<?php
class Pemeriksaan_model {
    private $table = 'pemeriksaan';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getPemeriksaanPaginated($limit, $offset, $tanggal = '', $santri_id = '', $status = '') {
        $query = 'SELECT p.*, s.nama as nama_santri, s.nis, u.username as nama_petugas 
                  FROM ' . $this->table . ' p
                  JOIN santri s ON p.santri_id = s.id
                  JOIN users u ON p.petugas_id = u.id
                  WHERE 1=1';
                  
        if (!empty($tanggal)) {
            $query .= ' AND DATE(p.tanggal_pemeriksaan) = :tanggal';
        }
        if (!empty($santri_id)) {
            $query .= ' AND p.santri_id = :santri_id';
        }
        if (!empty($status)) {
            $query .= ' AND p.status = :status';
        }
        
        $query .= ' ORDER BY p.tanggal_pemeriksaan DESC LIMIT :limit OFFSET :offset';
        
        $this->db->query($query);
        
        if (!empty($tanggal)) $this->db->bind('tanggal', $tanggal);
        if (!empty($santri_id)) $this->db->bind('santri_id', $santri_id);
        if (!empty($status)) $this->db->bind('status', $status);
        $this->db->bind('limit', $limit);
        $this->db->bind('offset', $offset);
        
        return $this->db->resultSet();
    }
    
    public function countPemeriksaan($tanggal = '', $santri_id = '', $status = '') {
        $query = 'SELECT COUNT(*) as total FROM ' . $this->table . ' p WHERE 1=1';
                  
        if (!empty($tanggal)) {
            $query .= ' AND DATE(p.tanggal_pemeriksaan) = :tanggal';
        }
        if (!empty($santri_id)) {
            $query .= ' AND p.santri_id = :santri_id';
        }
        if (!empty($status)) {
            $query .= ' AND p.status = :status';
        }
        
        $this->db->query($query);
        if (!empty($tanggal)) $this->db->bind('tanggal', $tanggal);
        if (!empty($santri_id)) $this->db->bind('santri_id', $santri_id);
        if (!empty($status)) $this->db->bind('status', $status);
        
        return $this->db->single()['total'];
    }

    public function getPemeriksaanById($id) {
        $this->db->query('SELECT p.*, s.nama as nama_santri, s.nis, u.username as nama_petugas 
                          FROM ' . $this->table . ' p
                          JOIN santri s ON p.santri_id = s.id
                          JOIN users u ON p.petugas_id = u.id
                          WHERE p.id = :id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function getGejalaByPemeriksaan($pemeriksaan_id) {
        $this->db->query('SELECT g.id, g.nama_gejala, g.deskripsi 
                          FROM pemeriksaan_gejala pg
                          JOIN gejala g ON pg.gejala_id = g.id
                          WHERE pg.pemeriksaan_id = :id');
        $this->db->bind('id', $pemeriksaan_id);
        return $this->db->resultSet();
    }
    
    public function getRiwayatBySantri($santri_id) {
        $this->db->query('SELECT p.*, u.username as nama_petugas 
                          FROM ' . $this->table . ' p
                          JOIN users u ON p.petugas_id = u.id
                          WHERE p.santri_id = :santri_id
                          ORDER BY p.tanggal_pemeriksaan DESC');
        $this->db->bind('santri_id', $santri_id);
        return $this->db->resultSet();
    }

    public function tambahPemeriksaan($data, $gejala_ids = []) {
        $query = "INSERT INTO pemeriksaan 
                    (santri_id, petugas_id, tanggal_pemeriksaan, keluhan_utama, suhu_tubuh, tekanan_darah, berat_badan, tinggi_badan, hasil_pemeriksaan, catatan_petugas, status) 
                  VALUES 
                    (:santri_id, :petugas_id, :tanggal, :keluhan, :suhu, :tensi, :berat, :tinggi, :hasil, :catatan, :status)";
        
        $this->db->query($query);
        $this->db->bind('santri_id', $data['santri_id']);
        $this->db->bind('petugas_id', $_SESSION['user_id']); // current logged in user
        $this->db->bind('tanggal', $data['tanggal_pemeriksaan']);
        $this->db->bind('keluhan', $data['keluhan_utama']);
        $this->db->bind('suhu', $data['suhu_tubuh'] ?: null);
        $this->db->bind('tensi', $data['tekanan_darah']);
        $this->db->bind('berat', $data['berat_badan'] ?: null);
        $this->db->bind('tinggi', $data['tinggi_badan'] ?: null);
        $this->db->bind('hasil', $data['hasil_pemeriksaan']);
        $this->db->bind('catatan', $data['catatan_petugas']);
        $this->db->bind('status', $data['status'] ?? 'Menunggu Dokter');
        $this->db->execute();
        
        $pemeriksaan_id = $this->db->lastInsertId();
        
        // Insert gejala
        if (!empty($gejala_ids) && is_array($gejala_ids)) {
            foreach ($gejala_ids as $g_id) {
                $this->db->query("INSERT INTO pemeriksaan_gejala (pemeriksaan_id, gejala_id) VALUES (:p_id, :g_id)");
                $this->db->bind('p_id', $pemeriksaan_id);
                $this->db->bind('g_id', $g_id);
                $this->db->execute();
            }
        }
        
        return $this->db->rowCount();
    }
    
    public function updatePemeriksaan($data, $gejala_ids = []) {
        $query = "UPDATE pemeriksaan SET 
                    santri_id = :santri_id, 
                    tanggal_pemeriksaan = :tanggal, 
                    keluhan_utama = :keluhan, 
                    suhu_tubuh = :suhu, 
                    tekanan_darah = :tensi, 
                    berat_badan = :berat, 
                    tinggi_badan = :tinggi, 
                    hasil_pemeriksaan = :hasil, 
                    catatan_petugas = :catatan, 
                    status = :status 
                  WHERE id = :id";
                  
        $this->db->query($query);
        $this->db->bind('santri_id', $data['santri_id']);
        $this->db->bind('tanggal', $data['tanggal_pemeriksaan']);
        $this->db->bind('keluhan', $data['keluhan_utama']);
        $this->db->bind('suhu', $data['suhu_tubuh'] ?: null);
        $this->db->bind('tensi', $data['tekanan_darah']);
        $this->db->bind('berat', $data['berat_badan'] ?: null);
        $this->db->bind('tinggi', $data['tinggi_badan'] ?: null);
        $this->db->bind('hasil', $data['hasil_pemeriksaan']);
        $this->db->bind('catatan', $data['catatan_petugas']);
        $this->db->bind('status', $data['status']);
        $this->db->bind('id', $data['id']);
        $this->db->execute();
        
        // Update gejala (delete old, insert new)
        $this->db->query("DELETE FROM pemeriksaan_gejala WHERE pemeriksaan_id = :id");
        $this->db->bind('id', $data['id']);
        $this->db->execute();
        
        if (!empty($gejala_ids) && is_array($gejala_ids)) {
            foreach ($gejala_ids as $g_id) {
                $this->db->query("INSERT INTO pemeriksaan_gejala (pemeriksaan_id, gejala_id) VALUES (:p_id, :g_id)");
                $this->db->bind('p_id', $data['id']);
                $this->db->bind('g_id', $g_id);
                $this->db->execute();
            }
        }
        
        return true;
    }
}
