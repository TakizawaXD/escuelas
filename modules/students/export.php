<?php
// /modules/students/export.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$search = Auth::sanitize($_GET['search'] ?? '');
$course_id = isset($_GET['course_id']) && $_GET['course_id'] !== '' ? (int)$_GET['course_id'] : null;

$query = "
    SELECT u.document, u.first_name, u.last_name, u.email, c.name as course_name, s.grade, s.gpa, s.scalability
    FROM students s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN courses c ON s.course_id = c.id
    WHERE 1=1
";

$params = [];
if (!empty($search)) {
    $query .= " AND (u.document LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR c.name LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}

if ($course_id) {
    $query .= " AND s.course_id = ?";
    $params[] = $course_id;
}

$query .= " ORDER BY u.last_name ASC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=estudiantes_export_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');
// UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Column headers
fputcsv($output, ['Documento', 'Nombres', 'Apellidos', 'Correo', 'Curso', 'Grado', 'Promedio GPA', 'Escalabilidad']);

// Data rows
foreach ($students as $row) {
    fputcsv($output, $row);
}
fclose($output);
exit;
