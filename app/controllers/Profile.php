<?php
class Profile extends Controller {
    public function index() {
        AuthMiddleware::check();
        $data['judul'] = 'Profil Pengguna';
        $data['user'] = [
            'username' => $_SESSION['username'],
            'roles' => $_SESSION['user_roles']
        ];
        
        if (in_array('Santri', $_SESSION['user_roles'])) {
            $data['detail_santri'] = $this->model('Santri_model')->getSantriByUserId($_SESSION['user_id']);
        }
        
        $this->view('templates/header', $data);
        $this->view('profile/index', $data);
        $this->view('templates/footer');
    }
}
