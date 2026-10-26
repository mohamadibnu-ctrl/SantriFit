<?php
class Notification_model {
    private $table = 'notifications';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Hitung waktu lampau (time ago) dalam bahasa Indonesia
     */
    public static function timeAgo($datetime) {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'Baru saja';
        } elseif ($diff < 3600) {
            $mins = max(1, floor($diff / 60));
            return $mins . ' menit yang lalu';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' jam yang lalu';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' hari yang lalu';
        } else {
            return date('d M Y, H:i', $timestamp);
        }
    }

    /**
     * Dapatkan icon dan warna badge berdasarkan tipe notifikasi
     */
    public static function getTypeMetadata($type) {
        switch ($type) {
            case 'pemeriksaan':
                return [
                    'icon' => '🩺',
                    'bootstrap_icon' => 'bi-clipboard2-pulse',
                    'color' => '#0c8c8c',
                    'bg_class' => 'bg-info-subtle text-info-emphasis'
                ];
            case 'perhatian':
                return [
                    'icon' => '⚠️',
                    'bootstrap_icon' => 'bi-exclamation-triangle-fill',
                    'color' => '#f59e0b',
                    'bg_class' => 'bg-warning-subtle text-warning-emphasis'
                ];
            case 'rekomendasi':
                return [
                    'icon' => '💊',
                    'bootstrap_icon' => 'bi-prescription2',
                    'color' => '#8b5cf6',
                    'bg_class' => 'bg-purple-subtle text-purple'
                ];
            case 'obat':
                return [
                    'icon' => '💊',
                    'bootstrap_icon' => 'bi-capsule',
                    'color' => '#10b981',
                    'bg_class' => 'bg-success-subtle text-success'
                ];
            case 'ai':
                return [
                    'icon' => '🤖',
                    'bootstrap_icon' => 'bi-robot',
                    'color' => '#0284c7',
                    'bg_class' => 'bg-primary-subtle text-primary'
                ];
            case 'sistem':
            default:
                return [
                    'icon' => 'ℹ️',
                    'bootstrap_icon' => 'bi-info-circle-fill',
                    'color' => '#64748b',
                    'bg_class' => 'bg-secondary-subtle text-secondary'
                ];
        }
    }

    /**
     * Bangun klausul SQL filter berdasarkan peran pengguna (RBAC)
     */
    private function buildRoleClause($userId, $roles) {
        $roles = is_array($roles) ? $roles : (array)$roles;
        $isSuperUser = in_array('Super User', $roles);

        if ($isSuperUser) {
            // Super User dapat melihat notifikasi sistem, administrasi, dan broadcast
            return "(user_id = :user_id OR user_id IS NULL)";
        }

        // Bangun kondisi role-specific
        $roleClauses = ["target_role IS NULL"];
        foreach ($roles as $idx => $role) {
            $roleClauses[] = "target_role = :role_" . $idx;
        }

        $rolesSql = implode(" OR ", $roleClauses);
        return "(user_id = :user_id OR (user_id IS NULL AND ($rolesSql)))";
    }

    /**
     * Bind parameter peran pengguna ke query PDO
     */
    private function bindRoleParams($userId, $roles) {
        $roles = is_array($roles) ? $roles : (array)$roles;
        $this->db->bind('user_id', $userId);

        $isSuperUser = in_array('Super User', $roles);
        if (!$isSuperUser) {
            foreach ($roles as $idx => $role) {
                $this->db->bind(':role_' . $idx, $role);
            }
        }
    }

    /**
     * Ambil notifikasi sesuai hak akses role dan filter kategori
     */
    public function getNotifications($userId, $roles, $filter = 'all', $limit = 30) {
        $roleClause = $this->buildRoleClause($userId, $roles);
        $filterClause = "";

        if ($filter === 'unread') {
            $filterClause = " AND is_read = 0";
        } elseif (in_array($filter, ['pemeriksaan', 'perhatian', 'rekomendasi', 'obat', 'ai', 'sistem'])) {
            $filterClause = " AND type = :filter_type";
        }

        $sql = "SELECT * FROM {$this->table} 
                WHERE {$roleClause} {$filterClause} 
                ORDER BY is_read ASC, created_at DESC 
                LIMIT :limit";

        $this->db->query($sql);
        $this->bindRoleParams($userId, $roles);
        if ($filter !== 'all' && $filter !== 'unread' && !empty($filterClause)) {
            $this->db->bind(':filter_type', $filter);
        }
        $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);

        $rows = $this->db->resultSet();
        foreach ($rows as &$row) {
            $row['time_ago'] = self::timeAgo($row['created_at']);
            $meta = self::getTypeMetadata($row['type']);
            $row['icon'] = $meta['icon'];
            $row['bootstrap_icon'] = $meta['bootstrap_icon'];
            $row['color'] = $meta['color'];
            $row['bg_class'] = $meta['bg_class'];
        }

        return $rows;
    }

    /**
     * Hitung jumlah notifikasi yang belum dibaca (untuk badge lonceng 🔔)
     */
    public function getUnreadCount($userId, $roles) {
        $roleClause = $this->buildRoleClause($userId, $roles);
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE {$roleClause} AND is_read = 0";

        $this->db->query($sql);
        $this->bindRoleParams($userId, $roles);
        $res = $this->db->single();

        return (int)($res['total'] ?? 0);
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca
     */
    public function markAsRead($id, $userId = null) {
        $this->db->query("UPDATE {$this->table} SET is_read = 1 WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Tandai semua notifikasi pengguna sebagai sudah dibaca
     */
    public function markAllAsRead($userId, $roles) {
        $roleClause = $this->buildRoleClause($userId, $roles);
        $sql = "UPDATE {$this->table} SET is_read = 1 WHERE {$roleClause} AND is_read = 0";
        $this->db->query($sql);
        $this->bindRoleParams($userId, $roles);
        return $this->db->execute();
    }

    /**
     * Tambah notifikasi baru ke database
     */
    public function addNotification($data) {
        $sql = "INSERT INTO {$this->table} 
                (user_id, target_role, type, title, message, reference_type, reference_id, is_read, created_at) 
                VALUES 
                (:user_id, :target_role, :type, :title, :message, :reference_type, :reference_id, 0, NOW())";

        $this->db->query($sql);
        $this->db->bind(':user_id', $data['user_id'] ?? null);
        $this->db->bind(':target_role', $data['target_role'] ?? null);
        $this->db->bind(':type', $data['type'] ?? 'sistem');
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':message', $data['message']);
        $this->db->bind(':reference_type', $data['reference_type'] ?? null);
        $this->db->bind(':reference_id', $data['reference_id'] ?? null);

        return $this->db->execute();
    }
}