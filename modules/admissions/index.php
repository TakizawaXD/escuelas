<?php
// /modules/admissions/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$applications = $db->query("SELECT * FROM admission_applications ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="max-w-7xl mx-auto space-y-6 animate-fade-in pb-10">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Solicitudes de Admisión</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Gestione a los candidatos que han aplicado desde el portal público.</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider">Candidato</th>
                        <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider">Acudiente / Contacto</th>
                        <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider">Grado a Aplicar</th>
                        <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider">Estado</th>
                        <th class="py-4 px-6 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($applications)): ?>
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-500">
                                <i class="fa-regular fa-folder-open text-4xl mb-3 text-slate-300 block"></i>
                                <p class="text-sm">Aún no hay solicitudes de admisión registradas.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($applications as $app): ?>
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-4 px-6">
                                <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($app['student_first_name'] . ' ' . $app['student_last_name']) ?></p>
                                <p class="text-xs text-slate-500">Generada: <?= date('d M Y', strtotime($app['created_at'])) ?></p>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-sm font-semibold text-slate-700"><?= htmlspecialchars($app['parent_first_name'] . ' ' . $app['parent_last_name']) ?></p>
                                <div class="flex flex-col space-y-1 mt-1">
                                    <span class="text-xs text-slate-500"><i class="fa-solid fa-envelope mr-1 text-slate-400"></i><?= htmlspecialchars($app['parent_email']) ?></span>
                                    <span class="text-xs text-slate-500"><i class="fa-solid fa-phone mr-1 text-slate-400"></i><?= htmlspecialchars($app['parent_phone']) ?></span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 font-bold text-xs">
                                    <?= htmlspecialchars($app['target_grade']) ?>
                                </span>
                                <?php if (!empty($app['previous_school'])): ?>
                                    <p class="text-[10px] text-slate-400 mt-1 uppercase">De: <?= htmlspecialchars($app['previous_school']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6">
                                <?php
                                $statusColor = 'bg-slate-100 text-slate-700';
                                if ($app['status'] === 'Aceptado') $statusColor = 'bg-emerald-100 text-emerald-800';
                                if ($app['status'] === 'Rechazado') $statusColor = 'bg-red-100 text-red-800';
                                if ($app['status'] === 'Entrevista') $statusColor = 'bg-amber-100 text-amber-800';
                                ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold <?= $statusColor ?>">
                                    <?= htmlspecialchars($app['status']) ?>
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right space-x-2">
                                <form action="/modules/admissions/update_status.php" method="POST" class="inline-block">
                                    <input type="hidden" name="id" value="<?= $app['id'] ?>">
                                    <select name="status" onchange="this.form.submit()" class="text-xs border border-slate-200 rounded-lg px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white cursor-pointer font-semibold text-slate-600">
                                        <option value="">Cambiar Estado</option>
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="Entrevista">Entrevista</option>
                                        <option value="Aceptado">Aceptado</option>
                                        <option value="Rechazado">Rechazado</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                        <?php if (!empty($app['notes'])): ?>
                        <tr class="bg-slate-50/30">
                            <td colspan="5" class="px-6 py-3 text-xs text-slate-500 border-b border-slate-100 border-dashed">
                                <i class="fa-solid fa-comment-dots mr-1"></i> <span class="font-semibold">Notas del Acudiente:</span> <?= htmlspecialchars($app['notes']) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
