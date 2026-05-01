<?php
// /modules/parent_portal/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ESTUDIANTE', 'PADRE'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$u = Auth::user();

// Fetch student ID based on parent or direct student access
$student_ids = [];
if (Auth::hasRole('ESTUDIANTE')) {
    $stmt = $db->prepare("SELECT id FROM students WHERE user_id = ?");
    $stmt->execute([$u['id']]);
    $st_id = $stmt->fetchColumn();
    if ($st_id) $student_ids[] = $st_id;
} elseif (Auth::hasRole('PADRE')) {
    $stmt = $db->prepare("SELECT id FROM students WHERE parent_user_id = ?");
    $stmt->execute([$u['id']]);
    $student_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Student Info
$studentsInfo = [];
if (!empty($student_ids)) {
    $in = implode(',', array_fill(0, count($student_ids), '?'));
    $stmt = $db->prepare("
        SELECT s.*, u.first_name, u.last_name, u.document, c.name as course_name 
        FROM students s
        JOIN users u ON s.user_id = u.id
        JOIN courses c ON s.course_id = c.id
        WHERE s.id IN ($in)
    ");
    $stmt->execute($student_ids);
    $studentsInfo = $stmt->fetchAll();
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<!-- Header Section -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Portal para Padres y Alumnos</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Consulte el rendimiento académico, asistencia y recibos.</p>
    </div>
</div>

<?php if (empty($studentsInfo)): ?>
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 text-center text-slate-500">
        <i class="fa-solid fa-graduation-cap text-3xl text-slate-300 mb-2"></i>
        <p>No se encontraron estudiantes asociados a este perfil.</p>
    </div>
<?php else: ?>
    <?php foreach ($studentsInfo as $std): ?>
        <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-slate-100 mb-8 space-y-6">
            
            <!-- Perfil Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-800"><?= htmlspecialchars($std['first_name'] . ' ' . $std['last_name']) ?></h3>
                    <p class="text-xs text-slate-400 font-medium">Documento: <?= htmlspecialchars($std['document']) ?></p>
                </div>
                <div class="inline-block px-3 py-1 bg-indigo-50 border border-indigo-100 rounded-xl text-indigo-700 font-bold text-sm">
                    Curso: <?= htmlspecialchars($std['course_name']) ?>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Academic Report -->
                <div class="space-y-4">
                    <h4 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-star text-amber-500"></i>
                        <span>Calificaciones Académicas</span>
                    </h4>
                    <?php
                    // Fetch all grades for student
                    $gStmt = $db->prepare("
                        SELECT g.*, sub.name as subject_name 
                        FROM grades g 
                        JOIN subjects sub ON g.subject_id = sub.id 
                        WHERE g.student_id = ?
                    ");
                    $gStmt->execute([$std['id']]);
                    $grades = $gStmt->fetchAll();
                    ?>
                    <?php if (empty($grades)): ?>
                        <p class="text-sm text-slate-400 italic">No se han registrado notas aún.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                                <thead class="bg-slate-50 text-slate-400 uppercase tracking-wider font-bold">
                                    <tr>
                                        <th scope="col" class="px-3 py-2.5">Materia</th>
                                        <th scope="col" class="px-3 py-2.5">Periodo</th>
                                        <th scope="col" class="px-3 py-2.5">Examen</th>
                                        <th scope="col" class="px-3 py-2.5">Talleres</th>
                                        <th scope="col" class="px-3 py-2.5">Proyecto</th>
                                        <th scope="col" class="px-3 py-2.5">Promedio</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                                    <?php foreach ($grades as $g): ?>
                                        <tr>
                                            <td class="px-3 py-2.5 font-bold text-slate-800"><?= htmlspecialchars($g['subject_name']) ?></td>
                                            <td class="px-3 py-2.5">Periodo <?= $g['period'] ?></td>
                                            <td class="px-3 py-2.5"><?= number_format($g['exam_grade'], 1) ?></td>
                                            <td class="px-3 py-2.5"><?= number_format($g['workshop_grade'], 1) ?></td>
                                            <td class="px-3 py-2.5"><?= number_format($g['project_grade'], 1) ?></td>
                                            <td class="px-3 py-2.5">
                                                <span class="inline-block px-2 py-0.5 rounded-lg font-extrabold text-xs <?= $g['final_grade'] >= 3.0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' ?>">
                                                    <?= number_format($g['final_grade'], 2) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Attendance Report -->
                <div class="space-y-4">
                    <h4 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-calendar-check text-indigo-500"></i>
                        <span>Registro de Asistencia</span>
                    </h4>
                    <?php
                    // Fetch attendance count
                    $aStmt = $db->prepare("
                        SELECT status, COUNT(*) as count 
                        FROM attendance 
                        WHERE student_id = ? 
                        GROUP BY status
                    ");
                    $aStmt->execute([$std['id']]);
                    $attCounts = $aStmt->fetchAll(PDO::FETCH_KEY_PAIR);
                    ?>
                    <?php if (empty($attCounts)): ?>
                        <p class="text-sm text-slate-400 italic">No se han registrado asistencias aún.</p>
                    <?php else: ?>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-emerald-50/60 p-4 rounded-2xl border border-emerald-100 text-center">
                                <span class="text-xs text-emerald-600 font-bold uppercase tracking-wider block">Presente</span>
                                <span class="text-2xl font-extrabold text-emerald-700"><?= $attCounts['Presente'] ?? 0 ?></span>
                            </div>
                            <div class="bg-rose-50/60 p-4 rounded-2xl border border-rose-100 text-center">
                                <span class="text-xs text-rose-600 font-bold uppercase tracking-wider block">Ausente</span>
                                <span class="text-2xl font-extrabold text-rose-700"><?= $attCounts['Ausente'] ?? 0 ?></span>
                            </div>
                            <div class="bg-amber-50/60 p-4 rounded-2xl border border-amber-100 text-center">
                                <span class="text-xs text-amber-600 font-bold uppercase tracking-wider block">Tardanza</span>
                                <span class="text-2xl font-extrabold text-amber-700"><?= $attCounts['Tardanza'] ?? 0 ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Financial Report -->
            <div class="border-t border-slate-100 pt-5 space-y-4">
                <h4 class="font-bold text-slate-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-wallet text-teal-600"></i>
                    <span>Estado Financiero y Pagos</span>
                </h4>
                <?php
                // Fetch student payments
                $pStmt = $db->prepare("SELECT * FROM payments WHERE student_id = ? ORDER BY id DESC");
                $pStmt->execute([$std['id']]);
                $payRecs = $pStmt->fetchAll();
                ?>
                <?php if (empty($payRecs)): ?>
                    <p class="text-sm text-slate-400 italic">No se han registrado cobros.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                            <thead class="bg-slate-50 text-slate-400 uppercase font-bold tracking-wider">
                                <tr>
                                    <th scope="col" class="px-3 py-2.5">Concepto</th>
                                    <th scope="col" class="px-3 py-2.5">Monto</th>
                                    <th scope="col" class="px-3 py-2.5">Vencimiento</th>
                                    <th scope="col" class="px-3 py-2.5">Estado</th>
                                    <th scope="col" class="px-3 py-2.5">Fecha Pago</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                                <?php foreach ($payRecs as $p): ?>
                                    <tr>
                                        <td class="px-3 py-2.5 font-bold text-slate-800"><?= htmlspecialchars($p['concept']) ?></td>
                                        <td class="px-3 py-2.5 font-bold">$<?= number_format($p['amount'], 2) ?></td>
                                        <td class="px-3 py-2.5 text-slate-400"><?= date('d M, Y', strtotime($p['due_date'])) ?></td>
                                        <td class="px-3 py-2.5">
                                            <?php if ($p['status'] === 'Pagado'): ?>
                                                <span class="inline-block px-2 py-0.5 rounded font-bold bg-emerald-100 text-emerald-700 text-xs">Pagado</span>
                                            <?php else: ?>
                                                <span class="inline-block px-2 py-0.5 rounded font-bold bg-amber-100 text-amber-700 text-xs">Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 py-2.5 text-slate-500">
                                            <?= $p['payment_date'] ? date('d M, Y', strtotime($p['payment_date'])) : '<span class="text-slate-300 italic">Pendiente</span>' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
