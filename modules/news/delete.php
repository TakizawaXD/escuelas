<?php
// /modules/news/delete.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COMUNICACIONES'])) {
    header("Location: /index.php");
    exit;
}

$id = $_GET['id'] ?? null;
if ($id) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("DELETE FROM news WHERE id = ?");
    if ($stmt->execute([$id])) {
        header("Location: /modules/news/index.php?msg=deleted");
        exit;
    }
}

header("Location: /modules/news/index.php?error=nodelete");
exit;
