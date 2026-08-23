<?php
// /modules/exams/results.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR', 'DOCENTE'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$exam_id = $_GET['id'] ?? null;

if (!$exam_id) {
    header("Location: /modules/exams/index.php");
    exit;
}

// Fetch exam details
$stmt = $db->prepare("
    SELECT e.*, sub.name as subject_name, sub.course_id, c.name as course_name 
    FROM exams e 
    JOIN subjects sub ON e.subject_id = sub.id
    JOIN courses c ON sub.course_id = c.id
    WHERE e.id = ?
");
$stmt->execute([$exam_id]);
$exam = $stmt->fetch();

if (!$exam) {
    header("Location: /modules/exams/index.php");
    exit;
}

// Handle Form Submission
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scores = $_POST['score'] ?? [];
    $remarks = $_POST['remarks'] ?? [];
    
    $db->beginTransaction();
    try {
        $insertStmt = $db->prepare("INSERT INTO exam_results (exam_id, student_id, score, remarks) VALUES (?, ?, ?, ?)");
        $updateStmt = $db->prepare("UPDATE exam_results SET score = ?, remarks = ? WHERE id = ?");
        
        foreach ($scores as $student_id => $score) {
            $student_id = (int)$student_id;
            $score = (float)$score;
            $remark = Auth::sanitize($remarks[$student_id] ?? '');
            
            // check if exists
            $checkStmt = $db->prepare("SELECT id FROM exam_results WHERE exam_id = ? AND student_id = ?");
            $checkStmt->execute([$exam_id, $student_id]);
            $existing = $checkStmt->fetch();
            
            if ($existing) {
                $updateStmt->execute([$score, $remark, $existing['id']]);
            } else {
                $insertStmt->execute([$exam_id, $student_id, $score, $remark]);
            }
        }
        $db->commit();
        $success = 'Calificaciones guardadas exitosamente.';
    } catch (Exception $e) {
        $db->rollBack();
        $error = 'Error al guardar calificaciones.';
    }
}

// Fetch students for this course and their existing grades for this exam
$studentsStmt = $db->prepare("
    SELECT s.id, u.first_name, u.last_name, s.document, er.score, er.remarks
    FROM students s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN exam_results er ON er.student_id = s.id AND er.exam_id = ?
    WHERE s.course_id = ?
    ORDER BY u.last_name ASC
");
$studentsStmt->execute([$exam_id, $exam['course_id']]);
$students = $studentsStmt->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Calificar Examen</h2>
        <p class="text-slate-500 font-medium text-sm mt-1"><?= htmlspecialchars($exam['title']) ?> - <?= htmlspecialchars($exam['subject_name']) ?> (<?= htmlspecialchars($exam['course_name']) ?>)</p>
    </div>
    <a href="/modules/exams/index.php" class="inline-flex items-center space-x-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-3 rounded-xl font-bold transition active:scale-[0.98]">
        <i class="fa-solid fa-arrow-left text-sm"></i>
        <span>Volver</span>
    </a>
</div>

<?php if ($success): ?>
    <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 font-medium text-sm border border-emerald-100 flex items-center space-x-2">
        <i class="fa-solid fa-circle-check"></i>
        <span><?= htmlspecialchars($success) ?></span>
    </div>
<?php endif; ?>

<div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-6">
    <div class="bg-indigo-50 p-6 flex flex-col sm:flex-row justify-between items-center border-b border-slate-100">
        <div>
            <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider block mb-1">Información de la Prueba</span>
            <div class="font-bold text-indigo-900 text-lg">Puntaje Máximo: <?= $exam['max_score'] ?></div>
        </div>
        <div class="mt-2 sm:mt-0 text-sm font-bold text-indigo-600 bg-white px-4 py-2 rounded-xl shadow-sm">
            <i class="fa-regular fa-calendar mr-1"></i> <?= date('d M Y', strtotime($exam['exam_date'])) ?>
        </div>
    </div>

    <form method="POST" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
            <thead class="bg-slate-50/60 text-slate-400 uppercase tracking-wider text-xs font-bold">
                <tr>
                    <th scope="col" class="px-6 py-4">Estudiante</th>
                    <th scope="col" class="px-6 py-4 w-48">Puntaje</th>
                    <th scope="col" class="px-6 py-4">Observaciones / Feedback</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="3" class="px-6 py-10 text-center text-slate-400">
                            No hay estudiantes en este curso.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $std): ?>
                        <tr class="hover:bg-slate-50/40 transition">
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800">
                                <div>
                                    <p><?= htmlspecialchars($std['last_name'] . ' ' . $std['first_name']) ?></p>
                                    <span class="text-xs text-slate-400 font-mono">DNI: <?= htmlspecialchars($std['document']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" step="0.1" min="0" max="<?= $exam['max_score'] ?>" name="score[<?= $std['id'] ?>]" value="<?= $std['score'] ?>"
                                       class="block w-full px-3 py-2 bg-white border border-slate-200 text-slate-800 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 outline-none font-bold text-sm shadow-inner text-center">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="text" name="remarks[<?= $std['id'] ?>]" value="<?= htmlspecialchars($std['remarks'] ?? '') ?>" placeholder="Ej. Excelente desarrollo analítico"
                                       class="block w-full px-3 py-2 bg-slate-50 border border-slate-200 text-slate-800 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if (!empty($students)): ?>
        <div class="bg-slate-50/40 p-6 flex justify-end border-t border-slate-100">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 transition active:scale-[0.98]">
                <i class="fa-solid fa-floppy-disk mr-2"></i> Guardar Resultados
            </button>
        </div>
        <?php endif; ?>
    </form>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
