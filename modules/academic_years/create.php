<?php
// /modules/academic_years/create.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    header("Location: /index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = Auth::sanitize($_POST['name'] ?? '');
    $start_date = Auth::sanitize($_POST['start_date'] ?? '');
    $end_date = Auth::sanitize($_POST['end_date'] ?? '');
    $active = isset($_POST['active']) ? 1 : 0;

    if (empty($name) || empty($start_date) || empty($end_date)) {
        $error = 'Por favor completa todos los campos requeridos.';
    } else {
        $db = Database::getInstance()->getConnection();
        
        // Check if name exists
        $stmt = $db->prepare("SELECT id FROM academic_years WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            $error = 'Ya existe un año lectivo con este nombre.';
        } else {
            // If this is set to active, optionally deactivate others. Let's keep it simple for now and allow multiple active or just let user manage it.
            // Better UX: if setting to active, deactivate others
            if ($active == 1) {
                $db->exec("UPDATE academic_years SET active = 0");
            }

            $stmt = $db->prepare("INSERT INTO academic_years (name, start_date, end_date, active) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $start_date, $end_date, $active])) {
                header("Location: /modules/academic_years/index.php");
                exit;
            } else {
                $error = 'Error al crear el año lectivo.';
            }
        }
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Agregar Año Lectivo</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Registrar un nuevo periodo académico en el sistema.</p>
    </div>
    <a href="/modules/academic_years/index.php" class="inline-flex items-center space-x-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-3 rounded-xl font-bold transition active:scale-[0.98]">
        <i class="fa-solid fa-arrow-left text-sm"></i>
        <span>Volver al Listado</span>
    </a>
</div>

<div class="bg-white p-8 md:p-12 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 max-w-3xl mx-auto relative overflow-hidden group">
    <!-- ambient background -->
    <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-50 rounded-full blur-3xl opacity-50 pointer-events-none transition-opacity group-hover:opacity-100"></div>
    <div class="relative z-10">
    <?php if ($error): ?>
        <div class="bg-rose-50 text-rose-600 p-4 rounded-xl mb-6 font-medium text-sm border border-rose-100 flex items-center space-x-2">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <div>
            <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Nombre del Periodo <span class="text-rose-500">*</span></label>
            <input type="text" name="name" required placeholder="Ej. Año Lectivo 2026-2027"
                   class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Fecha de Inicio <span class="text-rose-500">*</span></label>
                <input type="date" name="start_date" required
                       class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            </div>
            
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Fecha de Fin <span class="text-rose-500">*</span></label>
                <input type="date" name="end_date" required
                       class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            </div>
        </div>

        <div class="flex items-center mt-4">
            <input id="active" name="active" type="checkbox" class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <label for="active" class="ml-2 block text-sm font-bold text-slate-700">
                Marcar como el Año Lectivo Activo (Actual)
            </label>
        </div>
        <p class="text-xs text-slate-500 mt-1 ml-7">Al marcar esta opción, los demás periodos se marcarán como inactivos automáticamente.</p>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button type="submit" class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white px-8 py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 transition-all flex items-center justify-center">
                Guardar Año Lectivo
            </button>
        </div>
    </form>
</div>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
