<?php
// /modules/discipline/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR', 'DOCENTE', 'PADRE', 'ESTUDIANTE'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$search = Auth::sanitize($_GET['search'] ?? '');
$user_id = Auth::user()['id'];
$role = Auth::user()['role_name'];

$query = "
    SELECT dr.*, 
           u_student.first_name as student_first_name, u_student.last_name as student_last_name, u_student.document as student_document,
           u_teacher.first_name as teacher_first_name, u_teacher.last_name as teacher_last_name
    FROM discipline_reports dr
    JOIN students s ON dr.student_id = s.id
    JOIN users u_student ON s.user_id = u_student.id
    JOIN teachers t ON dr.teacher_id = t.id
    JOIN users u_teacher ON t.user_id = u_teacher.id
    WHERE 1=1
";

$params = [];

// Filter by role: Students and Parents should only see their own. Teachers maybe only see what they wrote or their students.
// For simplicity in this demo, ADMIN/DIRECTOR/COORDINADOR/DOCENTE see all. PADRE/ESTUDIANTE see only their related.
if ($role === 'ESTUDIANTE') {
    $query .= " AND s.user_id = ?";
    $params[] = $user_id;
} elseif ($role === 'PADRE') {
    $query .= " AND s.parent_user_id = ?";
    $params[] = $user_id;
}

if (!empty($search)) {
    $query .= " AND (u_student.document LIKE ? OR u_student.first_name LIKE ? OR u_student.last_name LIKE ? OR dr.type LIKE ?)";
    array_push($params, "%$search%", "%$search%", "%$search%", "%$search%");
}

$query .= " ORDER BY dr.date DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$reports = $stmt->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<!-- Header Section -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Convivencia Estudiantil</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Libro de anotaciones y seguimiento disciplinario.</p>
    </div>
    <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR', 'DOCENTE'])): ?>
    <a href="/modules/discipline/create.php" class="inline-flex items-center space-x-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 transition active:scale-[0.98]">
        <i class="fa-solid fa-plus text-sm"></i>
        <span>Nueva Anotación</span>
    </a>
    <?php endif; ?>
</div>

<!-- Search Filtering -->
<div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100/80 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
    <form method="GET" class="w-full max-w-md flex items-center space-x-2">
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </div>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar por estudiante o tipo..."
                   class="block w-full pl-10 pr-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
        </div>
        <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition text-sm">Filtrar</button>
    </form>
    <div class="text-slate-400 text-sm font-medium">
        Total de reportes: <span class="font-extrabold text-slate-700"><?= count($reports) ?></span>
    </div>
</div>

<!-- Table list -->
<div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-6">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
            <thead class="bg-slate-50/60 text-slate-400 uppercase tracking-wider text-xs font-bold">
                <tr>
                    <th scope="col" class="px-6 py-4">Fecha</th>
                    <th scope="col" class="px-6 py-4">Estudiante</th>
                    <th scope="col" class="px-6 py-4">Tipo</th>
                    <th scope="col" class="px-6 py-4">Descripción / Observación</th>
                    <th scope="col" class="px-6 py-4">Reportado Por</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                <?php if (empty($reports)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                            No hay anotaciones registradas.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reports as $row): ?>
                        <tr class="hover:bg-slate-50/40 transition">
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-slate-500 text-xs">
                                <?= date('d/m/Y H:i', strtotime($row['date'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800">
                                <?= htmlspecialchars($row['student_first_name'] . ' ' . $row['student_last_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (strtolower($row['type']) === 'positiva'): ?>
                                    <span class="inline-flex items-center space-x-1 text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-full text-xs font-bold">
                                        <i class="fa-solid fa-star text-[10px]"></i>
                                        <span>Positiva</span>
                                    </span>
                                <?php elseif (strtolower($row['type']) === 'negativa'): ?>
                                    <span class="inline-flex items-center space-x-1 text-rose-600 bg-rose-50 px-2.5 py-1 rounded-full text-xs font-bold border border-rose-100">
                                        <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                                        <span>Negativa</span>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center space-x-1 text-amber-700 bg-amber-100 px-2.5 py-1 rounded-full text-xs font-bold">
                                        <i class="fa-solid fa-bell text-[10px]"></i>
                                        <span>Incidente</span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-sm max-w-md truncate" title="<?= htmlspecialchars($row['description']) ?>">
                                <?= htmlspecialchars($row['description']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500">
                                Prof. <?= htmlspecialchars($row['teacher_first_name'] . ' ' . $row['teacher_last_name']) ?>
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
