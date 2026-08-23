<?php
// /modules/users/roles.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT * FROM roles ORDER BY id ASC");
$roles = $stmt->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-5xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/users/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Gestión de Roles</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Administra los roles disponibles en el sistema escolar.</p>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($roles as $role): ?>
                <div class="border border-slate-200 rounded-2xl p-6 hover:shadow-md hover:border-indigo-200 transition">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-xl">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <span class="bg-slate-100 text-slate-500 px-2.5 py-1 rounded-lg text-xs font-bold">ID: <?= $role['id'] ?></span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800"><?= htmlspecialchars($role['name']) ?></h3>
                    <p class="text-sm text-slate-500 mt-1 mb-4 h-10 overflow-hidden text-ellipsis">
                        <?= htmlspecialchars($role['description'] ?? 'Sin descripción.') ?>
                    </p>
                    <a href="/modules/users/permissions.php?role_id=<?= $role['id'] ?>" class="block w-full text-center bg-white border border-slate-200 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 py-2 rounded-xl text-sm font-bold transition">
                        Ver Permisos
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
