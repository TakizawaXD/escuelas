<?php
// /modules/payments/reconciliation.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'FINANCIERO'])) {
    header("Location: /index.php");
    exit;
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-4xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/payments/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Conciliación Bancaria</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Importa los extractos bancarios para cruzar pagos.</p>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 text-center">
        <div class="w-20 h-20 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center text-4xl mx-auto mb-6">
            <i class="fa-solid fa-building-columns"></i>
        </div>
        <h3 class="text-xl font-bold text-slate-700 mb-2">Próximamente: Motor de Conciliación</h3>
        <p class="text-slate-500 max-w-md mx-auto mb-8">Esta herramienta te permitirá subir archivos de transacciones bancarias (MT940 o CSV) para identificar y marcar pagos automáticamente como "Pagados".</p>
        
        <div class="border-2 border-dashed border-slate-200 rounded-2xl p-8 bg-slate-50 opacity-50 cursor-not-allowed">
            <i class="fa-solid fa-file-import text-4xl text-slate-300 mb-2"></i>
            <p class="font-bold text-slate-500">Arrastra tu extracto bancario aquí</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
