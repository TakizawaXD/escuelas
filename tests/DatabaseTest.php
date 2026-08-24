<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Database;

class DatabaseTest extends TestCase
{
    public function testDatabaseConnection()
    {
        $db = Database::getInstance()->getConnection();
        $this->assertInstanceOf(\PDO::class, $db);
    }

    public function testRolesTableHasRecords()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT COUNT(*) as count FROM roles");
        $row = $stmt->fetch();
        
        $this->assertGreaterThan(0, (int)$row['count']);
    }
}
