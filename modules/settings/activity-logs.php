<?php
// /modules/settings/activity-logs.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN'])) {
    header("Location: /index.php");
    exit;
}

// Simulando registros
$logs = [
    ['user' => 'Admin Principal', 'action' => 'Eliminó el usuario ID: 15', 'ip' => '192.168.1.5', 'date' => date('Y-m-d H:i', strtotime('-10 minutes'))],
    ['user' => 'Coordinador Juan', 'action' => 'Creó un nuevo estudiante', 'ip' => '192.168.1.10', 'date' => date('Y-m-d H:i', strtotime('-2 hours'))],
    ['user' => 'Finanzas Maria', 'action' => 'Exportó los recibos a CSV', 'ip' => '10.0.0.4', 'date' => date('Y-m-d H:i', strtotime('-5 hours'))],
    ['user' => 'Admin Principal', 'action' => 'Modificó la escala de calificaciones', 'ip' => '192.168.1.5', 'date' => date('Y-m-d H:i', strtotime('-1 day'))],
];

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center space-x-4">
            <a href="/modules/settings/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Registro de Auditoría</h2>
                <p class="text-slate-500 font-medium text-sm mt-1">Historial de acciones sensibles realizadas por los usuarios.</p>
            </div>
        </div>
        <button class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2 rounded-xl text-sm font-bold transition">
            <i class="fa-solid fa-download mr-1"></i> Exportar Logs
        </button>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50">
                <tr>
                    <th class="py-4 px-6 font-bold text-slate-500 text-sm border-b border-slate-100">Fecha y Hora</th>
                    <th class="py-4 px-6 font-bold text-slate-500 text-sm border-b border-slate-100">Usuario</th>
                    <th class="py-4 px-6 font-bold text-slate-500 text-sm border-b border-slate-100">Acción Realizada</th>
                    <th class="py-4 px-6 font-bold text-slate-500 text-sm border-b border-slate-100 text-right">Dirección IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                <?php foreach($logs as $log): ?>
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-4 px-6 font-medium text-slate-500 whitespace-nowrap"><?= $log['date'] ?></td>
                    <td class="py-4 px-6 font-bold text-slate-800"><?= htmlspecialchars($log['user']) ?></td>
                    <td class="py-4 px-6 font-medium"><?= htmlspecialchars($log['action']) ?></td>
                    <td class="py-4 px-6 text-right font-mono text-xs text-slate-500"><?= $log['ip'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
