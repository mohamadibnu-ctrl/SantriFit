<?php
class Santri extends Controller {
    public function __construct() {
        AuthMiddleware::checkRole(['Super User', 'Pengurus', 'Perawat', 'Dokter']);
    }

    public function index() {
        $data['judul'] = 'Data Santri';
        
        $keyword = $_GET['q'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $santriModel = $this->model('Santri_model');
        $data['santri'] = $santriModel->getSantriPaginated($limit, $offset, $keyword, $status);
        $totalData = $santriModel->countSantri($keyword, $status);
        $data['totalPages'] = ceil($totalData / $limit);
        $data['currentPage'] = $page;
        $data['keyword'] = $keyword;
        $data['status'] = $status;
        
        $this->view('templates/header', $data);
        $this->view('santri/index', $data);
        $this->view('templates/footer');
    }

    public function detail($id) {
        $data['judul'] = 'Detail Santri';
        $data['santri'] = $this->model('Santri_model')->getSantriById($id);
        
        if (!$data['santri']) {
            Flasher::setFlash('Data santri tidak ditemukan.', '!', 'danger');
            header('Location: ' . BASEURL . '/santri');
            exit;
        }

        // Hitung Usia
        $tgl_lahir = new DateTime($data['santri']['tanggal_lahir']);
        $sekarang = new DateTime('today');
        $data['usia'] = $tgl_lahir->diff($sekarang)->y;

        $this->view('templates/header', $data);
        $this->view('santri/detail', $data);
        $this->view('templates/footer');
    }

    public function tambah() {
        $data['judul'] = 'Tambah Data Santri';
        $this->view('templates/header', $data);
        $this->view('santri/form', $data);
        $this->view('templates/footer');
    }

    public function prosesTambah() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/santri');
            exit;
        }
        
        // Handle upload foto
        $foto_name = null;
        if (!empty($_FILES['foto']['name'])) {
            $foto_name = $this->uploadFoto();
            if (!$foto_name) {
                header('Location: ' . BASEURL . '/santri/tambah');
                exit;
            }
        }
        
        $data = [
            'nis' => $_POST['nis'],
            'nama' => $_POST['nama'],
            'foto' => $foto_name,
            'tempat_lahir' => $_POST['tempat_lahir'],
            'tanggal_lahir' => $_POST['tanggal_lahir'],
            'jenis_kelamin' => $_POST['jenis_kelamin'],
            'alamat' => $_POST['alamat'],
            'kamar' => $_POST['kamar'],
            'kelas' => $_POST['kelas'],
            'no_kontak' => $_POST['no_kontak'],
            'nama_wali' => $_POST['nama_wali'],
            'kontak_wali' => $_POST['kontak_wali'],
            'riwayat_penyakit' => $_POST['riwayat_penyakit'],
            'alergi' => $_POST['alergi'],
            'status_aktif' => $_POST['status_aktif'] ?? '1'
        ];
        
