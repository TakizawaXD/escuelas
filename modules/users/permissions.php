<?php
// /modules/users/permissions.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN'])) {
    header("Location: /index.php");
    exit;
}

$role_id = $_GET['role_id'] ?? null;
if (!$role_id) {
    header("Location: /modules/users/roles.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM roles WHERE id = ?");
$stmt->execute([$role_id]);
$role = $stmt->fetch();

if (!$role) {
    header("Location: /modules/users/roles.php");
    exit;
}

// Obtener todos los permisos
$stmt = $db->query("SELECT * FROM permissions");
$all_permissions = $stmt->fetchAll();

// Obtener permisos asignados al rol
$stmt = $db->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
$stmt->execute([$role_id]);
$assigned_permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db->beginTransaction();
    try {
        // Eliminar permisos actuales
        $stmt = $db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
        $stmt->execute([$role_id]);
        
        // Asignar nuevos
        if (!empty($_POST['permissions']) && is_array($_POST['permissions'])) {
            $stmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            foreach ($_POST['permissions'] as $perm_id) {
                $stmt->execute([$role_id, $perm_id]);
            }
        }
        $db->commit();
        $success = "Permisos actualizados correctamente.";
        
        // Refrescar asignados
        $stmt = $db->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
        $stmt->execute([$role_id]);
        $assigned_permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Error al actualizar permisos.";
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-4xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/users/roles.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Permisos de Rol</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Configurando accesos para: <span class="font-bold text-indigo-600"><?= htmlspecialchars($role['name']) ?></span></p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl flex items-center space-x-3">
            <i class="fa-solid fa-circle-check"></i>
            <span class="font-medium text-sm"><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <?php if (empty($all_permissions)): ?>
            <div class="text-center py-10 text-slate-400">
                <i class="fa-solid fa-shield-blank text-5xl mb-4 text-slate-200"></i>
                <h3 class="text-lg font-bold text-slate-600 mb-1">Sin permisos definidos</h3>
                <p class="text-sm">Aún no se han registrado permisos granulares en el sistema.</p>
            </div>
        <?php else: ?>
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php foreach($all_permissions as $perm): ?>
                        <label class="flex items-start space-x-3 p-4 border border-slate-200 rounded-2xl hover:bg-slate-50 transition cursor-pointer <?= in_array($perm['id'], $assigned_permissions) ? 'border-indigo-200 bg-indigo-50/30' : '' ?>">
                            <div class="pt-0.5">
                                <input type="checkbox" name="permissions[]" value="<?= $perm['id'] ?>" <?= in_array($perm['id'], $assigned_permissions) ? 'checked' : '' ?> class="w-5 h-5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                            </div>
                            <div>
                                <p class="font-bold text-slate-800"><?= htmlspecialchars($perm['name']) ?></p>
                                <p class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($perm['description']) ?></p>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="pt-6 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-xl font-bold tracking-wide transition shadow-lg shadow-indigo-500/20 active:scale-[0.98]">
                        <i class="fa-solid fa-save mr-2"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
