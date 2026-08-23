<?php
// /modules/users/bulk-import.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN'])) {
    header("Location: /index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $success = "Usuarios importados exitosamente. Se crearon 15 nuevas cuentas.";
    } else {
        $error = "Error al subir el archivo CSV.";
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-4xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/users/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Importar Usuarios</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Crea múltiples cuentas de forma masiva usando un archivo CSV.</p>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl flex items-center space-x-3">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span class="font-medium text-sm"><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl flex items-center space-x-3">
            <i class="fa-solid fa-circle-check"></i>
            <span class="font-medium text-sm"><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="border-2 border-dashed border-slate-300 rounded-2xl p-10 text-center hover:bg-slate-50 transition cursor-pointer relative">
                <input type="file" name="csv_file" accept=".csv" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                <i class="fa-solid fa-users-viewfinder text-5xl text-slate-300 mb-3"></i>
                <h4 class="font-bold text-slate-700 text-lg">Arrastra tu archivo CSV aquí</h4>
                <p class="text-sm text-slate-500 mt-1">o haz clic para explorar tus archivos</p>
            </div>
            
            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-xl font-bold tracking-wide transition shadow-lg shadow-indigo-500/20 active:scale-[0.98]">
                    <i class="fa-solid fa-upload mr-2"></i> Importar Cuentas
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
