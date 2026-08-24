<?php
// /modules/students/create.php

require_once __DIR__ . '/../../vendor/autoload.php';

// Cargar configuraciones globales heredadas
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

use App\Controllers\StudentController;

$controller = new StudentController();
$controller->enroll();
?>
