<?php
// /modules/support/contact.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = htmlspecialchars($_POST['subject'] ?? '');
    $message = htmlspecialchars($_POST['message'] ?? '');
    
    if (empty($subject) || empty($message)) {
        $error = "Por favor completa todos los campos.";
    } else {
        // Aquí se puede guardar en una tabla 'tickets' o enviar por correo
        $success = "Ticket de soporte enviado. El administrador revisará tu caso pronto.";
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-3xl mx-auto">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Soporte Técnico</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Envía un reporte o solicitud al equipo administrativo.</p>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl flex items-center space-x-3">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span class="font-medium text-sm"><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl flex items-center space-x-3">
            <i class="fa-solid fa-circle-check"></i>
            <span class="font-medium text-sm"><?= htmlspecialchars($success) ?></span>
        </div>
    <?php else: ?>
        <form method="POST" class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 space-y-6">
            <div class="space-y-2">
                <label for="subject" class="block text-sm font-bold text-slate-700">Asunto <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-heading text-sm"></i>
                    </div>
                    <input type="text" id="subject" name="subject" required placeholder="Ej. Problema con mis calificaciones"
                           class="block w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-sm">
                </div>
            </div>

            <div class="space-y-2">
                <label for="message" class="block text-sm font-bold text-slate-700">Descripción del Problema <span class="text-red-500">*</span></label>
                <textarea id="message" name="message" rows="5" required placeholder="Detalla aquí tu problema para que podamos ayudarte mejor..."
                          class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-sm"></textarea>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-xl font-bold tracking-wide transition shadow-lg shadow-indigo-500/20 active:scale-[0.98]">
                    <i class="fa-solid fa-paper-plane mr-2"></i> Enviar Ticket
                </button>
            </div>
        </form>
    <?php endif; ?>

    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mt-6">
        <h3 class="text-amber-800 font-bold mb-2 flex items-center space-x-2">
            <i class="fa-solid fa-lightbulb"></i>
            <span>Antes de enviar</span>
        </h3>
        <p class="text-amber-700 text-sm">
            Si tu problema es sobre acceso a la plataforma o cambio de contraseñas, por favor verifica con tu coordinador primero. Los tickets de soporte técnico se responden en un plazo de 24 a 48 horas hábiles.
        </p>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
