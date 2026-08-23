<?php
// /modules/attendance/justification.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
Auth::redirectIfNotAuth();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>
<div class="space-y-6 animate-fade-in max-w-4xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/attendance/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Justificación de Faltas</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Registra incapacidades médicas o excusas válidas.</p>
        </div>
    </div>
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex flex-col items-center justify-center py-20 text-center">
        <i class="fa-solid fa-person-digging text-6xl text-slate-300 mb-4"></i>
        <h3 class="text-xl font-bold text-slate-700 mb-2">Próximamente</h3>
        <p class="text-slate-500 max-w-md">La gestión de justificaciones documentadas (PDF, imágenes médicas) estará disponible pronto.</p>
    </div>
</div>
<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
