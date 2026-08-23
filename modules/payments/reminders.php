<?php
// /modules/payments/reminders.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'FINANCIERO'])) {
    header("Location: /index.php");
    exit;
}

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulación del envío de correos de recordatorio
    $success = "Se han enviado recordatorios a todos los acudientes con pagos atrasados.";
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-3xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/payments/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Recordatorios de Pago</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Gestión de alertas y notificaciones a morosos.</p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="bg-emerald-50 text-emerald-600 px-4 py-3 rounded-xl flex items-center space-x-3 border border-emerald-200">
            <i class="fa-solid fa-circle-check"></i>
            <span class="font-medium text-sm"><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 text-center">
        <div class="w-24 h-24 bg-red-50 text-red-500 rounded-full flex items-center justify-center text-4xl mx-auto mb-6">
            <i class="fa-solid fa-bell-slash"></i>
        </div>
        <h3 class="text-2xl font-bold text-slate-800 mb-2">Enviar Notificaciones Masivas</h3>
        <p class="text-slate-600 mb-8 max-w-md mx-auto">El sistema detectará a todos los usuarios con cobros en estado "Atrasado" y enviará un correo electrónico automático de recordatorio.</p>
        
        <form method="POST">
            <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-8 py-3 rounded-xl font-bold tracking-wide transition shadow-lg shadow-red-500/20 active:scale-[0.98]">
                <i class="fa-solid fa-paper-plane mr-2"></i> Enviar Recordatorios Ahora
            </button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
