<?php
// /modules/grades/reports.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
Auth::redirectIfNotAuth();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>
<div class="space-y-6 animate-fade-in max-w-5xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/grades/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Boletines Académicos</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Generación de boletines por periodo y estudiante.</p>
        </div>
    </div>
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex flex-col items-center justify-center py-20 text-center">
        <div class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center text-4xl mb-4">
            <i class="fa-solid fa-file-invoice"></i>
        </div>
        <h3 class="text-xl font-bold text-slate-700 mb-2">Módulo de Reportes Avanzados</h3>
        <p class="text-slate-500 max-w-md">La generación automática de boletines está programada para la siguiente fase de desarrollo.</p>
    </div>
</div>
<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
