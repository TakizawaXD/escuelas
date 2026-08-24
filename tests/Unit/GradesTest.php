<?php

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

class GradesTest extends TestCase
{
    /**
     * Calcula la nota final con ponderaciones típicas del sistema:
     * - Examen: 40%
     * - Taller: 30%
     * - Proyecto: 30%
     */
    private function calculateFinalGrade(float $exam, float $workshop, float $project): float
    {
        return round(($exam * 0.40) + ($workshop * 0.30) + ($project * 0.30), 2);
    }

    public function testFinalGradeCalculation()
    {
        // 4.0 * 0.4 + 4.5 * 0.3 + 5.0 * 0.3 = 1.6 + 1.35 + 1.5 = 4.45
        $final = $this->calculateFinalGrade(4.0, 4.5, 5.0);
        $this->assertEquals(4.45, $final);

        // 3.0 * 0.4 + 3.0 * 0.3 + 3.0 * 0.3 = 1.2 + 0.9 + 0.9 = 3.0
        $final = $this->calculateFinalGrade(3.0, 3.0, 3.0);
        $this->assertEquals(3.00, $final);
    }

    public function testPerformanceStatus()
    {
        $status = function(float $grade) {
            if ($grade >= 4.5) return 'Excelente';
            if ($grade >= 4.0) return 'Sobresaliente';
            if ($grade >= 3.0) return 'Aceptable';
            return 'Insuficiente';
        };

        $this->assertEquals('Excelente', $status(4.7));
        $this->assertEquals('Sobresaliente', $status(4.2));
        $this->assertEquals('Aceptable', $status(3.5));
        $this->assertEquals('Insuficiente', $status(2.8));
    }
}
