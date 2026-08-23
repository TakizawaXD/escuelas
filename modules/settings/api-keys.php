<?php
// /modules/settings/api-keys.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN'])) {
    header("Location: /index.php");
    exit;
}

// Simulando API Keys
$api_keys = [
    ['id' => 1, 'name' => 'Integración Moodle', 'key' => 'sk_live_moodle_8f92j3n4...', 'last_used' => 'Hace 2 horas', 'status' => 'Activa'],
    ['id' => 2, 'name' => 'App Móvil Padres', 'key' => 'sk_live_mobile_4j9s82m1...', 'last_used' => 'Hace 5 minutos', 'status' => 'Activa'],
    ['id' => 3, 'name' => 'Legacy CRM', 'key' => 'sk_test_crm_1l0s9d8f...', 'last_used' => 'Hace 2 meses', 'status' => 'Revocada'],
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
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">API Keys</h2>
                <p class="text-slate-500 font-medium text-sm mt-1">Gestiona las llaves para integraciones externas y apps web.</p>
            </div>
        </div>
        <button class="bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2.5 rounded-xl font-bold transition shadow-lg shadow-indigo-500/20 active:scale-[0.98]">
            <i class="fa-solid fa-plus mr-1"></i> Generar Nueva API Key
        </button>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50">
                <tr>
                    <th class="py-4 px-6 font-bold text-slate-500 text-sm border-b border-slate-100">Nombre de Integración</th>
                    <th class="py-4 px-6 font-bold text-slate-500 text-sm border-b border-slate-100">Clave (Token)</th>
                    <th class="py-4 px-6 font-bold text-slate-500 text-sm border-b border-slate-100">Último Uso</th>
                    <th class="py-4 px-6 font-bold text-slate-500 text-sm border-b border-slate-100 text-center">Estado</th>
                    <th class="py-4 px-6 font-bold text-slate-500 text-sm border-b border-slate-100 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                <?php foreach($api_keys as $key): ?>
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-4 px-6 font-bold text-slate-800"><?= htmlspecialchars($key['name']) ?></td>
                    <td class="py-4 px-6 font-mono text-xs text-slate-400">
                        <?= $key['status'] === 'Activa' ? htmlspecialchars($key['key']) : '<del class="text-slate-300">'.htmlspecialchars($key['key']).'</del>' ?>
                    </td>
                    <td class="py-4 px-6 font-medium text-slate-500"><?= htmlspecialchars($key['last_used']) ?></td>
                    <td class="py-4 px-6 text-center">
                        <?php if ($key['status'] === 'Activa'): ?>
                            <span class="inline-block px-2.5 py-1 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-lg text-xs font-bold uppercase tracking-wider">Activa</span>
                        <?php else: ?>
                            <span class="inline-block px-2.5 py-1 bg-red-50 text-red-600 border border-red-100 rounded-lg text-xs font-bold uppercase tracking-wider">Revocada</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-4 px-6 text-right space-x-2">
                        <?php if ($key['status'] === 'Activa'): ?>
                            <button title="Revocar" class="text-red-500 hover:text-red-700 p-2"><i class="fa-solid fa-ban"></i></button>
                        <?php endif; ?>
                        <button title="Eliminar" class="text-slate-400 hover:text-red-600 p-2"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
