<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Auth;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $_SESSION = [];
        }
    }

    public function testSanitizeInput()
    {
        $dirty = " <script>alert('xss');</script> ";
        $clean = Auth::sanitize($dirty);
        
        $this->assertEquals("&lt;script&gt;alert(&#039;xss&#039;);&lt;/script&gt;", $clean);
    }

    public function testCsrfTokenGeneration()
    {
        $token = Auth::csrfToken();
        $this->assertNotEmpty($token);
        $this->assertEquals(64, strlen($token)); // 32 bytes in hex is 64 chars
    }

    public function testVerifyCsrfSuccess()
    {
        $token = Auth::csrfToken();
        $this->assertTrue(Auth::verifyCsrf($token));
    }
}
