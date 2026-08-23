<?php
// /modules/certificates/generate.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$error = '';
$success = '';

// Get all active students for dropdown
$students = $db->query("
    SELECT s.id, u.first_name, u.last_name, s.document, c.name as course_name 
    FROM students s 
    JOIN users u ON s.user_id = u.id 
    LEFT JOIN courses c ON s.course_id = c.id
    ORDER BY u.last_name ASC
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)$_POST['student_id'];
    $title = Auth::sanitize($_POST['title']);
    $issue_date = Auth::sanitize($_POST['issue_date']);
    $description = Auth::sanitize($_POST['description'] ?? '');

    if (empty($student_id) || empty($title) || empty($issue_date)) {
        $error = 'Por favor, completa los campos obligatorios.';
    } else {
        $stmt = $db->prepare("INSERT INTO certificates (student_id, title, issue_date, description) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$student_id, $title, $issue_date, $description])) {
            header("Location: /modules/certificates/index.php");
            exit;
        } else {
            $error = 'Error al emitir el certificado.';
        }
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Emitir Certificado</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Generar nuevo diploma o reconocimiento oficial para un estudiante.</p>
    </div>
    <a href="/modules/certificates/index.php" class="inline-flex items-center space-x-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-3 rounded-xl font-bold transition active:scale-[0.98]">
        <i class="fa-solid fa-arrow-left text-sm"></i>
        <span>Volver a Certificados</span>
    </a>
</div>

<div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 max-w-2xl relative overflow-hidden">
    <!-- Decorative background element -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-amber-400 opacity-5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>

    <?php if ($error): ?>
        <div class="bg-rose-50 text-rose-600 p-4 rounded-xl mb-6 font-medium text-sm border border-rose-100 flex items-center space-x-2 relative z-10">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6 relative z-10">
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Estudiante Galardonado <span class="text-rose-500">*</span></label>
            <select name="student_id" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-amber-500 focus:border-amber-500 outline-none text-slate-800 transition">
                <option value="">Seleccione al estudiante...</option>
                <?php foreach($students as $std): ?>
                    <option value="<?= $std['id'] ?>">
                        <?= htmlspecialchars($std['last_name'] . ' ' . $std['first_name'] . ' - ' . ($std['course_name'] ?? 'Sin Curso')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Título del Certificado <span class="text-rose-500">*</span></label>
                <input type="text" name="title" required placeholder="Ej. Diploma de Excelencia Académica"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-amber-500 focus:border-amber-500 outline-none text-slate-800 transition">
            </div>
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Fecha de Emisión <span class="text-rose-500">*</span></label>
                <input type="date" name="issue_date" required value="<?= date('Y-m-d') ?>"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-amber-500 focus:border-amber-500 outline-none text-slate-800 transition">
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Motivo o Descripción (Opcional)</label>
            <textarea name="description" rows="3" placeholder="Por su destacado rendimiento en..."
                      class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-amber-500 focus:border-amber-500 outline-none text-slate-800 transition resize-none"></textarea>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button type="submit" class="bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-amber-500/30 hover:shadow-amber-500/40 transition active:scale-[0.98] flex items-center space-x-2">
                <i class="fa-solid fa-stamp"></i>
                <span>Emitir Oficialmente</span>
            </button>
        </div>
    </form>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
