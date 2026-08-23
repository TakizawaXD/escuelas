<?php
// /modules/subjects/resources.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /modules/subjects/index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT name FROM subjects WHERE id = ?");
$stmt->execute([$id]);
$subject = $stmt->fetch();

if (!$subject) {
    header("Location: /modules/subjects/index.php");
    exit;
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-4xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/subjects/view.php?id=<?= $id ?>" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Materiales de Estudio</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Materia: <?= htmlspecialchars($subject['name']) ?></p>
        </div>
    </div>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
            <h3 class="text-xl font-bold text-slate-800">Recursos y Enlaces</h3>
            <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'DOCENTE'])): ?>
                <button class="bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2.5 rounded-xl font-bold tracking-wide transition shadow-lg shadow-indigo-500/20 active:scale-[0.98]">
                    <i class="fa-solid fa-cloud-arrow-up mr-2"></i> Subir Material
                </button>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Mockup Resource 1 -->
            <div class="flex items-center space-x-4 p-4 border border-slate-200 rounded-2xl hover:bg-slate-50 transition group">
                <div class="w-12 h-12 bg-red-50 text-red-500 rounded-xl flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-file-pdf"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-bold text-slate-800 truncate">Guía de Estudio N1.pdf</h4>
                    <p class="text-xs text-slate-500">2.4 MB • Subido hace 2 días</p>
                </div>
                <button class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 flex items-center justify-center transition">
                    <i class="fa-solid fa-download"></i>
                </button>
            </div>

            <!-- Mockup Resource 2 -->
            <div class="flex items-center space-x-4 p-4 border border-slate-200 rounded-2xl hover:bg-slate-50 transition group">
                <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-file-word"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-bold text-slate-800 truncate">Taller Práctico.docx</h4>
                    <p class="text-xs text-slate-500">1.1 MB • Subido hace 1 semana</p>
                </div>
                <button class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 flex items-center justify-center transition">
                    <i class="fa-solid fa-download"></i>
                </button>
            </div>
            
            <!-- Mockup Resource 3 -->
            <div class="flex items-center space-x-4 p-4 border border-slate-200 rounded-2xl hover:bg-slate-50 transition group">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-500 rounded-xl flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-link"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-bold text-slate-800 truncate">Video Referencia (YouTube)</h4>
                    <p class="text-xs text-slate-500">Enlace Externo</p>
                </div>
                <button class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 flex items-center justify-center transition">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </button>
            </div>
        </div>

        <!-- Pagination or empty state if needed -->
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
