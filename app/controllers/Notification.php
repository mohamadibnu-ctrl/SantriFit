<?php
class Notification extends Controller {
    private $notifModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Sesi telah berakhir. Silakan login kembali.']);
            exit;
        }
        $this->notifModel = $this->model('Notification_model');
    }

    public function index() {
        $this->list();
    }

    public function list() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];
        $roles = $_SESSION['user_roles'] ?? [];
        $filter = $_GET['filter'] ?? 'all';

        $notifications = $this->notifModel->getNotifications($userId, $roles, $filter, 40);
        $unreadCount = $this->notifModel->getUnreadCount($userId, $roles);

        echo json_encode([
            'success' => true,
            'unread_count' => $unreadCount,
            'data' => $notifications
        ]);
        exit;
    }

    public function unread_count() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];
        $roles = $_SESSION['user_roles'] ?? [];

        $unreadCount = $this->notifModel->getUnreadCount($userId, $roles);

        echo json_encode([
            'success' => true,
            'unread_count' => $unreadCount
        ]);
        exit;
    }

    public function mark_read($id = null) {
        header('Content-Type: application/json');
        $id = $id ?? ($_POST['id'] ?? null);
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID notifikasi diperlukan.']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $this->notifModel->markAsRead($id, $userId);

        $roles = $_SESSION['user_roles'] ?? [];
        $unreadCount = $this->notifModel->getUnreadCount($userId, $roles);

        echo json_encode([
            'success' => true,
            'message' => 'Notifikasi ditandai telah dibaca.',
            'unread_count' => $unreadCount
        ]);
        exit;
    }

    public function mark_all_read() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];
        $roles = $_SESSION['user_roles'] ?? [];

        $this->notifModel->markAllAsRead($userId, $roles);

        echo json_encode([
            'success' => true,
            'message' => 'Semua notifikasi ditandai telah dibaca.',
            'unread_count' => 0
        ]);
        exit;
    }
}