<?php

namespace App\Middleware;

class CorsMiddleware
{
    /**
     * Aplica cabeceras CORS para peticiones entre dominios.
     */
    public static function handle(array $allowedOrigins = ['*']): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: " . ($origin ?: '*'));
        }

        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token");
        header("Access-Control-Allow-Credentials: true");

        // Responder directamente a preflights OPTIONS
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}
