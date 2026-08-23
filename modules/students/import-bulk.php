<?php
// /modules/students/import-bulk.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    header("Location: /index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));
        if ($ext === 'csv') {
            // Lógica simulada de importación
            $success = "El archivo CSV ha sido procesado exitosamente. Se importaron 45 estudiantes.";
        } else {
            $error = "Por favor, sube un archivo en formato CSV.";
        }
    } else {
        $error = "Ocurrió un error al subir el archivo.";
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-4xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/students/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Importación Masiva</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Sube un archivo CSV para matricular estudiantes en lote.</p>
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
        <div class="mb-6 flex justify-between items-center bg-indigo-50 p-4 rounded-xl border border-indigo-100">
            <div>
                <h4 class="font-bold text-indigo-800">Plantilla CSV</h4>
                <p class="text-xs text-indigo-600 mt-1">Descarga el archivo de ejemplo para conocer el formato requerido.</p>
            </div>
            <a href="#" class="bg-white text-indigo-600 px-4 py-2 rounded-lg font-bold text-sm shadow-sm border border-indigo-200 hover:bg-indigo-60 transition">
                <i class="fa-solid fa-download mr-1"></i> Descargar Plantilla
            </a>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="border-2 border-dashed border-slate-300 rounded-2xl p-10 text-center hover:bg-slate-50 transition cursor-pointer relative">
                <input type="file" name="csv_file" accept=".csv" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                <i class="fa-solid fa-file-csv text-5xl text-slate-300 mb-3"></i>
                <h4 class="font-bold text-slate-700 text-lg">Arrastra tu archivo CSV aquí</h4>
                <p class="text-sm text-slate-500 mt-1">o haz clic para explorar tus archivos</p>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-xl font-bold tracking-wide transition shadow-lg shadow-indigo-500/20 active:scale-[0.98]">
                    <i class="fa-solid fa-upload mr-2"></i> Importar Datos
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
