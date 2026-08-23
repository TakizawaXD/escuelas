<?php
// /modules/courses/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$search = Auth::sanitize($_GET['search'] ?? '');

$query = "SELECT * FROM courses WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR description LIKE ?)";
    $params = ["%$search%", "%$search%"];
}

$query .= " ORDER BY id DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$courses = $stmt->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in">
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Gestión de Cursos</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Administra los grados, cursos y niveles académicos de la institución.</p>
        </div>
        <a href="/modules/courses/create.php" class="inline-flex items-center space-x-2 bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-3 rounded-2xl font-bold tracking-wide transition shadow-lg shadow-indigo-500/20 active:scale-[0.98]">
            <i class="fa-solid fa-plus"></i>
            <span>Nuevo Curso</span>
        </a>
    </div>

    <!-- Search / Filter Area -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" class="w-full max-w-md flex items-center space-x-2">
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar curso por nombre o descripción..."
                       class="block w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 outline-none text-sm font-medium transition">
            </div>
            <button type="submit" class="px-5 py-3 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition text-sm">Filtrar</button>
        </form>
        <div class="text-slate-400 text-sm font-medium">
            Total: <span class="font-extrabold text-slate-700"><?= count($courses) ?></span> cursos
        </div>
    </div>

    <!-- List table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50/60 text-slate-400 uppercase tracking-wider text-xs font-bold">
                    <tr>
                        <th scope="col" class="px-6 py-4">ID</th>
                        <th scope="col" class="px-6 py-4">Nombre del Curso</th>
                        <th scope="col" class="px-6 py-4">Descripción</th>
                        <th scope="col" class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                    <?php if (empty($courses)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-400 font-medium">
                                No se encontraron cursos registrados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($courses as $row): ?>
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-slate-400 font-mono">
                                    #<?= htmlspecialchars($row['id']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="font-bold text-slate-800 text-base"><?= htmlspecialchars($row['name']) ?></p>
                                </td>
                                <td class="px-6 py-4 text-slate-500 max-w-xs truncate" title="<?= htmlspecialchars($row['description']) ?>">
                                    <?= $row['description'] ? htmlspecialchars($row['description']) : '<span class="text-slate-300 italic">Sin descripción</span>' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="/modules/courses/edit.php?id=<?= $row['id'] ?>" class="inline-flex p-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-xl transition" title="Editar">
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
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
