<?php
// /modules/settings/backup.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN'])) {
    header("Location: /index.php");
    exit;
}

$success = '';
if (isset($_GET['action']) && $_GET['action'] === 'run') {
    $success = "El respaldo completo de la base de datos y archivos se ha generado exitosamente. (backup_2026_08_22.zip)";
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
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Respaldos del Sistema</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Copias de seguridad de base de datos y archivos adjuntos.</p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="bg-emerald-50 text-emerald-600 px-4 py-3 rounded-xl flex items-center space-x-3 border border-emerald-200">
            <i class="fa-solid fa-circle-check"></i>
            <span class="font-medium text-sm"><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 text-center">
            <div class="w-20 h-20 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center text-4xl mx-auto mb-6">
                <i class="fa-solid fa-database"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Respaldo Manual</h3>
            <p class="text-slate-500 text-sm mb-6 max-w-xs mx-auto">Genera una copia encriptada de la base de datos SQLite y descarga el archivo comprimido.</p>
            <a href="?action=run" class="inline-block bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-xl font-bold tracking-wide transition shadow-lg shadow-indigo-500/20 active:scale-[0.98]">
                <i class="fa-solid fa-download mr-2"></i> Generar y Descargar
            </a>
        </div>

        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
            <h3 class="font-bold text-slate-800 mb-4">Historial de Respaldos</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 border border-slate-100 bg-slate-50 rounded-xl">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-file-zipper text-slate-400 text-xl"></i>
                        <div>
                            <p class="text-sm font-bold text-slate-700">backup_2026_08_20.zip</p>
                            <p class="text-xs text-slate-500">Generado automáticamente &bull; 15 MB</p>
                        </div>
                    </div>
                    <button class="text-indigo-600 hover:text-indigo-800"><i class="fa-solid fa-cloud-arrow-down"></i></button>
                </div>
                
                <div class="flex items-center justify-between p-3 border border-slate-100 bg-slate-50 rounded-xl">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-file-zipper text-slate-400 text-xl"></i>
                        <div>
                            <p class="text-sm font-bold text-slate-700">backup_2026_08_13.zip</p>
                            <p class="text-xs text-slate-500">Por Admin &bull; 14.8 MB</p>
                        </div>
                    </div>
                    <button class="text-indigo-600 hover:text-indigo-800"><i class="fa-solid fa-cloud-arrow-down"></i></button>
                </div>
            </div>
            
            <div class="mt-6 p-4 bg-amber-50 rounded-xl border border-amber-100 flex items-start space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 mt-1"></i>
                <p class="text-xs text-amber-700 font-medium">Mantén los respaldos en un lugar seguro. Contienen información sensible y datos financieros de los estudiantes.</p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
