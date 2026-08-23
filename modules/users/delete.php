<?php
// /modules/users/delete.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN'])) {
    header("Location: /index.php");
    exit;
}

$id = $_GET['id'] ?? null;
$token = $_GET['csrf_token'] ?? '';
Auth::verifyCsrf($token); // Validar antes de borrar

if ($id && $id != $_SESSION['user_id']) { // Prevenir auto-eliminación
    $db = Database::getInstance()->getConnection();
    
    // Implementar Soft Delete (inactivar) por seguridad
    $stmt = $db->prepare("UPDATE users SET status = 0 WHERE id = ?");
    if ($stmt->execute([$id])) {
        header("Location: /modules/users/index.php?msg=deleted");
        exit;
    }
}

header("Location: /modules/users/index.php?error=nodelete");
exit;
