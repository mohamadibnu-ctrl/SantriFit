<?php
class Rekomendasi extends Controller {
    public function __construct() {
        AuthMiddleware::checkRole(['Super User', 'Dokter', 'Perawat']);
    }
    
    public function index() {
        $data['judul'] = 'Daftar Rekomendasi & Resep';
        $data['rekomendasi'] = $this->model('Rekomendasi_model')->getAllRekomendasi();
        $this->view('templates/header', $data);
        $this->view('rekomendasi/index', $data);
        $this->view('templates/footer');
    }

    public function detail($id) {
        $data['judul'] = 'Detail Rekomendasi Medis';
        $data['rekomendasi'] = $this->model('Rekomendasi_model')->getRekomendasiById($id);
        if (!$data['rekomendasi']) {
            Flasher::setFlash('Data tidak ditemukan', '!', 'danger');
            header('Location: ' . BASEURL . '/rekomendasi');
            exit;
        }
        $data['resep'] = $this->model('Rekomendasi_model')->getResepByRekomendasi($id);
        
        $this->view('templates/header', $data);
        $this->view('rekomendasi/detail', $data);
        $this->view('templates/footer');
    }

    public function form($pemeriksaan_id) {
        AuthMiddleware::checkRole(['Super User', 'Dokter']); // Hanya dokter
        $data['judul'] = 'Buat/Update Rekomendasi Dokter';
        
        // Cek jika sudah ada
        $rek = $this->model('Rekomendasi_model')->getRekomendasiByPemeriksaan($pemeriksaan_id);
        if ($rek) {
            $data['rekomendasi'] = $rek;
            $data['resep'] = $this->model('Rekomendasi_model')->getResepByRekomendasi($rek['id']);
        }
        
        $data['pemeriksaan'] = $this->model('Pemeriksaan_model')->getPemeriksaanById($pemeriksaan_id);
        $data['obat_master'] = $this->model('Obat_model')->getObatAktif();

        $this->view('templates/header', $data);
        $this->view('rekomendasi/form', $data);
        $this->view('templates/footer');
    }

    public function simpan() {
        AuthMiddleware::checkRole(['Super User', 'Dokter']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/pemeriksaan');
            exit;
        }

        $id = $_POST['id'] ?? '';
        $model = $this->model('Rekomendasi_model');
        
        if (empty($id)) {
            $id = $model->simpanRekomendasi($_POST);
        } else {
            $model->updateRekomendasi($_POST);
        }

        // Handle Resep
        if (!empty($_POST['obat_id'])) {
            for ($i = 0; $i < count($_POST['obat_id']); $i++) {
                if (!empty($_POST['obat_id'][$i])) {
                    $resep = [
                        'rekomendasi_id' => $id,
                        'obat_id' => $_POST['obat_id'][$i],
                        'dosis' => $_POST['dosis'][$i],
                        'aturan_minum' => $_POST['aturan_minum'][$i],
                        'jumlah' => $_POST['jumlah'][$i],
                        'durasi_hari' => $_POST['durasi_hari'][$i]
                    ];
                    $model->tambahResep($resep);
                }
            }
        }
        
        Flasher::setFlash('Rekomendasi berhasil', 'disimpan', 'success');
        
        if ($_POST['status'] == 'Final') {
            header('Location: ' . BASEURL . '/rekomendasi');
        } else {
            header('Location: ' . BASEURL . '/rekomendasi/form/' . $_POST['pemeriksaan_id']);
        }
        exit;
    }
    
    public function hapus_resep($resep_id, $pemeriksaan_id) {
        AuthMiddleware::checkRole(['Super User', 'Dokter']);
        if ($this->model('Rekomendasi_model')->hapusResep($resep_id)) {
            Flasher::setFlash('Resep berhasil', 'dihapus', 'success');
        } else {
            Flasher::setFlash('Gagal menghapus resep (Mungkin sudah Final)', '!', 'danger');
        }
        header('Location: ' . BASEURL . '/rekomendasi/form/' . $pemeriksaan_id);
        exit;
    }
}
