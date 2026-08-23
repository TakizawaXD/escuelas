<?php
// /modules/cafeteria/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();

$db = Database::getInstance()->getConnection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    $day = Auth::sanitize($_POST['day_of_week'] ?? '');
    $meal_type = Auth::sanitize($_POST['meal_type'] ?? 'Almuerzo');
    $description = Auth::sanitize($_POST['description'] ?? '');
    
    if ($day && $description) {
        // check if exists to update or insert
        $stmtCheck = $db->prepare("SELECT id FROM cafeteria_menus WHERE day_of_week = ? AND meal_type = ?");
        $stmtCheck->execute([$day, $meal_type]);
        $existing = $stmtCheck->fetch();
        
        if ($existing) {
            $stmt = $db->prepare("UPDATE cafeteria_menus SET description = ? WHERE id = ?");
            if ($stmt->execute([$description, $existing['id']])) {
                $success = 'Menú actualizado.';
            }
        } else {
            $stmt = $db->prepare("INSERT INTO cafeteria_menus (day_of_week, meal_type, description) VALUES (?, ?, ?)");
            if ($stmt->execute([$day, $meal_type, $description])) {
                $success = 'Menú guardado.';
            }
        }
    }
}

// Fetch all menus
$menusRaw = $db->query("SELECT * FROM cafeteria_menus")->fetchAll();
$menus = [];
foreach ($menusRaw as $m) {
    $menus[$m['day_of_week']][$m['meal_type']] = $m['description'];
}

$days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
$meal_types = ['Desayuno', 'Almuerzo'];

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
    <div class="relative rounded-3xl bg-gradient-to-br from-amber-900 via-slate-900 to-orange-950 p-8 shadow-2xl overflow-hidden text-white">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-72 h-72 rounded-full bg-amber-500 blur-[80px] opacity-30"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 rounded-full bg-orange-500 blur-[80px] opacity-20"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="text-4xl font-extrabold tracking-tight mb-2">Comedor <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 to-orange-300">Escolar</span></h2>
                <p class="text-amber-200/80 font-medium text-sm max-w-md leading-relaxed">Planificación del menú alimenticio semanal para toda la institución.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-4">
                <div class="glass-panel px-6 py-3 rounded-2xl flex items-center space-x-4 shadow-lg shadow-black/10">
                    <div class="w-12 h-12 rounded-full bg-amber-500/30 flex items-center justify-center text-amber-200">
                        <i class="fa-solid fa-utensils text-xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-amber-200/70 tracking-widest">Estado</p>
                        <p class="text-lg font-extrabold text-white leading-none">Semana Activa</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="bg-emerald-50 text-emerald-600 p-4 rounded-2xl font-bold text-sm border border-emerald-200 shadow-sm flex items-center space-x-3 animate-fade-in">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])): ?>
    <!-- Admin Menu Planner -->
    <div class="glass-panel p-8 rounded-3xl shadow-sm border border-slate-100/80 z-20 relative">
        <h3 class="font-extrabold text-slate-800 text-lg flex items-center mb-6">
            <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mr-3">
                <i class="fa-solid fa-pen-to-square"></i>
            </div>
            Actualizar Menú Semanal
        </h3>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
            <div class="relative group">
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Día de la Semana</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-amber-500 transition">
                        <i class="fa-solid fa-calendar-day"></i>
                    </div>
                    <select name="day_of_week" required class="block w-full pl-11 pr-10 py-3.5 bg-white/50 hover:bg-white/80 focus:bg-white border border-transparent focus:border-amber-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition shadow-inner appearance-none cursor-pointer">
                        <?php foreach($days as $d): ?>
                            <option value="<?= $d ?>"><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <div class="relative group">
                <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Tipo de Comida</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-amber-500 transition">
                        <i class="fa-solid fa-bowl-food"></i>
                    </div>
                    <select name="meal_type" required class="block w-full pl-11 pr-10 py-3.5 bg-white/50 hover:bg-white/80 focus:bg-white border border-transparent focus:border-amber-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition shadow-inner appearance-none cursor-pointer">
                        <?php foreach($meal_types as $mt): ?>
                            <option value="<?= $mt ?>"><?= $mt ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2 flex flex-col md:flex-row gap-4">
                <div class="flex-1 relative group">
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Descripción del Menú</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-amber-500 transition">
                            <i class="fa-solid fa-align-left"></i>
                        </div>
                        <input type="text" name="description" required placeholder="Ej. Sopa de tomate, Pollo asado..."
                               class="block w-full pl-11 pr-4 py-3.5 bg-white/50 hover:bg-white/80 focus:bg-white border border-transparent focus:border-amber-200 text-slate-800 rounded-xl outline-none text-sm font-medium transition shadow-inner">
                    </div>
                </div>
                <div class="flex-shrink-0 flex items-end">
                    <button type="submit" class="w-full md:w-auto h-[52px] bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-white px-8 rounded-xl font-bold transition shadow-lg shadow-amber-500/30 hover:-translate-y-0.5 border border-amber-400/30 flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Guardar</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Menu Display Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <?php foreach($days as $day): 
            $isToday = ($day === 'Lunes'); // Simplification for UI demonstration, could use date('N') logic
        ?>
        <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden flex flex-col hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="bg-gradient-to-r from-slate-800 to-slate-900 text-white text-center py-4 font-extrabold uppercase tracking-widest text-sm relative overflow-hidden">
                <div class="absolute inset-0 bg-white/5 opacity-50"></div>
                <span class="relative z-10"><?= $day ?></span>
            </div>
            <div class="p-6 flex-1 space-y-6">
                <?php foreach($meal_types as $type): 
                    $desc = $menus[$day][$type] ?? '';
                    $hasMenu = !empty($desc);
                ?>
                    <div class="group relative">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-2 text-xs font-extrabold uppercase tracking-widest text-amber-600">
                                <div class="w-6 h-6 rounded-md bg-amber-50 flex items-center justify-center border border-amber-100">
                                    <?php if ($type === 'Desayuno'): ?>
                                        <i class="fa-solid fa-mug-hot"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-burger"></i>
                                    <?php endif; ?>
                                </div>
                                <span><?= $type ?></span>
                            </div>
                        </div>
                        <div class="relative">
                            <?php if ($hasMenu): ?>
                                <p class="text-sm font-medium text-slate-700 bg-slate-50/80 p-4 rounded-2xl border border-slate-100 shadow-inner group-hover:bg-amber-50/30 group-hover:border-amber-100 transition duration-300 leading-relaxed min-h-[80px]">
                                    <?= htmlspecialchars($desc) ?>
                                </p>
                            <?php else: ?>
                                <div class="flex flex-col items-center justify-center p-4 rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 min-h-[80px] text-slate-400">
                                    <i class="fa-solid fa-circle-question mb-1 opacity-50"></i>
                                    <span class="text-xs font-semibold">No asignado</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
