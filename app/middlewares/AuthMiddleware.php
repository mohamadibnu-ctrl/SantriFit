<?php

class AuthMiddleware
{
    public static function check()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASEURL . '/auth/index');
            exit;
        }
    }

    public static function checkRole(array $allowed_roles)
    {
        self::check(); // Ensure user is logged in first

        $user_roles = $_SESSION['user_roles'] ?? [];
        
        $hasAccess = false;
        foreach ($user_roles as $role) {
            if (in_array($role, $allowed_roles)) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            header('Location: ' . BASEURL . '/auth/unauthorized');
            exit;
        }
    }
}