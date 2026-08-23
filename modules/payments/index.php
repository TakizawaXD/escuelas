<?php
// /modules/payments/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'ESTUDIANTE', 'PADRE'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$u = Auth::user();

$search = Auth::sanitize($_GET['search'] ?? '');

$query = "
    SELECT pay.*, u.first_name, u.last_name, u.document, c.name as course_name
    FROM payments pay
    JOIN students s ON pay.student_id = s.id
    JOIN users u ON s.user_id = u.id
    JOIN courses c ON s.course_id = c.id
    WHERE 1=1
";

$params = [];
// Restrict viewing for students and parents
if (Auth::hasRole('ESTUDIANTE')) {
    $stmt = $db->prepare("SELECT id FROM students WHERE user_id = ?");
    $stmt->execute([$u['id']]);
    $st_id = $stmt->fetchColumn();

    $query .= " AND pay.student_id = ?";
    $params[] = $st_id;
} elseif (Auth::hasRole('PADRE')) {
    $stmt = $db->prepare("SELECT id FROM students WHERE parent_user_id = ?");
    $stmt->execute([$u['id']]);
    $st_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($st_ids)) {
        $in = implode(',', array_fill(0, count($st_ids), '?'));
        $query .= " AND pay.student_id IN ($in)";
        $params = array_merge($params, $st_ids);
    } else {
        $query .= " AND pay.student_id = -1"; // Empty set
    }
}

if (!empty($search)) {
    $query .= " AND (pay.concept LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY pay.id DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$payments = $stmt->fetchAll();

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
    <div class="relative rounded-3xl bg-gradient-to-br from-emerald-900 via-slate-900 to-teal-950 p-8 shadow-2xl overflow-hidden text-white">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-72 h-72 rounded-full bg-emerald-500 blur-[80px] opacity-30"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 rounded-full bg-teal-500 blur-[80px] opacity-20"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="text-4xl font-extrabold tracking-tight mb-2">Finanzas y <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-300 to-teal-300">Pagos</span></h2>
                <p class="text-emerald-200/80 font-medium text-sm max-w-md leading-relaxed">Control de pensiones, matrículas y estado de cuenta de estudiantes.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-4">
                <div class="glass-panel px-6 py-3 rounded-2xl flex items-center space-x-4 shadow-lg shadow-black/10">
                    <div class="w-12 h-12 rounded-full bg-emerald-500/30 flex items-center justify-center text-emerald-200">
                        <i class="fa-solid fa-file-invoice-dollar text-xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-emerald-200/70 tracking-widest">Recibos Total</p>
                        <p class="text-2xl font-extrabold text-white leading-none"><?= count($payments) ?></p>
                    </div>
                </div>
                
                <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR'])): ?>
                <a href="/modules/payments/create.php" class="inline-flex h-14 items-center space-x-2 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white px-6 rounded-2xl font-bold tracking-wide transition shadow-lg shadow-emerald-500/30 hover:-translate-y-0.5 border border-emerald-400/30">
                    <i class="fa-solid fa-plus text-sm"></i>
                    <span>Generar Cobro</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Advanced Search & Filter Bar (Glassmorphism) -->
    <div class="glass-panel p-2 rounded-2xl shadow-sm flex flex-col md:flex-row items-center justify-between gap-2 z-20 relative">
        <form method="GET" class="w-full flex flex-col md:flex-row items-center gap-2">
            <div class="relative w-full group">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar por estudiante, documento o concepto de cobro..."
                       class="block w-full pl-12 pr-4 py-4 bg-white/50 hover:bg-white/80 focus:bg-white border border-transparent focus:border-emerald-200 text-slate-800 rounded-xl outline-none text-sm font-medium transition shadow-inner placeholder-slate-400">
            </div>
            <button type="submit" class="w-full md:w-auto px-8 py-4 bg-slate-900 hover:bg-black text-white font-bold rounded-xl transition flex justify-center items-center space-x-2 shrink-0">
                <i class="fa-solid fa-filter text-xs"></i>
                <span>Filtrar Recibos</span>
            </button>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden relative z-10">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-400 uppercase tracking-widest text-[10px] font-bold">
                        <th scope="col" class="px-6 py-5">Estudiante / Deudor</th>
                        <th scope="col" class="px-6 py-5">Detalle del Concepto</th>
                        <th scope="col" class="px-6 py-5">Monto (USD)</th>
                        <th scope="col" class="px-6 py-5">Vencimiento</th>
                        <th scope="col" class="px-6 py-5">Estado</th>
                        <th scope="col" class="px-6 py-5 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center text-slate-400 font-medium">
                                <div class="w-20 h-20 mx-auto bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-300 shadow-inner">
                                    <i class="fa-solid fa-receipt text-3xl"></i>
                                </div>
                                <p class="text-base text-slate-500">No hay registros financieros que coincidan con la búsqueda.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $row): 
                            $isPaid = $row['status'] === 'Pagado';
                            $statusBg = $isPaid ? 'bg-emerald-50 border-emerald-200 text-emerald-700 shadow-emerald-500/10' : 'bg-amber-50 border-amber-200 text-amber-700 shadow-amber-500/10';
                            $statusIcon = $isPaid ? 'fa-circle-check' : 'fa-clock';
                        ?>
                            <tr class="group hover:bg-emerald-50/30 transition duration-300">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-bold flex-shrink-0 text-xs">
                                            <?= strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <p class="font-extrabold text-slate-800 text-base leading-tight group-hover:text-emerald-600 transition">
                                                <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                            </p>
                                            <span class="text-[10px] text-slate-400 font-mono mt-0.5 inline-block bg-slate-100 px-1.5 py-0.5 rounded">ID: <?= htmlspecialchars($row['document']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-700"><?= htmlspecialchars($row['concept']) ?></div>
                                    <div class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($row['course_name']) ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-extrabold text-lg text-slate-800 tracking-tight">
                                        $<?= number_format($row['amount'], 2) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center text-slate-600 font-medium text-sm">
                                        <i class="fa-regular fa-calendar text-slate-400 mr-2"></i>
                                        <?= date('d M, Y', strtotime($row['due_date'])) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold border shadow-sm transition-all <?= $statusBg ?>">
                                        <i class="fa-solid <?= $statusIcon ?> mr-1.5 text-[10px]"></i>
                                        <span><?= $row['status'] ?></span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR']) && !$isPaid): ?>
                                        <div class="flex items-center justify-center opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                            <a href="/modules/payments/pay.php?id=<?= $row['id'] ?>" onclick="return confirm('¿Confirma que ha recibido el pago por este concepto?')" class="inline-flex items-center space-x-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition text-xs font-bold shadow-md shadow-emerald-500/20 hover:-translate-y-0.5">
                                                <i class="fa-solid fa-cash-register"></i>
                                                <span>Recibir Pago</span>
                                            </a>
                                        </div>
                                    <?php elseif ($isPaid): ?>
                                        <span class="inline-flex items-center space-x-1.5 px-3 py-1 bg-slate-50 border border-slate-200 text-slate-500 rounded-lg text-xs font-semibold">
                                            <i class="fa-solid fa-calendar-check text-[10px]"></i>
                                            <span>Pagado el <?= date('d/m', strtotime($row['payment_date'])) ?></span>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs font-bold text-slate-400 italic">Contacto Admon</span>
                                    <?php endif; ?>
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
