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

<!-- Premium UI Styles -->
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
    
    /* Input Animations */
    input[type=number]:focus {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
</style>

<div class="space-y-8 animate-fade-in pb-10">
    <!-- Hero Header with Stats -->
    <div class="relative rounded-3xl bg-gradient-to-br from-violet-900 via-slate-900 to-fuchsia-950 p-8 shadow-2xl overflow-hidden text-white">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-72 h-72 rounded-full bg-violet-500 blur-[80px] opacity-30"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 rounded-full bg-fuchsia-500 blur-[80px] opacity-20"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="text-4xl font-extrabold tracking-tight mb-2">Control de <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-300 to-fuchsia-300">Calificaciones</span></h2>
                <p class="text-violet-200/80 font-medium text-sm max-w-md leading-relaxed">Gestión académica y reporte de notas interactivo por periodo.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-4">
                <div class="glass-panel px-6 py-3 rounded-2xl flex items-center space-x-4 shadow-lg shadow-black/10">
                    <div class="w-12 h-12 rounded-full bg-violet-500/30 flex items-center justify-center text-violet-200">
                        <i class="fa-solid fa-graduation-cap text-xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-violet-200/70 tracking-widest">Estudiantes Lista</p>
                        <p class="text-2xl font-extrabold text-white leading-none"><?= count($studentsGrades) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Search & Filter Bar (Glassmorphism) -->
    <div class="glass-panel p-6 rounded-3xl shadow-sm border border-slate-100/80 z-20 relative">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
            <div class="relative group">
                <label for="subject_id" class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Materia / Curso</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-violet-500 transition">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <select name="subject_id" id="subject_id" required class="block w-full pl-11 pr-10 py-3.5 bg-white/50 hover:bg-white/80 focus:bg-white border border-transparent focus:border-violet-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition shadow-inner appearance-none cursor-pointer">
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
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <div class="relative group">
                <label for="period" class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Periodo Escolar</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-violet-500 transition">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <select name="period" id="period" required class="block w-full pl-11 pr-10 py-3.5 bg-white/50 hover:bg-white/80 focus:bg-white border border-transparent focus:border-violet-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition shadow-inner appearance-none cursor-pointer">
                        <?php for ($p = 1; $p <= 4; $p++): ?>
                            <option value="<?= $p ?>" <?= $p == $period ? 'selected' : '' ?>>Periodo <?= $p ?></option>
                        <?php endfor; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <div>
                <button type="submit" class="w-full h-[52px] bg-slate-900 hover:bg-black text-white font-bold rounded-xl transition shadow-md hover:shadow-lg flex justify-center items-center space-x-2">
                    <i class="fa-solid fa-rotate text-sm"></i>
                    <span>Cargar Planilla</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Planilla Form List -->
    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden relative z-10">
        <form method="POST" action="/modules/grades/register.php" class="overflow-x-auto">
            <input type="hidden" name="subject_id" value="<?= $subject_id ?>">
            <input type="hidden" name="period" value="<?= $period ?>">
            
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50/50 border-b border-slate-100 text-slate-400 uppercase tracking-widest text-[10px] font-bold">
                    <tr>
                        <th scope="col" class="px-6 py-5">Estudiante</th>
                        <th scope="col" class="px-6 py-5">Examen <span class="text-[9px] lowercase bg-slate-200 px-1.5 py-0.5 rounded text-slate-500 ml-1">0-5</span></th>
                        <th scope="col" class="px-6 py-5">Talleres <span class="text-[9px] lowercase bg-slate-200 px-1.5 py-0.5 rounded text-slate-500 ml-1">0-5</span></th>
                        <th scope="col" class="px-6 py-5">Proyectos <span class="text-[9px] lowercase bg-slate-200 px-1.5 py-0.5 rounded text-slate-500 ml-1">0-5</span></th>
                        <th scope="col" class="px-6 py-5">Promedio Actual</th>
                        <th scope="col" class="px-6 py-5">Observaciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($studentsGrades)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center text-slate-400 font-medium">
                                <div class="w-20 h-20 mx-auto bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-300 shadow-inner">
                                    <i class="fa-solid fa-users-slash text-3xl"></i>
                                </div>
                                <p class="text-base text-slate-500">No hay estudiantes asignados en esta materia para el curso actual.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($studentsGrades as $std): 
                            $finalGrade = number_format($std['final_grade'], 2);
                            $badgeColor = 'bg-slate-100 text-slate-500 border-slate-200';
                            if ($std['final_grade'] >= 4.0) {
                                $badgeColor = 'bg-emerald-50 text-emerald-600 border-emerald-200 shadow-emerald-500/10';
                            } elseif ($std['final_grade'] >= 3.0) {
                                $badgeColor = 'bg-amber-50 text-amber-600 border-amber-200 shadow-amber-500/10';
                            } elseif ($std['final_grade'] > 0) {
                                $badgeColor = 'bg-rose-50 text-rose-600 border-rose-200 shadow-rose-500/10';
                            }
                        ?>
                            <tr class="group hover:bg-violet-50/30 transition duration-300">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-100 to-fuchsia-50 border-2 border-white flex items-center justify-center text-violet-600 font-extrabold flex-shrink-0 shadow-md transform group-hover:scale-105 transition duration-300 text-sm">
                                            <?= strtoupper(substr($std['name'], 0, 2)) ?>
                                        </div>
                                        <div>
                                            <p class="font-extrabold text-slate-800 text-base leading-tight group-hover:text-violet-600 transition">
                                                <?= htmlspecialchars($std['name']) ?>
                                            </p>
                                            <span class="text-[10px] text-slate-400 font-mono mt-1 inline-block bg-slate-100 px-1.5 py-0.5 rounded">ID: <?= htmlspecialchars($std['document']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <input type="number" step="0.1" min="0" max="5" name="exam_grade[<?= $std['id'] ?>]" value="<?= $std['exam_grade'] ?>"
                                           class="block w-20 px-3 py-2 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-center focus:ring-violet-500 focus:border-violet-500 outline-none font-bold text-sm transition-all duration-300 focus:bg-white">
                                </td>
                                <td class="px-6 py-4">
                                    <input type="number" step="0.1" min="0" max="5" name="workshop_grade[<?= $std['id'] ?>]" value="<?= $std['workshop_grade'] ?>"
                                           class="block w-20 px-3 py-2 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-center focus:ring-violet-500 focus:border-violet-500 outline-none font-bold text-sm transition-all duration-300 focus:bg-white">
                                </td>
                                <td class="px-6 py-4">
                                    <input type="number" step="0.1" min="0" max="5" name="project_grade[<?= $std['id'] ?>]" value="<?= $std['project_grade'] ?>"
                                           class="block w-20 px-3 py-2 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-center focus:ring-violet-500 focus:border-violet-500 outline-none font-bold text-sm transition-all duration-300 focus:bg-white">
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-extrabold border shadow-sm transition-all <?= $badgeColor ?>">
                                        <?= $finalGrade ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="relative group/input">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within/input:text-violet-500 transition">
                                            <i class="fa-regular fa-comment-dots"></i>
                                        </div>
                                        <input type="text" name="comments[<?= $std['id'] ?>]" value="<?= htmlspecialchars($std['comments']) ?>" placeholder="Excelente desempeño..."
                                               class="block w-full min-w-[250px] pl-9 pr-4 py-2 bg-slate-50 hover:bg-slate-100/50 border border-slate-200 text-slate-800 rounded-xl focus:ring-violet-500 focus:border-violet-500 outline-none text-sm font-medium transition-all duration-300 focus:bg-white">
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <!-- Floating Save Button Area -->
                        <tr class="bg-slate-50/80 sticky bottom-0 border-t border-slate-200/60 backdrop-blur-sm shadow-xl z-20">
                            <td colspan="6" class="px-6 py-5 text-right">
                                <div class="flex justify-end items-center space-x-4">
                                    <span class="text-sm font-medium text-slate-500"><i class="fa-solid fa-circle-info text-violet-400 mr-2"></i>Verifica las calificaciones antes de guardar.</span>
                                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-500 hover:to-fuchsia-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-violet-500/30 hover:-translate-y-0.5 flex items-center space-x-2 border border-violet-400/30">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        <span>Guardar Calificaciones</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
    </div>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
