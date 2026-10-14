<?php
class Dashboard_model {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getSuperUserStats() {
        $stats = [];
        $this->db->query("SELECT COUNT(id) as total FROM santri");
        $stats['total_santri'] = $this->db->single()['total'];
        
        // No status_aktif in schema, assume all are active
        $stats['santri_aktif'] = $stats['total_santri'];
        
        $this->db->query("SELECT COUNT(id) as total FROM users");
        $stats['total_user'] = $this->db->single()['total'];
        
        $this->db->query("SELECT COUNT(id) as total FROM dokter");
        $stats['total_dokter'] = $this->db->single()['total'];
        
        $this->db->query("SELECT COUNT(id) as total FROM perawat");
        $stats['total_perawat'] = $this->db->single()['total'];
        
        $this->db->query("SELECT COUNT(id) as total FROM pemeriksaan");
        $stats['total_pemeriksaan'] = $this->db->single()['total'];
        
        $this->db->query("SELECT COUNT(id) as total FROM pemeriksaan WHERE MONTH(tanggal_pemeriksaan) = MONTH(CURRENT_DATE()) AND YEAR(tanggal_pemeriksaan) = YEAR(CURRENT_DATE())");
        $stats['pemeriksaan_bulan_ini'] = $this->db->single()['total'];
        
        return $stats;
    }

    public function getPengurusStats() {
        $stats = [];
        $this->db->query("SELECT COUNT(id) as total FROM santri");
        $stats['total_santri'] = $this->db->single()['total'];
        
        $this->db->query("SELECT COUNT(id) as total FROM pemeriksaan WHERE DATE(tanggal_pemeriksaan) = CURRENT_DATE()");
        $stats['pemeriksaan_hari_ini'] = $this->db->single()['total'];
        
        $this->db->query("SELECT COUNT(id) as total FROM pemeriksaan WHERE status IN ('Menunggu', 'Diperiksa', 'Rujuk')");
        $stats['santri_sakit'] = $this->db->single()['total'];
        
        $this->db->query("SELECT p.*, s.nama, s.nis FROM pemeriksaan p JOIN santri s ON p.santri_id = s.id ORDER BY p.tanggal_pemeriksaan DESC LIMIT 5");
        $stats['pemeriksaan_terbaru'] = $this->db->resultSet();
        
        return $stats;
    }

    public function getPerawatStats() {
        $stats = [];
        $this->db->query("SELECT COUNT(id) as total FROM pemeriksaan WHERE DATE(tanggal_pemeriksaan) = CURRENT_DATE()");
        $stats['pemeriksaan_hari_ini'] = $this->db->single()['total'];
        
        $this->db->query("SELECT COUNT(id) as total FROM pemeriksaan WHERE status = 'Menunggu'");
        $stats['menunggu_dokter'] = $this->db->single()['total'];
        
        $this->db->query("SELECT COUNT(id) as total FROM pemeriksaan WHERE status = 'Rujuk'");
        $stats['tindak_lanjut'] = $this->db->single()['total'];
        
        $this->db->query("SELECT p.*, s.nama, s.nis FROM pemeriksaan p JOIN santri s ON p.santri_id = s.id ORDER BY p.tanggal_pemeriksaan DESC LIMIT 5");
        $stats['pemeriksaan_terbaru'] = $this->db->resultSet();
        
        return $stats;
    }

    public function getDokterStats() {
        $stats = [];
        $this->db->query("SELECT COUNT(id) as total FROM pemeriksaan WHERE status = 'Menunggu'");
        $stats['perlu_direview'] = $this->db->single()['total'];
        
        $this->db->query("SELECT COUNT(id) as total FROM pemeriksaan WHERE DATE(tanggal_pemeriksaan) = CURRENT_DATE() AND status != 'Selesai'");
        $stats['pemeriksaan_hari_ini'] = $this->db->single()['total'];
        
        // Assume recommendations missing resep as 'belum selesai' or just total recommendations for now
        $this->db->query("SELECT COUNT(id) as total FROM rekomendasi_dokter");
        $stats['rekomendasi_belum_selesai'] = $this->db->single()['total'];
        
        $this->db->query("SELECT p.*, s.nama, s.nis FROM pemeriksaan p JOIN santri s ON p.santri_id = s.id WHERE p.status = 'Menunggu' ORDER BY p.tanggal_pemeriksaan DESC LIMIT 5");
        $stats['pasien_terbaru'] = $this->db->resultSet();
        
        return $stats;
    }

    public function getSantriStats($user_id) {
        $stats = [];
        
        $this->db->query("SELECT * FROM santri WHERE user_id = :user_id");
        $this->db->bind('user_id', $user_id);
        $santri = $this->db->single();
        
        if ($santri) {
            $stats['profil'] = $santri;
            
            $this->db->query("SELECT * FROM pemeriksaan WHERE santri_id = :santri_id ORDER BY tanggal_pemeriksaan DESC LIMIT 1");
            $this->db->bind('santri_id', $santri['id']);
            $stats['pemeriksaan_terakhir'] = $this->db->single();
            
            $this->db->query("SELECT COUNT(id) as total FROM pemeriksaan WHERE santri_id = :santri_id");
            $this->db->bind('santri_id', $santri['id']);
            $stats['total_pemeriksaan'] = $this->db->single()['total'];
        }
        
        return $stats;
    }
}
