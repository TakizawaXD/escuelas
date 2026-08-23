<?php
// /modules/subjects/create.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$courses = $db->query("SELECT * FROM courses ORDER BY name ASC")->fetchAll();
$teachers = $db->query("
    SELECT t.*, u.first_name, u.last_name 
    FROM teachers t
    JOIN users u ON t.user_id = u.id
    ORDER BY u.first_name ASC
")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = Auth::sanitize($_POST['name'] ?? '');
    $description = Auth::sanitize($_POST['description'] ?? '');
    $course_id = intval($_POST['course_id'] ?? 0);
    $teacher_id = !empty($_POST['teacher_id']) ? intval($_POST['teacher_id']) : null;
    $weekly_hours = intval($_POST['weekly_hours'] ?? 4);
    $study_material = Auth::sanitize($_POST['study_material'] ?? '');

    if (!empty($name) && !empty($course_id)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO subjects (name, description, course_id, teacher_id, weekly_hours, study_material)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $description, $course_id, $teacher_id, $weekly_hours, $study_material]);

            header("Location: /modules/subjects/index.php");
            exit;
        } catch (PDOException $e) {
            $error = 'Error creando materia: ' . $e->getMessage();
        }
    } else {
        $error = 'Por favor complete todos los campos obligatorios.';
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="max-w-3xl mx-auto bg-white rounded-3xl shadow-sm border border-slate-100 p-8 animate-fade-in">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-1">Crear Materia</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Defina los detalles del programa académico y materiales.</p>
        </div>
        <a href="/modules/subjects/index.php" class="text-sm font-semibold text-slate-500 hover:text-slate-800 flex items-center space-x-1">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver</span>
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="mb-5 p-4 rounded-xl bg-red-50 text-red-700 text-sm border border-red-200/60 flex items-start space-x-3">
            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
            <span class="font-medium"><?= $error ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
                <label for="name" class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Nombre de Materia *</label>
                <input type="text" name="name" id="name" required placeholder="Ej. Álgebra Avanzada"
                       class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 outline-none text-sm font-medium transition">
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Descripción</label>
                <input type="text" name="description" id="description" placeholder="Ej. Temas de cálculo básico y geometría analítica"
                       class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 outline-none text-sm font-medium transition">
            </div>

            <div>
                <label for="course_id" class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Grado / Curso *</label>
                <select name="course_id" id="course_id" required
                        class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                    <option value="">Seleccione un grado...</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="teacher_id" class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Docente Asignado</label>
                <select name="teacher_id" id="teacher_id"
                        class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                    <option value="">Sin asignar...</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="weekly_hours" class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Intensidad Horaria (Horas/Semana) *</label>
                <input type="number" name="weekly_hours" id="weekly_hours" required min="1" max="40" value="4"
                       class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
            </div>

            <div>
                <label for="study_material" class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">URL de Material de Estudio</label>
                <input type="url" name="study_material" id="study_material" placeholder="Ej. https://enlace-a-material.com/pdf"
                       class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
            </div>
        </div>

        <div class="pt-4 flex items-center justify-end space-x-2 border-t border-slate-100">
            <a href="/modules/subjects/index.php" class="px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-all text-sm">Cancelar</a>
            <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 text-sm flex items-center justify-center space-x-2">Guardar Materia</button>
        </div>
    </form>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
