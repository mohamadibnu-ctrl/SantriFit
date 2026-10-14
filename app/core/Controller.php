<?php
class Controller {
    public function view($view, $data = []) {
        require_once '../app/views/' . $view . '.php';
    }

    public function model($model) {
        require_once '../app/models/' . $model . '.php';
        return new $model;
    }

    public function validate($data, $rules) {
        $errors = [];
        foreach ($rules as $field => $rule) {
            if ($rule === 'required' && empty($data[$field])) {
                $errors[$field] = ucfirst($field) . ' tidak boleh kosong.';
            }
        }
        return $errors;
    }
}
