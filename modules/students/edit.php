<?php
// /modules/students/edit.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    header("Location: /index.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("
    SELECT s.*, u.first_name, u.last_name, u.document 
    FROM students s 
    JOIN users u ON s.user_id = u.id 
    WHERE s.id = ?
");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: /modules/students/index.php");
    exit;
}

$courses = $db->query("SELECT * FROM courses ORDER BY name ASC")->fetchAll();
$parentUsers = $db->query("SELECT * FROM users WHERE role_id = 6 ORDER BY first_name ASC")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = intval($_POST['course_id'] ?? 0);
    $parent_user_id = !empty($_POST['parent_user_id']) ? intval($_POST['parent_user_id']) : null;
    $birth_date = Auth::sanitize($_POST['birth_date'] ?? '');
    $address = Auth::sanitize($_POST['address'] ?? '');
    $photo_url = Auth::sanitize($_POST['photo_url'] ?? '');
    $grade = Auth::sanitize($_POST['grade'] ?? '');
    $gpa = floatval($_POST['gpa'] ?? 0.00);
    $scalability = Auth::sanitize($_POST['scalability'] ?? '');

    if (!empty($course_id) && !empty($birth_date)) {
        try {
            $stmt = $db->prepare("
                UPDATE students 
                SET course_id = ?, parent_user_id = ?, birth_date = ?, address = ?, photo_url = ?, grade = ?, gpa = ?, scalability = ? 
                WHERE id = ?
            ");
            $stmt->execute([$course_id, $parent_user_id, $birth_date, $address, $photo_url, $grade, $gpa, $scalability, $id]);

            header("Location: /modules/students/index.php");
            exit;
        } catch (PDOException $e) {
            $error = 'Error actualizando: ' . $e->getMessage();
        }
    } else {
        $error = 'Por favor complete todos los campos obligatorios.';
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="max-w-4xl mx-auto bg-white rounded-3xl shadow-sm border border-slate-100 p-8 animate-fade-in">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Editar Estudiante</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Actualiza la información académica y los reportes de <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></p>
        </div>
        <a href="/modules/students/index.php" class="text-sm font-semibold text-slate-500 hover:text-slate-800 flex items-center space-x-1">
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
                <label class="block text-sm font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Perfil del Estudiante</label>
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                    <div>
                        <p class="font-bold text-slate-800 text-base"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></p>
                        <p class="text-slate-500 text-xs font-mono"><?= htmlspecialchars($student['document']) ?></p>
                    </div>
                </div>
            </div>

            <div>
                <label for="course_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Curso / Grado *</label>
                <select name="course_id" id="course_id" required
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                    <option value="">Seleccione un curso...</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $student['course_id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="parent_user_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Acudiente / Padre</label>
                <select name="parent_user_id" id="parent_user_id"
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                    <option value="">No asignar...</option>
                    <?php foreach ($parentUsers as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $p['id'] == $student['parent_user_id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name'] . ' (' . $p['document'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="birth_date" class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha de Nacimiento *</label>
                <input type="date" name="birth_date" id="birth_date" required value="<?= htmlspecialchars($student['birth_date']) ?>"
                       class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
            </div>

            <div>
                <label for="photo_url" class="block text-sm font-semibold text-slate-700 mb-1.5">URL de Foto de Perfil</label>
                <input type="url" name="photo_url" id="photo_url" placeholder="https://images.unsplash.com/..." value="<?= htmlspecialchars($student['photo_url'] ?? '') ?>"
                       class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
            </div>

            <div>
                <label for="grade" class="block text-sm font-semibold text-slate-700 mb-1.5">Grado / Nivel</label>
                <input type="text" name="grade" id="grade" placeholder="Ej. 10° Grado" value="<?= htmlspecialchars($student['grade'] ?? '') ?>"
                       class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
            </div>

            <div>
                <label for="gpa" class="block text-sm font-semibold text-slate-700 mb-1.5">Promedio de Notas (GPA)</label>
                <input type="number" step="0.01" name="gpa" id="gpa" placeholder="Ej. 4.50" value="<?= htmlspecialchars($student['gpa'] ?? '0.00') ?>"
                       class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
            </div>

            <div class="md:col-span-2">
                <label for="scalability" class="block text-sm font-semibold text-slate-700 mb-1.5">Escalabilidad / Reporte de Progreso Académico</label>
                <textarea name="scalability" id="scalability" placeholder="Anotaciones sobre metas, fortalezas y rendimiento a futuro del estudiante..." rows="3"
                          class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition"><?= htmlspecialchars($student['scalability'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="pt-4 flex items-center justify-end space-x-2 border-t border-slate-100">
            <a href="/modules/students/index.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition text-sm">Cancelar</a>
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition text-sm shadow-md">Actualizar Matrícula</button>
        </div>
    </form>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
