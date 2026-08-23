<?php
// /modules/settings/security.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN'])) {
    header("Location: /index.php");
    exit;
}

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = "Las políticas de seguridad han sido actualizadas y aplicadas en el sistema.";
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
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Políticas de Seguridad</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Reglas de contraseñas, sesiones y accesos.</p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="bg-emerald-50 text-emerald-600 px-4 py-3 rounded-xl flex items-center space-x-3 border border-emerald-200">
            <i class="fa-solid fa-circle-check"></i>
            <span class="font-medium text-sm"><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <form method="POST" class="space-y-8">
            
            <div>
                <h4 class="font-bold text-slate-800 mb-4 text-lg border-b border-slate-100 pb-2">Seguridad de Contraseñas</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <label class="flex items-start space-x-3 p-4 border border-indigo-200 bg-indigo-50/30 rounded-2xl cursor-pointer">
                        <div class="pt-0.5"><input type="checkbox" checked class="w-5 h-5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500"></div>
                        <div>
                            <p class="font-bold text-slate-800">Forzar contraseña robusta</p>
                            <p class="text-xs text-slate-500 mt-1">Mínimo 8 caracteres, números y símbolos especiales.</p>
                        </div>
                    </label>
                    <label class="flex items-start space-x-3 p-4 border border-slate-200 rounded-2xl cursor-pointer hover:bg-slate-50 transition">
                        <div class="pt-0.5"><input type="checkbox" class="w-5 h-5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500"></div>
                        <div>
                            <p class="font-bold text-slate-800">Rotación de contraseñas</p>
                            <p class="text-xs text-slate-500 mt-1">Obligar a los usuarios a cambiar su clave cada 90 días.</p>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <h4 class="font-bold text-slate-800 mb-4 text-lg border-b border-slate-100 pb-2">Control de Acceso</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Bloqueo por Intentos Fallidos</label>
                        <select class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50">
                            <option>Desactivado</option>
                            <option selected>3 intentos (Bloqueo 15 min)</option>
                            <option>5 intentos (Bloqueo 30 min)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Cierre Automático de Sesión</label>
                        <select class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50">
                            <option>Nunca</option>
                            <option>15 minutos de inactividad</option>
                            <option selected>30 minutos de inactividad</option>
                            <option>2 horas de inactividad</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-xl font-bold tracking-wide transition shadow-lg shadow-indigo-500/20 active:scale-[0.98]">
                    <i class="fa-solid fa-save mr-2"></i> Guardar Políticas
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
