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

<!-- Premium UI Styles -->
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
</style>

<div class="space-y-8 animate-fade-in pb-10">
    <!-- Hero Header with Stats -->
    <div class="relative rounded-3xl bg-gradient-to-br from-amber-600 via-orange-600 to-rose-600 p-8 shadow-2xl overflow-hidden text-white">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-72 h-72 rounded-full bg-amber-400 blur-[80px] opacity-30"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 rounded-full bg-rose-500 blur-[80px] opacity-20"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="text-4xl font-extrabold tracking-tight mb-2">Gestión de <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-200 to-yellow-200">Asignaturas</span></h2>
                <p class="text-amber-100/90 font-medium text-sm max-w-md leading-relaxed">Directorio oficial de la malla curricular, materias y materiales de estudio.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-4">
                <div class="glass-panel px-6 py-3 rounded-2xl flex items-center space-x-4 shadow-lg shadow-black/10">
                    <div class="w-12 h-12 rounded-full bg-amber-500/30 flex items-center justify-center text-amber-200">
                        <i class="fa-solid fa-book text-xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-amber-100/70 tracking-widest">Total Materias</p>
                        <p class="text-2xl font-extrabold text-white leading-none"><?= count($subjects) ?></p>
                    </div>
                </div>
                
                <a href="/modules/subjects/create.php" class="inline-flex h-14 items-center space-x-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-white px-6 rounded-2xl font-bold tracking-wide transition shadow-lg shadow-amber-500/30 hover:-translate-y-0.5 border border-amber-400/30">
                    <i class="fa-solid fa-plus text-sm"></i>
                    <span>Crear Materia</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Advanced Search & Filter Bar (Glassmorphism) -->
    <div class="glass-panel p-2 rounded-2xl shadow-sm flex flex-col md:flex-row items-center justify-between gap-2 z-20 relative">
        <form method="GET" class="w-full flex flex-col md:flex-row items-center gap-2">
            <div class="relative w-full group">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-orange-500 transition">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar por materia, profesor..."
                       class="block w-full pl-12 pr-4 py-4 bg-white/50 hover:bg-white/80 focus:bg-white border border-transparent focus:border-orange-200 text-slate-800 rounded-xl outline-none text-sm font-medium transition shadow-inner placeholder-slate-400">
            </div>
            <button type="submit" class="w-full md:w-auto px-8 py-4 bg-slate-900 hover:bg-black text-white font-bold rounded-xl transition flex justify-center items-center space-x-2 shrink-0">
                <i class="fa-solid fa-filter text-xs"></i>
                <span>Aplicar Filtro</span>
            </button>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden relative z-10">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-400 uppercase tracking-widest text-[10px] font-bold">
                        <th scope="col" class="px-6 py-5">Asignatura</th>
                        <th scope="col" class="px-6 py-5">Grado / Nivel</th>
                        <th scope="col" class="px-6 py-5">Docente Asignado</th>
                        <th scope="col" class="px-6 py-5">Recursos</th>
                        <th scope="col" class="px-6 py-5 text-center">Acciones Rápidas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($subjects)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center text-slate-400 font-medium">
                                <div class="w-20 h-20 mx-auto bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-300 shadow-inner">
                                    <i class="fa-solid fa-book-open-reader text-3xl"></i>
                                </div>
                                <p class="text-base text-slate-500">No se encontraron asignaturas con los filtros aplicados.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($subjects as $row): ?>
                            <tr class="group hover:bg-orange-50/30 transition duration-300">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-100 to-orange-50 border-2 border-white flex items-center justify-center text-orange-600 font-extrabold flex-shrink-0 shadow-md transform group-hover:scale-105 transition duration-300 text-lg">
                                            <i class="fa-solid fa-book"></i>
                                        </div>
                                        <div>
                                            <p class="font-extrabold text-slate-800 text-base leading-tight group-hover:text-orange-600 transition">
                                                <?= htmlspecialchars($row['name']) ?>
                                            </p>
                                            <div class="flex items-center space-x-2 mt-1">
                                                <span class="text-xs text-slate-400 truncate max-w-[200px]"><?= htmlspecialchars($row['description']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 shadow-sm">
                                        <?= htmlspecialchars($row['course_name']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($row['teacher_first']): ?>
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-xs">
                                                <?= strtoupper(substr($row['teacher_first'], 0, 1) . substr($row['teacher_last'], 0, 1)) ?>
                                            </div>
                                            <span class="text-sm font-semibold text-slate-700"><?= htmlspecialchars($row['teacher_first'] . ' ' . $row['teacher_last']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-slate-100 text-slate-500 text-xs font-medium border border-slate-200">
                                            <i class="fa-solid fa-triangle-exclamation mr-1.5 text-amber-500"></i> No asignado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col space-y-2 text-xs">
                                        <div class="flex items-center text-slate-600 font-medium">
                                            <i class="fa-regular fa-clock w-4 text-slate-400"></i>
                                            <span><?= intval($row['weekly_hours']) ?> horas/sem</span>
                                        </div>
                                        <?php if (!empty($row['study_material'])): ?>
                                            <a href="<?= htmlspecialchars($row['study_material']) ?>" target="_blank" class="flex items-center text-emerald-600 hover:text-emerald-700 font-semibold group-hover:underline">
                                                <i class="fa-solid fa-link w-4 text-emerald-400"></i>
                                                <span>Ver Material</span>
                                            </a>
                                        <?php else: ?>
                                            <div class="flex items-center text-slate-400">
                                                <i class="fa-solid fa-unlink w-4"></i>
                                                <span>Sin material</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        <a href="/modules/subjects/edit.php?id=<?= $row['id'] ?>" class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-orange-600 hover:border-orange-200 hover:bg-orange-50 flex items-center justify-center shadow-sm transition" title="Editar Materia">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                    </div>
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
