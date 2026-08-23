<?php
// /modules/transport/assignments.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$route_id = $_GET['id'] ?? null;

if (!$route_id) {
    header("Location: /modules/transport/index.php");
    exit;
}

// Fetch route details
$stmt = $db->prepare("SELECT * FROM transport_routes WHERE id = ?");
$stmt->execute([$route_id]);
$route = $stmt->fetch();

if (!$route) {
    header("Location: /modules/transport/index.php");
    exit;
}

$error = '';
$success = '';

// Handle delete assignment
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['assignment_id'])) {
    $assignment_id = $_POST['assignment_id'];
    $stmt = $db->prepare("DELETE FROM transport_assignments WHERE id = ? AND route_id = ?");
    $stmt->execute([$assignment_id, $route_id]);
    $success = "Asignación eliminada.";
}

// Handle add assignment
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $student_id = (int)$_POST['student_id'];
    $stop_name = Auth::sanitize($_POST['stop_name']);
    
    if ($student_id <= 0 || empty($stop_name)) {
        $error = "Seleccione un estudiante e ingrese la parada.";
    } else {
        // Check capacity
        $stmt = $db->prepare("SELECT COUNT(*) FROM transport_assignments WHERE route_id = ?");
        $stmt->execute([$route_id]);
        $current_passengers = $stmt->fetchColumn();
        
        if ($current_passengers >= $route['capacity']) {
            $error = "La ruta ya está al máximo de su capacidad.";
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO transport_assignments (route_id, student_id, stop_name) VALUES (?, ?, ?)");
                $stmt->execute([$route_id, $student_id, $stop_name]);
                $success = "Estudiante asignado correctamente a la ruta.";
            } catch (PDOException $e) {
                // Unique constraint failed = student already in another route or this route
                $error = "El estudiante ya está asignado a una ruta de transporte.";
            }
        }
    }
}

// Fetch current assignments
$stmt = $db->prepare("
    SELECT ta.id as assignment_id, ta.stop_name, 
           u.first_name, u.last_name, u.document, c.name as course_name
    FROM transport_assignments ta
    JOIN students s ON ta.student_id = s.id
    JOIN users u ON s.user_id = u.id
    JOIN courses c ON s.course_id = c.id
    WHERE ta.route_id = ?
    ORDER BY ta.stop_name ASC
");
$stmt->execute([$route_id]);
$assignments = $stmt->fetchAll();

// Fetch unassigned students for dropdown
$stmt = $db->query("
    SELECT s.id, u.first_name, u.last_name, c.name as course_name
    FROM students s
    JOIN users u ON s.user_id = u.id
    JOIN courses c ON s.course_id = c.id
    WHERE u.status = 1 
      AND s.id NOT IN (SELECT student_id FROM transport_assignments)
    ORDER BY u.last_name ASC
");
$unassigned_students = $stmt->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Pasajeros de la Ruta</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Ruta: <span class="font-bold text-slate-800"><?= htmlspecialchars($route['name']) ?></span> (<?= htmlspecialchars($route['vehicle_plate']) ?>)</p>
    </div>
    <a href="/modules/transport/index.php" class="inline-flex items-center space-x-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-3 rounded-xl font-bold transition active:scale-[0.98]">
        <i class="fa-solid fa-arrow-left text-sm"></i>
        <span>Volver a Rutas</span>
    </a>
</div>

<?php if ($error): ?>
    <div class="bg-rose-50 text-rose-600 p-4 rounded-xl mb-6 font-medium text-sm border border-rose-100 flex items-center space-x-2">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 font-medium text-sm border border-emerald-100 flex items-center space-x-2">
        <i class="fa-solid fa-check"></i>
        <span><?= htmlspecialchars($success) ?></span>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- List of assignments -->
    <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-bold text-slate-800">Manifiesto de Pasajeros</h3>
            <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2 py-1 rounded">
                Ocupación: <?= count($assignments) ?> / <?= $route['capacity'] ?>
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead class="bg-slate-50/60 text-slate-400 uppercase tracking-wider text-xs font-bold">
                    <tr>
                        <th scope="col" class="px-6 py-3">Estudiante</th>
                        <th scope="col" class="px-6 py-3">Curso</th>
                        <th scope="col" class="px-6 py-3">Parada de Bus</th>
                        <th scope="col" class="px-6 py-3 text-center">Remover</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                    <?php if (empty($assignments)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-400">
                                No hay pasajeros asignados a esta ruta.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($assignments as $row): ?>
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="px-6 py-3 whitespace-nowrap font-bold text-slate-800">
                                    <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                    <div class="text-[10px] text-slate-400 font-normal"><?= htmlspecialchars($row['document']) ?></div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <span class="text-xs font-bold text-slate-500 border border-slate-200 px-2 py-1 rounded">
                                        <?= htmlspecialchars($row['course_name']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-slate-700">
                                    <i class="fa-solid fa-map-pin text-rose-400 mr-1 text-[10px]"></i>
                                    <?= htmlspecialchars($row['stop_name']) ?>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-center">
                                    <form method="POST" onsubmit="return confirm('¿Quitar a este estudiante de la ruta?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="assignment_id" value="<?= $row['assignment_id'] ?>">
                                        <button type="submit" class="text-rose-500 hover:text-rose-700 bg-rose-50 p-2 rounded-lg transition" title="Quitar de la ruta">
                                            <i class="fa-solid fa-user-minus text-xs"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Assignment Form -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 h-fit">
        <h3 class="font-bold text-slate-800 mb-4 flex items-center">
            <i class="fa-solid fa-user-plus text-indigo-500 mr-2"></i>
            Añadir Pasajero
        </h3>
        
        <?php if (count($assignments) >= $route['capacity']): ?>
            <div class="bg-rose-50 text-rose-600 p-3 rounded-xl text-sm font-bold text-center border border-rose-100">
                La ruta está llena.
            </div>
        <?php else: ?>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add">
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Estudiante (Sin Ruta) <span class="text-rose-500">*</span></label>
                    <select name="student_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-slate-800 transition text-sm">
                        <option value="">Seleccione un alumno...</option>
                        <?php foreach($unassigned_students as $st): ?>
                            <option value="<?= $st['id'] ?>">
                                <?= htmlspecialchars($st['last_name'] . ' ' . $st['first_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nombre de la Parada <span class="text-rose-500">*</span></label>
                    <input type="text" name="stop_name" required placeholder="Ej. Calle 5, Plaza Mayor..."
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-slate-800 transition text-sm">
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 transition active:scale-[0.98] text-sm">
                    Asignar a la Ruta
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
