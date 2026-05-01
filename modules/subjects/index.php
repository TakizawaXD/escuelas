<?php
// /modules/subjects/index.php
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
    SELECT s.*, c.name as course_name, u.first_name as teacher_first, u.last_name as teacher_last
    FROM subjects s
    JOIN courses c ON s.course_id = c.id
    LEFT JOIN teachers t ON s.teacher_id = t.id
    LEFT JOIN users u ON t.user_id = u.id
    WHERE 1=1
";

$params = [];
if (!empty($search)) {
    $query .= " AND (s.name LIKE ? OR c.name LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
}

$query .= " ORDER BY s.id DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$subjects = $stmt->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in">
    <!-- Header Area -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Gestión de Materias</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Directorio oficial de materias y materiales de estudio.</p>
        </div>
        <a href="/modules/subjects/create.php" class="inline-flex items-center space-x-2 bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-3 rounded-2xl font-bold tracking-wide transition shadow-lg shadow-indigo-500/20 active:scale-[0.98]">
            <i class="fa-solid fa-book"></i>
            <span>Crear Materia</span>
        </a>
    </div>

    <!-- Filtering Bar -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100/80 flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" class="w-full max-w-md flex items-center space-x-2">
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar por materia, profesor..."
                       class="block w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 outline-none text-sm font-medium transition">
            </div>
            <button type="submit" class="px-5 py-3 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition text-sm">Filtrar</button>
        </form>
        <div class="text-slate-400 text-sm font-medium">
            Total: <span class="font-extrabold text-slate-700"><?= count($subjects) ?></span> materias registradas.
        </div>
    </div>

    <!-- List table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50/60 text-slate-400 uppercase tracking-wider text-xs font-bold">
                    <tr>
                        <th scope="col" class="px-6 py-4">Materia</th>
                        <th scope="col" class="px-6 py-4">Curso / Grado</th>
                        <th scope="col" class="px-6 py-4">Docente</th>
                        <th scope="col" class="px-6 py-4">Horas</th>
                        <th scope="col" class="px-6 py-4">Material de Estudio</th>
                        <th scope="col" class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                    <?php if (empty($subjects)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400 font-medium">
                                No se encontraron materias registradas.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($subjects as $row): ?>
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800">
                                    <div>
                                        <p class="text-base text-slate-900 leading-tight"><?= htmlspecialchars($row['name']) ?></p>
                                        <span class="text-xs text-slate-400 font-medium"><?= htmlspecialchars($row['description']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-block px-2.5 py-1 text-xs font-bold rounded-lg border border-indigo-100 bg-indigo-50 text-indigo-700">
                                        <?= htmlspecialchars($row['course_name']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-700">
                                    <?= $row['teacher_first'] ? htmlspecialchars($row['teacher_first'] . ' ' . $row['teacher_last']) : '<span class="text-slate-300 italic">No asignado</span>' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= intval($row['weekly_hours']) ?> horas</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if (!empty($row['study_material'])): ?>
                                        <a href="<?= htmlspecialchars($row['study_material']) ?>" target="_blank" class="inline-flex items-center space-x-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-100/60 rounded-lg text-xs font-bold transition">
                                            <i class="fa-solid fa-link"></i>
                                            <span>Abrir Material</span>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-slate-300 italic text-xs">Sin material</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="/modules/subjects/edit.php?id=<?= $row['id'] ?>" class="inline-flex p-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-xl transition">
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
