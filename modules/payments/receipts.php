<?php
// /modules/payments/receipts.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /modules/payments/index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("
    SELECT p.*, s.id as student_id, u.first_name, u.last_name, u.document 
    FROM payments p 
    JOIN students s ON p.student_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$payment = $stmt->fetch();

if (!$payment) {
    header("Location: /modules/payments/index.php");
    exit;
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-4xl mx-auto">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="/modules/payments/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Recibo de Pago</h2>
                <p class="text-slate-500 font-medium text-sm mt-1">Comprobante de transacción #<?= str_pad($payment['id'], 6, '0', STR_PAD_LEFT) ?></p>
            </div>
        </div>
        <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/20 transition">
            <i class="fa-solid fa-print mr-2"></i> Imprimir Recibo
        </button>
    </div>

    <div class="bg-white p-10 rounded-3xl shadow-sm border border-slate-200" id="receipt-area">
        <div class="border-b-2 border-dashed border-slate-200 pb-8 mb-8 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-slate-900 text-white rounded-2xl flex items-center justify-center text-3xl font-bold">
                    <i class="fa-solid fa-school"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-slate-900 tracking-tight">SISTEMA ESCOLAR</h3>
                    <p class="text-slate-500 text-sm">NIT: 800.123.456-7</p>
                </div>
            </div>
            <div class="text-right">
                <h4 class="text-lg font-bold text-slate-700">Recibo #<?= str_pad($payment['id'], 6, '0', STR_PAD_LEFT) ?></h4>
                <p class="text-slate-500 text-sm">Fecha Emisión: <?= date('d M Y') ?></p>
                <div class="mt-2 inline-block px-3 py-1 rounded text-xs font-bold uppercase tracking-wider <?= $payment['status'] === 'Pagado' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
                    <?= htmlspecialchars($payment['status']) ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8 mb-8">
            <div>
                <h5 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Cobrado A:</h5>
                <p class="font-bold text-slate-800 text-lg"><?= htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']) ?></p>
                <p class="text-slate-500">Documento: <?= htmlspecialchars($payment['document']) ?></p>
                <p class="text-slate-500">ID Estudiante: <?= htmlspecialchars($payment['student_id']) ?></p>
            </div>
            <div class="text-right">
                <h5 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Detalles de Pago:</h5>
                <p class="text-slate-500">Fecha Límite: <span class="font-medium text-slate-800"><?= date('d M Y', strtotime($payment['due_date'])) ?></span></p>
                <?php if ($payment['payment_date']): ?>
                    <p class="text-slate-500">Fecha de Pago: <span class="font-medium text-slate-800"><?= date('d M Y', strtotime($payment['payment_date'])) ?></span></p>
                <?php endif; ?>
            </div>
        </div>

        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-600 text-sm">
                    <th class="py-3 px-4 font-bold border-b border-slate-200">Concepto</th>
                    <th class="py-3 px-4 font-bold border-b border-slate-200 text-right">Monto</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="py-4 px-4 border-b border-slate-100 font-medium text-slate-800"><?= htmlspecialchars($payment['concept']) ?></td>
                    <td class="py-4 px-4 border-b border-slate-100 text-right font-bold text-slate-800">$<?= number_format($payment['amount'], 2) ?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td class="py-4 px-4 text-right font-bold text-slate-500 uppercase tracking-wider text-sm">Total a Pagar</td>
                    <td class="py-4 px-4 text-right font-black text-slate-900 text-2xl">$<?= number_format($payment['amount'], 2) ?></td>
                </tr>
            </tfoot>
        </table>
        
        <div class="mt-12 text-center text-sm text-slate-400">
            <p>Este es un comprobante generado por el sistema.</p>
            <p>Gracias por confiar en nuestra institución.</p>
        </div>
    </div>
</div>

<style>
    @media print {
        body * { visibility: hidden; }
        #receipt-area, #receipt-area * { visibility: visible; }
        #receipt-area { position: absolute; left: 0; top: 0; width: 100%; border: none; box-shadow: none; }
    }
</style>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
