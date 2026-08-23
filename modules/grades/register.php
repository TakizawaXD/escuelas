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

                // SQLite-compatible: check if record exists, then update or insert
                $checkStmt = $db->prepare("SELECT id FROM grades WHERE student_id = ? AND subject_id = ? AND period = ?");
                $checkStmt->execute([$student_id, $subject_id, $period]);
                $existing = $checkStmt->fetchColumn();

                if ($existing) {
                    $stmt = $db->prepare("
                        UPDATE grades 
                        SET exam_grade = ?, workshop_grade = ?, project_grade = ?, final_grade = ?, comments = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$exam, $workshop, $project, $final, $comment, $existing]);
                } else {
                    $stmt = $db->prepare("
                        INSERT INTO grades (student_id, subject_id, period, exam_grade, workshop_grade, project_grade, final_grade, comments)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$student_id, $subject_id, $period, $exam, $workshop, $project, $final, $comment]);
                }
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
