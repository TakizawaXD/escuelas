<?php
// /modules/teachers/index.php
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
    SELECT t.*, u.document, u.first_name, u.last_name, u.email
    FROM teachers t
    JOIN users u ON t.user_id = u.id
    WHERE 1=1
";

$params = [];
if (!empty($search)) {
    $query .= " AND (u.document LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR t.specialty LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
}

$query .= " ORDER BY t.id DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$teachers = $stmt->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<!-- Header Section -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Gestión de Docentes</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Directorio de profesores vinculados a la institución.</p>
    </div>
    <a href="/modules/teachers/create.php" class="inline-flex items-center space-x-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 transition active:scale-[0.98]">
        <i class="fa-solid fa-chalkboard-user text-sm"></i>
        <span>Agregar Docente</span>
    </a>
</div>

<!-- Search Filtering -->
<div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100/80 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
    <form method="GET" class="w-full max-w-md flex items-center space-x-2">
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </div>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar por documento, nombre o especialidad..."
                   class="block w-full pl-10 pr-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
        </div>
        <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition text-sm">Filtrar</button>
    </form>
    <div class="text-slate-400 text-sm font-medium">
        Total: <span class="font-extrabold text-slate-700"><?= count($teachers) ?></span> docentes registrados.
    </div>
</div>

<!-- List table -->
<div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
            <thead class="bg-slate-50/60 text-slate-400 uppercase tracking-wider text-xs font-bold">
                <tr>
                    <th scope="col" class="px-6 py-4">Docente</th>
                    <th scope="col" class="px-6 py-4">Documento</th>
                    <th scope="col" class="px-6 py-4">Especialidad</th>
                    <th scope="col" class="px-6 py-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                <?php if (empty($teachers)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-slate-400">
                            No se encontraron docentes registrados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($teachers as $row): ?>
                        <tr class="hover:bg-slate-50/40 transition">
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800">
                                <div>
                                    <p><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></p>
                                    <span class="text-xs text-slate-400 font-medium"><?= htmlspecialchars($row['email']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-slate-500 text-xs"><?= htmlspecialchars($row['document']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-block px-2.5 py-1 text-xs font-bold rounded-lg border border-teal-100 bg-teal-50 text-teal-700">
                                    <?= htmlspecialchars($row['specialty']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="/modules/teachers/edit.php?id=<?= $row['id'] ?>" class="inline-flex p-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-xl transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
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
