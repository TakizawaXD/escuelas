<?php
// /modules/teachers/view.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR', 'DOCENTE'])) {
    header("Location: /index.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /modules/teachers/index.php");
    exit;
}

$db = Database::getInstance()->getConnection();

$query = "
    SELECT t.*, u.document, u.first_name, u.last_name, u.email, u.phone, u.status, u.created_at
    FROM teachers t
    JOIN users u ON t.user_id = u.id
    WHERE t.id = ?
";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$teacher = $stmt->fetch();

if (!$teacher) {
    header("Location: /modules/teachers/index.php");
    exit;
}

// Obtener las materias asignadas
$stmt = $db->prepare("SELECT s.name, s.weekly_hours, c.name as course_name FROM subjects s JOIN courses c ON s.course_id = c.id WHERE s.teacher_id = ?");
$stmt->execute([$id]);
$subjects = $stmt->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-5xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/teachers/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Perfil del Docente</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Información detallada y carga académica.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Columna Izquierda: Información de Perfil -->
        <div class="md:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col items-center text-center">
                <div class="w-24 h-24 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold text-3xl border-4 border-white shadow-md mb-4">
                    <?= strtoupper(substr($teacher['first_name'], 0, 1) . substr($teacher['last_name'], 0, 1)) ?>
                </div>
                <h3 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) ?></h3>
                <p class="text-sm font-bold text-indigo-600 mt-1"><?= htmlspecialchars($teacher['specialty']) ?></p>
                <div class="mt-3 inline-block px-3 py-1 rounded-full text-xs font-bold <?= $teacher['status'] ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' ?>">
                    <?= $teacher['status'] ? 'ACTIVO' : 'INACTIVO' ?>
                </div>
                
                <div class="w-full mt-6 space-y-3 text-left">
                    <div class="flex items-center space-x-3 text-sm text-slate-600">
                        <i class="fa-solid fa-id-card text-slate-400 w-5"></i>
                        <span><?= htmlspecialchars($teacher['document']) ?></span>
                    </div>
                    <div class="flex items-center space-x-3 text-sm text-slate-600">
                        <i class="fa-solid fa-envelope text-slate-400 w-5"></i>
                        <span><?= htmlspecialchars($teacher['email']) ?></span>
                    </div>
                    <div class="flex items-center space-x-3 text-sm text-slate-600">
                        <i class="fa-solid fa-phone text-slate-400 w-5"></i>
                        <span><?= htmlspecialchars($teacher['phone'] ?? 'No registrado') ?></span>
                    </div>
                    <div class="flex items-center space-x-3 text-sm text-slate-600">
                        <i class="fa-solid fa-calendar-days text-slate-400 w-5"></i>
                        <span>Miembro desde: <?= date('d/m/Y', strtotime($teacher['created_at'])) ?></span>
                    </div>
                </div>

                <div class="w-full mt-6 grid grid-cols-2 gap-2">
                    <a href="/modules/teachers/edit.php?id=<?= $teacher['id'] ?>" class="text-center bg-slate-100 hover:bg-slate-200 text-slate-700 py-2 rounded-xl font-bold text-sm transition">
                        Editar
                    </a>
                    <a href="/modules/teachers/delete.php?id=<?= $teacher['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar a este docente?');" class="text-center bg-red-50 hover:bg-red-100 text-red-600 py-2 rounded-xl font-bold text-sm transition">
                        Eliminar
                    </a>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Clases y Carga Académica -->
        <div class="md:col-span-2 space-y-6">
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="/modules/teachers/classes.php?id=<?= $teacher['id'] ?>" class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 hover:border-indigo-200 hover:shadow-md transition flex flex-col items-center text-center">
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-xl mb-3">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <h4 class="font-bold text-slate-800">Materias Asignadas</h4>
                    <p class="text-xs text-slate-500 mt-1"><?= count($subjects) ?> materias en total</p>
                </a>
                
                <a href="/modules/teachers/schedule.php?id=<?= $teacher['id'] ?>" class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 hover:border-indigo-200 hover:shadow-md transition flex flex-col items-center text-center">
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-xl mb-3">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <h4 class="font-bold text-slate-800">Horario de Clases</h4>
                    <p class="text-xs text-slate-500 mt-1">Ver disponibilidad semanal</p>
                </a>
                
                <a href="/modules/teachers/performance.php?id=<?= $teacher['id'] ?>" class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 hover:border-indigo-200 hover:shadow-md transition flex flex-col items-center text-center">
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-xl mb-3">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <h4 class="font-bold text-slate-800">Desempeño</h4>
                    <p class="text-xs text-slate-500 mt-1">Métricas y evaluaciones</p>
                </a>
            </div>

            <!-- Resumen de Materias -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-3">Carga Académica Actual</h3>
                
                <?php if (empty($subjects)): ?>
                    <div class="text-center py-6 text-slate-400">
                        <i class="fa-solid fa-book-open text-3xl mb-2"></i>
                        <p class="text-sm">El docente no tiene materias asignadas actualmente.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach($subjects as $sub): ?>
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <div>
                                    <h4 class="font-bold text-slate-700"><?= htmlspecialchars($sub['name']) ?></h4>
                                    <p class="text-xs text-slate-500 font-medium"><?= htmlspecialchars($sub['course_name']) ?></p>
                                </div>
                                <div class="bg-white px-3 py-1 rounded-lg border border-slate-200 text-xs font-bold text-slate-600 shadow-sm">
                                    <?= htmlspecialchars($sub['weekly_hours']) ?> Hrs/Semana
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
