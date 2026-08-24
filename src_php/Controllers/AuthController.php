<?php

namespace App\Controllers;

use App\Helpers\SecurityHelper;
use App\Services\LoggerService;
use App\Validators\Validator;
use Database;
use Auth;

class AuthController extends BaseController
{
    /**
     * Muestra la vista de formulario de login.
     */
    public function showLoginForm(?string $error = null): void
    {
        $bruteForceCheck = Auth::checkBruteForce();
        if ($bruteForceCheck !== true) {
            $error = $bruteForceCheck;
        }

        // Renderiza la vista de inicio de sesión
        include __DIR__ . '/../../views/auth/login_view.php';
    }

    /**
     * Procesa la solicitud de inicio de sesión.
     */
    public function login(): void
    {
        $bruteForceCheck = Auth::checkBruteForce();
        if ($bruteForceCheck !== true) {
            $this->showLoginForm($bruteForceCheck);
            return;
        }

        // Validar CSRF
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $this->showLoginForm('Token CSRF inválido.');
            return;
        }

        // Sanitizar y validar los inputs
        $data = $this->sanitizeInput($_POST);
        $document = $data['document'] ?? '';
        $password = $_POST['password'] ?? '';

        $validator = new Validator();
        if (!$validator->validate($data, [
            'document' => ['required'],
            'password' => ['required']
        ])) {
            $this->showLoginForm('Por favor complete todos los campos.');
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT u.*, r.name as role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.document = ? AND u.status = 1
            ");
            $stmt->execute([$document]);
            $user = $stmt->fetch();

            if ($user && SecurityHelper::verifyPassword($password, $user['password'])) {
                // Autenticación correcta, limpiar intentos de login fallidos
                Auth::clearLoginAttempts();
                
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user'] = $user;

                // Registrar auditoría
                LoggerService::logActivity($user['id'], 'LOGIN_SUCCESS', 'Inicio de sesión exitoso en el portal.');

                header("Location: /index.php");
                exit;
            } else {
                // Registrar intento fallido
                Auth::recordFailedLogin();
                LoggerService::logActivity(null, 'LOGIN_FAILED', "Documento ingresado: $document");
                $this->showLoginForm('Documento o contraseña incorrectos, o cuenta inactiva.');
            }
        } catch (\PDOException $e) {
            LoggerService::error("Excepción en login: " . $e->getMessage());
            $this->showLoginForm('Error del sistema: ' . $e->getMessage());
        }
    }
}
