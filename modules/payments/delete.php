<?php
// /modules/payments/delete.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'FINANCIERO'])) {
    header("Location: /index.php");
    exit;
}

$id = $_GET['id'] ?? null;
$token = $_GET['csrf_token'] ?? '';
Auth::verifyCsrf($token); // Validar antes de borrar

if ($id) {
    $db = Database::getInstance()->getConnection();
    
    // Verificamos si existe y si está pendiente (por seguridad contable no se suelen borrar pagos completados directamente)
    $stmt = $db->prepare("SELECT status FROM payments WHERE id = ?");
    $stmt->execute([$id]);
    $payment = $stmt->fetch();
    
    if ($payment) {
        if ($payment['status'] !== 'Pagado') {
            $stmt = $db->prepare("DELETE FROM payments WHERE id = ?");
            if ($stmt->execute([$id])) {
                header("Location: /modules/payments/index.php?msg=deleted");
                exit;
            }
        } else {
            // Si está pagado, no dejar borrar sin permisos especiales (simulado con error)
            header("Location: /modules/payments/index.php?error=paid");
            exit;
        }
    }
}

header("Location: /modules/payments/index.php?error=nodelete");
exit;
