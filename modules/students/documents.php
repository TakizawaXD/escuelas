<?php
// /modules/students/documents.php
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
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Documentos Requeridos</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Estudiante: <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></p>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Document 1 -->
            <div class="border border-slate-200 rounded-2xl p-5 hover:shadow-md transition bg-slate-50 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-16 h-16 bg-emerald-500 rounded-bl-full z-0 opacity-10"></div>
                <div class="relative z-10 flex justify-between items-start">
                    <div class="w-12 h-12 bg-white text-slate-400 rounded-xl shadow-sm flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <span class="bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-lg text-xs font-bold">Completado</span>
                </div>
                <h4 class="font-bold text-slate-800 mt-4">Documento de Identidad</h4>
                <p class="text-xs text-slate-500 mt-1 mb-4">Copia de DNI o Pasaporte</p>
                <button class="w-full bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 py-2 rounded-xl text-sm font-bold transition">
                    Ver Archivo
                </button>
            </div>

            <!-- Document 2 -->
            <div class="border border-slate-200 rounded-2xl p-5 hover:shadow-md transition bg-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-16 h-16 bg-red-500 rounded-bl-full z-0 opacity-10"></div>
                <div class="relative z-10 flex justify-between items-start">
                    <div class="w-12 h-12 bg-slate-50 text-slate-400 border border-slate-100 rounded-xl flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-notes-medical"></i>
                    </div>
                    <span class="bg-red-50 text-red-600 border border-red-100 px-2.5 py-1 rounded-lg text-xs font-bold">Pendiente</span>
                </div>
                <h4 class="font-bold text-slate-800 mt-4">Certificado Médico</h4>
                <p class="text-xs text-slate-500 mt-1 mb-4">Examen físico reciente</p>
                <button class="w-full bg-indigo-50 text-indigo-600 hover:bg-indigo-100 py-2 rounded-xl text-sm font-bold transition">
                    Subir Archivo
                </button>
            </div>
            
            <!-- Document 3 -->
            <div class="border border-slate-200 rounded-2xl p-5 hover:shadow-md transition bg-slate-50 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-16 h-16 bg-emerald-500 rounded-bl-full z-0 opacity-10"></div>
                <div class="relative z-10 flex justify-between items-start">
                    <div class="w-12 h-12 bg-white text-slate-400 rounded-xl shadow-sm flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-file-contract"></i>
                    </div>
                    <span class="bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-lg text-xs font-bold">Completado</span>
                </div>
                <h4 class="font-bold text-slate-800 mt-4">Contrato de Matrícula</h4>
                <p class="text-xs text-slate-500 mt-1 mb-4">Firmado por el acudiente</p>
                <button class="w-full bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 py-2 rounded-xl text-sm font-bold transition">
                    Ver Archivo
                </button>
            </div>
        </div>
        
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
