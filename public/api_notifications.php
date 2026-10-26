<?php
// Direct REST API for Chatbot Notifications with Session Auth & RBAC
if (!session_id()) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/Notification_model.php';

// Cek autentikasi sesi pengguna
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Sesi tidak ditemukan atau kedaluwarsa. Silakan login kembali.']);
    exit;
}

$userId = $_SESSION['user_id'];
$roles = $_SESSION['user_roles'] ?? ['Pengguna'];
$notifModel = new Notification_model();

$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

switch ($action) {
    case 'unread_count':
        $count = $notifModel->getUnreadCount($userId, $roles);
        echo json_encode(['success' => true, 'unread_count' => $count]);
        break;

    case 'mark_read':
        $id = $_POST['id'] ?? ($_GET['id'] ?? null);
        if ($id) {
            $notifModel->markAsRead($id, $userId);
        }
        $count = $notifModel->getUnreadCount($userId, $roles);
        echo json_encode(['success' => true, 'unread_count' => $count]);
        break;

    case 'mark_all_read':
        $notifModel->markAllAsRead($userId, $roles);
        echo json_encode(['success' => true, 'unread_count' => 0]);
        break;

    case 'list':
    default:
        $filter = $_GET['filter'] ?? 'all';
        $list = $notifModel->getNotifications($userId, $roles, $filter, 40);
        $count = $notifModel->getUnreadCount($userId, $roles);
        echo json_encode([
            'success' => true,
            'unread_count' => $count,
            'data' => $list
        ]);
        break;
}