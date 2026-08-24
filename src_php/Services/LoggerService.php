<?php

namespace App\Services;

use Database;

class LoggerService
{
    private static function logToFile(string $file, string $message, string $level = 'INFO'): void
    {
        $dir = __DIR__ . '/../../logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $logFile = "$dir/$file";
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $formattedMessage = "[$timestamp] [$level] [$ip] $message" . PHP_EOL;
        
        file_put_contents($logFile, $formattedMessage, FILE_APPEND);
    }

    public static function error(string $message): void
    {
        self::logToFile('error.log', $message, 'ERROR');
    }

    public static function audit(string $message): void
    {
        self::logToFile('audit.log', $message, 'AUDIT');
    }

    public static function security(string $message): void
    {
        self::logToFile('security.log', $message, 'SECURITY');
    }

    /**
     * Guarda una acción en la tabla `activity_logs` en base de datos.
     */
    public static function logActivity(?int $userId, string $action, string $details = ''): bool
    {
        // También registramos en el archivo de auditoría local
        self::audit("User ID: " . ($userId ?? 'GUEST') . " | Action: $action | Details: $details");

        try {
            $db = Database::getInstance()->getConnection();
            // Ejecutamos una inserción segura en caso de que la tabla ya exista
            $stmt = $db->prepare("
                INSERT INTO activity_logs (user_id, action, ip_address, details)
                VALUES (?, ?, ?, ?)
            ");
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            return $stmt->execute([$userId, $action, $ip, $details]);
        } catch (\Throwable $e) {
            // Si la tabla no existe o falla por otra razón, capturamos silenciosamente para no detener la ejecución
            self::error("Fallo al guardar log de actividad en BD: " . $e->getMessage());
            return false;
        }
    }
}
