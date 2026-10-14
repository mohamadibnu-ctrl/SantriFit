<?php
class Home extends Controller {
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth/index');
            exit;
        }

        $data['judul'] = 'Beranda SantriFit';
        $roles = $_SESSION['user_roles'] ?? [];
        $dashboardModel = $this->model('Dashboard_model');
        
        $data['stats'] = [];

        if (in_array('Super User', $roles)) {
            $data['stats']['super_user'] = $dashboardModel->getSuperUserStats();
        }
        if (in_array('Pengurus', $roles)) {
            $data['stats']['pengurus'] = $dashboardModel->getPengurusStats();
        }
        if (in_array('Perawat', $roles)) {
            $data['stats']['perawat'] = $dashboardModel->getPerawatStats();
        }
        if (in_array('Dokter', $roles)) {
            $data['stats']['dokter'] = $dashboardModel->getDokterStats();
        }
        if (in_array('Santri', $roles)) {
            $data['stats']['santri'] = $dashboardModel->getSantriStats($_SESSION['user_id']);
        }

        $this->view('templates/header', $data);
        $this->view('home/index', $data);
        $this->view('templates/footer');
    }
}
