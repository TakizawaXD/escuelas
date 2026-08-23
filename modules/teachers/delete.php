<?php
// /modules/teachers/delete.php
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
    
    // Obtener el user_id para posiblemente inactivar al usuario (Soft Delete o Delete)
    $stmt = $db->prepare("SELECT user_id FROM teachers WHERE id = ?");
    $stmt->execute([$id]);
    $teacher = $stmt->fetch();
    
    if ($teacher) {
        // En este sistema, eliminar un docente también podría implicar inactivar su usuario
        // pero mantendremos solo la eliminación del registro de teacher por ahora.
        $stmt = $db->prepare("DELETE FROM teachers WHERE id = ?");
        if ($stmt->execute([$id])) {
            // Opcional: Inactivar usuario asociado
            $db->prepare("UPDATE users SET status = 0 WHERE id = ?")->execute([$teacher['user_id']]);
            
            header("Location: /modules/teachers/index.php?msg=deleted");
            exit;
        }
    }
}

header("Location: /modules/teachers/index.php?error=nodelete");
exit;
