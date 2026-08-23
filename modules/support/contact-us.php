<?php
// /modules/support/contact-us.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isAuth = isset($_SESSION['user_id']);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $message = htmlspecialchars($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error = "Por favor completa todos los campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor ingresa un correo electrónico válido.";
    } else {
        // En un entorno real, aquí enviarías un correo o lo guardarías en BD.
        $success = "Gracias por contactarnos. Hemos recibido tu mensaje y te responderemos pronto.";
    }
}

include __DIR__ . '/../../views/layout/header.php';
if ($isAuth) {
    include __DIR__ . '/../../views/layout/sidebar.php';
}
?>

<div class="space-y-6 animate-fade-in max-w-2xl mx-auto <?= !$isAuth ? 'pt-10' : '' ?>">
    <div class="text-center space-y-4 mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-indigo-100 text-indigo-600 rounded-full mb-2 shadow-inner">
            <i class="fa-solid fa-headset text-4xl"></i>
        </div>
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Contáctanos</h1>
        <p class="text-slate-500 font-medium text-lg">
            ¿Tienes alguna duda o necesitas ayuda? Envíanos un mensaje y nuestro equipo de soporte se pondrá en contacto contigo.
        </p>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl flex items-center space-x-3 mb-6">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span class="font-medium text-sm"><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl flex items-center space-x-3 mb-6">
            <i class="fa-solid fa-circle-check"></i>
            <span class="font-medium text-sm"><?= htmlspecialchars($success) ?></span>
        </div>
    <?php else: ?>
        <form method="POST" class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 space-y-6">
            <div class="space-y-2">
                <label for="name" class="block text-sm font-bold text-slate-700">Nombre Completo <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-user text-sm"></i>
                    </div>
                    <input type="text" id="name" name="name" required placeholder="Ej. Juan Pérez"
                           class="block w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-sm">
                </div>
            </div>

            <div class="space-y-2">
                <label for="email" class="block text-sm font-bold text-slate-700">Correo Electrónico <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-envelope text-sm"></i>
                    </div>
                    <input type="email" id="email" name="email" required placeholder="ejemplo@correo.com"
                           class="block w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-sm">
                </div>
            </div>

            <div class="space-y-2">
                <label for="message" class="block text-sm font-bold text-slate-700">Mensaje <span class="text-red-500">*</span></label>
                <textarea id="message" name="message" rows="5" required placeholder="Describe tu consulta o problema..."
                          class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-sm"></textarea>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-xl font-bold tracking-wide transition shadow-lg shadow-indigo-500/20 active:scale-[0.98]">
                    <i class="fa-solid fa-paper-plane mr-2"></i> Enviar Mensaje
                </button>
            </div>
        </form>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-8">
        <div class="bg-indigo-50 p-6 rounded-2xl flex items-center space-x-4 border border-indigo-100">
            <div class="w-12 h-12 bg-indigo-100 rounded-xl text-indigo-600 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-phone"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Llámanos</p>
                <p class="font-bold text-slate-800">+1 (555) 123-4567</p>
            </div>
        </div>
        <div class="bg-indigo-50 p-6 rounded-2xl flex items-center space-x-4 border border-indigo-100">
            <div class="w-12 h-12 bg-indigo-100 rounded-xl text-indigo-600 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-envelope"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Escríbenos</p>
                <p class="font-bold text-slate-800">soporte@escuela.com</p>
            </div>
        </div>
    </div>
</div>

<?php 
if ($isAuth) {
    include __DIR__ . '/../../views/layout/footer.php'; 
} else {
    echo '</body></html>';
}
?>
