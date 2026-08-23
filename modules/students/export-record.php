<?php
// /modules/students/export-record.php
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

// En un caso real aquí se usaría una librería como TCPDF o Dompdf.
// Para este mockup, mostraremos una interfaz que simula la generación.
include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-3xl mx-auto flex flex-col items-center justify-center min-h-[60vh] text-center">
    
    <div class="w-24 h-24 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center text-4xl mb-4 animate-pulse">
        <i class="fa-solid fa-file-pdf"></i>
    </div>
    
    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Generando Expediente PDF...</h2>
    <p class="text-slate-500 font-medium text-lg mt-2">
        Compilando datos de <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
    </p>

    <div class="w-full max-w-md bg-slate-100 rounded-full h-2.5 mt-8 overflow-hidden">
        <div class="bg-indigo-600 h-2.5 rounded-full generate-bar" style="width: 0%"></div>
    </div>
    <p class="text-sm text-slate-400 mt-2" id="status-text">Iniciando...</p>

</div>

<style>
    .generate-bar {
        animation: loadingBar 3s ease-in-out forwards;
    }
    @keyframes loadingBar {
        0% { width: 0%; }
        50% { width: 60%; }
        100% { width: 100%; }
    }
</style>

<script>
    setTimeout(() => { document.getElementById('status-text').innerText = "Recopilando calificaciones..."; }, 1000);
    setTimeout(() => { document.getElementById('status-text').innerText = "Generando documento..."; }, 2000);
    setTimeout(() => { 
        document.getElementById('status-text').innerHTML = "<span class='text-emerald-500 font-bold'>¡Listo! La descarga comenzará automáticamente.</span>";
        // Simular descarga regresando
        setTimeout(() => { window.location.href = "/modules/students/view.php?id=<?= $id ?>"; }, 1500);
    }, 3000);
</script>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
