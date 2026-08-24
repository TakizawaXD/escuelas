<?php

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

class PaymentsTest extends TestCase
{
    private function isPaymentOverdue(string $dueDate, string $status): bool
    {
        if ($status === 'Pagado') {
            return false;
        }
        $today = strtotime(date('Y-m-d'));
        $due = strtotime($dueDate);
        return $today > $due;
    }

    public function testPaymentIsNotOverdueIfPaid()
    {
        // Vencido en fecha pero pagado -> no debe estar vencido
        $this->assertFalse($this->isPaymentOverdue('2020-01-01', 'Pagado'));
    }

    public function testPaymentIsOverdueIfPendingAndPastDue()
    {
        // Pendiente y fecha pasada -> vencido
        $this->assertTrue($this->isPaymentOverdue('2020-01-01', 'Pendiente'));
    }

    public function testPaymentIsNotOverdueIfPendingAndFuture()
    {
        // Pendiente y fecha futura -> no vencido
        $futureDate = date('Y-m-d', strtotime('+10 days'));
        $this->assertFalse($this->isPaymentOverdue($futureDate, 'Pendiente'));
    }
}
