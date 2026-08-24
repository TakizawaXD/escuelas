<?php
// /config/app.php

// Inicia la carga de variables de entorno si existe Composer
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->safeLoad();
    }
}

return [
    'name' => $_ENV['APP_NAME'] ?? $_SERVER['APP_NAME'] ?? getenv('APP_NAME') ?: 'Portal Escolar',
    'env' => $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? getenv('APP_ENV') ?: 'development',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: true, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? $_SERVER['APP_URL'] ?? getenv('APP_URL') ?: 'http://localhost:8080',
    
    'db' => [
        'path' => $_ENV['DB_PATH'] ?? $_SERVER['DB_PATH'] ?? getenv('DB_PATH') ?: 'database/escuela_erp.sqlite',
    ],
    
    'security' => [
        'key' => $_ENV['SECRET_KEY'] ?? $_SERVER['SECRET_KEY'] ?? getenv('SECRET_KEY') ?: 'default-secret-key-32-chars-long-please-change-it',
    ],
    
    'mail' => [
        'host' => $_ENV['SMTP_HOST'] ?? $_SERVER['SMTP_HOST'] ?? getenv('SMTP_HOST') ?: 'smtp.gmail.com',
        'port' => intval($_ENV['SMTP_PORT'] ?? $_SERVER['SMTP_PORT'] ?? getenv('SMTP_PORT') ?: 587),
        'user' => $_ENV['SMTP_USER'] ?? $_SERVER['SMTP_USER'] ?? getenv('SMTP_USER') ?: 'escuela@ejemplo.com',
        'pass' => $_ENV['SMTP_PASS'] ?? $_SERVER['SMTP_PASS'] ?? getenv('SMTP_PASS') ?: '',
        'secure' => $_ENV['SMTP_SECURE'] ?? $_SERVER['SMTP_SECURE'] ?? getenv('SMTP_SECURE') ?: 'tls',
    ]
];
