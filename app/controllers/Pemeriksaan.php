<?php
class Pemeriksaan extends Controller {
    public function __construct() {
        AuthMiddleware::checkRole(['Super User', 'Dokter', 'Perawat']);
    }

    public function index() {
        $data['judul'] = 'Daftar Pemeriksaan Medis';
        
        $tanggal = $_GET['tanggal'] ?? '';
        $santri_id = $_GET['santri_id'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $model = $this->model('Pemeriksaan_model');
        $data['pemeriksaan'] = $model->getPemeriksaanPaginated($limit, $offset, $tanggal, $santri_id, $status);
        $totalData = $model->countPemeriksaan($tanggal, $santri_id, $status);
        
        $data['totalPages'] = ceil($totalData / $limit);
        $data['currentPage'] = $page;
        $data['tanggal'] = $tanggal;
        $data['santri_id'] = $santri_id;
        $data['status'] = $status;
        
        $data['semua_santri'] = $this->model('Santri_model')->getAllSantri();
        
        $this->view('templates/header', $data);
        $this->view('pemeriksaan/index', $data);
        $this->view('templates/footer');
    }

    public function detail($id) {
        $data['judul'] = 'Detail Pemeriksaan';
        $model = $this->model('Pemeriksaan_model');
        $data['pemeriksaan'] = $model->getPemeriksaanById($id);
        
        if (!$data['pemeriksaan']) {
            Flasher::setFlash('Data pemeriksaan tidak ditemukan.', '!', 'danger');
            header('Location: ' . BASEURL . '/pemeriksaan');
            exit;
        }
        
        $data['gejala'] = $model->getGejalaByPemeriksaan($id);
        
        $this->view('templates/header', $data);
        $this->view('pemeriksaan/detail', $data);
        $this->view('templates/footer');
    }

    public function tambah() {
        AuthMiddleware::checkRole(['Super User', 'Perawat', 'Dokter']);
        
        $data['judul'] = 'Catat Pemeriksaan Baru';
        $data['santri'] = $this->model('Santri_model')->getAllSantri();
        $data['gejala_master'] = $this->model('Medis_model')->getAllGejala(); 
        
        $this->view('templates/header', $data);
        $this->view('pemeriksaan/form', $data);
        $this->view('templates/footer');
    }

    public function prosesTambah() {
        AuthMiddleware::checkRole(['Super User', 'Perawat', 'Dokter']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/pemeriksaan');
            exit;
        }
        
        $gejala_ids = $_POST['gejala'] ?? [];
        
        if ($this->model('Pemeriksaan_model')->tambahPemeriksaan($_POST, $gejala_ids) > 0) {
            Flasher::setFlash('Pemeriksaan baru berhasil dicatat', 'sukses', 'success');
        } else {
            Flasher::setFlash('Gagal mencatat pemeriksaan', '!', 'danger');
        }
        
        header('Location: ' . BASEURL . '/pemeriksaan');
        exit;
    }
    
    public function edit($id) {
        $data['judul'] = 'Update Data Pemeriksaan';
        $model = $this->model('Pemeriksaan_model');
        $data['pemeriksaan'] = $model->getPemeriksaanById($id);
        
        if (!$data['pemeriksaan']) {
            Flasher::setFlash('Data pemeriksaan tidak ditemukan.', '!', 'danger');
            header('Location: ' . BASEURL . '/pemeriksaan');
            exit;
        }
        
        $data['santri'] = $this->model('Santri_model')->getAllSantri();
        $data['gejala_master'] = $this->model('Medis_model')->getAllGejala();
        
        $gejala_terpilih = $model->getGejalaByPemeriksaan($id);
        $data['gejala_ids'] = array_column($gejala_terpilih, 'id');
        
        $this->view('templates/header', $data);
        $this->view('pemeriksaan/form', $data);
        $this->view('templates/footer');
    }
    
    public function prosesEdit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/pemeriksaan');
            exit;
        }
        
        $gejala_ids = $_POST['gejala'] ?? [];
        
        if ($this->model('Pemeriksaan_model')->updatePemeriksaan($_POST, $gejala_ids)) {
            Flasher::setFlash('Pemeriksaan berhasil diupdate', 'sukses', 'success');
        } else {
            Flasher::setFlash('Gagal mengupdate pemeriksaan', '!', 'danger');
        }
        
        header('Location: ' . BASEURL . '/pemeriksaan');
        exit;
    }
    
    public function riwayat($santri_id) {
        $data['judul'] = 'Riwayat Medis Santri';
        $data['santri'] = $this->model('Santri_model')->getSantriById($santri_id);
        
        if (!$data['santri']) {
            Flasher::setFlash('Data santri tidak ditemukan.', '!', 'danger');
            header('Location: ' . BASEURL . '/santri');
            exit;
        }
        
        $data['riwayat'] = $this->model('Pemeriksaan_model')->getRiwayatBySantri($santri_id);
        
        $this->view('templates/header', $data);
        $this->view('pemeriksaan/riwayat', $data);
        $this->view('templates/footer');
    }
}
