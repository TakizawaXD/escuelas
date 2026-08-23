<?php
// /modules/teachers/schedule.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /modules/teachers/index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT t.id, u.first_name, u.last_name FROM teachers t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
$stmt->execute([$id]);
$teacher = $stmt->fetch();

if (!$teacher) {
    header("Location: /modules/teachers/index.php");
    exit;
}

// Fetch real schedule from DB
$scheduleStmt = $db->prepare("
    SELECT sch.day_of_week, sch.start_time, sch.end_time,
           sub.name as subject_name, c.name as course_name, cl.name as classroom_name
    FROM schedules sch
    JOIN subjects sub ON sch.subject_id = sub.id
    JOIN courses c ON sch.course_id = c.id
    JOIN classrooms cl ON sch.classroom_id = cl.id
    WHERE sch.teacher_id = ?
    ORDER BY sch.day_of_week ASC, sch.start_time ASC
");
$scheduleStmt->execute([$id]);
$scheduleRows = $scheduleStmt->fetchAll();

// Build indexed grid: [day_of_week][start_time] => row
$scheduleGrid = [];
$timeSlots = [];
$days = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes'];

foreach ($scheduleRows as $row) {
    $slot = $row['start_time'] . '-' . $row['end_time'];
    if (!in_array($slot, $timeSlots)) $timeSlots[] = $slot;
    $scheduleGrid[$row['day_of_week']][$slot] = $row;
}
sort($timeSlots);

// Palette for subjects (cycle through colors)
$colors = [
    'indigo'  => 'bg-indigo-50/70 text-indigo-700 border-indigo-200',
    'emerald' => 'bg-emerald-50/70 text-emerald-700 border-emerald-200',
    'violet'  => 'bg-violet-50/70 text-violet-700 border-violet-200',
    'amber'   => 'bg-amber-50/70 text-amber-700 border-amber-200',
    'rose'    => 'bg-rose-50/70 text-rose-700 border-rose-200',
    'teal'    => 'bg-teal-50/70 text-teal-700 border-teal-200',
];
$colorKeys = array_keys($colors);
$subjectColors = [];
$colorIdx = 0;
foreach ($scheduleRows as $row) {
    if (!isset($subjectColors[$row['subject_name']])) {
        $subjectColors[$row['subject_name']] = $colors[$colorKeys[$colorIdx % count($colorKeys)]];
        $colorIdx++;
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<style>
.schedule-cell { min-width: 130px; }
</style>

<div class="space-y-8 animate-fade-in pb-10 max-w-6xl mx-auto">

    <!-- Hero Header -->
    <div class="relative rounded-3xl bg-gradient-to-br from-indigo-900 via-slate-900 to-violet-950 p-8 shadow-2xl overflow-hidden text-white">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-72 h-72 rounded-full bg-indigo-500 blur-[80px] opacity-20"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <a href="/modules/teachers/view.php?id=<?= $id ?>" class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition">
                    <i class="fa-solid fa-arrow-left text-white"></i>
                </a>
                <div>
                    <h2 class="text-3xl font-extrabold tracking-tight">Horario <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-300 to-violet-300">de Clases</span></h2>
                    <p class="text-indigo-200/80 text-sm mt-1 font-medium">
                        <i class="fa-solid fa-user-tie mr-1"></i>
                        Docente: <?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) ?>
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <div class="bg-white/10 border border-white/20 px-5 py-2.5 rounded-2xl text-sm font-bold">
                    <i class="fa-solid fa-calendar-week mr-2 text-indigo-300"></i>
                    <?= count($scheduleRows) ?> bloques asignados
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($scheduleRows)): ?>
        <!-- No schedule yet -->
        <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 p-16 text-center">
            <div class="w-20 h-20 mx-auto bg-slate-50 rounded-full flex items-center justify-center mb-5 text-slate-300 shadow-inner">
                <i class="fa-solid fa-calendar-xmark text-4xl"></i>
            </div>
            <h3 class="text-xl font-extrabold text-slate-700 mb-2">Sin horario asignado</h3>
            <p class="text-slate-400 text-sm max-w-sm mx-auto">Este docente aún no tiene bloques de clase registrados en el sistema. Un administrador puede crear el horario desde el módulo de configuración.</p>
        </div>
    <?php else: ?>
        <!-- Schedule Grid -->
        <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100">
                            <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-widest text-slate-400 w-36">Horario</th>
                            <?php foreach ($days as $dayNum => $dayName): ?>
                                <th class="schedule-cell px-5 py-4 text-center text-xs font-extrabold uppercase tracking-widest text-slate-400"><?= $dayName ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach ($timeSlots as $slot): ?>
                            <?php [$start, $end] = explode('-', $slot); ?>
                            <tr class="hover:bg-slate-50/40 transition duration-200">
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-block bg-slate-100 text-slate-600 font-extrabold text-xs px-3 py-2 rounded-xl leading-snug">
                                        <?= htmlspecialchars($start) ?><br>
                                        <span class="text-slate-400 font-medium"><?= htmlspecialchars($end) ?></span>
                                    </span>
                                </td>
                                <?php foreach ($days as $dayNum => $dayName): ?>
                                    <td class="schedule-cell px-3 py-3 text-center">
                                        <?php if (isset($scheduleGrid[$dayNum][$slot])): ?>
                                            <?php $entry = $scheduleGrid[$dayNum][$slot]; ?>
                                            <?php $cls = $subjectColors[$entry['subject_name']] ?? 'bg-slate-50 text-slate-700 border-slate-200'; ?>
                                            <div class="rounded-xl border px-3 py-2.5 <?= $cls ?>">
                                                <p class="font-extrabold text-sm leading-tight"><?= htmlspecialchars($entry['subject_name']) ?></p>
                                                <p class="text-xs mt-0.5 font-medium opacity-80"><?= htmlspecialchars($entry['course_name']) ?></p>
                                                <p class="text-[10px] mt-1 opacity-60 font-bold uppercase tracking-wider">
                                                    <i class="fa-solid fa-door-open mr-0.5"></i> <?= htmlspecialchars($entry['classroom_name']) ?>
                                                </p>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-slate-200 font-bold">—</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Legend -->
            <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/30 flex flex-wrap gap-3 items-center">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-2">Materias:</span>
                <?php foreach ($subjectColors as $subjectName => $colorClass): ?>
                    <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-lg border text-xs font-bold <?= $colorClass ?>">
                        <i class="fa-solid fa-circle text-[6px]"></i>
                        <span><?= htmlspecialchars($subjectName) ?></span>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
