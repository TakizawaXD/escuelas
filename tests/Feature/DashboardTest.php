<?php

namespace App\Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Controllers\DashboardController;
use Database;
use Auth;

class DashboardTest extends TestCase
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

    public function testGuestDashboardRenders()
    {
        $controller = new DashboardController();

        ob_start();
        $controller->index();
        $html = ob_get_clean();

        $this->assertStringContainsString('enfant', $html);
        $this->assertStringContainsString('Nuestros Servicios', $html);
    }
}
