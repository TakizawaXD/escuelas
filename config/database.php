<?php
// /config/database.php

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $config = require __DIR__ . '/app.php';
        $dbPath = $config['db']['path'];

        if (substr($dbPath, 0, 1) !== '/' && !preg_match('/^[a-zA-Z]:\\\\/', $dbPath)) {
            $sqliteFile = __DIR__ . '/../' . $dbPath;
        } else {
            $sqliteFile = $dbPath;
        }
        $schemaFile = __DIR__ . '/../database/schema_sqlite.sql';

        try {
            $this->pdo = new PDO("sqlite:" . $sqliteFile, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            // Turn on foreign key constraints for SQLite
            $this->pdo->exec("PRAGMA foreign_keys = ON;");

            // Verify if tables exist, if not, import schema automatically
            $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='roles'");
            if (!$stmt->fetch()) {
                if (file_exists($schemaFile)) {
                    $sql = file_get_contents($schemaFile);
                    $this->pdo->exec($sql);
                }
            }
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos SQLite: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}
?>
