<?php

namespace App\Controllers;

use Database;
use Auth;

class DashboardController extends BaseController
{
    /**
     * Carga el portal de inicio o el panel de control correspondiente.
     */
    public function index(): void
    {
        $db = Database::getInstance()->getConnection();

        // 1. Si no está logueado, mostrar landing page institucional (Guest View)
        if (!Auth::check()) {
            try {
                $newsList = $db->query("SELECT * FROM news ORDER BY id DESC LIMIT 3")->fetchAll();
            } catch (\Exception $e) {
                $newsList = [];
            }
            include __DIR__ . '/../../views/dashboard/guest_view.php';
            return;
        }

        // 2. Si está logueado, preparar datos del Dashboard (Logged In View)
        $u = Auth::user();
        
        $totalStudents = 0;
        $totalTeachers = 0;
        $averageGrade = 0.0;
        $totalDebts = 0.0;
        $attendancePercentage = 0.0;
        $chartLabels = '[]';
        $chartData = '[]';

        if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
            try {
                $totalStudents = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
                $totalTeachers = $db->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
                
                $avg = $db->query("SELECT AVG(final_grade) FROM grades")->fetchColumn();
                $averageGrade = $avg ? round((float)$avg, 2) : 0.0;

                $debt = $db->query("SELECT SUM(amount) FROM payments WHERE status = 'Pendiente'")->fetchColumn();
                $totalDebts = $debt ? round((float)$debt, 2) : 0.0;

                $courseData = $db->query("
                    SELECT c.name, COUNT(s.id) as count 
                    FROM courses c 
                    LEFT JOIN students s ON c.id = s.course_id 
                    GROUP BY c.id
                ")->fetchAll();
                
                $chartLabels = json_encode(array_column($courseData, 'name'));
                $chartData = json_encode(array_column($courseData, 'count'));
            } catch (\Exception $e) {
                // Capturar errores silenciosamente para no romper la visualización
            }
        }

        // Cargar notificaciones
        try {
            $recentNotifications = $db->prepare("
                SELECT n.*, u.first_name, u.last_name, r.name as role_name
                FROM notifications n
                JOIN users u ON n.user_id = u.id
                LEFT JOIN roles r ON n.target_role_id = r.id
                ORDER BY n.id DESC LIMIT 4
            ");
            $recentNotifications->execute();
            $notifications = $recentNotifications->fetchAll();
        } catch (\Exception $e) {
            $notifications = [];
        }

        // Renderizar layout completo
        include __DIR__ . '/../../views/layout/header.php';
        include __DIR__ . '/../../views/layout/sidebar.php';
        include __DIR__ . '/../../views/dashboard/logged_in_view.php';
        include __DIR__ . '/../../views/layout/footer.php';
    }
}
