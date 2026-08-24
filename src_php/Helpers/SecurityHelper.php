<?php

namespace App\Helpers;

class SecurityHelper
{
    /**
     * Hashing passwords with BCRYPT.
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Verifica la contraseña contra su hash.
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Genera un token aleatorio seguro.
     */
    public static function generateRandomToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Encripta información sensible usando AES-256-CBC.
     */
    public static function encrypt(string $data, string $key): string
    {
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = random_bytes($ivLength);
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', substr(hash('sha256', $key), 0, 32), 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Desencripta información sensible encriptada previamente.
     */
    public static function decrypt(string $payload, string $key): ?string
    {
        try {
            $data = base64_decode($payload);
            $ivLength = openssl_cipher_iv_length('aes-256-cbc');
            $iv = substr($data, 0, $ivLength);
            $encrypted = substr($data, $ivLength);
            $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', substr(hash('sha256', $key), 0, 32), 0, $iv);
            return $decrypted !== false ? $decrypted : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
