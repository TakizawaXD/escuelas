<?php
// /auth/login.php

require_once __DIR__ . '/../vendor/autoload.php';

// Carga de archivos globales heredados
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

use App\Controllers\AuthController;

$controller = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->login();
} else {
    $controller->showLoginForm();
}
?>
