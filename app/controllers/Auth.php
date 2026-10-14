<?php
class Auth extends Controller {
    public function index() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL);
            exit;
        }
        $data['judul'] = 'Login';
        $this->view('templates/header', $data);
        $this->view('auth/login');
        $this->view('templates/footer');
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            header('Location: ' . BASEURL . '/auth/index');
            exit;
        }
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $userModel = $this->model('User_model');
        $user = $userModel->getUserByUsername($username);
        
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true); // Prevent Session Fixation
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_roles'] = $userModel->getUserRoles($user['id']);
            
            header('Location: ' . BASEURL);
            exit;
        } else {
            Flasher::setFlash('Username atau password salah', '!', 'danger');
            header('Location: ' . BASEURL . '/auth/index');
            exit;
        }
    }
    
    public function logout() {
        session_unset();
        session_destroy();
        header('Location: ' . BASEURL . '/auth/index');
        exit;
    }
    
    public function unauthorized() {
        $data['judul'] = 'Akses Ditolak';
        $this->view('templates/header', $data);
        $this->view('auth/unauthorized');
        $this->view('templates/footer');
    }
}
