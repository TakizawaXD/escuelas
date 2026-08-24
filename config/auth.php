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

    // --- CIBERSEGURIDAD: PREVENCIÓN CSRF ---
    public static function csrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf($token) {
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            die('Error de validación CSRF. Petición rechazada por seguridad.');
        }
        return true;
    }

    // --- CIBERSEGURIDAD: PROTECCIÓN FUERZA BRUTA ---
    public static function getIpAddress() {
        return $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public static function checkBruteForce() {
        $ip = self::getIpAddress();
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT attempts, last_attempt FROM login_attempts WHERE ip_address = ?");
        $stmt->execute([$ip]);
        $record = $stmt->fetch();

        if ($record) {
            $attempts = $record['attempts'];
            $last_attempt = strtotime($record['last_attempt']);
            $time_passed = time() - $last_attempt;
            
            // 5 intentos max. Bloqueo de 15 minutos (900 segundos)
            if ($attempts >= 5 && $time_passed < 900) {
                $minutes_left = ceil((900 - $time_passed) / 60);
                return "Demasiados intentos fallidos. Tu IP ha sido bloqueada por seguridad. Intenta en {$minutes_left} minutos.";
            } elseif ($time_passed >= 900) {
                // Ya pasó el tiempo de castigo, limpiar
                self::clearLoginAttempts();
            }
        }
        return true;
    }

    public static function recordFailedLogin() {
        $ip = self::getIpAddress();
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT id, attempts FROM login_attempts WHERE ip_address = ?");
        $stmt->execute([$ip]);
        $record = $stmt->fetch();

        if ($record) {
            $stmt = $db->prepare("UPDATE login_attempts SET attempts = attempts + 1, last_attempt = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$record['id']]);
        } else {
            $stmt = $db->prepare("INSERT INTO login_attempts (ip_address, attempts) VALUES (?, 1)");
            $stmt->execute([$ip]);
        }
    }

    public static function clearLoginAttempts() {
        $ip = self::getIpAddress();
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
        $stmt->execute([$ip]);
    }
}
