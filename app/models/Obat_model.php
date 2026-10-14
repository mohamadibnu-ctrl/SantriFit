<?php
class Obat_model {
    private $table = 'obat';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllObat() {
        $this->db->query('SELECT * FROM ' . $this->table . ' ORDER BY nama_obat ASC');
        return $this->db->resultSet();
    }
    
    public function getObatAktif() {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE status_aktif = "1" AND stok > 0 ORDER BY nama_obat ASC');
        return $this->db->resultSet();
    }

    public function getObatById($id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE id = :id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function tambahDataObat($data) {
        $query = "INSERT INTO obat (kode_obat, nama_obat, satuan, stok, deskripsi, status_aktif) 
                  VALUES (:kode, :nama, :satuan, :stok, :deskripsi, :status)";
        
        $this->db->query($query);
        $this->db->bind('kode', $data['kode_obat']);
        $this->db->bind('nama', $data['nama_obat']);
        $this->db->bind('satuan', $data['satuan']);
        $this->db->bind('stok', $data['stok']);
        $this->db->bind('deskripsi', $data['deskripsi']);
        $this->db->bind('status', $data['status_aktif'] ?? '1');
        
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateDataObat($data) {
        $query = "UPDATE obat SET 
                    kode_obat = :kode,
                    nama_obat = :nama,
                    satuan = :satuan,
                    stok = :stok,
                    deskripsi = :deskripsi,
                    status_aktif = :status
                  WHERE id = :id";
                  
        $this->db->query($query);
        $this->db->bind('kode', $data['kode_obat']);
        $this->db->bind('nama', $data['nama_obat']);
        $this->db->bind('satuan', $data['satuan']);
        $this->db->bind('stok', $data['stok']);
        $this->db->bind('deskripsi', $data['deskripsi']);
        $this->db->bind('status', $data['status_aktif'] ?? '1');
        $this->db->bind('id', $data['id']);
        
        $this->db->execute();
        return $this->db->rowCount();
    }
    
    public function updateStok($id, $jumlah, $operasi = 'kurang') {
        if ($operasi == 'kurang') {
            $this->db->query("UPDATE obat SET stok = stok - :jumlah WHERE id = :id AND stok >= :jumlah");
        } else {
            $this->db->query("UPDATE obat SET stok = stok + :jumlah WHERE id = :id");
        }
        $this->db->bind('jumlah', $jumlah);
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }
}
