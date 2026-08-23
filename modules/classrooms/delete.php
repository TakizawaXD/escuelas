<?php
// /modules/classrooms/delete.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    header("Location: /index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    if ($id) {
        $db = Database::getInstance()->getConnection();
        
        try {
            $stmt = $db->prepare("DELETE FROM classrooms WHERE id = ?");
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            // Could fail due to foreign key constraints if there are schedules tied to it.
        }
    }
}

header("Location: /modules/classrooms/index.php");
exit;
