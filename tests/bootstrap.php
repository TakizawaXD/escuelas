<?php
// tests/bootstrap.php

require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno específicas para pruebas (.env.testing)
if (file_exists(__DIR__ . '/../.env.testing')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..', '.env.testing');
    $dotenv->load();
} else {
    $_ENV['APP_ENV'] = 'testing';
    $_ENV['DB_PATH'] = 'database/escuela_erp_testing.sqlite';
    $_ENV['SECRET_KEY'] = 'test-environment-secret-key-32-chars-long';
}

// Requerir archivos de configuración globales heredados
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Asegurar que la base de datos de pruebas esté inicializada y vacía o con el esquema base
$db = Database::getInstance()->getConnection();
