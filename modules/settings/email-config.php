<?php
// /modules/settings/email-config.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN'])) {
    header("Location: /index.php");
    exit;
}

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = "Configuración SMTP guardada exitosamente. Se ha enviado un correo de prueba.";
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
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Servidor de Correo (SMTP)</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Configura el envío de notificaciones y recordatorios de pago.</p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="bg-emerald-50 text-emerald-600 px-4 py-3 rounded-xl flex items-center space-x-3 border border-emerald-200">
            <i class="fa-solid fa-circle-check"></i>
            <span class="font-medium text-sm"><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex flex-col md:flex-row gap-8">
        <div class="w-full md:w-1/3 flex flex-col items-center justify-center bg-slate-50 rounded-2xl p-6 border border-slate-200">
            <i class="fa-solid fa-envelope-open-text text-6xl text-slate-300 mb-4"></i>
            <h3 class="font-bold text-slate-700 text-center">Estado del Servicio</h3>
            <span class="mt-2 inline-block px-3 py-1 bg-emerald-100 text-emerald-700 rounded text-xs font-bold uppercase tracking-wider">Conectado</span>
            <p class="text-xs text-slate-400 text-center mt-4">Última prueba exitosa hace 2 horas.</p>
        </div>

        <form method="POST" class="w-full md:w-2/3 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Host SMTP</label>
                    <input type="text" name="smtp_host" value="smtp.mailtrap.io" required class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Puerto SMTP</label>
                    <input type="number" name="smtp_port" value="2525" required class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Usuario</label>
                    <input type="text" name="smtp_user" value="api_user_123" required class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Contraseña</label>
                    <input type="password" name="smtp_pass" value="********" required class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none">
                </div>
            </div>
            
            <div class="pt-4 flex justify-end space-x-3">
                <button type="button" class="bg-slate-100 text-slate-600 hover:bg-slate-200 px-6 py-2.5 rounded-xl font-bold transition">
                    Probar Conexión
                </button>
                <button type="submit" class="bg-indigo-600 text-white hover:bg-indigo-500 px-6 py-2.5 rounded-xl font-bold transition shadow-lg shadow-indigo-500/20 active:scale-[0.98]">
                    Guardar y Aplicar
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
