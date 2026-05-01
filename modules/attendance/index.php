<?php
// /modules/attendance/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR', 'DOCENTE'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$u = Auth::user();

// Fetch subjects
if (Auth::hasRole('DOCENTE')) {
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
    $subjectsStmt = $db->query("
        SELECT s.*, c.name as course_name 
        FROM subjects s 
        JOIN courses c ON s.course_id = c.id
    ");
}

$subjects = $subjectsStmt->fetchAll();

$subject_id = intval($_GET['subject_id'] ?? 0);
$date = Auth::sanitize($_GET['date'] ?? date('Y-m-d'));

if ($subject_id === 0 && !empty($subjects)) {
    $subject_id = $subjects[0]['id'];
}

$studentsAttendance = [];
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
        // Find existing attendance
        $aStmt = $db->prepare("
            SELECT * FROM attendance 
            WHERE student_id = ? AND subject_id = ? AND date = ?
        ");
        $aStmt->execute([$st['id'], $subject_id, $date]);
        $attRec = $aStmt->fetch();

        $studentsAttendance[] = [
            'id' => $st['id'],
            'name' => $st['first_name'] . ' ' . $st['last_name'],
            'document' => $st['document'],
            'status' => $attRec ? $attRec['status'] : 'Presente',
            'justification' => $attRec ? $attRec['justification'] : ''
        ];
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<!-- Header Section -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Registro de Asistencia</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Control diario de presencia escolar.</p>
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
            <label for="date" class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha de Registro</label>
            <input type="date" name="date" id="date" value="<?= htmlspecialchars($date) ?>" required
                   class="block w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
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
    <form method="POST" action="/modules/attendance/register.php" class="overflow-x-auto">
        <input type="hidden" name="subject_id" value="<?= $subject_id ?>">
        <input type="hidden" name="date" value="<?= $date ?>">
        
        <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
            <thead class="bg-slate-50/60 text-slate-400 uppercase tracking-wider text-xs font-bold">
                <tr>
                    <th scope="col" class="px-6 py-4">Estudiante</th>
                    <th scope="col" class="px-6 py-4">Estado de Asistencia</th>
                    <th scope="col" class="px-6 py-4">Justificación / Observaciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                <?php if (empty($studentsAttendance)): ?>
                    <tr>
                        <td colspan="3" class="px-6 py-10 text-center text-slate-400">
                            No hay estudiantes asignados en esta materia.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($studentsAttendance as $std): ?>
                        <tr class="hover:bg-slate-50/40 transition">
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800">
                                <div>
                                    <p><?= htmlspecialchars($std['name']) ?></p>
                                    <span class="text-xs text-slate-400 font-mono"><?= htmlspecialchars($std['document']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-4">
                                    <label class="flex items-center space-x-1 font-semibold text-slate-700 cursor-pointer">
                                        <input type="radio" name="status[<?= $std['id'] ?>]" value="Presente" <?= $std['status'] === 'Presente' ? 'checked' : '' ?>
                                               class="text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                        <span class="text-xs">Presente</span>
                                    </label>
                                    <label class="flex items-center space-x-1 font-semibold text-slate-700 cursor-pointer">
                                        <input type="radio" name="status[<?= $std['id'] ?>]" value="Ausente" <?= $std['status'] === 'Ausente' ? 'checked' : '' ?>
                                               class="text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                        <span class="text-xs">Ausente</span>
                                    </label>
                                    <label class="flex items-center space-x-1 font-semibold text-slate-700 cursor-pointer">
                                        <input type="radio" name="status[<?= $std['id'] ?>]" value="Tardanza" <?= $std['status'] === 'Tardanza' ? 'checked' : '' ?>
                                               class="text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                        <span class="text-xs">Tardanza</span>
                                    </label>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="text" name="justification[<?= $std['id'] ?>]" value="<?= htmlspecialchars($std['justification']) ?>" placeholder="Ej. Presenta excusa médica"
                                       class="block w-full min-w-[200px] px-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="bg-slate-50/40">
                        <td colspan="3" class="px-6 py-4 text-right">
                            <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition text-sm shadow-md">
                                <i class="fa-solid fa-floppy-disk mr-2"></i> Guardar Asistencia
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
