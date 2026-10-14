<?php
class Pemberian_model {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }
    
    public function getPendingResep() {
        $query = "SELECT ro.id as resep_id, ro.dosis, ro.aturan_minum, ro.jumlah,
                         o.nama_obat, o.satuan,
                         rd.id as rekomendasi_id, rd.diagnosis_medis,
                         p.tanggal_pemeriksaan, s.nama as nama_santri, s.nis, s.id as santri_id,
                         po.waktu_diberikan, u.username as nama_perawat
                  FROM resep_obat ro
                  JOIN rekomendasi_dokter rd ON ro.rekomendasi_id = rd.id
                  JOIN obat o ON ro.obat_id = o.id
                  JOIN pemeriksaan p ON rd.pemeriksaan_id = p.id
                  JOIN santri s ON p.santri_id = s.id
                  LEFT JOIN pemberian_obat po ON po.resep_id = ro.id
                  LEFT JOIN users u ON po.perawat_id = u.id
                  WHERE rd.status = 'Final' 
                  ORDER BY po.waktu_diberikan ASC, p.tanggal_pemeriksaan DESC";
        $this->db->query($query);
        return $this->db->resultSet();
    }
    
    public function catatPemberian($resep_id, $catatan = '') {
        $query = "INSERT INTO pemberian_obat (resep_id, perawat_id, catatan) 
                  VALUES (:resep_id, :perawat_id, :catatan)";
        $this->db->query($query);
        $this->db->bind('resep_id', $resep_id);
        $this->db->bind('perawat_id', $_SESSION['user_id']);
        $this->db->bind('catatan', $catatan);
        
        $this->db->execute();
        
        $this->db->query("SELECT obat_id, jumlah FROM resep_obat WHERE id = :id");
        $this->db->bind('id', $resep_id);
        $resep = $this->db->single();
        if ($resep) {
            $this->db->query("UPDATE obat SET stok = stok - :jumlah WHERE id = :id AND stok >= :jumlah");
            $this->db->bind('jumlah', $resep['jumlah']);
            $this->db->bind('id', $resep['obat_id']);
            $this->db->execute();
        }
        
        return $this->db->rowCount();
    }
}
