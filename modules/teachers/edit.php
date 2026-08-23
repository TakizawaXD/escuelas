<?php
// /modules/teachers/edit.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    header("Location: /index.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("
    SELECT t.*, u.first_name, u.last_name, u.document 
    FROM teachers t 
    JOIN users u ON t.user_id = u.id 
    WHERE t.id = ?
");
$stmt->execute([$id]);
$teacher = $stmt->fetch();

if (!$teacher) {
    header("Location: /modules/teachers/index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $specialty = Auth::sanitize($_POST['specialty'] ?? '');

    if (!empty($specialty)) {
        try {
            $stmt = $db->prepare("UPDATE teachers SET specialty = ? WHERE id = ?");
            $stmt->execute([$specialty, $id]);

            header("Location: /modules/teachers/index.php");
            exit;
        } catch (PDOException $e) {
            $error = 'Error actualizando: ' . $e->getMessage();
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
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-1">Editar Docente</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Actualice la información laboral de <?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) ?></p>
        </div>
        <a href="/modules/teachers/index.php" class="text-sm font-semibold text-slate-500 hover:text-slate-800 flex items-center space-x-1">
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
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Perfil del Docente</label>
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/60 flex items-center justify-between">
                    <div>
                        <p class="font-bold text-slate-800 text-base"><?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) ?></p>
                        <p class="text-slate-500 text-xs font-mono"><?= htmlspecialchars($teacher['document']) ?></p>
                    </div>
                </div>
            </div>

            <div>
                <label for="specialty" class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Especialidad del Docente *</label>
                <input type="text" name="specialty" id="specialty" value="<?= htmlspecialchars($teacher['specialty']) ?>" required
                       class="block w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            </div>
        </div>

        <div class="pt-4 flex items-center justify-end space-x-2 border-t border-slate-100">
            <a href="/modules/teachers/index.php" class="px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-all text-sm">Cancelar</a>
            <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 text-sm flex items-center justify-center space-x-2">Actualizar Docente</button>
        </div>
    </form>
</div>
</div>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
