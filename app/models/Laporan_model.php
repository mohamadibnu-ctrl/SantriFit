<?php
class Laporan_model {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getTrenPemeriksaanBulanan() {
        // Tren 6 bulan terakhir
        $query = "
            SELECT 
                MONTHNAME(tanggal_pemeriksaan) AS bulan,
                COUNT(id) AS jumlah 
            FROM pemeriksaan 
            WHERE tanggal_pemeriksaan >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY YEAR(tanggal_pemeriksaan), MONTH(tanggal_pemeriksaan), MONTHNAME(tanggal_pemeriksaan)
            ORDER BY YEAR(tanggal_pemeriksaan) ASC, MONTH(tanggal_pemeriksaan) ASC
        ";
        $this->db->query($query);
        return $this->db->resultSet();
    }

    public function getPenyakitTerbanyak() {
        // Keluhan / Gejala terbanyak
        $query = "
            SELECT 
                g.nama_gejala AS penyakit, 
                COUNT(pg.pemeriksaan_id) AS jumlah
            FROM pemeriksaan_gejala pg
            JOIN gejala g ON pg.gejala_id = g.id
            GROUP BY g.id, g.nama_gejala
            ORDER BY jumlah DESC
            LIMIT 5
        ";
        $this->db->query($query);
        return $this->db->resultSet();
    }

    public function getStatistikStatusPemeriksaan() {
        $query = "
            SELECT 
                status, 
                COUNT(id) AS jumlah 
            FROM pemeriksaan 
            GROUP BY status
        ";
        $this->db->query($query);
        return $this->db->resultSet();
    }
}