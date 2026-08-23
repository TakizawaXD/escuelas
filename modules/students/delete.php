<?php
// /modules/students/delete.php
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
    
    // Obtener el user_id
    $stmt = $db->prepare("SELECT user_id FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch();
    
    if ($student) {
        $stmt = $db->prepare("DELETE FROM students WHERE id = ?");
        if ($stmt->execute([$id])) {
            // Opcional: Inactivar usuario asociado
            $db->prepare("UPDATE users SET status = 0 WHERE id = ?")->execute([$student['user_id']]);
            
            header("Location: /modules/students/index.php?msg=deleted");
            exit;
        }
    }
}

header("Location: /modules/students/index.php?error=nodelete");
exit;
