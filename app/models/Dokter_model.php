<?php
class Dokter_model {
    private $table = 'dokter';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllDokter() {
        $this->db->query('SELECT d.*, u.username FROM ' . $this->table . ' d LEFT JOIN users u ON d.user_id = u.id ORDER BY d.nama ASC');
        return $this->db->resultSet();
    }

    public function getDokterById($id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE id = :id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }
    
    public function getDokterByUserId($user_id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE user_id = :user_id');
        $this->db->bind('user_id', $user_id);
        return $this->db->single();
    }

    public function tambahDataDokter($data) {
        $query = "INSERT INTO dokter (nama, no_izin_praktek, spesialisasi, user_id) 
                  VALUES (:nama, :nip, :spesialisasi, :user_id)";
        
        $this->db->query($query);
        $this->db->bind('nama', $data['nama']);
        $this->db->bind('nip', $data['no_izin_praktek']);
        $this->db->bind('spesialisasi', $data['spesialisasi']);
        $this->db->bind('user_id', $data['user_id']);
        
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateDataDokter($data) {
        $query = "UPDATE dokter SET 
                    nama = :nama,
                    no_izin_praktek = :nip,
                    spesialisasi = :spesialisasi,
                    user_id = :user_id
                  WHERE id = :id";
                  
        $this->db->query($query);
        $this->db->bind('nama', $data['nama']);
        $this->db->bind('nip', $data['no_izin_praktek']);
        $this->db->bind('spesialisasi', $data['spesialisasi']);
        $this->db->bind('user_id', $data['user_id']);
        $this->db->bind('id', $data['id']);
        
        $this->db->execute();
        return $this->db->rowCount();
    }
}
