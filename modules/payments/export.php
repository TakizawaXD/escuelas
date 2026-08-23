<?php
// /modules/payments/export.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'FINANCIERO'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$stmt = $db->query("
    SELECT p.id, u.first_name, u.last_name, p.concept, p.amount, p.status, p.due_date 
    FROM payments p 
    JOIN students s ON p.student_id = s.id
    JOIN users u ON s.user_id = u.id
    ORDER BY p.id DESC
");
$payments = $stmt->fetchAll();

// En un caso real, aquí usaríamos fputcsv o una librería PDF. 
// Como mock, si se pasa ?format=csv, simularemos la descarga.
if (isset($_GET['format']) && $_GET['format'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=pagos_exportados.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Estudiante', 'Concepto', 'Monto', 'Estado', 'Vencimiento'));
    foreach ($payments as $row) {
        fputcsv($output, array($row['id'], $row['first_name'].' '.$row['last_name'], $row['concept'], $row['amount'], $row['status'], $row['due_date']));
    }
    fclose($output);
    exit;
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-3xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/payments/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Exportar Cobros</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Descarga la información financiera para sistemas externos (Contabilidad).</p>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex flex-col md:flex-row gap-6">
        <a href="?format=csv" class="flex-1 bg-slate-50 border border-slate-200 rounded-2xl p-6 text-center hover:bg-indigo-50 hover:border-indigo-200 transition group">
            <i class="fa-solid fa-file-csv text-5xl text-emerald-500 mb-4 group-hover:scale-110 transition-transform"></i>
            <h4 class="font-bold text-slate-800 text-lg">Exportar a CSV</h4>
            <p class="text-sm text-slate-500 mt-1">Ideal para Excel y bases de datos.</p>
        </a>
        <a href="#" onclick="alert('Exportación PDF en construcción.'); return false;" class="flex-1 bg-slate-50 border border-slate-200 rounded-2xl p-6 text-center hover:bg-indigo-50 hover:border-indigo-200 transition group">
            <i class="fa-solid fa-file-pdf text-5xl text-red-500 mb-4 group-hover:scale-110 transition-transform"></i>
            <h4 class="font-bold text-slate-800 text-lg">Exportar a PDF</h4>
            <p class="text-sm text-slate-500 mt-1">Ideal para reportes impresos.</p>
        </a>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
