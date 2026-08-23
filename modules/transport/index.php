<?php
// /modules/transport/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();

$query = "
    SELECT tr.*, COUNT(ta.id) as assigned_students
    FROM transport_routes tr
    LEFT JOIN transport_assignments ta ON tr.id = ta.route_id
    GROUP BY tr.id
    ORDER BY tr.name ASC
";
$stmt = $db->prepare($query);
$stmt->execute();
$routes = $stmt->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<!-- Header Section -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Rutas de Transporte</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Gestión de autobuses y conductores escolares.</p>
    </div>
    <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR'])): ?>
    <a href="/modules/transport/create.php" class="inline-flex items-center space-x-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 transition active:scale-[0.98]">
        <i class="fa-solid fa-plus text-sm"></i>
        <span>Nueva Ruta</span>
    </a>
    <?php endif; ?>
</div>

<!-- Table list -->
<div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-6">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
            <thead class="bg-slate-50/60 text-slate-400 uppercase tracking-wider text-xs font-bold">
                <tr>
                    <th scope="col" class="px-6 py-4">Ruta</th>
                    <th scope="col" class="px-6 py-4">Conductor</th>
                    <th scope="col" class="px-6 py-4">Vehículo</th>
                    <th scope="col" class="px-6 py-4">Ocupación</th>
                    <th scope="col" class="px-6 py-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                <?php if (empty($routes)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                            No hay rutas de transporte registradas.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($routes as $row): 
                        $percentage = ($row['capacity'] > 0) ? round(($row['assigned_students'] / $row['capacity']) * 100) : 0;
                        $color = $percentage > 90 ? 'bg-rose-500' : ($percentage > 75 ? 'bg-amber-500' : 'bg-emerald-500');
                    ?>
                        <tr class="hover:bg-slate-50/40 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                                        <i class="fa-solid fa-bus"></i>
                                    </div>
                                    <span class="font-bold text-slate-800"><?= htmlspecialchars($row['name']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-700">
                                <i class="fa-regular fa-id-card text-slate-400 mr-1.5"></i>
                                <?= htmlspecialchars($row['driver_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono bg-slate-100 px-2 py-1 rounded text-slate-700 border border-slate-200">
                                    <?= htmlspecialchars($row['vehicle_plate']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="text-xs font-bold text-slate-700"><?= $row['assigned_students'] ?> / <?= $row['capacity'] ?> pax</span>
                                    <span class="text-[10px] text-slate-400">(<?= $percentage ?>%)</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5">
                                    <div class="<?= $color ?> h-1.5 rounded-full" style="width: <?= min($percentage, 100) ?>%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center space-x-1.5">
                                <a href="/modules/transport/assignments.php?id=<?= $row['id'] ?>" class="inline-flex p-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 rounded-xl transition" title="Pasajeros (Asignaciones)">
                                    <i class="fa-solid fa-users-viewfinder"></i>
                                </a>
                                
                                <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR'])): ?>
                                <a href="/modules/transport/edit.php?id=<?= $row['id'] ?>" class="inline-flex p-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-xl transition" title="Editar">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                
                                <form method="POST" action="/modules/transport/delete.php" class="inline-block" onsubmit="return confirm('¿Estás seguro de eliminar esta ruta? Se eliminarán también las asignaciones asociadas.');">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="inline-flex p-2 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl transition" title="Eliminar">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
