<?php
// /modules/payments/create.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();

$students = $db->query("
    SELECT s.*, u.first_name, u.last_name, u.document 
    FROM students s
    JOIN users u ON s.user_id = u.id
    ORDER BY u.first_name ASC
")->fetchAll();

$courses = $db->query("SELECT * FROM courses ORDER BY name ASC")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_type = Auth::sanitize($_POST['target_type'] ?? 'student');
    $concept = Auth::sanitize($_POST['concept'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0.00);
    $due_date = Auth::sanitize($_POST['due_date'] ?? date('Y-m-d'));

    if (!empty($concept) && $amount > 0 && !empty($due_date)) {
        try {
            if ($target_type === 'course') {
                $course_id = intval($_POST['course_id'] ?? 0);
                if ($course_id > 0) {
                    // Assign to all students in that course
                    $stmt = $db->prepare("SELECT id FROM students WHERE course_id = ?");
                    $stmt->execute([$course_id]);
                    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

                    if (!empty($ids)) {
                        $stmt = $db->prepare("
                            INSERT INTO payments (student_id, concept, amount, status, due_date)
                            VALUES (?, ?, ?, 'Pendiente', ?)
                        ");
                        foreach ($ids as $std_id) {
                            $stmt->execute([$std_id, $concept, $amount, $due_date]);
                        }
                    }
                    header("Location: /modules/payments/index.php");
                    exit;
                } else {
                    $error = 'Por favor seleccione un curso válido.';
                }
            } else {
                $student_id = intval($_POST['student_id'] ?? 0);
                if ($student_id > 0) {
                    $stmt = $db->prepare("
                        INSERT INTO payments (student_id, concept, amount, status, due_date)
                        VALUES (?, ?, ?, 'Pendiente', ?)
                    ");
                    $stmt->execute([$student_id, $concept, $amount, $due_date]);

                    header("Location: /modules/payments/index.php");
                    exit;
                } else {
                    $error = 'Por favor seleccione un estudiante.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Error asignando pago: ' . $e->getMessage();
        }
    } else {
        $error = 'Por favor complete todos los campos obligatorios.';
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<!-- Form -->
<div class="max-w-3xl mx-auto bg-white rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 p-8 md:p-12 relative overflow-hidden group">
    <!-- Premium ambient background -->
    <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-50 rounded-full blur-3xl opacity-50 pointer-events-none transition-opacity group-hover:opacity-100"></div>
    <div class="relative z-10">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-1">Asignar Cobro</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Cree deudas individuales o para cursos completos.</p>
        </div>
        <a href="/modules/payments/index.php" class="text-sm font-semibold text-slate-500 hover:text-slate-800 flex items-center space-x-1">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver</span>
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="mb-5 p-4 rounded-xl bg-red-50 text-red-700 text-sm border border-red-200/60 flex items-start space-x-3">
            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
            <span class="font-medium"><?= $error ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <!-- Target Type Select -->
        <div class="bg-slate-50/60 p-4 rounded-2xl border border-slate-100 flex items-center justify-around mb-4">
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="radio" name="target_type" value="student" checked onclick="toggleTarget('student')" class="text-indigo-600 focus:ring-indigo-500 border-slate-300">
                <span class="text-sm font-bold text-slate-700">Por Estudiante</span>
            </label>
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="radio" name="target_type" value="course" onclick="toggleTarget('course')" class="text-indigo-600 focus:ring-indigo-500 border-slate-300">
                <span class="text-sm font-bold text-slate-700">Por Curso Completo</span>
            </label>
        </div>

        <!-- Student selector -->
        <div id="panel-student" class="space-y-4">
            <div>
                <label for="student_id" class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Estudiante *</label>
                <select name="student_id" id="student_id"
                        class="block w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    <option value="">Seleccione un estudiante...</option>
                    <?php foreach ($students as $std): ?>
                        <option value="<?= $std['id'] ?>"><?= htmlspecialchars($std['first_name'] . ' ' . $std['last_name'] . ' (' . $std['document'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Course selector -->
        <div id="panel-course" class="hidden space-y-4">
            <div>
                <label for="course_id" class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Curso / Grado *</label>
                <select name="course_id" id="course_id"
                        class="block w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    <option value="">Seleccione un curso...</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-slate-400 mt-1 font-medium">Todos los estudiantes en este curso recibirán el cobro.</p>
            </div>
        </div>

        <!-- Payment properties -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label for="concept" class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Concepto del Cobro *</label>
                <input type="text" name="concept" id="concept" required placeholder="Ej. Matrícula - Año Escolar, Pensión Mayo"
                       class="block w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            </div>

            <div>
                <label for="amount" class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Monto del Cobro ($) *</label>
                <input type="number" step="0.01" min="0" name="amount" id="amount" required placeholder="Ej. 150000.00"
                       class="block w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            </div>

            <div>
                <label for="due_date" class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Fecha de Vencimiento *</label>
                <input type="date" name="due_date" id="due_date" required value="<?= date('Y-m-d', strtotime('+15 days')) ?>"
                       class="block w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            </div>
        </div>

        <div class="pt-4 flex items-center justify-end space-x-2 border-t border-slate-100">
            <a href="/modules/payments/index.php" class="px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-all text-sm">Cancelar</a>
            <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 text-sm flex items-center justify-center space-x-2">Crear Cobro</button>
        </div>
    </form>
</div>

<script>
function toggleTarget(target) {
    const stdPanel = document.getElementById('panel-student');
    const crsPanel = document.getElementById('panel-course');

    if (target === 'course') {
        stdPanel.classList.add('hidden');
        crsPanel.classList.remove('hidden');
    } else {
        stdPanel.classList.remove('hidden');
        crsPanel.classList.add('hidden');
    }
}
</script>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
