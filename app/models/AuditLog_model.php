<?php
class AuditLog_model {
    private $table = 'audit_logs';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function logAction($user_id, $aksi, $tabel_terdampak, $record_id, $detail_perubahan = null) {
        $this->db->query("INSERT INTO {$this->table} (user_id, aksi, tabel_terdampak, record_id, detail_perubahan) 
                          VALUES (:user_id, :aksi, :tabel, :record_id, :detail)");
        $this->db->bind('user_id', $user_id);
        $this->db->bind('aksi', $aksi);
        $this->db->bind('tabel', $tabel_terdampak);
        $this->db->bind('record_id', $record_id);
        $this->db->bind('detail', $detail_perubahan);
        return $this->db->execute();
    }
    
    public function getRecentLogs($limit = 50) {
        $this->db->query('SELECT a.*, u.username FROM ' . $this->table . ' a
                          LEFT JOIN users u ON a.user_id = u.id
                          ORDER BY a.created_at DESC LIMIT :limit');
        $this->db->bind('limit', $limit);
        return $this->db->resultSet();
    }
}
