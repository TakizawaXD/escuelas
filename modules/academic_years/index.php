<?php
// /modules/academic_years/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();

$query = "SELECT * FROM academic_years ORDER BY start_date DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$academic_years = $stmt->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<!-- Header Section -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Años Lectivos</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Gestión de periodos académicos de la institución.</p>
    </div>
    <a href="/modules/academic_years/create.php" class="inline-flex items-center space-x-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 transition active:scale-[0.98]">
        <i class="fa-solid fa-plus text-sm"></i>
        <span>Agregar Año Lectivo</span>
    </a>
</div>

<!-- Table list -->
<div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-6">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
            <thead class="bg-slate-50/60 text-slate-400 uppercase tracking-wider text-xs font-bold">
                <tr>
                    <th scope="col" class="px-6 py-4">Nombre del Periodo</th>
                    <th scope="col" class="px-6 py-4">Fecha Inicio</th>
                    <th scope="col" class="px-6 py-4">Fecha Fin</th>
                    <th scope="col" class="px-6 py-4">Estado</th>
                    <th scope="col" class="px-6 py-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                <?php if (empty($academic_years)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                            No hay años lectivos registrados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($academic_years as $row): ?>
                        <tr class="hover:bg-slate-50/40 transition">
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800"><?= htmlspecialchars($row['name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-slate-500 text-xs"><?= htmlspecialchars($row['start_date']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-slate-500 text-xs"><?= htmlspecialchars($row['end_date']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($row['active'] == 1): ?>
                                    <span class="inline-flex items-center space-x-1 text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-full text-xs font-bold">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i>
                                        <span>Activo</span>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center space-x-1 text-slate-400 bg-slate-100 px-2.5 py-1 rounded-full text-xs font-bold">
                                        <i class="fa-solid fa-circle text-[10px]"></i>
                                        <span>Inactivo</span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center space-x-1.5">
                                <a href="/modules/academic_years/edit.php?id=<?= $row['id'] ?>" class="inline-flex p-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-xl transition" title="Editar">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                
                                <form method="POST" action="/modules/academic_years/delete.php" class="inline-block" onsubmit="return confirm('¿Estás seguro de eliminar este año lectivo?');">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="inline-flex p-2 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl transition" title="Eliminar">
                                        <i class="fa-solid fa-trash-can"></i>
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

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
