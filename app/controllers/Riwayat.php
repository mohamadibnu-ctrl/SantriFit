<?php
class Riwayat extends Controller {
    public function __construct() {
        AuthMiddleware::checkRole(['Super User', 'Dokter', 'Perawat', 'Pengurus', 'Santri']);
    }

    public function detail($santri_id) {
        $allowedRoles = array_intersect(['Super User', 'Dokter', 'Perawat', 'Pengurus'], $_SESSION['user_roles']);
        if (empty($allowedRoles)) {
            $santri = $this->model('Santri_model')->getSantriById($santri_id);
            if (!$santri || $santri['user_id'] != $_SESSION['user_id']) {
                header('Location: ' . BASEURL . '/unauthorized');
                exit;
            }
        }
        
        $data['judul'] = 'Riwayat Kesehatan Santri';
        $data['santri'] = $this->model('Santri_model')->getSantriById($santri_id);
        
        if (!$data['santri']) {
            Flasher::setFlash('Data santri tidak ditemukan', '!', 'danger');
            header('Location: ' . BASEURL . '/home');
            exit;
        }

        $mulai = $_GET['mulai'] ?? '';
        $akhir = $_GET['akhir'] ?? '';
        $keluhan = $_GET['keluhan'] ?? '';
        
        $data['mulai'] = $mulai;
        $data['akhir'] = $akhir;
        $data['keluhan'] = $keluhan;
        
        $data['riwayat'] = $this->model('Riwayat_model')->getRiwayatKesehatanSantri($santri_id, $mulai, $akhir, $keluhan);
        
        $this->view('templates/header', $data);
        $this->view('riwayat/detail', $data);
        $this->view('templates/footer');
    }
}
