<?php
class Riwayat_model {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getRiwayatKesehatanSantri($santri_id, $filter_mulai = '', $filter_akhir = '', $keluhan = '') {
        $sql = "SELECT p.*, u.username as nama_petugas, 
                       rd.id as rekomendasi_id, rd.diagnosis_medis, rd.catatan_saran, rd.status as status_rekomendasi
                FROM pemeriksaan p
                LEFT JOIN users u ON p.petugas_id = u.id
                LEFT JOIN rekomendasi_dokter rd ON rd.pemeriksaan_id = p.id
                WHERE p.santri_id = :santri_id";
        
        if (!empty($filter_mulai) && !empty($filter_akhir)) {
            $sql .= " AND DATE(p.tanggal_pemeriksaan) BETWEEN :mulai AND :akhir";
        }
        
        if (!empty($keluhan)) {
            $sql .= " AND p.keluhan_utama LIKE :keluhan";
        }
        
        $sql .= " ORDER BY p.tanggal_pemeriksaan DESC";
        
        $this->db->query($sql);
        $this->db->bind('santri_id', $santri_id);
        
        if (!empty($filter_mulai) && !empty($filter_akhir)) {
            $this->db->bind('mulai', $filter_mulai);
            $this->db->bind('akhir', $filter_akhir);
        }
        if (!empty($keluhan)) {
            $this->db->bind('keluhan', "%$keluhan%");
        }
        
        $pemeriksaan = $this->db->resultSet();
        
        foreach ($pemeriksaan as &$p) {
            $this->db->query("SELECT g.nama_gejala FROM pemeriksaan_gejala pg JOIN gejala g ON pg.gejala_id = g.id WHERE pg.pemeriksaan_id = :pid");
            $this->db->bind('pid', $p['id']);
            $p['gejala'] = $this->db->resultSet();
            
            $p['resep'] = [];
            if ($p['rekomendasi_id']) {
                $this->db->query("SELECT ro.*, o.nama_obat, o.satuan, po.waktu_diberikan 
                                  FROM resep_obat ro 
                                  JOIN obat o ON ro.obat_id = o.id 
                                  LEFT JOIN pemberian_obat po ON po.resep_id = ro.id
                                  WHERE ro.rekomendasi_id = :rid");
                $this->db->bind('rid', $p['rekomendasi_id']);
                $p['resep'] = $this->db->resultSet();
            }
        }
        
        return $pemeriksaan;
    }
}
