<?php

namespace App\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Database;

class StudentCrudTest extends TestCase
{
    protected \PDO $db;

    protected function setUp(): void
    {
        $this->db = Database::getInstance()->getConnection();
        $this->db->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->db->rollBack();
    }

    public function testCreateAndQueryStudent()
    {
        // 1. Insertar Curso de prueba si no existe
        $this->db->exec("INSERT OR IGNORE INTO courses (id, name, description) VALUES (999, 'Curso de Test', 'Curso para pruebas unitarias')");

        // 2. Insertar Usuario Estudiante (Rol 5)
        $document = 'TESTSTUDENT123';
        $email = 'teststudent@escuela.com';
        $pwdHash = password_hash('password123', PASSWORD_BCRYPT);
        
        $stmt = $this->db->prepare("
            INSERT INTO users (role_id, document, first_name, last_name, email, phone, password, status)
            VALUES (5, ?, 'Juan', 'Perez', ?, '123456', ?, 1)
        ");
        $stmt->execute([$document, $email, $pwdHash]);
        $userId = $this->db->lastInsertId();

        // 3. Crear registro de estudiante
        $stmt = $this->db->prepare("
            INSERT INTO students (user_id, course_id, birth_date, address, grade, gpa)
            VALUES (?, 999, '2010-05-15', 'Calle Falsa 123', 'A', 4.5)
        ");
        $stmt->execute([$userId]);
        $studentId = $this->db->lastInsertId();

        // Validar creación exitosa
        $this->assertNotEmpty($studentId);

        // 4. Consultar estudiante y verificar datos
        $stmt = $this->db->prepare("SELECT s.*, u.first_name, u.last_name FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?");
        $stmt->execute([$studentId]);
        $student = $stmt->fetch();

        $this->assertEquals('Juan', $student['first_name']);
        $this->assertEquals('Perez', $student['last_name']);
        $this->assertEquals('Calle Falsa 123', $student['address']);
        $this->assertEquals(4.5, (float)$student['gpa']);

        // 5. Actualizar GPA del estudiante
        $stmt = $this->db->prepare("UPDATE students SET gpa = 4.8 WHERE id = ?");
        $stmt->execute([$studentId]);

        // Verificar actualización
        $stmt = $this->db->prepare("SELECT gpa FROM students WHERE id = ?");
        $stmt->execute([$studentId]);
        $updatedGpa = $stmt->fetchColumn();
        $this->assertEquals(4.8, (float)$updatedGpa);
    }
}
