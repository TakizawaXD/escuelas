<?php
// /modules/settings/academic-periods.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    header("Location: /index.php");
    exit;
}

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = "El período académico ha sido configurado correctamente para 2026-2027.";
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-4xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/settings/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Períodos Académicos</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Configura semestres, trimestres y fechas de evaluación.</p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="bg-emerald-50 text-emerald-600 px-4 py-3 rounded-xl flex items-center space-x-3 border border-emerald-200">
            <i class="fa-solid fa-circle-check"></i>
            <span class="font-medium text-sm"><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre del Período</label>
                    <input type="text" name="name" value="Ciclo 2026-2027" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50 text-slate-800 font-medium">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Estado</label>
                    <select class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50 text-emerald-600 font-bold">
                        <option>ACTIVO (En curso)</option>
                        <option>CERRADO</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha de Inicio</label>
                    <input type="date" value="2026-08-15" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha de Finalización</label>
                    <input type="date" value="2027-06-30" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50">
                </div>
            </div>
            
            <hr class="border-slate-100 my-4">

            <div>
                <h4 class="font-bold text-slate-800 mb-4">Cortes Evaluativos (Trimestres/Bimestres)</h4>
                <div class="space-y-3">
                    <div class="flex items-center space-x-4">
                        <input type="text" value="Primer Trimestre" class="w-1/3 px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 text-sm">
                        <input type="date" class="w-1/3 px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 text-sm">
                        <input type="date" class="w-1/3 px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 text-sm">
                    </div>
                    <div class="flex items-center space-x-4">
                        <input type="text" value="Segundo Trimestre" class="w-1/3 px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 text-sm">
                        <input type="date" class="w-1/3 px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 text-sm">
                        <input type="date" class="w-1/3 px-3 py-2 border border-slate-200 rounded-lg bg-slate-50 text-sm">
                    </div>
                    <button type="button" class="text-indigo-600 text-sm font-bold mt-2 hover:underline"><i class="fa-solid fa-plus"></i> Añadir Corte</button>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-xl font-bold tracking-wide transition shadow-lg shadow-indigo-500/20 active:scale-[0.98]">
                    <i class="fa-solid fa-save mr-2"></i> Guardar Configuración
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
