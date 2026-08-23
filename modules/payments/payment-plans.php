<?php
// /modules/payments/payment-plans.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'FINANCIERO'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $total_amount = $_POST['total_amount'] ?? 0;
    $installments = $_POST['installments'] ?? 1;

    if (!empty($name) && $total_amount > 0 && $installments > 0) {
        $stmt = $db->prepare("INSERT INTO payment_plans (name, description, total_amount, installments) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $description, $total_amount, $installments])) {
            $success = "Plan de pago creado exitosamente.";
        } else {
            $error = "Error al guardar el plan de pago.";
        }
    } else {
        $error = "Todos los campos obligatorios deben estar completos y ser mayores a cero.";
    }
}

$stmt = $db->query("SELECT * FROM payment_plans ORDER BY id DESC");
$plans = $stmt->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-6xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/payments/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Planes de Pago</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Configura opciones de pago a cuotas para el año escolar.</p>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Formulario -->
        <div class="lg:col-span-1 bg-white p-6 rounded-3xl shadow-sm border border-slate-100 h-fit">
            <h3 class="text-lg font-bold text-slate-800 mb-6">Nuevo Plan</h3>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre del Plan</label>
                    <input type="text" name="name" required placeholder="Ej: Anualidad 2026" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Monto Total ($)</label>
                    <input type="number" step="0.01" name="total_amount" required class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Número de Cuotas</label>
                    <input type="number" name="installments" min="1" required class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Descripción (Opcional)</label>
                    <textarea name="description" rows="2" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50"></textarea>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white py-2.5 rounded-xl font-bold transition">
                    Crear Plan
                </button>
            </form>
        </div>

        <!-- Listado -->
        <div class="lg:col-span-2">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach($plans as $plan): ?>
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 hover:shadow-md transition">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-bold text-slate-800 text-lg"><?= htmlspecialchars($plan['name']) ?></h4>
                            <span class="bg-indigo-50 text-indigo-600 px-2.5 py-1 rounded-lg text-xs font-bold">
                                <?= $plan['installments'] ?> Cuotas
                            </span>
                        </div>
                        <p class="text-sm text-slate-500 mb-4 h-10 overflow-hidden text-ellipsis"><?= htmlspecialchars($plan['description'] ?? 'Sin descripción.') ?></p>
                        
                        <div class="border-t border-slate-100 pt-3 flex justify-between items-end">
                            <div>
                                <p class="text-xs text-slate-400 font-bold uppercase">Total</p>
                                <p class="text-lg font-black text-slate-800">$<?= number_format($plan['total_amount'], 2) ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-slate-400 font-bold uppercase">Por Cuota</p>
                                <p class="text-sm font-bold text-indigo-600">$<?= number_format($plan['total_amount'] / $plan['installments'], 2) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($plans)): ?>
                    <div class="col-span-1 sm:col-span-2 bg-slate-50 border border-dashed border-slate-300 p-8 rounded-2xl text-center text-slate-500">
                        <i class="fa-solid fa-money-check-dollar text-4xl mb-2 text-slate-300"></i>
                        <p>No hay planes de pago registrados.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