        try {
            if ($this->model('Santri_model')->tambahDataSantri($data) > 0) {
                Flasher::setFlash('berhasil', 'ditambahkan', 'success');
                header('Location: ' . BASEURL . '/santri');
                exit;
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                Flasher::setFlash('NIS sudah terdaftar!', 'Gagal', 'danger');
            } else {
                Flasher::setFlash('Error system: ' . $e->getMessage(), 'Gagal', 'danger');
            }
            header('Location: ' . BASEURL . '/santri/tambah');
            exit;
        }
    }
    
    public function edit($id) {
        $data['judul'] = 'Edit Data Santri';
        $data['santri'] = $this->model('Santri_model')->getSantriById($id);
        
        if (!$data['santri']) {
            Flasher::setFlash('Data santri tidak ditemukan.', '!', 'danger');
            header('Location: ' . BASEURL . '/santri');
            exit;
        }
        
        $this->view('templates/header', $data);
        $this->view('santri/form', $data);
        $this->view('templates/footer');
    }
    
    public function prosesEdit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/santri');
            exit;
        }
        
        $id = $_POST['id'];
        $foto_lama = $_POST['foto_lama'];
        $foto_name = null;
        
        if (!empty($_FILES['foto']['name'])) {
            $foto_name = $this->uploadFoto();
            if (!$foto_name) {
                header('Location: ' . BASEURL . '/santri/edit/' . $id);
                exit;
            }
            if ($foto_lama && file_exists('../public/img/santri/' . $foto_lama)) {
                unlink('../public/img/santri/' . $foto_lama);
            }
        }
        
        $data = [
            'id' => $id,
            'nis' => $_POST['nis'],
            'nama' => $_POST['nama'],
            'foto' => $foto_name,
            'tempat_lahir' => $_POST['tempat_lahir'],
            'tanggal_lahir' => $_POST['tanggal_lahir'],
            'jenis_kelamin' => $_POST['jenis_kelamin'],
            'alamat' => $_POST['alamat'],
            'kamar' => $_POST['kamar'],
            'kelas' => $_POST['kelas'],
            'no_kontak' => $_POST['no_kontak'],
            'nama_wali' => $_POST['nama_wali'],
            'kontak_wali' => $_POST['kontak_wali'],
            'riwayat_penyakit' => $_POST['riwayat_penyakit'],
            'alergi' => $_POST['alergi'],
            'status_aktif' => $_POST['status_aktif'] ?? '1'
        ];
        
        try {
            $this->model('Santri_model')->updateDataSantri($data);
            Flasher::setFlash('berhasil', 'diubah', 'success');
            header('Location: ' . BASEURL . '/santri');
            exit;
        } catch (PDOException $e) {
            Flasher::setFlash('Error system: ' . $e->getMessage(), 'Gagal', 'danger');
            header('Location: ' . BASEURL . '/santri/edit/' . $id);
            exit;
        }
    }
    
    public function nonaktifkan($id) {
        if ($this->model('Santri_model')->nonaktifkanSantri($id) > 0) {
            Flasher::setFlash('berhasil', 'dinonaktifkan', 'success');
        } else {
            Flasher::setFlash('gagal', 'dinonaktifkan', 'danger');
        }
        header('Location: ' . BASEURL . '/santri');
        exit;
    }

    private function uploadFoto() {
        $namaFile = $_FILES['foto']['name'];
        $ukuranFile = $_FILES['foto']['size'];
        $error = $_FILES['foto']['error'];
        $tmpName = $_FILES['foto']['tmp_name'];

        if ($error === 4) {
            Flasher::setFlash('Pilih foto terlebih dahulu!', 'Gagal', 'danger');
            return false;
        }

        $ekstensiValid = ['jpg', 'jpeg', 'png'];
        $ekstensiGambar = explode('.', $namaFile);
        $ekstensiGambar = strtolower(end($ekstensiGambar));

        if (!in_array($ekstensiGambar, $ekstensiValid)) {
            Flasher::setFlash('Ekstensi file tidak valid!', 'Gagal', 'danger');
            return false;
        }

        // Validasi MIME Type sesungguhnya
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmpName);
        finfo_close($finfo);

        $validMimeTypes = ['image/jpeg', 'image/png'];
        if (!in_array($mime, $validMimeTypes)) {
            Flasher::setFlash('File yang diunggah berisi konten berbahaya!', 'Gagal', 'danger');
            return false;
        }

        if ($ukuranFile > 2000000) { // 2MB
            Flasher::setFlash('Ukuran foto terlalu besar (Maks 2MB)!', 'Gagal', 'danger');
            return false;
        }

        $namaFileBaru = uniqid();
        $namaFileBaru .= '.';
        $namaFileBaru .= $ekstensiGambar;

        move_uploaded_file($tmpName, '../public/img/santri/' . $namaFileBaru);

        return $namaFileBaru;
    }
}
