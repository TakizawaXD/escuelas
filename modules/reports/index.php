<?php
// /modules/reports/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();

// Datos simulados/estáticos basados en consultas para demostración visual
$total_students = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_teachers = $db->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
$active_courses = $db->query("SELECT COUNT(*) FROM courses WHERE is_active = 1")->fetchColumn();
$total_revenue = $db->query("SELECT SUM(amount) FROM payments WHERE status = 'Pagado'")->fetchColumn() ?? 0;

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Centro de Reportes</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Métricas clave, indicadores y exportación de datos globales.</p>
        </div>
        <div class="flex space-x-3">
            <button class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-indigo-600 px-4 py-2 rounded-xl text-sm font-bold transition flex items-center shadow-sm">
                <i class="fa-solid fa-file-csv mr-2"></i> CSV
            </button>
            <button class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl text-sm font-bold transition flex items-center shadow-lg shadow-indigo-500/20">
                <i class="fa-solid fa-file-pdf mr-2"></i> Reporte PDF
            </button>
        </div>
    </div>

    <!-- KPIs Principales -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-center relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-indigo-50 rounded-bl-full -mr-4 -mt-4 opacity-50 group-hover:scale-110 transition-transform"></div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 relative z-10">Matrícula Activa</p>
            <h3 class="text-3xl font-black text-slate-800 relative z-10"><?= $total_students ?></h3>
            <p class="text-xs font-semibold text-emerald-500 mt-2 flex items-center relative z-10"><i class="fa-solid fa-arrow-trend-up mr-1"></i> +5% este mes</p>
        </div>
        
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-center relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-amber-50 rounded-bl-full -mr-4 -mt-4 opacity-50 group-hover:scale-110 transition-transform"></div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 relative z-10">Docentes</p>
            <h3 class="text-3xl font-black text-slate-800 relative z-10"><?= $total_teachers ?></h3>
            <p class="text-xs font-semibold text-slate-400 mt-2 flex items-center relative z-10"><i class="fa-solid fa-minus mr-1"></i> Estable</p>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-center relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-emerald-50 rounded-bl-full -mr-4 -mt-4 opacity-50 group-hover:scale-110 transition-transform"></div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 relative z-10">Ingresos (Pagado)</p>
            <h3 class="text-3xl font-black text-slate-800 relative z-10">$<?= number_format($total_revenue, 2) ?></h3>
            <p class="text-xs font-semibold text-emerald-500 mt-2 flex items-center relative z-10"><i class="fa-solid fa-arrow-trend-up mr-1"></i> +12% vs año pasado</p>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-center relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-red-50 rounded-bl-full -mr-4 -mt-4 opacity-50 group-hover:scale-110 transition-transform"></div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 relative z-10">Cursos Activos</p>
            <h3 class="text-3xl font-black text-slate-800 relative z-10"><?= $active_courses ?></h3>
            <p class="text-xs font-semibold text-slate-400 mt-2 flex items-center relative z-10"><i class="fa-solid fa-check-double mr-1"></i> Actualizado</p>
        </div>
    </div>

    <!-- Secciones de Reportes -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Reporte Académico -->
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 lg:col-span-2">
            <div class="flex items-center justify-between mb-6 border-b border-slate-100 pb-4">
                <h3 class="font-bold text-slate-800 text-lg">Distribución de Notas (Simulación)</h3>
                <i class="fa-solid fa-graduation-cap text-slate-300 text-2xl"></i>
            </div>
            
            <div class="space-y-6">
                <!-- Bar 1 -->
                <div>
                    <div class="flex justify-between text-sm font-bold text-slate-700 mb-1">
                        <span>Excelente (9.0 - 10.0)</span>
                        <span>45%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-3">
                        <div class="bg-emerald-500 h-3 rounded-full" style="width: 45%"></div>
                    </div>
                </div>
                <!-- Bar 2 -->
                <div>
                    <div class="flex justify-between text-sm font-bold text-slate-700 mb-1">
                        <span>Bueno (8.0 - 8.9)</span>
                        <span>35%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-3">
                        <div class="bg-blue-500 h-3 rounded-full" style="width: 35%"></div>
                    </div>
                </div>
                <!-- Bar 3 -->
                <div>
                    <div class="flex justify-between text-sm font-bold text-slate-700 mb-1">
                        <span>Regular (6.0 - 7.9)</span>
                        <span>15%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-3">
                        <div class="bg-amber-500 h-3 rounded-full" style="width: 15%"></div>
                    </div>
                </div>
                <!-- Bar 4 -->
                <div>
                    <div class="flex justify-between text-sm font-bold text-slate-700 mb-1">
                        <span>Deficiente (0.0 - 5.9)</span>
                        <span>5%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-3">
                        <div class="bg-red-500 h-3 rounded-full" style="width: 5%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Descargas Rápidas -->
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 lg:col-span-1">
            <h3 class="font-bold text-slate-800 text-lg mb-6 border-b border-slate-100 pb-4">Reportes Listos</h3>
            <div class="space-y-4">
                <a href="#" class="flex items-center space-x-4 p-4 border border-slate-100 bg-slate-50 rounded-2xl hover:bg-indigo-50 hover:border-indigo-200 transition group">
                    <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center font-bold flex-shrink-0 group-hover:scale-110 transition">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-700">Padrón de Alumnos</h4>
                        <p class="text-xs text-slate-500">Listado completo</p>
                    </div>
                </a>
                
                <a href="#" class="flex items-center space-x-4 p-4 border border-slate-100 bg-slate-50 rounded-2xl hover:bg-emerald-50 hover:border-emerald-200 transition group">
                    <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center font-bold flex-shrink-0 group-hover:scale-110 transition">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-700">Estado de Cartera</h4>
                        <p class="text-xs text-slate-500">Saldos pendientes</p>
                    </div>
                </a>
                
                <a href="#" class="flex items-center space-x-4 p-4 border border-slate-100 bg-slate-50 rounded-2xl hover:bg-amber-50 hover:border-amber-200 transition group">
                    <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center font-bold flex-shrink-0 group-hover:scale-110 transition">
                        <i class="fa-solid fa-clipboard-user"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-700">Asistencia Global</h4>
                        <p class="text-xs text-slate-500">Inasistencias del mes</p>
                    </div>
                </a>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
