<?php
/**
 * Controller Profile SantriFit (STEP 4)
 * Menangani tampilan profil serta manajemen koneksi akun Telegram pengguna.
 */
class Profile extends Controller {
    public function index() {
        AuthMiddleware::check();
        $userId = $_SESSION['user_id'];

        $data['judul'] = 'Profil Pengguna';
        $data['user'] = [
            'id' => $userId,
            'username' => $_SESSION['username'],
            'roles' => $_SESSION['user_roles']
        ];
        
        if (in_array('Santri', $_SESSION['user_roles'])) {
            $data['detail_santri'] = $this->model('Santri_model')->getSantriByUserId($userId);
        }

        // Status koneksi Telegram untuk ringkasan profil
        $telegramModel = $this->model('Telegram_model');
        $data['telegram_account'] = $telegramModel->getAccountByUserId($userId);
        $data['bot_username'] = TelegramConfig::getBotUsername();
        
        $this->view('templates/header', $data);
        $this->view('profile/index', $data);
        $this->view('templates/footer');
    }

    /**
     * Halaman manajemen koneksi akun Telegram
     */
    public function telegram() {
        AuthMiddleware::check();
        $userId = $_SESSION['user_id'];

        $data['judul'] = 'Hubungkan Telegram';
        $data['user'] = [
            'id' => $userId,
            'username' => $_SESSION['username']
        ];

        $telegramModel = $this->model('Telegram_model');
        $data['telegram_account'] = $telegramModel->getAccountByUserId($userId);
        $data['active_token'] = $telegramModel->getActiveTokenByUserId($userId);
        $data['bot_username'] = TelegramConfig::getBotUsername();

        $this->view('templates/header', $data);
        $this->view('profile/telegram', $data);
        $this->view('templates/footer');
    }

    /**
     * Membuat token linking sementara baru untuk user
     */
    public function generate_token() {
        AuthMiddleware::check();
        $userId = $_SESSION['user_id'];

        $telegramModel = $this->model('Telegram_model');
        $tokenData = $telegramModel->createLinkToken($userId, 15);

        Flasher::setFlash('Kode linking baru berhasil dibuat.', 'Gunakan kode sebelum kedaluwarsa.', 'success');
        header('Location: ' . BASEURL . '/profile/telegram');
        exit;
    }

    /**
     * Memutuskan hubungan (Unlink) akun Telegram
     */
    public function unlink() {
        AuthMiddleware::check();
        $userId = $_SESSION['user_id'];

        $telegramModel = $this->model('Telegram_model');
        $telegramModel->unlinkAccount($userId);

        Flasher::setFlash('Hubungan akun Telegram berhasil diputuskan.', '', 'info');
        header('Location: ' . BASEURL . '/profile/telegram');
        exit;
    }

    /**
     * Mengirim notifikasi uji coba ke Telegram pengguna (STEP 7)
     */
    public function test_notification() {
        AuthMiddleware::check();
        $userId = $_SESSION['user_id'];

        require_once dirname(__DIR__) . '/services/TelegramNotificationService.php';
        $notifService = new TelegramNotificationService();
        $result = $notifService->sendActivity(
            $userId, 
            "Ini adalah notifikasi uji coba dari sistem SantriFit. Integrasi notifikasi Telegram Anda berfungsi dengan sempurna!", 
            "Uji Coba Notifikasi SantriFit"
        );

        if ($result['success']) {
            Flasher::setFlash('Notifikasi uji coba berhasil dikirim ke akun Telegram Anda.', '', 'success');
        } else {
            Flasher::setFlash('Gagal mengirim notifikasi uji coba: ' . $result['message'], '', 'danger');
        }

        header('Location: ' . BASEURL . '/profile/telegram');
        exit;
    }

    /**
     * Endpoint API status koneksi Telegram (untuk polling real-time saat menunggu verifikasi Telegram)
     */
    public function telegram_status() {
        header('Content-Type: application/json; charset=utf-8');
        AuthMiddleware::check();
        $userId = $_SESSION['user_id'];

        $telegramModel = $this->model('Telegram_model');
        $account = $telegramModel->getAccountByUserId($userId);

        if ($account && (int)$account['is_verified'] === 1) {
            echo json_encode([
                'is_linked' => true,
                'username' => $account['telegram_username'] ? '@' . $account['telegram_username'] : 'Terhubung',
                'linked_at' => $account['created_at']
            ]);
        } else {
            echo json_encode([
                'is_linked' => false
            ]);
        }
        exit;
    }
}
