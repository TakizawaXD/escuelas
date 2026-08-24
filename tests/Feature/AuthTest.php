<?php

namespace App\Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Controllers\AuthController;
use Database;

class AuthTest extends TestCase
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

    public function testShowLoginForm()
    {
        $controller = new AuthController();
        
        ob_start();
        $controller->showLoginForm();
        $html = ob_get_clean();

        $this->assertStringContainsString('Iniciar Sesión', $html);
        $this->assertStringContainsString('csrf_token', $html);
    }
}
