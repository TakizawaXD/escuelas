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

<!-- Premium UI Styles -->
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
    
    /* Custom Radio Buttons */
    .att-radio {
        appearance: none;
        background-color: #fff;
        margin: 0;
        font: inherit;
        color: currentColor;
        width: 1.15em;
        height: 1.15em;
        border: 0.15em solid currentColor;
        border-radius: 50%;
        display: grid;
        place-content: center;
        transition: 0.2s all ease-in-out;
    }
    .att-radio::before {
        content: "";
        width: 0.65em;
        height: 0.65em;
        border-radius: 50%;
        transform: scale(0);
        transition: 120ms transform ease-in-out;
        box-shadow: inset 1em 1em currentColor;
    }
    .att-radio:checked::before {
        transform: scale(1);
    }
    
    .status-present { color: #059669; }
    .status-absent { color: #e11d48; }
    .status-late { color: #d97706; }
</style>

<div class="space-y-8 animate-fade-in pb-10">
    <!-- Hero Header with Stats -->
    <div class="relative rounded-3xl bg-gradient-to-br from-blue-900 via-slate-900 to-cyan-950 p-8 shadow-2xl overflow-hidden text-white">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-72 h-72 rounded-full bg-blue-500 blur-[80px] opacity-30"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 rounded-full bg-cyan-500 blur-[80px] opacity-20"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="text-4xl font-extrabold tracking-tight mb-2">Registro de <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-300 to-cyan-300">Asistencia</span></h2>
                <p class="text-blue-200/80 font-medium text-sm max-w-md leading-relaxed">Control diario de presencia escolar e inasistencias justificadas.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-4">
                <div class="glass-panel px-6 py-3 rounded-2xl flex items-center space-x-4 shadow-lg shadow-black/10">
                    <div class="w-12 h-12 rounded-full bg-blue-500/30 flex items-center justify-center text-blue-200">
                        <i class="fa-solid fa-users text-xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-blue-200/70 tracking-widest">Alumnos (Hoy)</p>
                        <p class="text-2xl font-extrabold text-white leading-none"><?= count($studentsAttendance) ?></p>
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
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                    <select name="subject_id" id="subject_id" required class="block w-full pl-11 pr-10 py-3.5 bg-white/50 hover:bg-white/80 focus:bg-white border border-transparent focus:border-blue-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition shadow-inner appearance-none cursor-pointer">
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
                <label for="date" class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Fecha de Registro</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition">
                        <i class="fa-solid fa-calendar"></i>
                    </div>
                    <input type="date" name="date" id="date" value="<?= htmlspecialchars($date) ?>" required
                           class="block w-full pl-11 pr-4 py-3.5 bg-white/50 hover:bg-white/80 focus:bg-white border border-transparent focus:border-blue-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition shadow-inner">
                </div>
            </div>

            <div>
                <button type="submit" class="w-full h-[52px] bg-slate-900 hover:bg-black text-white font-bold rounded-xl transition shadow-md hover:shadow-lg flex justify-center items-center space-x-2">
                    <i class="fa-solid fa-rotate text-sm"></i>
                    <span>Cargar Asistencia</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Planilla Form List -->
    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden relative z-10">
        <form method="POST" action="/modules/attendance/register.php" class="overflow-x-auto">
            <input type="hidden" name="subject_id" value="<?= $subject_id ?>">
            <input type="hidden" name="date" value="<?= $date ?>">
            
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50/50 border-b border-slate-100 text-slate-400 uppercase tracking-widest text-[10px] font-bold">
                    <tr>
                        <th scope="col" class="px-6 py-5">Estudiante</th>
                        <th scope="col" class="px-6 py-5">Estado de Asistencia</th>
                        <th scope="col" class="px-6 py-5">Justificación / Observaciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($studentsAttendance)): ?>
                        <tr>
                            <td colspan="3" class="px-6 py-20 text-center text-slate-400 font-medium">
                                <div class="w-20 h-20 mx-auto bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-300 shadow-inner">
                                    <i class="fa-solid fa-users-slash text-3xl"></i>
                                </div>
                                <p class="text-base text-slate-500">No hay estudiantes asignados en esta materia.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($studentsAttendance as $std): ?>
                            <tr class="group hover:bg-blue-50/30 transition duration-300">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-100 to-cyan-50 border-2 border-white flex items-center justify-center text-blue-600 font-extrabold flex-shrink-0 shadow-md transform group-hover:scale-105 transition duration-300 text-xs">
                                            <?= strtoupper(substr($std['name'], 0, 2)) ?>
                                        </div>
                                        <div>
                                            <p class="font-extrabold text-slate-800 text-base leading-tight group-hover:text-blue-600 transition">
                                                <?= htmlspecialchars($std['name']) ?>
                                            </p>
                                            <span class="text-[10px] text-slate-400 font-mono mt-1 inline-block bg-slate-100 px-1.5 py-0.5 rounded">ID: <?= htmlspecialchars($std['document']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-4 bg-slate-50/80 px-3 py-2 rounded-xl border border-slate-100 inline-flex">
                                        <label class="flex items-center space-x-2 font-bold text-slate-700 cursor-pointer hover:bg-emerald-50 px-2 py-1 rounded-lg transition">
                                            <input type="radio" name="status[<?= $std['id'] ?>]" value="Presente" <?= $std['status'] === 'Presente' ? 'checked' : '' ?>
                                                   class="att-radio status-present">
                                            <span class="text-xs">Presente</span>
                                        </label>
                                        <div class="w-px h-4 bg-slate-200"></div>
                                        <label class="flex items-center space-x-2 font-bold text-slate-700 cursor-pointer hover:bg-rose-50 px-2 py-1 rounded-lg transition">
                                            <input type="radio" name="status[<?= $std['id'] ?>]" value="Ausente" <?= $std['status'] === 'Ausente' ? 'checked' : '' ?>
                                                   class="att-radio status-absent">
                                            <span class="text-xs">Ausente</span>
                                        </label>
                                        <div class="w-px h-4 bg-slate-200"></div>
                                        <label class="flex items-center space-x-2 font-bold text-slate-700 cursor-pointer hover:bg-amber-50 px-2 py-1 rounded-lg transition">
                                            <input type="radio" name="status[<?= $std['id'] ?>]" value="Tardanza" <?= $std['status'] === 'Tardanza' ? 'checked' : '' ?>
                                                   class="att-radio status-late">
                                            <span class="text-xs">Tardanza</span>
                                        </label>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="relative group/input">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within/input:text-blue-500 transition">
                                            <i class="fa-regular fa-comment-dots"></i>
                                        </div>
                                        <input type="text" name="justification[<?= $std['id'] ?>]" value="<?= htmlspecialchars($std['justification']) ?>" placeholder="Ej. Excusa médica..."
                                               class="block w-full min-w-[250px] pl-9 pr-4 py-2 bg-slate-50 hover:bg-slate-100/50 border border-slate-200 text-slate-800 rounded-xl focus:ring-blue-500 focus:border-blue-500 outline-none text-sm font-medium transition-all duration-300 focus:bg-white">
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <!-- Floating Save Button Area -->
                        <tr class="bg-slate-50/80 sticky bottom-0 border-t border-slate-200/60 backdrop-blur-sm shadow-xl z-20">
                            <td colspan="3" class="px-6 py-5 text-right">
                                <div class="flex justify-end items-center space-x-4">
                                    <span class="text-sm font-medium text-slate-500"><i class="fa-solid fa-circle-info text-blue-400 mr-2"></i>Recuerda guardar al terminar de tomar lista.</span>
                                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-blue-500/30 hover:-translate-y-0.5 flex items-center space-x-2 border border-blue-400/30">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        <span>Guardar Asistencia</span>
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
