<?php
// /modules/calendar/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();

$db = Database::getInstance()->getConnection();
$user_id = Auth::user()['id'];
$role = Auth::user()['role_name'];
$error = '';
$success = '';

// Add event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    $title = Auth::sanitize($_POST['title'] ?? '');
    $start_date = Auth::sanitize($_POST['start_date'] ?? '');
    $end_date = Auth::sanitize($_POST['end_date'] ?? '');
    $color = Auth::sanitize($_POST['color'] ?? '#4f46e5');
    
    if (empty($end_date)) $end_date = null;

    if ($title && $start_date) {
        $stmt = $db->prepare("INSERT INTO calendar_events (title, start_date, end_date, color, user_id) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $start_date, $end_date, $color, $user_id])) {
            $success = 'Evento registrado en el calendario.';
        } else {
            $error = 'Error al registrar el evento.';
        }
    }
}

// Delete event
if (isset($_GET['delete_id']) && Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    $del_id = (int)$_GET['delete_id'];
    $db->prepare("DELETE FROM calendar_events WHERE id = ?")->execute([$del_id]);
    header("Location: /modules/calendar/index.php");
    exit;
}

$events = $db->query("SELECT * FROM calendar_events ORDER BY start_date ASC")->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Calendario de Eventos</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Actividades, feriados y fechas cívicas de la institución.</p>
    </div>
</div>

<?php if ($success): ?>
    <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 font-medium text-sm border border-emerald-100 flex items-center space-x-2">
        <i class="fa-solid fa-circle-check"></i>
        <span><?= htmlspecialchars($success) ?></span>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])): ?>
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 h-fit">
        <h3 class="font-bold text-slate-800 text-sm uppercase tracking-widest mb-4"><i class="fa-solid fa-calendar-plus text-indigo-500 mr-2"></i> Nuevo Evento</h3>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Título del Evento <span class="text-rose-500">*</span></label>
                <input type="text" name="title" required placeholder="Ej. Día del Estudiante"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Fecha de Inicio <span class="text-rose-500">*</span></label>
                <input type="date" name="start_date" required
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Fecha de Fin (Opcional)</label>
                <input type="date" name="end_date"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Color del Evento</label>
                <select name="color" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                    <option value="#4f46e5">Índigo (Normal)</option>
                    <option value="#ef4444">Rojo (Feriado)</option>
                    <option value="#10b981">Verde (Cívico)</option>
                    <option value="#f59e0b">Naranja (Aviso)</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl font-bold transition shadow-md">
                Guardar Evento
            </button>
        </form>
    </div>
    <?php endif; ?>

    <div class="<?= Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR']) ? 'lg:col-span-2' : 'lg:col-span-3' ?>">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden h-full p-6">
            <h3 class="font-bold text-slate-800 text-sm uppercase tracking-widest mb-6"><i class="fa-solid fa-list-ul text-slate-500 mr-2"></i> Próximos Eventos</h3>
            <div class="space-y-4">
                <?php if (empty($events)): ?>
                    <div class="text-center text-slate-400 py-10">
                        <i class="fa-regular fa-calendar-xmark text-4xl mb-3 text-slate-200 block"></i>
                        No hay eventos programados.
                    </div>
                <?php else: ?>
                    <?php foreach ($events as $ev): ?>
                        <div class="flex items-start p-4 rounded-2xl border border-slate-100 hover:border-slate-200 transition group relative overflow-hidden">
                            <div class="absolute left-0 top-0 bottom-0 w-2" style="background-color: <?= $ev['color'] ?>"></div>
                            
                            <div class="ml-4 flex-1">
                                <h4 class="font-bold text-slate-800 text-lg"><?= htmlspecialchars($ev['title']) ?></h4>
                                <div class="flex items-center text-sm font-medium text-slate-500 mt-1">
                                    <i class="fa-regular fa-calendar mr-2"></i>
                                    <span><?= date('d M, Y', strtotime($ev['start_date'])) ?></span>
                                    <?php if ($ev['end_date']): ?>
                                        <span class="mx-2">-</span>
                                        <span><?= date('d M, Y', strtotime($ev['end_date'])) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])): ?>
                                <a href="?delete_id=<?= $ev['id'] ?>" onclick="return confirm('¿Eliminar evento?')" class="text-slate-300 hover:text-rose-500 transition px-3 py-2 opacity-0 group-hover:opacity-100">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
