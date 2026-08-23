<?php
// /modules/transport/delete.php
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
            $stmt = $db->prepare("DELETE FROM transport_routes WHERE id = ?");
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            // Foreign keys cascade delete in schema
        }
    }
}

header("Location: /modules/transport/index.php");
exit;
