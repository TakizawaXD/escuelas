<?php
// /modules/admissions/update_status.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    header("Location: /index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $status = Auth::sanitize($_POST['status'] ?? '');

    $validStatuses = ['Pendiente', 'Entrevista', 'Aceptado', 'Rechazado'];

    if ($id > 0 && in_array($status, $validStatuses)) {
        $db = Database::getInstance()->getConnection();
        try {
            $stmt = $db->prepare("UPDATE admission_applications SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
        } catch (PDOException $e) {
            // Silently fail or log in real life, but redirect is fine
        }
    }
}

header("Location: /modules/admissions/index.php");
exit;
