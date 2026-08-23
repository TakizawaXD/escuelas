<?php
// /modules/teachers/performance.php
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

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-5xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/teachers/view.php?id=<?= $id ?>" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Evaluación de Desempeño</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Docente: <?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Score General -->
        <div class="md:col-span-1 bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex flex-col items-center text-center justify-center">
            <h3 class="text-lg font-bold text-slate-700 mb-6">Puntaje Global</h3>
            <div class="relative w-40 h-40">
                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                    <path class="text-slate-100" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    <path class="text-emerald-500" stroke-dasharray="85, 100" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-4xl font-extrabold text-slate-800">4.2</span>
                    <span class="text-xs font-bold text-slate-400">de 5.0</span>
                </div>
            </div>
            <p class="mt-6 text-sm text-slate-500 font-medium px-4">
                El desempeño general se considera <strong class="text-emerald-600">Sobresaliente</strong> este semestre.
            </p>
        </div>

        <!-- Métricas detalladas -->
        <div class="md:col-span-2 bg-white p-8 rounded-3xl shadow-sm border border-slate-100 space-y-6">
            <h3 class="text-lg font-bold text-slate-700 border-b border-slate-100 pb-3">Desglose de Evaluación</h3>
            
            <div class="space-y-4">
                
                <div>
                    <div class="flex justify-between items-end mb-1">
                        <span class="text-sm font-bold text-slate-700">Puntualidad y Asistencia</span>
                        <span class="text-sm font-bold text-slate-900">95%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5">
                        <div class="bg-indigo-500 h-2.5 rounded-full" style="width: 95%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-end mb-1">
                        <span class="text-sm font-bold text-slate-700">Calidad del Material Didáctico</span>
                        <span class="text-sm font-bold text-slate-900">88%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5">
                        <div class="bg-indigo-500 h-2.5 rounded-full" style="width: 88%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-end mb-1">
                        <span class="text-sm font-bold text-slate-700">Evaluación de Estudiantes</span>
                        <span class="text-sm font-bold text-slate-900">82%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5">
                        <div class="bg-indigo-500 h-2.5 rounded-full" style="width: 82%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-end mb-1">
                        <span class="text-sm font-bold text-slate-700">Participación Institucional</span>
                        <span class="text-sm font-bold text-slate-900">75%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5">
                        <div class="bg-amber-400 h-2.5 rounded-full" style="width: 75%"></div>
                    </div>
                </div>

            </div>

            <div class="mt-6 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <h4 class="text-sm font-bold text-slate-700 mb-2">Comentarios de Coordinación</h4>
                <p class="text-sm text-slate-600 italic">
                    "Excelente manejo de grupo. Se recomienda mayor integración en las actividades extracurriculares del colegio."
                </p>
            </div>
            
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
