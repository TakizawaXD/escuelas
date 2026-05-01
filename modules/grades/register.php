<?php
// /modules/grades/register.php
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
    $period = intval($_POST['period'] ?? 1);

    $exams = $_POST['exam_grade'] ?? [];
    $workshops = $_POST['workshop_grade'] ?? [];
    $projects = $_POST['project_grade'] ?? [];
    $comments = $_POST['comments'] ?? [];

    if ($subject_id > 0 && !empty($exams)) {
        try {
            foreach ($exams as $student_id => $exam_grade) {
                $student_id = intval($student_id);
                $exam = floatval($exam_grade);
                $workshop = floatval($workshops[$student_id] ?? 0.00);
                $project = floatval($projects[$student_id] ?? 0.00);
                $comment = Auth::sanitize($comments[$student_id] ?? '');

                // Scale check
                $exam = min(5.00, max(0.00, $exam));
                $workshop = min(5.00, max(0.00, $workshop));
                $project = min(5.00, max(0.00, $project));

                // 35% Examen, 35% Talleres, 30% Proyecto
                $final = ($exam * 0.35) + ($workshop * 0.35) + ($project * 0.30);

                // Insert or Update ON DUPLICATE KEY
                $stmt = $db->prepare("
                    INSERT INTO grades (student_id, subject_id, period, exam_grade, workshop_grade, project_grade, final_grade, comments)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        exam_grade = VALUES(exam_grade),
                        workshop_grade = VALUES(workshop_grade),
                        project_grade = VALUES(project_grade),
                        final_grade = VALUES(final_grade),
                        comments = VALUES(comments)
                ");
                $stmt->execute([$student_id, $subject_id, $period, $exam, $workshop, $project, $final, $comment]);
            }
            
            header("Location: /modules/grades/index.php?subject_id=$subject_id&period=$period");
            exit;
        } catch (PDOException $e) {
            die("Error al guardar calificaciones: " . $e->getMessage());
        }
    }
}

header("Location: /modules/grades/index.php");
exit;
