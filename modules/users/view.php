<?php
// /modules/users/view.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN'])) {
    header("Location: /index.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /modules/users/index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("
    SELECT u.*, r.name as role_name 
    FROM users u 
    JOIN roles r ON u.role_id = r.id 
    WHERE u.id = ?
");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: /modules/users/index.php");
    exit;
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
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Perfil de Usuario</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Configuración y estado del acceso al sistema.</p>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex flex-col md:flex-row gap-8">
        <div class="w-full md:w-1/3 flex flex-col items-center text-center">
            <div class="w-28 h-28 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center font-bold text-4xl shadow-sm mb-4">
                <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
            </div>
            <h3 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h3>
            <p class="text-sm font-bold text-indigo-600 mt-1"><?= htmlspecialchars($user['role_name']) ?></p>
            
            <div class="mt-4 flex flex-col gap-2 w-full">
                <span class="inline-block px-3 py-1.5 rounded-lg text-xs font-bold <?= $user['status'] ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-red-50 text-red-600 border border-red-100' ?>">
                    <i class="fa-solid <?= $user['status'] ? 'fa-check-circle' : 'fa-ban' ?> mr-1"></i>
                    <?= $user['status'] ? 'CUENTA ACTIVA' : 'CUENTA SUSPENDIDA' ?>
                </span>
                
                <span class="inline-block px-3 py-1.5 rounded-lg text-xs font-bold <?= $user['email_verified'] ? 'bg-indigo-50 text-indigo-600 border border-indigo-100' : 'bg-amber-50 text-amber-600 border border-amber-100' ?>">
                    <i class="fa-solid <?= $user['email_verified'] ? 'fa-envelope-circle-check' : 'fa-envelope-open' ?> mr-1"></i>
                    <?= $user['email_verified'] ? 'EMAIL VERIFICADO' : 'EMAIL PENDIENTE' ?>
                </span>

                <span class="inline-block px-3 py-1.5 rounded-lg text-xs font-bold <?= $user['tfa_enabled'] ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200' ?>">
                    <i class="fa-solid <?= $user['tfa_enabled'] ? 'fa-shield-check' : 'fa-shield-halved' ?> mr-1"></i>
                    <?= $user['tfa_enabled'] ? '2FA ACTIVADO' : '2FA DESACTIVADO' ?>
                </span>
            </div>
        </div>

        <div class="w-full md:w-2/3 space-y-6">
            <div>
                <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Datos de Contacto</h4>
                <div class="space-y-4">
                    <div class="flex items-center space-x-3 text-slate-700">
                        <i class="fa-solid fa-id-badge text-slate-400 w-5"></i>
                        <span class="font-medium"><?= htmlspecialchars($user['document']) ?></span>
                    </div>
                    <div class="flex items-center space-x-3 text-slate-700">
                        <i class="fa-solid fa-envelope text-slate-400 w-5"></i>
                        <span class="font-medium"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="flex items-center space-x-3 text-slate-700">
                        <i class="fa-solid fa-phone text-slate-400 w-5"></i>
                        <span class="font-medium"><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></span>
                    </div>
                    <div class="flex items-center space-x-3 text-slate-700">
                        <i class="fa-solid fa-map-location-dot text-slate-400 w-5"></i>
                        <span class="font-medium"><?= htmlspecialchars($user['address'] ?? 'N/A') ?></span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex flex-wrap gap-2">
                <a href="/modules/users/edit.php?id=<?= $user['id'] ?>" class="bg-indigo-50 text-indigo-600 hover:bg-indigo-100 px-4 py-2 rounded-xl text-sm font-bold transition">
                    <i class="fa-solid fa-pen mr-1"></i> Editar Usuario
                </a>
                <a href="/modules/users/delete.php?id=<?= $user['id'] ?>&csrf_token=<?= Auth::csrfToken() ?>" onclick="return confirm('¿Seguro que deseas eliminar este usuario?');" class="bg-red-50 text-red-600 hover:bg-red-100 px-4 py-2 rounded-xl text-sm font-bold transition">
                    <i class="fa-solid fa-trash mr-1"></i> Desactivar
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
