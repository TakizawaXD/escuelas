<?php

namespace App\Middleware;

use Auth;

class AuthMiddleware
{
    /**
     * Valida que el usuario esté autenticado y posea los roles adecuados.
     */
    public static function handle(array $allowedRoles = []): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!Auth::check()) {
            header("Location: /auth/login.php");
            exit;
        }

        if (!empty($allowedRoles)) {
            if (!Auth::hasRole($allowedRoles)) {
                http_response_code(403);
                // Si existe la vista personalizada de error 403, la renderizamos.
                $errorView = __DIR__ . '/../../views/errors/403.php';
                if (file_exists($errorView)) {
                    include $errorView;
                } else {
                    echo "403 Forbidden - No autorizado.";
                }
                exit;
            }
        }
    }
}
