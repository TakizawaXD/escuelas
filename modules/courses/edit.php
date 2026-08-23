<?php
// /modules/courses/edit.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /modules/courses/index.php");
    exit;
}

// Obtener datos del curso
$stmt = $db->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$id]);
$course = $stmt->fetch();

if (!$course) {
    header("Location: /modules/courses/index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = Auth::sanitize($_POST['name'] ?? '');
    $description = Auth::sanitize($_POST['description'] ?? '');

    if (empty($name)) {
        $error = "El nombre del curso es obligatorio.";
    } else {
        // Verificar si existe otro curso con el mismo nombre
        $stmt = $db->prepare("SELECT id FROM courses WHERE name = ? AND id != ?");
        $stmt->execute([$name, $id]);
        if ($stmt->fetch()) {
            $error = "Ya existe otro curso con este nombre.";
        } else {
            $stmt = $db->prepare("UPDATE courses SET name = ?, description = ? WHERE id = ?");
            if ($stmt->execute([$name, $description, $id])) {
                $success = "Curso actualizado exitosamente.";
                $course['name'] = $name;
                $course['description'] = $description;
                header("refresh:2;url=/modules/courses/index.php");
            } else {
                $error = "Error al actualizar el curso.";
            }
        }
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-3xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/courses/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Editar Curso</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Modificando el curso: <?= htmlspecialchars($course['name']) ?></p>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl flex items-center space-x-3">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span class="font-medium text-sm"><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl flex items-center space-x-3">
            <i class="fa-solid fa-circle-check"></i>
            <span class="font-medium text-sm"><?= htmlspecialchars($success) ?> Redirigiendo...</span>
        </div>
    <?php else: ?>
        <form method="POST" class="bg-white p-8 md:p-12 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 relative overflow-hidden group space-y-6">
            <div class="space-y-2">
                <label for="name" class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Nombre del Curso <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" required value="<?= htmlspecialchars($course['name']) ?>"
                       class="block w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            </div>

            <div class="space-y-2">
                <label for="description" class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Descripción (Opcional)</label>
                <textarea id="description" name="description" rows="4"
                          class="block w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"><?= htmlspecialchars($course['description'] ?? '') ?></textarea>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white px-8 py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 transition-all flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-save mr-2"></i> Actualizar Curso
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
