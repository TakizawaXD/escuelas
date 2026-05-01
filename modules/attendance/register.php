<?php
// /modules/attendance/register.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR', 'DOCENTE'])) {
    header("Location: /index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance()->getConnection();

    $subject_id = intval($_POST['subject_id'] ?? 0);
    $date = Auth::sanitize($_POST['date'] ?? date('Y-m-d'));

    $statuses = $_POST['status'] ?? [];
    $justifications = $_POST['justification'] ?? [];

    if ($subject_id > 0 && !empty($statuses)) {
        try {
            foreach ($statuses as $student_id => $status) {
                $student_id = intval($student_id);
                $status = Auth::sanitize($status);
                $justification = Auth::sanitize($justifications[$student_id] ?? '');

                if (!in_array($status, ['Presente', 'Ausente', 'Tardanza'])) {
                    $status = 'Presente';
                }

                $stmt = $db->prepare("
                    INSERT INTO attendance (student_id, subject_id, date, status, justification)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        status = VALUES(status),
                        justification = VALUES(justification)
                ");
                $stmt->execute([$student_id, $subject_id, $date, $status, $justification]);
            }

            header("Location: /modules/attendance/index.php?subject_id=$subject_id&date=$date");
            exit;
        } catch (PDOException $e) {
            die("Error al guardar asistencia: " . $e->getMessage());
        }
    }
}

header("Location: /modules/attendance/index.php");
exit;
