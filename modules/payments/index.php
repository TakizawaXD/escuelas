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

<!-- Header Section -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Pagos y Pensiones</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Control de pagos y deudas escolares.</p>
    </div>
    <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR'])): ?>
        <a href="/modules/payments/create.php" class="inline-flex items-center space-x-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 transition active:scale-[0.98]">
            <i class="fa-solid fa-plus text-sm"></i>
            <span>Asignar Cobro</span>
        </a>
    <?php endif; ?>
</div>

<!-- Search Filtering -->
<div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100/80 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
    <form method="GET" class="w-full max-w-md flex items-center space-x-2">
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </div>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar por estudiante o concepto..."
                   class="block w-full pl-10 pr-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
        </div>
        <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition text-sm">Filtrar</button>
    </form>
    <div class="text-slate-400 text-sm font-medium">
        Total registros: <span class="font-extrabold text-slate-700"><?= count($payments) ?></span>
    </div>
</div>

<!-- Payments table -->
<div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
            <thead class="bg-slate-50/60 text-slate-400 uppercase tracking-wider text-xs font-bold">
                <tr>
                    <th scope="col" class="px-6 py-4">Estudiante</th>
                    <th scope="col" class="px-6 py-4">Concepto</th>
                    <th scope="col" class="px-6 py-4">Monto</th>
                    <th scope="col" class="px-6 py-4">Fecha Vencimiento</th>
                    <th scope="col" class="px-6 py-4">Estado</th>
                    <th scope="col" class="px-6 py-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                            No se encontraron deudas o recibos.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payments as $row): ?>
                        <tr class="hover:bg-slate-50/40 transition">
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800">
                                <div>
                                    <p><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></p>
                                    <span class="text-xs text-slate-400 font-mono"><?= htmlspecialchars($row['document']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-slate-700"><?= htmlspecialchars($row['concept']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap font-extrabold text-slate-800">
                                $<?= number_format($row['amount'], 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-500">
                                <?= date('d M, Y', strtotime($row['due_date'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($row['status'] === 'Pagado'): ?>
                                    <span class="inline-flex items-center space-x-1 text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-full text-xs font-bold">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i>
                                        <span>Pagado</span>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center space-x-1 text-amber-700 bg-amber-100 px-2.5 py-1 rounded-full text-xs font-bold">
                                        <i class="fa-solid fa-clock text-[10px]"></i>
                                        <span>Pendiente</span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR']) && $row['status'] === 'Pendiente'): ?>
                                    <a href="/modules/payments/pay.php?id=<?= $row['id'] ?>" onclick="return confirm('¿Está seguro de marcar este cobro como PAGADO?')" class="px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 rounded-xl transition text-xs font-bold">
                                        Registrar Pago
                                    </a>
                                <?php elseif ($row['status'] === 'Pagado'): ?>
                                    <span class="text-xs text-slate-400 font-medium">Pago: <?= date('d/m/Y', strtotime($row['payment_date'])) ?></span>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400">Por pagar</span>
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
