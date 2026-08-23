<?php
// /modules/students/transcript.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /modules/students/index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT u.first_name, u.last_name FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: /modules/students/index.php");
    exit;
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-5xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/students/view.php?id=<?= $id ?>" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Histórico Académico (Transcript)</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Estudiante: <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></p>
        </div>
        <div class="ml-auto">
            <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-sm transition">
                <i class="fa-solid fa-print mr-2"></i> Imprimir
            </button>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <div class="text-center mb-8 border-b border-slate-200 pb-6">
            <h3 class="text-2xl font-bold text-slate-800">Certificado de Calificaciones</h3>
            <p class="text-slate-500">Año Lectivo 2026</p>
        </div>

        <div class="space-y-8">
            <!-- Mockup de Semestre/Periodo -->
            <div>
                <h4 class="font-bold text-lg text-indigo-700 bg-indigo-50 px-4 py-2 rounded-xl mb-4">Primer Semestre</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase">Materia</th>
                                <th class="px-4 py-3 text-center font-bold text-slate-600 uppercase">Créditos/Horas</th>
                                <th class="px-4 py-3 text-center font-bold text-slate-600 uppercase">Nota Final</th>
                                <th class="px-4 py-3 text-center font-bold text-slate-600 uppercase">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-800">Matemáticas Avanzadas</td>
                                <td class="px-4 py-3 text-center text-slate-500">4</td>
                                <td class="px-4 py-3 text-center font-bold text-slate-800">4.5</td>
                                <td class="px-4 py-3 text-center"><span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-xs font-bold">Aprobado</span></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-800">Física General</td>
                                <td class="px-4 py-3 text-center text-slate-500">3</td>
                                <td class="px-4 py-3 text-center font-bold text-slate-800">4.0</td>
                                <td class="px-4 py-3 text-center"><span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-xs font-bold">Aprobado</span></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-800">Literatura</td>
                                <td class="px-4 py-3 text-center text-slate-500">2</td>
                                <td class="px-4 py-3 text-center font-bold text-slate-800">2.8</td>
                                <td class="px-4 py-3 text-center"><span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">Reprobado</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
