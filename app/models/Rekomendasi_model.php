<?php
class Rekomendasi_model {
    private $table = 'rekomendasi_dokter';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getRekomendasiById($id) {
        $this->db->query('SELECT r.*, p.tanggal_pemeriksaan, p.keluhan_utama, s.nama as nama_santri, s.nis, d.nama as nama_dokter 
                          FROM ' . $this->table . ' r
                          JOIN pemeriksaan p ON r.pemeriksaan_id = p.id
                          JOIN santri s ON p.santri_id = s.id
                          JOIN dokter d ON r.dokter_id = d.id
                          WHERE r.id = :id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }
    
    public function getRekomendasiByPemeriksaan($pemeriksaan_id) {
        $this->db->query('SELECT r.*, d.nama as nama_dokter 
                          FROM ' . $this->table . ' r
                          JOIN dokter d ON r.dokter_id = d.id
                          WHERE r.pemeriksaan_id = :pemeriksaan_id');
        $this->db->bind('pemeriksaan_id', $pemeriksaan_id);
        return $this->db->single();
    }

    public function getAllRekomendasi() {
        $this->db->query('SELECT r.*, p.tanggal_pemeriksaan, s.nama as nama_santri, s.nis, d.nama as nama_dokter 
                          FROM ' . $this->table . ' r
                          JOIN pemeriksaan p ON r.pemeriksaan_id = p.id
                          JOIN santri s ON p.santri_id = s.id
                          JOIN dokter d ON r.dokter_id = d.id
                          ORDER BY r.created_at DESC');
        return $this->db->resultSet();
    }

    public function simpanRekomendasi($data) {
        $this->db->query("SELECT id FROM dokter WHERE user_id = :user_id");
        $this->db->bind('user_id', $_SESSION['user_id']);
        $dokter = $this->db->single();
        if(!$dokter) return 0;

        $query = "INSERT INTO rekomendasi_dokter (pemeriksaan_id, dokter_id, diagnosis_medis, catatan_saran, status) 
                  VALUES (:pemeriksaan_id, :dokter_id, :diagnosis, :catatan, :status)";
        
        $this->db->query($query);
        $this->db->bind('pemeriksaan_id', $data['pemeriksaan_id']);
        $this->db->bind('dokter_id', $dokter['id']);
        $this->db->bind('diagnosis', $data['diagnosis_medis']);
        $this->db->bind('catatan', $data['catatan_saran']);
        $this->db->bind('status', $data['status'] ?? 'Draft');
        
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    public function updateRekomendasi($data) {
        $query = "UPDATE rekomendasi_dokter SET 
                    diagnosis_medis = :diagnosis,
                    catatan_saran = :catatan,
                    status = :status
                  WHERE id = :id AND status != 'Final'";
                  
        $this->db->query($query);
        $this->db->bind('diagnosis', $data['diagnosis_medis']);
        $this->db->bind('catatan', $data['catatan_saran']);
        $this->db->bind('status', $data['status'] ?? 'Draft');
        $this->db->bind('id', $data['id']);
        
        $this->db->execute();
        return $this->db->rowCount();
    }
    
    public function tambahResep($data) {
        $query = "INSERT INTO resep_obat (rekomendasi_id, obat_id, dosis, aturan_minum, jumlah, durasi_hari) 
                  VALUES (:rek_id, :obat_id, :dosis, :aturan, :jumlah, :durasi)";
        $this->db->query($query);
        $this->db->bind('rek_id', $data['rekomendasi_id']);
        $this->db->bind('obat_id', $data['obat_id']);
        $this->db->bind('dosis', $data['dosis']);
        $this->db->bind('aturan', $data['aturan_minum']);
        $this->db->bind('jumlah', $data['jumlah']);
        $this->db->bind('durasi', $data['durasi_hari'] ?: null);
        $this->db->execute();
        return $this->db->rowCount();
    }
    
    public function getResepByRekomendasi($rek_id) {
        $this->db->query('SELECT r.*, o.nama_obat, o.satuan 
                          FROM resep_obat r
                          JOIN obat o ON r.obat_id = o.id
                          WHERE r.rekomendasi_id = :rek_id');
        $this->db->bind('rek_id', $rek_id);
        return $this->db->resultSet();
    }
    
    public function hapusResep($id) {
        $this->db->query("SELECT rd.status FROM resep_obat ro JOIN rekomendasi_dokter rd ON ro.rekomendasi_id = rd.id WHERE ro.id = :id");
        $this->db->bind('id', $id);
        $status = $this->db->single();
        if ($status && $status['status'] == 'Final') {
            return false;
        }

        $this->db->query("DELETE FROM resep_obat WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }
}
