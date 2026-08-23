<?php
// /modules/payments/edit.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'FINANCIERO'])) {
    header("Location: /index.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /modules/payments/index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM payments WHERE id = ?");
$stmt->execute([$id]);
$payment = $stmt->fetch();

if (!$payment) {
    header("Location: /modules/payments/index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $concept = $_POST['concept'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $status = $_POST['status'] ?? 'Pendiente';
    $due_date = $_POST['due_date'] ?? '';
    
    if (empty($concept) || empty($amount) || empty($due_date)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        $payment_date = ($status === 'Pagado') ? date('Y-m-d') : null;
        
        $stmt = $db->prepare("UPDATE payments SET concept = ?, amount = ?, status = ?, due_date = ?, payment_date = ? WHERE id = ?");
        if ($stmt->execute([$concept, $amount, $status, $due_date, $payment_date, $id])) {
            $success = "El pago ha sido actualizado correctamente.";
            $payment['concept'] = $concept;
            $payment['amount'] = $amount;
            $payment['status'] = $status;
            $payment['due_date'] = $due_date;
        } else {
            $error = "Error al actualizar el pago.";
        }
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-2xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/payments/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Editar Cobro</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Modificar los detalles del cobro ID: <?= $id ?></p>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-50 text-red-600 px-4 py-3 rounded-xl flex items-center space-x-3 border border-red-200">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span class="font-medium text-sm"><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-emerald-50 text-emerald-600 px-4 py-3 rounded-xl flex items-center space-x-3 border border-emerald-200">
            <i class="fa-solid fa-circle-check"></i>
            <span class="font-medium text-sm"><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white p-8 md:p-12 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 relative overflow-hidden group">
    <!-- ambient background -->
    <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-50 rounded-full blur-3xl opacity-50 pointer-events-none transition-opacity group-hover:opacity-100"></div>
    <div class="relative z-10">
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Concepto</label>
                <input type="text" name="concept" value="<?= htmlspecialchars($payment['concept']) ?>" required class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Monto ($)</label>
                <input type="number" step="0.01" name="amount" value="<?= htmlspecialchars($payment['amount']) ?>" required class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha de Vencimiento</label>
                <input type="date" name="due_date" value="<?= htmlspecialchars($payment['due_date']) ?>" required class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Estado</label>
                <select name="status" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50 appearance-none">
                    <option value="Pendiente" <?= $payment['status'] === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="Pagado" <?= $payment['status'] === 'Pagado' ? 'selected' : '' ?>>Pagado</option>
                    <option value="Atrasado" <?= $payment['status'] === 'Atrasado' ? 'selected' : '' ?>>Atrasado</option>
                </select>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white px-8 py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 transition-all flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-save mr-2"></i> Guardar Cambios
                </button>
            </div>
        </div>
</form>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
