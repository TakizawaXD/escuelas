<?php
// /modules/exams/create.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR', 'DOCENTE'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$user_id = Auth::user()['id'];
$role = Auth::user()['role_name'];

// Get subjects and teachers for the dropdown
$subjects_query = "SELECT sub.id, sub.name, c.name as course_name FROM subjects sub JOIN courses c ON sub.course_id = c.id ORDER BY c.name ASC, sub.name ASC";
$subjects = $db->query($subjects_query)->fetchAll();

// Get the current teacher if logged in as DOCENTE
$teacher_id = null;
if ($role === 'DOCENTE') {
    $stmt = $db->prepare("SELECT id FROM teachers WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $teacher = $stmt->fetch();
    if ($teacher) {
        $teacher_id = $teacher['id'];
    }
} else {
    $teachers = $db->query("SELECT t.id, u.first_name, u.last_name FROM teachers t JOIN users u ON t.user_id = u.id ORDER BY u.last_name ASC")->fetchAll();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = (int)$_POST['subject_id'];
    $title = Auth::sanitize($_POST['title']);
    $exam_date = Auth::sanitize($_POST['exam_date']);
    $max_score = (float)$_POST['max_score'];
    
    $selected_teacher_id = $teacher_id;
    if (!$teacher_id && isset($_POST['teacher_id'])) {
        $selected_teacher_id = (int)$_POST['teacher_id'];
    }

    if (empty($subject_id) || empty($title) || empty($exam_date) || empty($selected_teacher_id)) {
        $error = 'Por favor completa todos los campos requeridos.';
    } else {
        $stmt = $db->prepare("INSERT INTO exams (subject_id, teacher_id, title, exam_date, max_score) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$subject_id, $selected_teacher_id, $title, $exam_date, $max_score])) {
            header("Location: /modules/exams/index.php");
            exit;
        } else {
            $error = 'Error al programar el examen.';
        }
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Programar Examen</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Crear una nueva evaluación para una asignatura.</p>
    </div>
    <a href="/modules/exams/index.php" class="inline-flex items-center space-x-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-3 rounded-xl font-bold transition active:scale-[0.98]">
        <i class="fa-solid fa-arrow-left text-sm"></i>
        <span>Volver al Listado</span>
    </a>
</div>

<div class="bg-white p-8 md:p-12 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 max-w-3xl mx-auto relative overflow-hidden group">
    <!-- ambient background -->
    <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-50 rounded-full blur-3xl opacity-50 pointer-events-none transition-opacity group-hover:opacity-100"></div>
    <div class="relative z-10">
    <?php if ($error): ?>
        <div class="bg-rose-50 text-rose-600 p-4 rounded-xl mb-6 font-medium text-sm border border-rose-100 flex items-center space-x-2">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <div>
            <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Título del Examen <span class="text-rose-500">*</span></label>
            <input type="text" name="title" required placeholder="Ej. Examen Parcial de Matemáticas..."
                   class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
        </div>
        
        <div>
            <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Asignatura y Curso <span class="text-rose-500">*</span></label>
            <select name="subject_id" required class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                <option value="">Seleccione...</option>
                <?php foreach($subjects as $sub): ?>
                    <option value="<?= $sub['id'] ?>">
                        <?= htmlspecialchars($sub['name'] . ' (' . $sub['course_name'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Fecha Programada <span class="text-rose-500">*</span></label>
                <input type="date" name="exam_date" required
                       class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            </div>
            
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Puntaje Máximo <span class="text-rose-500">*</span></label>
                <input type="number" step="0.1" name="max_score" required value="100"
                       class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            </div>
        </div>

        <?php if (!$teacher_id): ?>
        <div>
            <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Profesor Responsable <span class="text-rose-500">*</span></label>
            <select name="teacher_id" required class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                <option value="">Seleccione...</option>
                <?php foreach($teachers as $t): ?>
                    <option value="<?= $t['id'] ?>">
                        <?= htmlspecialchars($t['last_name'] . ' ' . $t['first_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button type="submit" class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white px-8 py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 transition-all flex items-center justify-center">
                Programar Examen
            </button>
        </div>
    </form>
</div>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
