<?php
// /modules/classrooms/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();

$query = "SELECT * FROM classrooms ORDER BY name ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$classrooms = $stmt->fetchAll();

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
    <div class="relative rounded-3xl bg-gradient-to-br from-indigo-900 via-slate-900 to-indigo-950 p-8 shadow-2xl overflow-hidden text-white">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-72 h-72 rounded-full bg-indigo-500 blur-[80px] opacity-30"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 rounded-full bg-blue-500 blur-[80px] opacity-20"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="text-4xl font-extrabold tracking-tight mb-2">Gestión de <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-300 to-blue-300">Aulas</span></h2>
                <p class="text-indigo-200/80 font-medium text-sm max-w-md leading-relaxed">Administración de salones, espacios físicos y control de capacidad.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-4">
                <div class="glass-panel px-6 py-3 rounded-2xl flex items-center space-x-4 shadow-lg shadow-black/10">
                    <div class="w-12 h-12 rounded-full bg-indigo-500/30 flex items-center justify-center text-indigo-300">
                        <i class="fa-solid fa-school text-xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-indigo-200/70 tracking-widest">Total Aulas</p>
                        <p class="text-2xl font-extrabold text-white leading-none"><?= count($classrooms) ?></p>
                    </div>
                </div>
                
                <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR'])): ?>
                <a href="/modules/classrooms/create.php" class="inline-flex h-14 items-center space-x-2 bg-gradient-to-r from-indigo-500 to-blue-500 hover:from-indigo-400 hover:to-blue-400 text-white px-6 rounded-2xl font-bold tracking-wide transition shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 border border-indigo-400/30">
                    <i class="fa-solid fa-plus text-sm"></i>
                    <span>Crear Aula</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden relative z-10">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-400 uppercase tracking-widest text-[10px] font-bold">
                        <th scope="col" class="px-6 py-5">Nombre / Identificador</th>
                        <th scope="col" class="px-6 py-5">Capacidad Máxima</th>
                        <th scope="col" class="px-6 py-5">Ubicación Física</th>
                        <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR'])): ?>
                        <th scope="col" class="px-6 py-5 text-center">Acciones Rápidas</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($classrooms)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-20 text-center text-slate-400 font-medium">
                                <div class="w-20 h-20 mx-auto bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-300 shadow-inner">
                                    <i class="fa-solid fa-chalkboard-user text-3xl"></i>
                                </div>
                                <p class="text-base text-slate-500">No se encontraron aulas registradas.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($classrooms as $row): ?>
                            <tr class="group hover:bg-indigo-50/30 transition duration-300">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-100 to-blue-50 border-2 border-white flex items-center justify-center text-indigo-600 font-extrabold flex-shrink-0 shadow-md transform group-hover:scale-105 transition duration-300 text-lg">
                                            <i class="fa-solid fa-chalkboard"></i>
                                        </div>
                                        <div>
                                            <p class="font-extrabold text-slate-800 text-base leading-tight group-hover:text-indigo-600 transition">
                                                <?= htmlspecialchars($row['name']) ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-50 text-slate-700 border border-slate-200 shadow-sm shadow-slate-200/10 space-x-1.5">
                                        <i class="fa-solid fa-users text-slate-400 text-[10px]"></i>
                                        <span><?= htmlspecialchars($row['capacity']) ?> alumnos</span>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center text-slate-500 font-medium text-sm">
                                        <i class="fa-solid fa-location-dot text-indigo-400 mr-2 w-4 text-center"></i>
                                        <?= htmlspecialchars($row['location'] ?: 'No especificada') ?>
                                    </div>
                                </td>
                                <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR'])): ?>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        <a href="/modules/classrooms/edit.php?id=<?= $row['id'] ?>" class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 flex items-center justify-center shadow-sm transition" title="Editar Aula">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form method="POST" action="/modules/classrooms/delete.php" class="inline-block" onsubmit="return confirm('¿Estás seguro de eliminar esta aula?');">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 flex items-center justify-center shadow-sm transition" title="Eliminar">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
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
