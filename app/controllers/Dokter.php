<?php
class Dokter extends Controller {
    public function __construct() {
        AuthMiddleware::checkRole(['Super User']);
    }

    public function index() {
        $data['judul'] = 'Data Dokter';
        $data['dokter'] = $this->model('Dokter_model')->getAllDokter();
        
        $this->view('templates/header', $data);
        $this->view('dokter/index', $data);
        $this->view('templates/footer');
    }

    public function tambah() {
        $data['judul'] = 'Tambah Dokter';
        $data['users'] = $this->model('User_model')->getAllUsers();
        $this->view('templates/header', $data);
        $this->view('dokter/form', $data);
        $this->view('templates/footer');
    }

    public function prosesTambah() {
        if ($this->model('Dokter_model')->tambahDataDokter($_POST) > 0) {
            Flasher::setFlash('berhasil', 'ditambahkan', 'success');
        } else {
            Flasher::setFlash('gagal', 'ditambahkan', 'danger');
        }
        header('Location: ' . BASEURL . '/dokter');
        exit;
    }

    public function edit($id) {
        $data['judul'] = 'Edit Dokter';
        $data['dokter'] = $this->model('Dokter_model')->getDokterById($id);
        $data['users'] = $this->model('User_model')->getAllUsers();
        
        $this->view('templates/header', $data);
        $this->view('dokter/form', $data);
        $this->view('templates/footer');
    }

    public function prosesEdit() {
        if ($this->model('Dokter_model')->updateDataDokter($_POST) > 0) {
            Flasher::setFlash('berhasil', 'diubah', 'success');
        } else {
            Flasher::setFlash('gagal', 'diubah', 'danger');
        }
        header('Location: ' . BASEURL . '/dokter');
        exit;
    }
}
