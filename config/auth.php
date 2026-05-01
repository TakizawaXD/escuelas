<?php
// /config/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';

class Auth {
    public static function check() {
        return isset($_SESSION['user_id']);
    }

    public static function user() {
        if (!self::check()) return null;
        if (!isset($_SESSION['user'])) {
            // Fetch user info from database
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT u.*, r.name as role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $_SESSION['user'] = $stmt->fetch();
        }
        return $_SESSION['user'];
    }

    public static function hasRole($role) {
        $u = self::user();
        if (!$u) return false;
        
        // Single role or array of roles
        if (is_array($role)) {
            return in_array($u['role_name'], $role);
        }
        return $u['role_name'] === $role;
    }

    public static function hasPermission($module) {
        // Module permissions by role
        $u = self::user();
        if (!$u) return false;
        
        $role = $u['role_name'];
        if ($role === 'ADMIN') return true; // Admin has access to everything
        
        $permissions = [
            'DIRECTOR' => ['users', 'students', 'teachers', 'subjects', 'grades', 'attendance', 'payments', 'notifications'],
            'COORDINADOR' => ['students', 'teachers', 'subjects', 'grades', 'attendance', 'notifications'],
            'DOCENTE' => ['subjects', 'grades', 'attendance', 'notifications'],
            'ESTUDIANTE' => ['grades', 'attendance', 'payments', 'notifications'],
            'PADRE' => ['grades', 'attendance', 'payments', 'notifications']
        ];

        return isset($permissions[$role]) && in_array($module, $permissions[$role]);
    }

    public static function redirectIfNotAuth() {
        if (!self::check()) {
            header("Location: /auth/login.php");
            exit;
        }
    }

    public static function sanitize($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}
