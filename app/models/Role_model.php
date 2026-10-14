<?php
class Role_model {
    private $table = 'roles';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllRoles() {
        $this->db->query('SELECT * FROM ' . $this->table);
        return $this->db->resultSet();
    }
}
