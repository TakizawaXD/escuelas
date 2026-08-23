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
$course_id = isset($_GET['course_id']) && $_GET['course_id'] !== '' ? (int)$_GET['course_id'] : null;

// Fetch all courses for the dropdown filter
$courses = $db->query("SELECT id, name FROM courses ORDER BY name ASC")->fetchAll();

$query = "
    SELECT s.*, u.document, u.first_name, u.last_name, u.email, c.name as course_name, p.first_name as parent_first, p.last_name as parent_last
    FROM students s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN courses c ON s.course_id = c.id
    LEFT JOIN users p ON s.parent_user_id = p.id
    WHERE 1=1
";

$params = [];
if (!empty($search)) {
    $query .= " AND (u.document LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR c.name LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}

if ($course_id) {
    $query .= " AND s.course_id = ?";
    $params[] = $course_id;
}

$query .= " ORDER BY s.id DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

// Calculate stats for premium UI
$total_students = count($students);
$avg_gpa = 0;
if ($total_students > 0) {
    $sum = array_sum(array_column($students, 'gpa'));
    $avg_gpa = $sum / $total_students;
}

// Generate query string for export
$exportQueryStr = http_build_query(['search' => $search, 'course_id' => $course_id]);

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
    .text-gradient {
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-image: linear-gradient(to right, #4f46e5, #ec4899);
    }
</style>

<div class="space-y-8 animate-fade-in pb-10">
    
    <!-- Hero Header with Stats -->
    <div class="relative rounded-3xl bg-gradient-to-br from-indigo-900 via-slate-900 to-indigo-950 p-8 shadow-2xl overflow-hidden text-white">
        <!-- Decorative blobs -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-72 h-72 rounded-full bg-indigo-500 blur-[80px] opacity-30"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 rounded-full bg-pink-500 blur-[80px] opacity-20"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="text-4xl font-extrabold tracking-tight mb-2">Gestión de <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-300 to-pink-300">Estudiantes</span></h2>
                <p class="text-indigo-200/80 font-medium text-sm max-w-md leading-relaxed">Perfil completo, fotos, promedios e indicadores de escalabilidad académica en tiempo real.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-4">
                <div class="glass-panel px-6 py-3 rounded-2xl flex items-center space-x-4 shadow-lg shadow-black/10">
                    <div class="w-12 h-12 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-300">
                        <i class="fa-solid fa-users text-xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-indigo-200/70 tracking-widest">Total Filtro</p>
                        <p class="text-2xl font-extrabold text-white leading-none"><?= $total_students ?></p>
                    </div>
                </div>
                
                <a href="/modules/students/create.php" class="inline-flex h-14 items-center space-x-2 bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-400 hover:to-indigo-500 text-white px-6 rounded-2xl font-bold tracking-wide transition shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5 active:scale-95 border border-indigo-400/30">
                    <i class="fa-solid fa-user-plus text-sm"></i>
                    <span>Matricular</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Advanced Search & Filter Bar (Glassmorphism) -->
    <div class="glass-panel p-2 rounded-2xl shadow-sm flex flex-col md:flex-row items-center justify-between gap-2 z-20 relative">
        <form method="GET" class="w-full flex flex-col md:flex-row items-center gap-2">
            <!-- Search Text -->
            <div class="relative w-full md:w-1/2 group">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar documento o nombre..."
                       class="block w-full pl-12 pr-4 py-4 bg-white/50 hover:bg-white/80 focus:bg-white border border-transparent focus:border-indigo-200 text-slate-800 rounded-xl outline-none text-sm font-medium transition shadow-inner placeholder-slate-400">
            </div>
            
            <!-- Course Filter -->
            <div class="relative w-full md:w-1/3 group">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition">
                    <i class="fa-solid fa-layer-group text-sm"></i>
                </div>
                <select name="course_id" class="block w-full pl-12 pr-4 py-4 bg-white/50 hover:bg-white/80 focus:bg-white border border-transparent focus:border-indigo-200 text-slate-800 rounded-xl outline-none text-sm font-medium transition shadow-inner appearance-none cursor-pointer">
                    <option value="">Todos los cursos</option>
                    <?php foreach($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($course_id == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="w-full md:w-auto px-8 py-4 bg-slate-900 hover:bg-black text-white font-bold rounded-xl transition shadow-md hover:shadow-lg flex justify-center items-center space-x-2 shrink-0">
                <i class="fa-solid fa-filter text-xs"></i>
                <span>Aplicar</span>
            </button>
        </form>
        
        <!-- Export Button -->
        <a href="/modules/students/export.php?<?= $exportQueryStr ?>" class="w-full md:w-auto px-6 py-4 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl transition shadow-md hover:shadow-lg hover:-translate-y-0.5 flex justify-center items-center space-x-2 shrink-0">
            <i class="fa-solid fa-file-csv"></i>
            <span>Exportar CSV</span>
        </a>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden relative z-10">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-400 uppercase tracking-widest text-[10px] font-bold">
                        <th scope="col" class="px-6 py-5">Perfil del Estudiante</th>
                        <th scope="col" class="px-6 py-5">Nivel Académico</th>
                        <th scope="col" class="px-6 py-5 text-center">GPA</th>
                        <th scope="col" class="px-6 py-5">Tutor / Acudiente</th>
                        <th scope="col" class="px-6 py-5 text-center">Acciones Rápidas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center text-slate-400 font-medium">
                                <div class="w-20 h-20 mx-auto bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-300 shadow-inner">
                                    <i class="fa-solid fa-user-slash text-3xl"></i>
                                </div>
                                <p class="text-base text-slate-500">No se encontraron estudiantes con los filtros aplicados.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $row): 
                            // Determine GPA color
                            $gpa = (float)$row['gpa'];
                            $gpaColor = 'bg-slate-100 text-slate-600 border-slate-200';
                            if ($gpa >= 8.5) $gpaColor = 'bg-emerald-50 text-emerald-600 border-emerald-200 shadow-emerald-500/10';
                            elseif ($gpa >= 6.0) $gpaColor = 'bg-amber-50 text-amber-600 border-amber-200 shadow-amber-500/10';
                            elseif ($gpa > 0) $gpaColor = 'bg-rose-50 text-rose-600 border-rose-200 shadow-rose-500/10';
                        ?>
                            <tr class="group hover:bg-indigo-50/30 transition duration-300">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-4">
                                        <?php if (!empty($row['photo_url'])): ?>
                                            <div class="w-14 h-14 rounded-2xl overflow-hidden flex-shrink-0 border-2 border-white shadow-md transform group-hover:scale-105 transition duration-300">
                                                <img src="<?= htmlspecialchars($row['photo_url']) ?>" alt="Avatar" class="w-full h-full object-cover">
                                            </div>
                                        <?php else: ?>
                                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-100 to-indigo-50 border-2 border-white flex items-center justify-center text-indigo-500 font-extrabold flex-shrink-0 shadow-md transform group-hover:scale-105 transition duration-300 text-lg">
                                                <?= strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <p class="font-extrabold text-slate-800 text-base leading-tight group-hover:text-indigo-600 transition">
                                                <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                            </p>
                                            <div class="flex items-center space-x-2 mt-1">
                                                <span class="text-[10px] text-slate-400 font-mono bg-slate-100 px-1.5 py-0.5 rounded"><?= htmlspecialchars($row['document']) ?></span>
                                                <span class="text-xs text-slate-400 truncate max-w-[150px]"><i class="fa-regular fa-envelope mr-1"></i><?= htmlspecialchars($row['email']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1.5">
                                        <p class="text-sm text-slate-700 font-bold"><?= htmlspecialchars($row['course_name'] ?? 'Sin Curso') ?></p>
                                        <span class="inline-flex items-center space-x-1 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-md bg-indigo-50 text-indigo-600 border border-indigo-100/50">
                                            <i class="fa-solid fa-layer-group text-[8px]"></i>
                                            <span><?= $row['grade'] ? htmlspecialchars($row['grade']) : 'Sin nivel' ?></span>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex flex-col items-center justify-center w-14 h-14 rounded-full border-[3px] <?= $gpaColor ?> shadow-sm transform group-hover:rotate-3 transition duration-300 relative">
                                        <span class="text-sm font-extrabold leading-none"><?= number_format($row['gpa'], 1) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($row['parent_first']): ?>
                                        <div class="flex items-center space-x-2">
                                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-xs font-bold">
                                                <i class="fa-solid fa-user-tie"></i>
                                            </div>
                                            <span class="text-sm font-bold text-slate-600"><?= htmlspecialchars($row['parent_first'] . ' ' . $row['parent_last']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="inline-flex items-center space-x-1 text-slate-400 text-xs font-medium bg-slate-50 px-2 py-1 rounded-lg border border-slate-100">
                                            <i class="fa-solid fa-circle-exclamation text-[10px]"></i> <span>Sin acudiente</span>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <!-- Edit Profile -->
                                        <a href="/modules/students/edit.php?id=<?= $row['id'] ?>" title="Editar Perfil" class="inline-flex items-center justify-center w-9 h-9 bg-white border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50 text-slate-400 hover:text-indigo-600 rounded-xl transition duration-300 shadow-sm hover:shadow group-hover:-translate-y-1">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <!-- Medical Record -->
                                        <a href="/modules/medical_records/index.php?search=<?= urlencode($row['document']) ?>" title="Ficha Médica" class="inline-flex items-center justify-center w-9 h-9 bg-white border border-slate-200 hover:border-rose-300 hover:bg-rose-50 text-slate-400 hover:text-rose-500 rounded-xl transition duration-300 shadow-sm hover:shadow group-hover:-translate-y-1">
                                            <i class="fa-solid fa-heart-pulse"></i>
                                        </a>
                                        <!-- Grades/Report -->
                                        <a href="/modules/grades/index.php?search=<?= urlencode($row['document']) ?>" title="Calificaciones" class="inline-flex items-center justify-center w-9 h-9 bg-white border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50 text-slate-400 hover:text-emerald-600 rounded-xl transition duration-300 shadow-sm hover:shadow group-hover:-translate-y-1">
                                            <i class="fa-solid fa-file-contract"></i>
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
