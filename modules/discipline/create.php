<?php
// /modules/discipline/create.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR', 'DOCENTE'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();

// Fetch students for dropdown
$stmt = $db->query("
    SELECT s.id, u.first_name, u.last_name, u.document, c.name as course_name
    FROM students s
    JOIN users u ON s.user_id = u.id
    JOIN courses c ON s.course_id = c.id
    WHERE u.status = 1
    ORDER BY u.last_name ASC
");
$students = $stmt->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)($_POST['student_id'] ?? 0);
    $type = Auth::sanitize($_POST['type'] ?? '');
    $description = Auth::sanitize($_POST['description'] ?? '');
    
    // We need the teacher_id of the logged in user
    // The current user must be mapped to a teacher record if they are a DOCENTE.
    // If ADMIN/DIRECTOR, maybe they shouldn't create it directly, or they need a mock teacher_id.
    // Let's find the teacher_id for this user.
    $user_id = Auth::user()['id'];
    $stmt = $db->prepare("SELECT id FROM teachers WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $teacher = $stmt->fetch();
    
    if (!$teacher) {
        $error = 'Tu usuario no está asociado a un perfil de Docente en el sistema.';
    } elseif ($student_id <= 0 || empty($type) || empty($description)) {
        $error = 'Por favor completa todos los campos.';
    } else {
        $stmt = $db->prepare("INSERT INTO discipline_reports (student_id, teacher_id, type, description) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$student_id, $teacher['id'], $type, $description])) {
            header("Location: /modules/discipline/index.php");
            exit;
        } else {
            $error = 'Error al guardar la anotación.';
        }
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Nueva Anotación</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Registrar un evento en el libro de convivencia.</p>
    </div>
    <a href="/modules/discipline/index.php" class="inline-flex items-center space-x-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-3 rounded-xl font-bold transition active:scale-[0.98]">
        <i class="fa-solid fa-arrow-left text-sm"></i>
        <span>Volver al Libro</span>
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
            <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Estudiante <span class="text-rose-500">*</span></label>
            <select name="student_id" required class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                <option value="">Seleccione un alumno...</option>
                <?php foreach($students as $st): ?>
                    <option value="<?= $st['id'] ?>">
                        <?= htmlspecialchars($st['last_name'] . ' ' . $st['first_name'] . ' - ' . $st['course_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Tipo de Anotación <span class="text-rose-500">*</span></label>
            <div class="grid grid-cols-3 gap-4">
                <label class="relative flex cursor-pointer rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:bg-slate-50 focus-within:ring-2 focus-within:ring-indigo-500">
                    <input type="radio" name="type" value="positiva" class="sr-only" required>
                    <div class="flex flex-col items-center w-full">
                        <i class="fa-solid fa-star text-emerald-500 text-2xl mb-2"></i>
                        <span class="font-bold text-slate-800 text-sm">Positiva</span>
                    </div>
                </label>
                
                <label class="relative flex cursor-pointer rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:bg-slate-50 focus-within:ring-2 focus-within:ring-indigo-500">
                    <input type="radio" name="type" value="negativa" class="sr-only" required>
                    <div class="flex flex-col items-center w-full">
                        <i class="fa-solid fa-triangle-exclamation text-rose-500 text-2xl mb-2"></i>
                        <span class="font-bold text-slate-800 text-sm">Negativa</span>
                    </div>
                </label>
                
                <label class="relative flex cursor-pointer rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:bg-slate-50 focus-within:ring-2 focus-within:ring-indigo-500">
                    <input type="radio" name="type" value="incidente" class="sr-only" required>
                    <div class="flex flex-col items-center w-full">
                        <i class="fa-solid fa-bell text-amber-500 text-2xl mb-2"></i>
                        <span class="font-bold text-slate-800 text-sm">Incidente</span>
                    </div>
                </label>
            </div>
            <style>
                input[type="radio"]:checked + div { color: #4f46e5; }
                input[type="radio"]:checked ~ * { border-color: #4f46e5; }
                label:has(input[type="radio"]:checked) { border-color: #4f46e5; background-color: #f5f3ff; }
            </style>
        </div>

        <div>
            <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Descripción de los hechos <span class="text-rose-500">*</span></label>
            <textarea name="description" required rows="4" placeholder="Describa objetivamente la situación observada..." 
                      class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button type="submit" class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white px-8 py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 transition-all flex items-center justify-center">
                Guardar Registro
            </button>
        </div>
    </form>
</div>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
