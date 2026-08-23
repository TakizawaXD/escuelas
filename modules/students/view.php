<?php
// /modules/students/view.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR', 'DOCENTE', 'PADRE'])) {
    header("Location: /index.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /modules/students/index.php");
    exit;
}

$db = Database::getInstance()->getConnection();

$query = "
    SELECT s.*, u.document, u.first_name, u.last_name, u.email, u.phone, u.status, u.created_at,
           c.name as course_name, p.first_name as parent_first, p.last_name as parent_last, p.phone as parent_phone
    FROM students s
    JOIN users u ON s.user_id = u.id
    JOIN courses c ON s.course_id = c.id
    LEFT JOIN users p ON s.parent_user_id = p.id
    WHERE s.id = ?
";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: /modules/students/index.php");
    exit;
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-6xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/students/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Expediente del Estudiante</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Información académica y personal detallada.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        <!-- Columna Izquierda: Perfil Básico -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col items-center text-center">
                <?php if (!empty($student['photo_url'])): ?>
                    <div class="w-28 h-28 rounded-2xl overflow-hidden border-4 border-white shadow-md mb-4 bg-slate-100">
                        <img src="<?= htmlspecialchars($student['photo_url']) ?>" alt="Foto" class="w-full h-full object-cover">
                    </div>
                <?php else: ?>
                    <div class="w-28 h-28 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center font-bold text-4xl border-4 border-white shadow-md mb-4">
                        <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                
                <h3 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></h3>
                <p class="text-sm font-bold text-indigo-600 mt-1"><?= htmlspecialchars($student['course_name']) ?></p>
                <div class="mt-3 inline-block px-3 py-1 rounded-full text-xs font-bold <?= $student['status'] ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' ?>">
                    <?= $student['status'] ? 'MATRICULADO' : 'INACTIVO' ?>
                </div>
                
                <div class="w-full mt-6 space-y-3 text-left">
                    <div class="flex items-center space-x-3 text-sm text-slate-600">
                        <i class="fa-solid fa-id-card text-slate-400 w-5"></i>
                        <span><?= htmlspecialchars($student['document']) ?></span>
                    </div>
                    <div class="flex items-center space-x-3 text-sm text-slate-600">
                        <i class="fa-solid fa-cake-candles text-slate-400 w-5"></i>
                        <span><?= htmlspecialchars($student['birth_date']) ?></span>
                    </div>
                    <div class="flex items-center space-x-3 text-sm text-slate-600">
                        <i class="fa-solid fa-map-location-dot text-slate-400 w-5"></i>
                        <span class="truncate"><?= htmlspecialchars($student['address'] ?? 'Sin dirección') ?></span>
                    </div>
                </div>

                <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])): ?>
                <div class="w-full mt-6 grid grid-cols-2 gap-2">
                    <a href="/modules/students/edit.php?id=<?= $student['id'] ?>" class="text-center bg-slate-100 hover:bg-slate-200 text-slate-700 py-2 rounded-xl font-bold text-sm transition">
                        Editar
                    </a>
                    <a href="/modules/students/delete.php?id=<?= $student['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar a este estudiante?');" class="text-center bg-red-50 hover:bg-red-100 text-red-600 py-2 rounded-xl font-bold text-sm transition">
                        Eliminar
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Acudiente -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <h4 class="font-bold text-slate-700 mb-4 border-b border-slate-100 pb-2">Acudiente / Tutor</h4>
                <?php if ($student['parent_first']): ?>
                    <div class="flex items-start space-x-3">
                        <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center font-bold text-sm shrink-0">
                            <?= strtoupper(substr($student['parent_first'], 0, 1) . substr($student['parent_last'], 0, 1)) ?>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($student['parent_first'] . ' ' . $student['parent_last']) ?></p>
                            <p class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-phone mr-1"></i> <?= htmlspecialchars($student['parent_phone'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-slate-400 italic">No tiene acudiente asignado.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Columna Central/Derecha: Herramientas Académicas -->
        <div class="lg:col-span-3 space-y-6">
            
            <!-- Quick Actions Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <a href="/modules/students/transcript.php?id=<?= $student['id'] ?>" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 hover:border-indigo-300 hover:shadow-md transition text-center group">
                    <div class="w-10 h-10 mx-auto bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-lg mb-2 group-hover:bg-indigo-600 group-hover:text-white transition">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <h5 class="font-bold text-slate-700 text-sm">Histórico Académico</h5>
                </a>
                <a href="/modules/students/documents.php?id=<?= $student['id'] ?>" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 hover:border-indigo-300 hover:shadow-md transition text-center group">
                    <div class="w-10 h-10 mx-auto bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-lg mb-2 group-hover:bg-indigo-600 group-hover:text-white transition">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                    <h5 class="font-bold text-slate-700 text-sm">Documentos</h5>
                </a>
                <a href="/modules/students/guardians.php?id=<?= $student['id'] ?>" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 hover:border-indigo-300 hover:shadow-md transition text-center group">
                    <div class="w-10 h-10 mx-auto bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-lg mb-2 group-hover:bg-indigo-600 group-hover:text-white transition">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <h5 class="font-bold text-slate-700 text-sm">Acudientes</h5>
                </a>
                <a href="/modules/students/export-record.php?id=<?= $student['id'] ?>" target="_blank" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 hover:border-indigo-300 hover:shadow-md transition text-center group">
                    <div class="w-10 h-10 mx-auto bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-lg mb-2 group-hover:bg-indigo-600 group-hover:text-white transition">
                        <i class="fa-solid fa-file-pdf"></i>
                    </div>
                    <h5 class="font-bold text-slate-700 text-sm">Exportar Ficha</h5>
                </a>
            </div>

            <!-- Resumen Académico -->
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-slate-800">Desempeño Actual</h3>
                    <div class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-center">
                        <p class="text-xs text-slate-500 font-bold uppercase tracking-wide">Promedio (GPA)</p>
                        <p class="text-2xl font-extrabold text-emerald-600"><?= number_format($student['gpa'], 2) ?></p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <h4 class="font-bold text-slate-700 text-sm mb-2">Escalabilidad y Observaciones</h4>
                        <div class="bg-amber-50 border border-amber-100 p-4 rounded-2xl text-amber-800 text-sm">
                            <?= $student['scalability'] ? nl2br(htmlspecialchars($student['scalability'])) : 'Sin observaciones registradas por el momento.' ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
