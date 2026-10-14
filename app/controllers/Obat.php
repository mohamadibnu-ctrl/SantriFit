<?php
class Obat extends Controller {
    public function __construct() {
        AuthMiddleware::checkRole(['Super User', 'Dokter', 'Perawat']);
    }

    public function index() {
        $data['judul'] = 'Daftar Obat';
        $data['obat'] = $this->model('Obat_model')->getAllObat();
        
        $this->view('templates/header', $data);
        $this->view('obat/index', $data);
        $this->view('templates/footer');
    }

    public function tambah() {
        AuthMiddleware::checkRole(['Super User']);
        $data['judul'] = 'Tambah Obat';
        
        $this->view('templates/header', $data);
        $this->view('obat/form', $data);
        $this->view('templates/footer');
    }

    public function prosesTambah() {
        AuthMiddleware::checkRole(['Super User']);
        if ($this->model('Obat_model')->tambahDataObat($_POST) > 0) {
            Flasher::setFlash('berhasil', 'ditambahkan', 'success');
        } else {
            Flasher::setFlash('gagal', 'ditambahkan', 'danger');
        }
        header('Location: ' . BASEURL . '/obat');
        exit;
    }

    public function edit($id) {
        AuthMiddleware::checkRole(['Super User']);
        $data['judul'] = 'Edit Obat';
        $data['obat'] = $this->model('Obat_model')->getObatById($id);
        
        $this->view('templates/header', $data);
        $this->view('obat/form', $data);
        $this->view('templates/footer');
    }

    public function prosesEdit() {
        AuthMiddleware::checkRole(['Super User']);
        if ($this->model('Obat_model')->updateDataObat($_POST) > 0) {
            Flasher::setFlash('berhasil', 'diubah', 'success');
        } else {
            Flasher::setFlash('gagal', 'diubah', 'danger');
        }
        header('Location: ' . BASEURL . '/obat');
        exit;
    }
}
