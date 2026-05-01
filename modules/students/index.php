<?php
// /modules/students/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$search = Auth::sanitize($_GET['search'] ?? '');

$query = "
    SELECT s.*, u.document, u.first_name, u.last_name, u.email, c.name as course_name, p.first_name as parent_first, p.last_name as parent_last
    FROM students s
    JOIN users u ON s.user_id = u.id
    JOIN courses c ON s.course_id = c.id
    LEFT JOIN users p ON s.parent_user_id = p.id
    WHERE 1=1
";

$params = [];
if (!empty($search)) {
    $query .= " AND (u.document LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR c.name LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
}

$query .= " ORDER BY s.id DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in">
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Gestión de Estudiantes</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Perfil completo, fotos, promedios e indicadores de escalabilidad académica.</p>
        </div>
        <a href="/modules/students/create.php" class="inline-flex items-center space-x-2 bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-3 rounded-2xl font-bold tracking-wide transition shadow-lg shadow-indigo-500/20 active:scale-[0.98]">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>Matricular Estudiante</span>
        </a>
    </div>

    <!-- Search / Filter Area -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" class="w-full max-w-md flex items-center space-x-2">
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar por documento, nombre o curso..."
                       class="block w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 outline-none text-sm font-medium transition">
            </div>
            <button type="submit" class="px-5 py-3 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition text-sm">Filtrar</button>
        </form>
        <div class="text-slate-400 text-sm font-medium">
            Total: <span class="font-extrabold text-slate-700"><?= count($students) ?></span> estudiantes
        </div>
    </div>

    <!-- List table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50/60 text-slate-400 uppercase tracking-wider text-xs font-bold">
                    <tr>
                        <th scope="col" class="px-6 py-4">Estudiante</th>
                        <th scope="col" class="px-6 py-4">Grado / Nivel</th>
                        <th scope="col" class="px-6 py-4">Promedio (GPA)</th>
                        <th scope="col" class="px-6 py-4">Acudiente</th>
                        <th scope="col" class="px-6 py-4">Escalabilidad / Progreso</th>
                        <th scope="col" class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400 font-medium">
                                No se encontraron estudiantes matriculados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $row): ?>
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <?php if (!empty($row['photo_url'])): ?>
                                            <div class="w-12 h-12 rounded-2xl overflow-hidden flex-shrink-0 border border-slate-200 shadow-sm">
                                                <img src="<?= htmlspecialchars($row['photo_url']) ?>" alt="Avatar" class="w-full h-full object-cover">
                                            </div>
                                        <?php else: ?>
                                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-500 font-extrabold flex-shrink-0">
                                                <?= strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <p class="font-bold text-slate-800 text-base leading-tight"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></p>
                                            <span class="text-xs text-slate-400 font-mono"><?= htmlspecialchars($row['document']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <p class="text-xs text-slate-400 font-medium"><?= htmlspecialchars($row['course_name']) ?></p>
                                        <span class="inline-block px-2 py-0.5 text-xs font-bold rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-600">
                                            <?= $row['grade'] ? htmlspecialchars($row['grade']) : 'Sin nivel' ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 bg-emerald-50 text-emerald-700 font-extrabold text-sm rounded-lg border border-emerald-100/60">
                                        <?= number_format($row['gpa'], 2) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-500">
                                    <?= $row['parent_first'] ? htmlspecialchars($row['parent_first'] . ' ' . $row['parent_last']) : '<span class="text-slate-300 italic">No asignado</span>' ?>
                                </td>
                                <td class="px-6 py-4 text-slate-500 max-w-xs truncate" title="<?= htmlspecialchars($row['scalability']) ?>">
                                    <?= $row['scalability'] ? htmlspecialchars($row['scalability']) : '<span class="text-slate-300 italic">Sin observaciones</span>' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="/modules/students/edit.php?id=<?= $row['id'] ?>" class="inline-flex p-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-xl transition">
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

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
