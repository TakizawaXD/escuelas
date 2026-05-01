<?php
// /modules/users/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$search = Auth::sanitize($_GET['search'] ?? '');

$query = "
    SELECT u.*, r.name as role_name 
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE 1=1
";

$params = [];
if (!empty($search)) {
    $query .= " AND (u.document LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
}

$query .= " ORDER BY u.id DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<!-- Header Section -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Gestión de Usuarios</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Directorio y control de accesos de la institución.</p>
    </div>
    <a href="/modules/users/create.php" class="inline-flex items-center space-x-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 transition active:scale-[0.98]">
        <i class="fa-solid fa-user-plus text-sm"></i>
        <span>Agregar Usuario</span>
    </a>
</div>

<!-- Search Filtering -->
<div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100/80 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
    <form method="GET" class="w-full max-w-md flex items-center space-x-2">
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </div>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar por documento, nombre o email..."
                   class="block w-full pl-10 pr-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
        </div>
        <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition text-sm">Filtrar</button>
    </form>
    <div class="text-slate-400 text-sm font-medium">
        Total: <span class="font-extrabold text-slate-700"><?= count($users) ?></span> usuarios registrados.
    </div>
</div>

<!-- Table list -->
<div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
            <thead class="bg-slate-50/60 text-slate-400 uppercase tracking-wider text-xs font-bold">
                <tr>
                    <th scope="col" class="px-6 py-4">Documento</th>
                    <th scope="col" class="px-6 py-4">Nombre Completo</th>
                    <th scope="col" class="px-6 py-4">Correo Electrónico</th>
                    <th scope="col" class="px-6 py-4">Rol</th>
                    <th scope="col" class="px-6 py-4">Estado</th>
                    <th scope="col" class="px-6 py-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                            No se encontraron usuarios en la búsqueda.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $row): ?>
                        <tr class="hover:bg-slate-50/40 transition">
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-slate-500 text-xs"><?= htmlspecialchars($row['document']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($row['email']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-block px-2.5 py-1 text-xs font-bold rounded-lg border border-slate-200/80 bg-slate-100 text-slate-700">
                                    <?= htmlspecialchars($row['role_name']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($row['status'] == 1): ?>
                                    <span class="inline-flex items-center space-x-1 text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-full text-xs font-bold">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i>
                                        <span>Activo</span>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center space-x-1 text-slate-400 bg-slate-100 px-2.5 py-1 rounded-full text-xs font-bold">
                                        <i class="fa-solid fa-circle text-[10px]"></i>
                                        <span>Inactivo</span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center space-x-1.5">
                                <a href="/modules/users/edit.php?id=<?= $row['id'] ?>" class="inline-flex p-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-xl transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                
                                <a href="/modules/users/status.php?id=<?= $row['id'] ?>" class="inline-flex p-2 <?= $row['status'] == 1 ? 'bg-rose-50 hover:bg-rose-100 text-rose-600' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-600' ?> rounded-xl transition">
                                    <i class="fa-solid <?= $row['status'] == 1 ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
