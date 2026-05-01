<?php
require_once __DIR__ . '/config/database.php';
try {
    $db = Database::getInstance()->getConnection();
    echo "Connection successful!\n";
    $stmt = $db->query("SELECT COUNT(*) as count FROM roles");
    $row = $stmt->fetch();
    echo "Total roles: " . $row['count'] . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
