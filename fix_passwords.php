<?php
// /fix_passwords.php
require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Hash for 'admin'
    $hashedPassword = password_hash('admin', PASSWORD_BCRYPT);
    
    // Update admin user password
    $db->exec("UPDATE users SET password = '{$hashedPassword}' WHERE id = 1");

    // Let's also update the schema script to have the correct hash
    $schemaFile = __DIR__ . '/database/schema_sqlite.sql';
    if (file_exists($schemaFile)) {
        $sql = file_get_contents($schemaFile);
        $oldHash = '$2y$10$vW90qIu7.8s268R790qGZem8X3R96S9V8Kj3I37V6A3h3S8Kj3I37';
        $newSql = str_replace($oldHash, $hashedPassword, $sql);
        file_put_contents($schemaFile, $newSql);
    }

    echo "Passwords updated successfully in both the SQLite database and the schema file!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
