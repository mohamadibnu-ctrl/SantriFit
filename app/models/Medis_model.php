<?php
class Medis_model {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllGejala() {
        $this->db->query('SELECT * FROM gejala ORDER BY nama_gejala ASC');
        return $this->db->resultSet();
    }

    public function getAllObat() {
        $this->db->query('SELECT * FROM obat ORDER BY nama_obat ASC');
        return $this->db->resultSet();
    }
    
    public function getResepByRekomendasi($rekomendasi_id) {
        $this->db->query('SELECT r.*, o.nama_obat, o.satuan 
                          FROM resep_obat r
                          JOIN obat o ON r.obat_id = o.id
                          WHERE r.rekomendasi_id = :id');
        $this->db->bind('id', $rekomendasi_id);
        return $this->db->resultSet();
    }
}
