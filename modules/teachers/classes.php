<?php
// /modules/teachers/classes.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /modules/teachers/index.php");
    exit;
}

$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("SELECT u.first_name, u.last_name FROM teachers t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
$stmt->execute([$id]);
$teacher = $stmt->fetch();

if (!$teacher) {
    header("Location: /modules/teachers/index.php");
    exit;
}

// Obtener las materias asignadas
$stmt = $db->prepare("SELECT s.id, s.name, s.weekly_hours, c.name as course_name FROM subjects s JOIN courses c ON s.course_id = c.id WHERE s.teacher_id = ?");
$stmt->execute([$id]);
$subjects = $stmt->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-4xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/teachers/view.php?id=<?= $id ?>" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Materias Asignadas</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Docente: <?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) ?></p>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <?php if (empty($subjects)): ?>
            <div class="text-center py-10 text-slate-400">
                <i class="fa-solid fa-book-open text-5xl mb-4 text-slate-200"></i>
                <h3 class="text-lg font-bold text-slate-600 mb-1">Sin asignaciones</h3>
                <p class="text-sm">El docente no tiene materias asignadas actualmente.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach($subjects as $sub): ?>
                    <a href="/modules/subjects/view.php?id=<?= $sub['id'] ?>" class="block p-5 rounded-2xl bg-white border border-slate-200 hover:border-indigo-300 hover:shadow-md transition group">
                        <div class="flex justify-between items-start mb-3">
                            <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                                <i class="fa-solid fa-book"></i>
                            </div>
                            <span class="bg-slate-100 text-slate-600 px-2.5 py-1 rounded-lg text-xs font-bold border border-slate-200">
                                <?= htmlspecialchars($sub['weekly_hours']) ?> Hrs/Semana
                            </span>
                        </div>
                        <h4 class="font-bold text-slate-800 text-lg group-hover:text-indigo-600 transition"><?= htmlspecialchars($sub['name']) ?></h4>
                        <p class="text-sm text-slate-500 font-medium mt-1"><?= htmlspecialchars($sub['course_name']) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
