<?php

namespace App\Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Controllers\StudentController;
use Database;

class StudentEnrollmentTest extends TestCase
{
    protected \PDO $db;

    protected function setUp(): void
    {
        $this->db = Database::getInstance()->getConnection();
        $this->db->beginTransaction();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $this->db->rollBack();
    }

    public function testEnrollFormRendersForAdmin()
    {
        // 1. Insertar un admin y rol de prueba
        $this->db->exec("INSERT OR IGNORE INTO roles (id, name) VALUES (1, 'ADMIN')");
        $this->db->exec("
            INSERT OR IGNORE INTO users (id, role_id, document, first_name, last_name, email, password, status)
            VALUES (999, 1, 'ADMIN123', 'Admin', 'User', 'admin@test.com', 'pwd', 1)
        ");

        // Simular sesión iniciada
        $_SESSION['user_id'] = 999;
        $_SESSION['user'] = [
            'id' => 999,
            'role_id' => 1,
            'role_name' => 'ADMIN',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@test.com'
        ];

        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = new StudentController();

        ob_start();
        $controller->enroll();
        $html = ob_get_clean();

        $this->assertStringContainsString('Matricular Estudiante', $html);
        $this->assertStringContainsString('Paso 1: Cuenta y Datos del Estudiante', $html);
        $this->assertStringContainsString('Asignación Académica', $html);
    }
}
