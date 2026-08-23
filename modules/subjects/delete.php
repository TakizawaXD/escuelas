<?php
// /modules/subjects/delete.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    header("Location: /index.php");
    exit;
}

$id = $_GET['id'] ?? null;
if ($id) {
    $db = Database::getInstance()->getConnection();
    
    // Eliminar la asignatura
    // (Por cascada, si hay dependencias como asistencia, podrían eliminarse o requerir soft-delete según diseño)
    $stmt = $db->prepare("DELETE FROM subjects WHERE id = ?");
    if ($stmt->execute([$id])) {
        // Redirigir con parámetro de éxito (opcional, en una implementación real usaríamos variables de sesión)
        header("Location: /modules/subjects/index.php?msg=deleted");
        exit;
    }
}

// Si hay error o no hay ID
header("Location: /modules/subjects/index.php?error=nodelete");
exit;
