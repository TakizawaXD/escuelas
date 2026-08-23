<?php
// /modules/exams/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();

$db = Database::getInstance()->getConnection();
$role = Auth::user()['role_name'];
$user_id = Auth::user()['id'];
$search = Auth::sanitize($_GET['search'] ?? '');

$query = "
    SELECT e.*, sub.name as subject_name, c.name as course_name, u.first_name, u.last_name
    FROM exams e
    JOIN subjects sub ON e.subject_id = sub.id
    JOIN courses c ON sub.course_id = c.id
    JOIN teachers t ON e.teacher_id = t.id
    JOIN users u ON t.user_id = u.id
    WHERE 1=1
";
$params = [];

if ($role === 'DOCENTE') {
    // Teachers only see their exams
    $query .= " AND t.user_id = ?";
    $params[] = $user_id;
} elseif ($role === 'ESTUDIANTE') {
    // Students see exams for their course
    $query .= " AND c.id = (SELECT course_id FROM students WHERE user_id = ?)";
    $params[] = $user_id;
}

if (!empty($search)) {
    $query .= " AND (e.title LIKE ? OR sub.name LIKE ?)";
    array_push($params, "%$search%", "%$search%");
}

$query .= " ORDER BY e.exam_date DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$exams = $stmt->fetchAll();

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
    <div class="relative rounded-3xl bg-gradient-to-br from-purple-900 via-slate-900 to-fuchsia-950 p-8 shadow-2xl overflow-hidden text-white">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-72 h-72 rounded-full bg-purple-500 blur-[80px] opacity-30"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 rounded-full bg-pink-500 blur-[80px] opacity-20"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="text-4xl font-extrabold tracking-tight mb-2">Gestión de <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-300 to-pink-300">Exámenes</span></h2>
                <p class="text-purple-200/80 font-medium text-sm max-w-md leading-relaxed">Programación y resultados de evaluaciones institucionales.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-4">
                <div class="glass-panel px-6 py-3 rounded-2xl flex items-center space-x-4 shadow-lg shadow-black/10">
                    <div class="w-12 h-12 rounded-full bg-purple-500/30 flex items-center justify-center text-purple-200">
                        <i class="fa-solid fa-file-signature text-xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-purple-200/70 tracking-widest">Evaluaciones</p>
                        <p class="text-2xl font-extrabold text-white leading-none"><?= count($exams) ?></p>
                    </div>
                </div>
                
                <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR', 'DOCENTE'])): ?>
                <a href="/modules/exams/create.php" class="inline-flex h-14 items-center space-x-2 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-400 hover:to-pink-400 text-white px-6 rounded-2xl font-bold tracking-wide transition shadow-lg shadow-purple-500/30 hover:-translate-y-0.5 border border-purple-400/30">
                    <i class="fa-solid fa-plus text-sm"></i>
                    <span>Programar Examen</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Advanced Search & Filter Bar (Glassmorphism) -->
    <div class="glass-panel p-2 rounded-2xl shadow-sm flex flex-col md:flex-row items-center justify-between gap-2 z-20 relative">
        <form method="GET" class="w-full flex flex-col md:flex-row items-center gap-2">
            <div class="relative w-full group">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-purple-500 transition">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar por título o asignatura..."
                       class="block w-full pl-12 pr-4 py-4 bg-white/50 hover:bg-white/80 focus:bg-white border border-transparent focus:border-purple-200 text-slate-800 rounded-xl outline-none text-sm font-medium transition shadow-inner placeholder-slate-400">
            </div>
            <button type="submit" class="w-full md:w-auto px-8 py-4 bg-slate-900 hover:bg-black text-white font-bold rounded-xl transition flex justify-center items-center space-x-2 shrink-0">
                <i class="fa-solid fa-filter text-xs"></i>
                <span>Buscar</span>
            </button>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden relative z-10">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-400 uppercase tracking-widest text-[10px] font-bold">
                        <th scope="col" class="px-6 py-5">Fecha Programada</th>
                        <th scope="col" class="px-6 py-5">Evaluación</th>
                        <th scope="col" class="px-6 py-5">Asignatura y Curso</th>
                        <th scope="col" class="px-6 py-5">Docente</th>
                        <th scope="col" class="px-6 py-5 text-center">Gestión</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($exams)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center text-slate-400 font-medium">
                                <div class="w-20 h-20 mx-auto bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-300 shadow-inner">
                                    <i class="fa-solid fa-file-circle-xmark text-3xl"></i>
                                </div>
                                <p class="text-base text-slate-500">No hay exámenes registrados para mostrar.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($exams as $row): 
                            $isPast = strtotime($row['exam_date']) < strtotime('today');
                            $dateColor = $isPast ? 'bg-slate-100 text-slate-600' : 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                            $dateIcon = $isPast ? 'fa-calendar-check' : 'fa-calendar-day';
                        ?>
                            <tr class="group hover:bg-purple-50/30 transition duration-300">
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold shadow-sm <?= $dateColor ?>">
                                        <i class="fa-solid <?= $dateIcon ?> text-[10px]"></i>
                                        <span><?= date('d M, Y', strtotime($row['exam_date'])) ?></span>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600 border border-purple-100/50">
                                            <i class="fa-solid fa-spell-check"></i>
                                        </div>
                                        <div>
                                            <p class="font-extrabold text-slate-800 text-base leading-tight group-hover:text-purple-600 transition">
                                                <?= htmlspecialchars($row['title']) ?>
                                            </p>
                                            <div class="text-[10px] text-slate-400 font-bold mt-0.5 uppercase tracking-wider">
                                                Puntaje Max: <span class="text-purple-500 bg-purple-50 px-1 rounded"><?= $row['max_score'] ?> pts</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-700"><?= htmlspecialchars($row['subject_name']) ?></div>
                                    <div class="text-xs text-slate-500 flex items-center mt-1">
                                        <i class="fa-solid fa-layer-group text-[10px] mr-1 text-slate-400"></i>
                                        <?= htmlspecialchars($row['course_name']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 text-[10px] font-bold">
                                            <?= strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1)) ?>
                                        </div>
                                        <span class="text-sm font-semibold text-slate-600"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR', 'DOCENTE'])): ?>
                                        <div class="flex items-center justify-center space-x-2 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                            <a href="/modules/exams/results.php?id=<?= $row['id'] ?>" class="inline-flex items-center space-x-1.5 px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white rounded-xl transition text-xs font-bold shadow-md shadow-emerald-500/20 hover:-translate-y-0.5">
                                                <i class="fa-solid fa-list-check"></i>
                                                <span>Calificar</span>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-slate-100 text-slate-500 text-xs font-medium border border-slate-200">
                                            <i class="fa-solid fa-clock-rotate-left mr-1.5"></i> Pendiente
                                        </span>
                                    <?php endif; ?>
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
