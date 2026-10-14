<?php
class User_model {
    private $table = 'users';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllUsers() {
        $this->db->query('SELECT * FROM ' . $this->table);
        return $this->db->resultSet();
    }
    
    // Auth related query framework
    public function getUserByUsername($username) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE username = :username');
        $this->db->bind('username', $username);
        return $this->db->single();
    }
    
    public function getUserRoles($user_id) {
        $this->db->query('SELECT r.nama_role FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = :id');
        $this->db->bind('id', $user_id);
        $roles = $this->db->resultSet();
        return array_column($roles, 'nama_role');
    }
}
