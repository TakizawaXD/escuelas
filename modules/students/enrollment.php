<?php
// /modules/students/enrollment.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    header("Location: /index.php");
    exit;
}

// Para propósitos del mockup, esta página simularía un wizard paso a paso de matrícula.
$step = $_GET['step'] ?? 1;

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-4xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/students/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Proceso de Matrícula</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Asistente paso a paso para nuevo ingreso.</p>
        </div>
    </div>

    <!-- Stepper -->
    <div class="flex items-center justify-between relative mb-8">
        <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-slate-200 z-0 rounded-full"></div>
        <div class="absolute left-0 top-1/2 transform -translate-y-1/2 h-1 bg-indigo-600 z-0 rounded-full transition-all duration-500" style="width: <?= ($step - 1) * 50 ?>%;"></div>
        
        <div class="relative z-10 flex flex-col items-center">
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold border-4 border-white shadow-sm transition-colors duration-300 <?= $step >= 1 ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-400' ?>">1</div>
            <span class="text-xs font-bold mt-2 text-slate-600">Datos</span>
        </div>
        <div class="relative z-10 flex flex-col items-center">
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold border-4 border-white shadow-sm transition-colors duration-300 <?= $step >= 2 ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-400' ?>">2</div>
            <span class="text-xs font-bold mt-2 text-slate-600">Acudiente</span>
        </div>
        <div class="relative z-10 flex flex-col items-center">
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold border-4 border-white shadow-sm transition-colors duration-300 <?= $step >= 3 ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-400' ?>">3</div>
            <span class="text-xs font-bold mt-2 text-slate-600">Documentos</span>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <?php if ($step == 1): ?>
            <h3 class="text-xl font-bold text-slate-800 mb-6">Paso 1: Datos Básicos del Estudiante</h3>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nombres</label>
                        <input type="text" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Apellidos</label>
                        <input type="text" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50">
                    </div>
                </div>
            </div>
            <div class="mt-8 flex justify-end">
                <a href="?step=2" class="bg-indigo-600 text-white px-6 py-2 rounded-xl font-bold">Siguiente <i class="fa-solid fa-arrow-right ml-2"></i></a>
            </div>
            
        <?php elseif ($step == 2): ?>
            <h3 class="text-xl font-bold text-slate-800 mb-6">Paso 2: Información del Acudiente</h3>
            <div class="space-y-4">
                <p class="text-sm text-slate-500">Busca un acudiente existente o crea uno nuevo.</p>
                <div class="relative">
                    <input type="text" placeholder="Buscar por número de documento..." class="w-full pl-10 pr-4 py-3 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50">
                    <i class="fa-solid fa-search absolute left-4 top-3.5 text-slate-400"></i>
                </div>
            </div>
            <div class="mt-8 flex justify-between">
                <a href="?step=1" class="text-slate-500 hover:text-slate-800 px-4 py-2 font-bold"><i class="fa-solid fa-arrow-left mr-2"></i> Atrás</a>
                <a href="?step=3" class="bg-indigo-600 text-white px-6 py-2 rounded-xl font-bold">Siguiente <i class="fa-solid fa-arrow-right ml-2"></i></a>
            </div>
            
        <?php else: ?>
            <h3 class="text-xl font-bold text-slate-800 mb-6">Paso 3: Carga de Documentos</h3>
            <div class="border-2 border-dashed border-slate-200 rounded-2xl p-8 text-center hover:bg-slate-50 transition cursor-pointer">
                <i class="fa-solid fa-cloud-arrow-up text-4xl text-indigo-400 mb-2"></i>
                <p class="font-bold text-slate-700">Arrastra archivos aquí o haz clic para subir</p>
                <p class="text-xs text-slate-400 mt-1">DNI, Certificados, Fotos tamaño carnet</p>
            </div>
            <div class="mt-8 flex justify-between">
                <a href="?step=2" class="text-slate-500 hover:text-slate-800 px-4 py-2 font-bold"><i class="fa-solid fa-arrow-left mr-2"></i> Atrás</a>
                <a href="/modules/students/index.php" class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-2 rounded-xl font-bold">Finalizar Matrícula <i class="fa-solid fa-check ml-2"></i></a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
