<?php

namespace App\Models;

use Database;

abstract class BaseModel
{
    protected \PDO $db;
    protected string $table;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todos los registros de la tabla.
     */
    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM `{$this->table}`");
        return $stmt->fetchAll();
    }

    /**
     * Busca un registro por ID.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Elimina un registro por ID.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
