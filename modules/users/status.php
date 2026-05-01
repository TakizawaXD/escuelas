<?php
// /modules/users/status.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    header("Location: /index.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("SELECT status FROM users WHERE id = ?");
$stmt->execute([$id]);
$current = $stmt->fetch();

if ($current) {
    $newStatus = $current['status'] == 1 ? 0 : 1;
    $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $id]);
}

header("Location: /modules/users/index.php");
exit;
