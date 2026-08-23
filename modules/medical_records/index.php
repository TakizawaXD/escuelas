<?php
// /modules/medical_records/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR', 'DOCENTE'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$search = Auth::sanitize($_GET['search'] ?? '');

$query = "
    SELECT s.id as student_id, u.document, u.first_name, u.last_name, c.name as course_name, 
           smr.id as record_id, smr.blood_type
    FROM students s
    JOIN users u ON s.user_id = u.id
    JOIN courses c ON s.course_id = c.id
    LEFT JOIN student_medical_records smr ON s.id = smr.student_id
    WHERE u.status = 1
";

$params = [];
if (!empty($search)) {
    $query .= " AND (u.document LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$query .= " ORDER BY u.last_name ASC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<!-- Header Section -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Fichas Médicas</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Gestión de historiales médicos y contactos de emergencia de los alumnos.</p>
    </div>
</div>

<!-- Search Filtering -->
<div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100/80 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
    <form method="GET" class="w-full max-w-md flex items-center space-x-2">
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </div>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar alumno por nombre o documento..."
                   class="block w-full pl-10 pr-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
        </div>
        <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition text-sm">Buscar</button>
    </form>
    <div class="text-slate-400 text-sm font-medium">
        Total de estudiantes: <span class="font-extrabold text-slate-700"><?= count($students) ?></span>
    </div>
</div>

<!-- Table list -->
<div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-6">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
            <thead class="bg-slate-50/60 text-slate-400 uppercase tracking-wider text-xs font-bold">
                <tr>
                    <th scope="col" class="px-6 py-4">Documento</th>
                    <th scope="col" class="px-6 py-4">Estudiante</th>
                    <th scope="col" class="px-6 py-4">Curso</th>
                    <th scope="col" class="px-6 py-4">Estado de Ficha</th>
                    <th scope="col" class="px-6 py-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                            No se encontraron estudiantes.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $row): ?>
                        <tr class="hover:bg-slate-50/40 transition">
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-slate-500 text-xs"><?= htmlspecialchars($row['document']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    <span><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-block px-2.5 py-1 text-xs font-bold rounded-lg border border-slate-200/80 bg-slate-100 text-slate-700">
                                    <?= htmlspecialchars($row['course_name']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($row['record_id']): ?>
                                    <span class="inline-flex items-center space-x-1 text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-full text-xs font-bold">
                                        <i class="fa-solid fa-file-medical text-[10px]"></i>
                                        <span>Completada <?= $row['blood_type'] ? '(' . htmlspecialchars($row['blood_type']) . ')' : '' ?></span>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center space-x-1 text-rose-600 bg-rose-50 px-2.5 py-1 rounded-full text-xs font-bold border border-rose-100">
                                        <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                                        <span>Faltante</span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center space-x-1.5">
                                <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])): ?>
                                    <a href="/modules/medical_records/edit.php?student_id=<?= $row['student_id'] ?>" class="inline-flex items-center justify-center space-x-2 px-4 py-2 <?= $row['record_id'] ? 'bg-indigo-50 hover:bg-indigo-100 text-indigo-600' : 'bg-rose-50 hover:bg-rose-100 text-rose-600' ?> rounded-xl transition text-xs font-bold">
                                        <i class="fa-solid <?= $row['record_id'] ? 'fa-pen-to-square' : 'fa-plus' ?>"></i>
                                        <span><?= $row['record_id'] ? 'Editar Ficha' : 'Crear Ficha' ?></span>
                                    </a>
                                <?php else: ?>
                                    <!-- Teachers can only view -->
                                    <a href="/modules/medical_records/view.php?student_id=<?= $row['student_id'] ?>" class="inline-flex items-center justify-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition text-xs font-bold">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                <?php endif; ?>
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
