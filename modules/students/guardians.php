<?php
// /modules/students/guardians.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /modules/students/index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("
    SELECT s.*, u.first_name, u.last_name, p.first_name as p_first, p.last_name as p_last, p.phone as p_phone, p.email as p_email
    FROM students s 
    JOIN users u ON s.user_id = u.id 
    LEFT JOIN users p ON s.parent_user_id = p.id
    WHERE s.id = ?
");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: /modules/students/index.php");
    exit;
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-4xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/students/view.php?id=<?= $id ?>" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Gestión de Acudientes</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Estudiante: <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></p>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-slate-800">Acudiente Principal Asignado</h3>
            <button class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl text-sm font-bold hover:bg-indigo-100 transition">
                <i class="fa-solid fa-exchange mr-1"></i> Cambiar Acudiente
            </button>
        </div>

        <?php if ($student['p_first']): ?>
            <div class="flex items-center p-6 bg-slate-50 rounded-2xl border border-slate-200">
                <div class="w-16 h-16 bg-white text-indigo-600 rounded-full flex items-center justify-center font-bold text-xl shadow-sm shrink-0 border border-slate-100">
                    <?= strtoupper(substr($student['p_first'], 0, 1) . substr($student['p_last'], 0, 1)) ?>
                </div>
                <div class="ml-4 flex-1">
                    <h4 class="font-bold text-slate-800 text-lg"><?= htmlspecialchars($student['p_first'] . ' ' . $student['p_last']) ?></h4>
                    <div class="flex space-x-4 mt-1 text-sm text-slate-500">
                        <span><i class="fa-solid fa-envelope mr-1"></i> <?= htmlspecialchars($student['p_email']) ?></span>
                        <span><i class="fa-solid fa-phone mr-1"></i> <?= htmlspecialchars($student['p_phone'] ?? 'N/A') ?></span>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="p-6 bg-amber-50 border border-amber-200 rounded-2xl text-center">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 text-3xl mb-2"></i>
                <p class="text-amber-800 font-bold">No hay acudiente asignado</p>
                <p class="text-sm text-amber-600 mt-1">Es obligatorio asignar un tutor legal al estudiante.</p>
            </div>
        <?php endif; ?>

        <div class="mt-8">
            <h4 class="font-bold text-slate-700 mb-4">Acudientes Secundarios / Autorizados</h4>
            <div class="border border-slate-200 border-dashed bg-slate-50 rounded-2xl p-6 text-center text-slate-500 cursor-pointer hover:bg-slate-100 transition">
                <i class="fa-solid fa-user-plus text-2xl mb-2 text-indigo-400"></i>
                <p class="font-medium text-sm">Añadir persona autorizada para retiro del colegio</p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
