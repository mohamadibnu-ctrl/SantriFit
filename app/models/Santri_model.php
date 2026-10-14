<?php
class Santri_model {
    private $table = 'santri';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllSantri() {
        $this->db->query('SELECT * FROM ' . $this->table . ' ORDER BY nama ASC');
        return $this->db->resultSet();
    }

    public function getSantriById($id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE id = :id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }
    
    public function getRiwayatKesehatan($santri_id) {
        $this->db->query('SELECT * FROM riwayat_kesehatan WHERE santri_id = :id ORDER BY tanggal_tercatat DESC');
        $this->db->bind('id', $santri_id);
        return $this->db->resultSet();
    }

    public function getSantriPaginated($limit, $offset, $keyword = '', $status = '') {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE 1=1';
        
        if (!empty($keyword)) {
            $query .= ' AND (nama LIKE :keyword OR nis LIKE :keyword)';
        }
        
        if ($status !== '') {
            $query .= ' AND status_aktif = :status';
        }
        
        $query .= ' ORDER BY nama ASC LIMIT :limit OFFSET :offset';
        
        $this->db->query($query);
        
        if (!empty($keyword)) {
            $this->db->bind('keyword', "%$keyword%");
        }
        if ($status !== '') {
            $this->db->bind('status', $status);
        }
        
        $this->db->bind('limit', $limit);
        $this->db->bind('offset', $offset);
        
        return $this->db->resultSet();
    }
    
    public function countSantri($keyword = '', $status = '') {
        $query = 'SELECT COUNT(*) as total FROM ' . $this->table . ' WHERE 1=1';
        
        if (!empty($keyword)) {
            $query .= ' AND (nama LIKE :keyword OR nis LIKE :keyword)';
        }
        
        if ($status !== '') {
            $query .= ' AND status_aktif = :status';
        }
        
        $this->db->query($query);
        
        if (!empty($keyword)) {
            $this->db->bind('keyword', "%$keyword%");
        }
        if ($status !== '') {
            $this->db->bind('status', $status);
        }
        
        return $this->db->single()['total'];
    }
    
    public function tambahDataSantri($data) {
        $query = "INSERT INTO santri 
                    (nis, nama, foto, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, kamar, kelas, no_kontak, nama_wali, kontak_wali, riwayat_penyakit, alergi, status_aktif) 
                  VALUES 
                    (:nis, :nama, :foto, :tempat_lahir, :tanggal_lahir, :jenis_kelamin, :alamat, :kamar, :kelas, :no_kontak, :nama_wali, :kontak_wali, :riwayat_penyakit, :alergi, :status_aktif)";
        
        $this->db->query($query);
        $this->db->bind('nis', $data['nis']);
        $this->db->bind('nama', $data['nama']);
        $this->db->bind('foto', $data['foto']);
        $this->db->bind('tempat_lahir', $data['tempat_lahir']);
        $this->db->bind('tanggal_lahir', $data['tanggal_lahir']);
        $this->db->bind('jenis_kelamin', $data['jenis_kelamin']);
        $this->db->bind('alamat', $data['alamat']);
        $this->db->bind('kamar', $data['kamar']);
        $this->db->bind('kelas', $data['kelas']);
        $this->db->bind('no_kontak', $data['no_kontak']);
        $this->db->bind('nama_wali', $data['nama_wali']);
        $this->db->bind('kontak_wali', $data['kontak_wali']);
        $this->db->bind('riwayat_penyakit', $data['riwayat_penyakit']);
        $this->db->bind('alergi', $data['alergi']);
        $this->db->bind('status_aktif', $data['status_aktif']);

        $this->db->execute();
        return $this->db->rowCount();
    }
    
    public function updateDataSantri($data) {
        $query = "UPDATE santri SET 
                    nis = :nis, 
                    nama = :nama, 
                    tempat_lahir = :tempat_lahir, 
                    tanggal_lahir = :tanggal_lahir, 
                    jenis_kelamin = :jenis_kelamin, 
                    alamat = :alamat, 
                    kamar = :kamar, 
                    kelas = :kelas, 
                    no_kontak = :no_kontak, 
                    nama_wali = :nama_wali, 
                    kontak_wali = :kontak_wali, 
                    riwayat_penyakit = :riwayat_penyakit, 
                    alergi = :alergi, 
                    status_aktif = :status_aktif";
                    
        // Hanya update foto jika ada yang baru
        if (!empty($data['foto'])) {
            $query .= ", foto = :foto";
        }
        
        $query .= " WHERE id = :id";
        
        $this->db->query($query);
        $this->db->bind('nis', $data['nis']);
        $this->db->bind('nama', $data['nama']);
        $this->db->bind('tempat_lahir', $data['tempat_lahir']);
        $this->db->bind('tanggal_lahir', $data['tanggal_lahir']);
        $this->db->bind('jenis_kelamin', $data['jenis_kelamin']);
        $this->db->bind('alamat', $data['alamat']);
        $this->db->bind('kamar', $data['kamar']);
        $this->db->bind('kelas', $data['kelas']);
        $this->db->bind('no_kontak', $data['no_kontak']);
        $this->db->bind('nama_wali', $data['nama_wali']);
        $this->db->bind('kontak_wali', $data['kontak_wali']);
        $this->db->bind('riwayat_penyakit', $data['riwayat_penyakit']);
        $this->db->bind('alergi', $data['alergi']);
        $this->db->bind('status_aktif', $data['status_aktif']);
        $this->db->bind('id', $data['id']);

        if (!empty($data['foto'])) {
            $this->db->bind('foto', $data['foto']);
        }

        $this->db->execute();
        return $this->db->rowCount();
    }

    public function nonaktifkanSantri($id) {
        $query = "UPDATE santri SET status_aktif = '0' WHERE id = :id";
        $this->db->query($query);
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }
}
