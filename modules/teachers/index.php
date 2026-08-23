<?php
// /modules/teachers/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$search = Auth::sanitize($_GET['search'] ?? '');

$query = "
    SELECT t.*, u.document, u.first_name, u.last_name, u.email
    FROM teachers t
    JOIN users u ON t.user_id = u.id
    WHERE 1=1
";

$params = [];
if (!empty($search)) {
    $query .= " AND (u.document LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR t.specialty LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
}

$query .= " ORDER BY t.id DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$teachers = $stmt->fetchAll();

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
    <div class="relative rounded-3xl bg-gradient-to-br from-teal-900 via-slate-900 to-teal-950 p-8 shadow-2xl overflow-hidden text-white">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-72 h-72 rounded-full bg-teal-500 blur-[80px] opacity-30"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 rounded-full bg-emerald-500 blur-[80px] opacity-20"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="text-4xl font-extrabold tracking-tight mb-2">Gestión de <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-300 to-emerald-300">Docentes</span></h2>
                <p class="text-teal-200/80 font-medium text-sm max-w-md leading-relaxed">Directorio de profesores y educadores vinculados a la institución.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-4">
                <div class="glass-panel px-6 py-3 rounded-2xl flex items-center space-x-4 shadow-lg shadow-black/10">
                    <div class="w-12 h-12 rounded-full bg-teal-500/20 flex items-center justify-center text-teal-300">
                        <i class="fa-solid fa-chalkboard-user text-xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-teal-200/70 tracking-widest">Total Staff</p>
                        <p class="text-2xl font-extrabold text-white leading-none"><?= count($teachers) ?></p>
                    </div>
                </div>
                
                <a href="/modules/teachers/create.php" class="inline-flex h-14 items-center space-x-2 bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-400 hover:to-teal-500 text-white px-6 rounded-2xl font-bold tracking-wide transition shadow-lg shadow-teal-500/30 hover:-translate-y-0.5 border border-teal-400/30">
                    <i class="fa-solid fa-plus text-sm"></i>
                    <span>Registrar</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Advanced Search & Filter Bar (Glassmorphism) -->
    <div class="glass-panel p-2 rounded-2xl shadow-sm flex flex-col md:flex-row items-center justify-between gap-2 z-20 relative">
        <form method="GET" class="w-full flex flex-col md:flex-row items-center gap-2">
            <div class="relative w-full group">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-teal-500 transition">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar por documento, nombre o especialidad..."
                       class="block w-full pl-12 pr-4 py-4 bg-white/50 hover:bg-white/80 focus:bg-white border border-transparent focus:border-teal-200 text-slate-800 rounded-xl outline-none text-sm font-medium transition shadow-inner placeholder-slate-400">
            </div>
            <button type="submit" class="w-full md:w-auto px-8 py-4 bg-slate-900 hover:bg-black text-white font-bold rounded-xl transition flex justify-center items-center space-x-2 shrink-0">
                <i class="fa-solid fa-filter text-xs"></i>
                <span>Aplicar Filtro</span>
            </button>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden relative z-10">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-400 uppercase tracking-widest text-[10px] font-bold">
                        <th scope="col" class="px-6 py-5">Perfil del Docente</th>
                        <th scope="col" class="px-6 py-5">Especialidad</th>
                        <th scope="col" class="px-6 py-5">Contacto</th>
                        <th scope="col" class="px-6 py-5 text-center">Acciones Rápidas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($teachers)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-20 text-center text-slate-400 font-medium">
                                <div class="w-20 h-20 mx-auto bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-300 shadow-inner">
                                    <i class="fa-solid fa-user-slash text-3xl"></i>
                                </div>
                                <p class="text-base text-slate-500">No se encontraron docentes con los filtros aplicados.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($teachers as $row): ?>
                            <tr class="group hover:bg-teal-50/30 transition duration-300">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-teal-100 to-emerald-50 border-2 border-white flex items-center justify-center text-teal-600 font-extrabold flex-shrink-0 shadow-md transform group-hover:scale-105 transition duration-300 text-lg">
                                            <?= strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <p class="font-extrabold text-slate-800 text-base leading-tight group-hover:text-teal-600 transition">
                                                <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                            </p>
                                            <div class="flex items-center space-x-2 mt-1">
                                                <span class="text-[10px] text-slate-400 font-mono bg-slate-100 px-1.5 py-0.5 rounded">ID: <?= htmlspecialchars($row['document']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-teal-50 text-teal-700 border border-teal-100 shadow-sm shadow-teal-500/10">
                                        <i class="fa-solid fa-award mr-1.5 text-teal-400"></i> <?= htmlspecialchars($row['specialty']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col space-y-1">
                                        <span class="text-xs text-slate-500"><i class="fa-solid fa-envelope mr-1 text-slate-400"></i> <?= htmlspecialchars($row['email']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        <a href="/modules/teachers/edit.php?id=<?= $row['id'] ?>" class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-teal-600 hover:border-teal-200 hover:bg-teal-50 flex items-center justify-center shadow-sm transition" title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <a href="/modules/teachers/performance.php?teacher_id=<?= $row['id'] ?>" class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 flex items-center justify-center shadow-sm transition" title="Ver Rendimiento">
                                            <i class="fa-solid fa-chart-line"></i>
                                        </a>
                                    </div>
                                </td>
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
