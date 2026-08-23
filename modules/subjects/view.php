<?php
// /modules/subjects/view.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR', 'DOCENTE'])) {
    header("Location: /index.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /modules/subjects/index.php");
    exit;
}

$db = Database::getInstance()->getConnection();

$query = "
    SELECT s.*, c.name as course_name, t.specialty, u.first_name as teacher_first, u.last_name as teacher_last
    FROM subjects s
    JOIN courses c ON s.course_id = c.id
    LEFT JOIN teachers t ON s.teacher_id = t.id
    LEFT JOIN users u ON t.user_id = u.id
    WHERE s.id = ?
";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$subject = $stmt->fetch();

if (!$subject) {
    header("Location: /modules/subjects/index.php");
    exit;
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-5xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/subjects/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Detalle de Materia</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Viendo la información detallada de la asignatura.</p>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- Info Principal -->
        <div class="md:col-span-2 space-y-6">
            <div class="flex items-start space-x-4">
                <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center flex-shrink-0 text-3xl font-bold">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-slate-800"><?= htmlspecialchars($subject['name']) ?></h3>
                    <div class="flex items-center space-x-2 mt-2">
                        <span class="inline-block px-3 py-1 text-xs font-bold rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100">
                            <?= htmlspecialchars($subject['course_name']) ?>
                        </span>
                        <span class="inline-block px-3 py-1 text-xs font-bold rounded-lg bg-slate-100 text-slate-600 border border-slate-200">
                            <?= htmlspecialchars($subject['weekly_hours']) ?> Horas/Semana
                        </span>
                    </div>
                </div>
            </div>

            <div class="prose prose-slate max-w-none">
                <h4 class="text-lg font-bold text-slate-800">Descripción</h4>
                <p class="text-slate-600 leading-relaxed">
                    <?= $subject['description'] ? nl2br(htmlspecialchars($subject['description'])) : '<span class="italic text-slate-400">Sin descripción registrada.</span>' ?>
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                <a href="/modules/subjects/curriculum.php?id=<?= $subject['id'] ?>" class="p-4 rounded-2xl bg-slate-50 hover:bg-indigo-50 border border-slate-100 hover:border-indigo-100 transition group flex items-center space-x-4">
                    <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-indigo-500 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <div>
                        <h5 class="font-bold text-slate-700 group-hover:text-indigo-700 transition">Plan de Estudios</h5>
                        <p class="text-xs text-slate-500">Ver temario y unidades</p>
                    </div>
                </a>

                <a href="/modules/subjects/resources.php?id=<?= $subject['id'] ?>" class="p-4 rounded-2xl bg-slate-50 hover:bg-indigo-50 border border-slate-100 hover:border-indigo-100 transition group flex items-center space-x-4">
                    <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-indigo-500 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                    <div>
                        <h5 class="font-bold text-slate-700 group-hover:text-indigo-700 transition">Materiales</h5>
                        <p class="text-xs text-slate-500">Archivos y enlaces</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Panel Lateral (Profesor) -->
        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 flex flex-col justify-between">
            <div>
                <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Docente Asignado</h4>
                <?php if ($subject['teacher_first']): ?>
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-14 h-14 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold text-xl border-2 border-white shadow-sm">
                            <?= strtoupper(substr($subject['teacher_first'], 0, 1) . substr($subject['teacher_last'], 0, 1)) ?>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800"><?= htmlspecialchars($subject['teacher_first'] . ' ' . $subject['teacher_last']) ?></p>
                            <p class="text-xs text-indigo-600 font-medium"><?= htmlspecialchars($subject['specialty']) ?></p>
                        </div>
                    </div>
                    <a href="/modules/teachers/view.php?id=<?= $subject['teacher_id'] ?>" class="text-sm text-indigo-600 font-bold hover:underline">Ver perfil del docente &rarr;</a>
                <?php else: ?>
                    <div class="flex flex-col items-center justify-center py-6 text-center text-slate-400">
                        <i class="fa-solid fa-user-xmark text-3xl mb-2"></i>
                        <p class="text-sm font-medium">No hay profesor asignado a esta materia.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="mt-8 border-t border-slate-200 pt-4">
                <a href="/modules/subjects/edit.php?id=<?= $subject['id'] ?>" class="block w-full text-center bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 px-4 py-2.5 rounded-xl font-bold transition">
                    <i class="fa-solid fa-pen-to-square mr-1"></i> Editar Materia
                </a>
                <a href="/modules/subjects/delete.php?id=<?= $subject['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar esta materia?');" class="block w-full text-center mt-2 text-red-600 hover:bg-red-50 px-4 py-2.5 rounded-xl font-bold transition">
                    <i class="fa-solid fa-trash mr-1"></i> Eliminar
                </a>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
