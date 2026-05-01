<?php
// /modules/grades/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR', 'DOCENTE'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$u = Auth::user();

// Fetch subjects based on user role (If teacher, only show their subjects)
if (Auth::hasRole('DOCENTE')) {
    // Find teacher_id for current user
    $stmt = $db->prepare("SELECT id FROM teachers WHERE user_id = ?");
    $stmt->execute([$u['id']]);
    $t_id = $stmt->fetchColumn();

    $subjectsStmt = $db->prepare("
        SELECT s.*, c.name as course_name 
        FROM subjects s 
        JOIN courses c ON s.course_id = c.id 
        WHERE s.teacher_id = ?
    ");
    $subjectsStmt->execute([$t_id]);
} else {
    // Admin, Director, Coordinator see all
    $subjectsStmt = $db->query("
        SELECT s.*, c.name as course_name 
        FROM subjects s 
        JOIN courses c ON s.course_id = c.id
    ");
}

$subjects = $subjectsStmt->fetchAll();

$subject_id = intval($_GET['subject_id'] ?? 0);
$period = intval($_GET['period'] ?? 1);

// Match filter or pick first subject
if ($subject_id === 0 && !empty($subjects)) {
    $subject_id = $subjects[0]['id'];
}

$studentsGrades = [];
if ($subject_id > 0) {
    // Fetch students enrolled in the subject's course
    $stmt = $db->prepare("
        SELECT s.*, u.first_name, u.last_name, u.document 
        FROM students s
        JOIN users u ON s.user_id = u.id
        JOIN subjects sub ON s.course_id = sub.course_id
        WHERE sub.id = ?
    ");
    $stmt->execute([$subject_id]);
    $studentsList = $stmt->fetchAll();

    foreach ($studentsList as $st) {
        // Find existing grade record
        $gStmt = $db->prepare("
            SELECT * FROM grades 
            WHERE student_id = ? AND subject_id = ? AND period = ?
        ");
        $gStmt->execute([$st['id'], $subject_id, $period]);
        $gradeRec = $gStmt->fetch();

        $studentsGrades[] = [
            'id' => $st['id'],
            'name' => $st['first_name'] . ' ' . $st['last_name'],
            'document' => $st['document'],
            'exam_grade' => $gradeRec ? floatval($gradeRec['exam_grade']) : 0.00,
            'workshop_grade' => $gradeRec ? floatval($gradeRec['workshop_grade']) : 0.00,
            'project_grade' => $gradeRec ? floatval($gradeRec['project_grade']) : 0.00,
            'final_grade' => $gradeRec ? floatval($gradeRec['final_grade']) : 0.00,
            'comments' => $gradeRec ? $gradeRec['comments'] : ''
        ];
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<!-- Header Section -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Control de Notas</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Gestión académica y reporte de calificaciones.</p>
    </div>
</div>

<!-- Filters -->
<div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100/80 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <div>
            <label for="subject_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Materia / Curso</label>
            <select name="subject_id" id="subject_id" required
                    class="block w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                <?php if (empty($subjects)): ?>
                    <option value="">No hay materias creadas...</option>
                <?php else: ?>
                    <?php foreach ($subjects as $sub): ?>
                        <option value="<?= $sub['id'] ?>" <?= $sub['id'] == $subject_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sub['name'] . ' (' . $sub['course_name'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="period" class="block text-sm font-semibold text-slate-700 mb-1.5">Periodo Escolar</label>
            <select name="period" id="period" required
                    class="block w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                <?php for ($p = 1; $p <= 4; $p++): ?>
                    <option value="<?= $p ?>" <?= $p == $period ? 'selected' : '' ?>>Periodo <?= $p ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div>
            <button type="submit" class="w-full md:w-auto px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition text-sm shadow-md">
                Cargar Planilla
            </button>
        </div>
    </form>
</div>

<!-- Planilla Form List -->
<div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
    <form method="POST" action="/modules/grades/register.php" class="overflow-x-auto">
        <input type="hidden" name="subject_id" value="<?= $subject_id ?>">
        <input type="hidden" name="period" value="<?= $period ?>">
        
        <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
            <thead class="bg-slate-50/60 text-slate-400 uppercase tracking-wider text-xs font-bold">
                <tr>
                    <th scope="col" class="px-6 py-4">Estudiante</th>
                    <th scope="col" class="px-6 py-4">Examen (0–5)</th>
                    <th scope="col" class="px-6 py-4">Talleres (0–5)</th>
                    <th scope="col" class="px-6 py-4">Proyectos (0–5)</th>
                    <th scope="col" class="px-6 py-4">Promedio</th>
                    <th scope="col" class="px-6 py-4">Observaciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                <?php if (empty($studentsGrades)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                            No hay estudiantes asignados en esta materia.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($studentsGrades as $std): ?>
                        <tr class="hover:bg-slate-50/40 transition">
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800">
                                <div>
                                    <p><?= htmlspecialchars($std['name']) ?></p>
                                    <span class="text-xs text-slate-400 font-mono"><?= htmlspecialchars($std['document']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" step="0.1" min="0" max="5" name="exam_grade[<?= $std['id'] ?>]" value="<?= $std['exam_grade'] ?>"
                                       class="block w-20 px-2 py-1.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-lg text-center focus:ring-indigo-500 focus:border-indigo-500 outline-none font-bold text-sm">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" step="0.1" min="0" max="5" name="workshop_grade[<?= $std['id'] ?>]" value="<?= $std['workshop_grade'] ?>"
                                       class="block w-20 px-2 py-1.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-lg text-center focus:ring-indigo-500 focus:border-indigo-500 outline-none font-bold text-sm">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" step="0.1" min="0" max="5" name="project_grade[<?= $std['id'] ?>]" value="<?= $std['project_grade'] ?>"
                                       class="block w-20 px-2 py-1.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-lg text-center focus:ring-indigo-500 focus:border-indigo-500 outline-none font-bold text-sm">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-extrabold px-3 py-1.5 rounded-xl <?= $std['final_grade'] >= 3.0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' ?>">
                                    <?= number_format($std['final_grade'], 2) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="text" name="comments[<?= $std['id'] ?>]" value="<?= htmlspecialchars($std['comments']) ?>" placeholder="Ej. Excelente trabajo"
                                       class="block w-full min-w-[200px] px-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="bg-slate-50/40">
                        <td colspan="6" class="px-6 py-4 text-right">
                            <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition text-sm shadow-md">
                                <i class="fa-solid fa-floppy-disk mr-2"></i> Guardar Calificaciones
                            </button>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
