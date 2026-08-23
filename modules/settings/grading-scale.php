<?php
// /modules/settings/grading-scale.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    header("Location: /index.php");
    exit;
}

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = "La escala de calificaciones ha sido actualizada.";
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
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Escala de Calificaciones</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Configura los rangos de notas y su equivalencia cualitativa.</p>
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
            <div class="flex items-center justify-between mb-2 border-b border-slate-100 pb-2">
                <span class="w-1/4 font-bold text-slate-600 text-sm uppercase">Nota Mínima</span>
                <span class="w-1/4 font-bold text-slate-600 text-sm uppercase">Nota Máxima</span>
                <span class="w-1/4 font-bold text-slate-600 text-sm uppercase">Etiqueta</span>
                <span class="w-1/4 font-bold text-slate-600 text-sm uppercase text-right">Color</span>
            </div>
            
            <div class="space-y-4">
                <div class="flex items-center space-x-4">
                    <input type="number" step="0.1" value="0.0" class="w-1/4 px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 text-center font-medium">
                    <input type="number" step="0.1" value="5.9" class="w-1/4 px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 text-center font-medium">
                    <input type="text" value="Deficiente" class="w-1/4 px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 font-medium">
                    <div class="w-1/4 flex justify-end"><input type="color" value="#ef4444" class="h-10 w-20 px-1 border border-slate-200 rounded-lg cursor-pointer"></div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <input type="number" step="0.1" value="6.0" class="w-1/4 px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 text-center font-medium">
                    <input type="number" step="0.1" value="7.9" class="w-1/4 px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 text-center font-medium">
                    <input type="text" value="Regular" class="w-1/4 px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 font-medium">
                    <div class="w-1/4 flex justify-end"><input type="color" value="#f59e0b" class="h-10 w-20 px-1 border border-slate-200 rounded-lg cursor-pointer"></div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <input type="number" step="0.1" value="8.0" class="w-1/4 px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 text-center font-medium">
                    <input type="number" step="0.1" value="8.9" class="w-1/4 px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 text-center font-medium">
                    <input type="text" value="Bueno" class="w-1/4 px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 font-medium">
                    <div class="w-1/4 flex justify-end"><input type="color" value="#3b82f6" class="h-10 w-20 px-1 border border-slate-200 rounded-lg cursor-pointer"></div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <input type="number" step="0.1" value="9.0" class="w-1/4 px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 text-center font-medium">
                    <input type="number" step="0.1" value="10.0" class="w-1/4 px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 text-center font-medium">
                    <input type="text" value="Excelente" class="w-1/4 px-4 py-2 border border-slate-200 rounded-xl bg-slate-50 font-medium">
                    <div class="w-1/4 flex justify-end"><input type="color" value="#10b981" class="h-10 w-20 px-1 border border-slate-200 rounded-lg cursor-pointer"></div>
                </div>
            </div>

            <button type="button" class="text-indigo-600 text-sm font-bold mt-2 hover:underline"><i class="fa-solid fa-plus"></i> Añadir Rango</button>

            <div class="pt-6 border-t border-slate-100 flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-xl font-bold tracking-wide transition shadow-lg shadow-indigo-500/20 active:scale-[0.98]">
                    <i class="fa-solid fa-save mr-2"></i> Guardar Escala
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
