<?php
// /modules/payments/pay.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    header("Location: /index.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("UPDATE payments SET status = 'Pagado', payment_date = NOW() WHERE id = ?");
$stmt->execute([$id]);

header("Location: /modules/payments/index.php");
exit;
