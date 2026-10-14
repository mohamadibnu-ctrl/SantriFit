<?php
class Pemberian extends Controller {
    public function __construct() {
        AuthMiddleware::checkRole(['Super User', 'Perawat']);
    }

    public function index() {
        $data['judul'] = 'Daftar Instruksi Pemberian Obat';
        $data['resep'] = $this->model('Pemberian_model')->getPendingResep();
        
        $this->view('templates/header', $data);
        $this->view('pemberian/index', $data);
        $this->view('templates/footer');
    }

    public function catat($resep_id) {
        if ($this->model('Pemberian_model')->catatPemberian($resep_id, 'Diberikan sesuai resep')) {
            Flasher::setFlash('Pemberian obat berhasil', 'dicatat', 'success');
        } else {
            Flasher::setFlash('Gagal', 'mencatat atau stok tidak mencukupi', 'danger');
        }
        header('Location: ' . BASEURL . '/pemberian');
        exit;
    }
}
