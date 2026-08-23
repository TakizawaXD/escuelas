<?php
// /modules/transport/edit.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: /modules/transport/index.php");
    exit;
}

$stmt = $db->prepare("SELECT * FROM transport_routes WHERE id = ?");
$stmt->execute([$id]);
$route = $stmt->fetch();

if (!$route) {
    header("Location: /modules/transport/index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = Auth::sanitize($_POST['name'] ?? '');
    $driver_name = Auth::sanitize($_POST['driver_name'] ?? '');
    $vehicle_plate = Auth::sanitize($_POST['vehicle_plate'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 40);

    if (empty($name) || empty($driver_name) || empty($vehicle_plate)) {
        $error = 'Por favor completa todos los campos requeridos.';
    } elseif ($capacity < 1) {
        $error = 'La capacidad debe ser mayor a 0.';
    } else {
        $stmt = $db->prepare("UPDATE transport_routes SET name = ?, driver_name = ?, vehicle_plate = ?, capacity = ? WHERE id = ?");
        if ($stmt->execute([$name, $driver_name, $vehicle_plate, $capacity, $id])) {
            header("Location: /modules/transport/index.php");
            exit;
        } else {
            $error = 'Error al actualizar la ruta.';
        }
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Editar Ruta</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Actualizar los datos del autobús y conductor.</p>
    </div>
    <a href="/modules/transport/index.php" class="inline-flex items-center space-x-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-3 rounded-xl font-bold transition active:scale-[0.98]">
        <i class="fa-solid fa-arrow-left text-sm"></i>
        <span>Volver a Rutas</span>
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
            <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Nombre de la Ruta <span class="text-rose-500">*</span></label>
            <input type="text" name="name" required value="<?= htmlspecialchars($route['name']) ?>"
                   class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Conductor Asignado <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-regular fa-id-card text-sm"></i>
                    </div>
                    <input type="text" name="driver_name" required value="<?= htmlspecialchars($route['driver_name']) ?>"
                           class="w-full pl-11 pr-4 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Matrícula (Placa) <span class="text-rose-500">*</span></label>
                <input type="text" name="vehicle_plate" required value="<?= htmlspecialchars($route['vehicle_plate']) ?>"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-slate-800 transition font-mono uppercase">
            </div>
        </div>

        <div>
            <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Capacidad del Bus (Asientos) <span class="text-rose-500">*</span></label>
            <input type="number" name="capacity" required min="1" value="<?= htmlspecialchars($route['capacity']) ?>"
                   class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button type="submit" class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white px-8 py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 transition-all flex items-center justify-center">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
