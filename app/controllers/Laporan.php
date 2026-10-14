<?php
class Laporan extends Controller {
    public function __construct() {
        AuthMiddleware::check();
        AuthMiddleware::checkRole(['Super User', 'Pengurus', 'Dokter', 'Perawat']);
    }

    public function index() {
        $data['judul'] = 'Laporan Kesehatan';
        
        $laporanModel = $this->model('Laporan_model');
        
        $data['tren_bulanan'] = $laporanModel->getTrenPemeriksaanBulanan();
        $data['penyakit_populer'] = $laporanModel->getPenyakitTerbanyak();
        $data['status_pemeriksaan'] = $laporanModel->getStatistikStatusPemeriksaan();

        $this->view('templates/header', $data);
        $this->view('laporan/index', $data);
        $this->view('templates/footer');
    }
}
