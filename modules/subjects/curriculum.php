<?php
// /modules/subjects/curriculum.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header("Location: /modules/subjects/index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT s.*, c.name as course_name FROM subjects s JOIN courses c ON s.course_id = c.id WHERE s.id = ?");
$stmt->execute([$id]);
$subject = $stmt->fetch();

if (!$subject) {
    header("Location: /modules/subjects/index.php");
    exit;
}

$success = '';
$error = '';

// CRUD Actions
$action = $_POST['action'] ?? ($_GET['action'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add_unit') {
        $title = Auth::sanitize($_POST['title'] ?? '');
        $description = Auth::sanitize($_POST['description'] ?? '');
        $week_number = intval($_POST['week_number'] ?? 1);
        $learning_goal = Auth::sanitize($_POST['learning_goal'] ?? '');

        if (!empty($title)) {
            $stmt2 = $db->prepare("INSERT INTO curriculum_units (subject_id, title, description, week_number, learning_goal) VALUES (?, ?, ?, ?, ?)");
            if ($stmt2->execute([$id, $title, $description, $week_number, $learning_goal])) {
                $success = 'Unidad temática registrada exitosamente.';
            } else {
                $error = 'Error al guardar la unidad.';
            }
        } else {
            $error = 'El título de la unidad es obligatorio.';
        }
    }

    if ($action === 'delete_unit') {
        $unit_id = intval($_POST['unit_id'] ?? 0);
        if ($unit_id && Auth::hasRole(['ADMIN', 'DIRECTOR', 'DOCENTE'])) {
            $stmt2 = $db->prepare("DELETE FROM curriculum_units WHERE id = ? AND subject_id = ?");
            $stmt2->execute([$unit_id, $id]);
            $success = 'Unidad eliminada.';
        }
    }
}

// Fetch all units
$units = $db->prepare("SELECT * FROM curriculum_units WHERE subject_id = ? ORDER BY week_number ASC, id ASC");
$units->execute([$id]);
$units = $units->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-8 animate-fade-in pb-10 max-w-5xl mx-auto">

    <!-- Header -->
    <div class="flex items-start justify-between gap-4">
        <div class="flex items-center space-x-4">
            <a href="/modules/subjects/view.php?id=<?= $id ?>" class="w-10 h-10 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm flex items-center justify-center">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Plan de Estudios</h2>
                <p class="text-slate-500 font-medium text-sm mt-1">
                    <span class="font-bold text-indigo-600"><?= htmlspecialchars($subject['name']) ?></span>
                    — <?= htmlspecialchars($subject['course_name']) ?>
                </p>
            </div>
        </div>
        <span class="hidden md:flex items-center space-x-2 bg-indigo-50 border border-indigo-100 text-indigo-700 px-4 py-2 rounded-xl text-sm font-bold">
            <i class="fa-solid fa-book-open"></i>
            <span><?= count($units) ?> Unidades</span>
        </span>
    </div>

    <?php if ($success): ?>
        <div class="bg-emerald-50 text-emerald-600 p-4 rounded-2xl font-bold text-sm border border-emerald-200 shadow-sm flex items-center space-x-3">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="bg-rose-50 text-rose-600 p-4 rounded-2xl font-bold text-sm border border-rose-200 shadow-sm flex items-center space-x-3">
            <i class="fa-solid fa-circle-exclamation text-lg"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Add Unit Form -->
        <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'DOCENTE'])): ?>
        <div class="lg:col-span-1">
            <div class="bg-white p-8 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 sticky top-6 relative overflow-hidden group">
                <div class="absolute -right-10 -top-10 w-36 h-36 bg-indigo-50 rounded-full blur-2xl opacity-60 transition-opacity group-hover:opacity-100 pointer-events-none"></div>
                <h3 class="font-extrabold text-slate-800 text-sm uppercase tracking-widest mb-6 flex items-center relative z-10">
                    <i class="fa-solid fa-layer-group text-indigo-500 mr-2"></i> Nueva Unidad Temática
                </h3>
                <form method="POST" class="space-y-5 relative z-10">
                    <input type="hidden" name="action" value="add_unit">
                    <div>
                        <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-400 mb-2">Título de la Unidad *</label>
                        <input type="text" name="title" required placeholder="Ej. Números Enteros y Operaciones"
                               class="block w-full px-4 py-3 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-400 mb-2">Semana de Inicio</label>
                        <input type="number" name="week_number" min="1" max="40" value="1"
                               class="block w-full px-4 py-3 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-400 mb-2">Objetivo de Aprendizaje</label>
                        <textarea name="learning_goal" rows="2" placeholder="¿Qué logrará el estudiante al finalizar esta unidad?"
                               class="block w-full px-4 py-3 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-medium transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-400 mb-2">Contenido / Descripción</label>
                        <textarea name="description" rows="3" placeholder="Temas, actividades, recursos..."
                               class="block w-full px-4 py-3 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-medium transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 resize-none"></textarea>
                    </div>
                    <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 transition-all flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-plus"></i>
                        <span>Agregar Unidad</span>
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Units List -->
        <div class="<?= Auth::hasRole(['ADMIN', 'DIRECTOR', 'DOCENTE']) ? 'lg:col-span-2' : 'lg:col-span-3' ?> space-y-4">
            <?php if (empty($units)): ?>
                <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 p-16 text-center">
                    <div class="w-20 h-20 mx-auto bg-slate-50 rounded-full flex items-center justify-center mb-5 text-slate-300 shadow-inner">
                        <i class="fa-solid fa-book-open text-4xl"></i>
                    </div>
                    <h3 class="text-lg font-extrabold text-slate-600 mb-2">Sin contenido curricular</h3>
                    <p class="text-slate-400 text-sm">Aún no se han registrado unidades temáticas para esta materia.</p>
                </div>
            <?php else: ?>
                <?php foreach ($units as $unit): ?>
                    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-slate-100/60 p-6 group hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)] transition duration-300">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start space-x-4 flex-1">
                                <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center font-extrabold text-indigo-600 text-sm">
                                    S<?= $unit['week_number'] ?>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-extrabold text-slate-800 text-base leading-tight"><?= htmlspecialchars($unit['title']) ?></h4>
                                    <?php if ($unit['learning_goal']): ?>
                                        <p class="text-xs font-bold text-indigo-600 mt-1.5 flex items-center space-x-1">
                                            <i class="fa-solid fa-bullseye text-[10px]"></i>
                                            <span><?= htmlspecialchars($unit['learning_goal']) ?></span>
                                        </p>
                                    <?php endif; ?>
                                    <?php if ($unit['description']): ?>
                                        <p class="text-sm text-slate-500 mt-2 leading-relaxed"><?= nl2br(htmlspecialchars($unit['description'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'DOCENTE'])): ?>
                                <form method="POST" class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition">
                                    <input type="hidden" name="action" value="delete_unit">
                                    <input type="hidden" name="unit_id" value="<?= $unit['id'] ?>">
                                    <button type="submit" onclick="return confirm('¿Eliminar esta unidad temática?')"
                                            class="w-9 h-9 rounded-xl bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition">
                                        <i class="fa-solid fa-trash text-sm"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
