<?php
// /modules/payments/reports.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'FINANCIERO'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();

// Lógica para reportes financieros
$stmt = $db->query("
    SELECT status, COUNT(*) as count, SUM(amount) as total 
    FROM payments 
    GROUP BY status
");
$stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-5xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/payments/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Reportes Financieros</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Análisis de ingresos y estado de cartera.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Ingresos Recibidos -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center space-x-4">
            <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl font-bold">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">Recaudado</p>
                <p class="text-2xl font-black text-slate-800">
                    $<?= number_format($stats['Pagado']['total'] ?? 0, 2) ?>
                </p>
            </div>
        </div>

        <!-- Cartera Pendiente -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center space-x-4">
            <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-2xl font-bold">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">Pendiente</p>
                <p class="text-2xl font-black text-slate-800">
                    $<?= number_format($stats['Pendiente']['total'] ?? 0, 2) ?>
                </p>
            </div>
        </div>

        <!-- Cartera Vencida -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center space-x-4">
            <div class="w-14 h-14 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center text-2xl font-bold">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">Vencido / Mora</p>
                <p class="text-2xl font-black text-slate-800">
                    $<?= number_format($stats['Atrasado']['total'] ?? 0, 2) ?>
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 mt-6 flex flex-col items-center justify-center py-16 text-center">
        <i class="fa-solid fa-chart-column text-6xl text-slate-200 mb-4"></i>
        <h3 class="text-xl font-bold text-slate-700 mb-2">Gráficos Avanzados Pronto</h3>
        <p class="text-slate-500 max-w-md">La integración con Chart.js para visualizar el flujo de caja estará disponible en la próxima actualización.</p>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
