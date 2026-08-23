<?php
// /auth/email-verification.php
require_once __DIR__ . '/../config/database.php';
session_start();

$token = $_GET['token'] ?? null;
$error = '';
$success = '';

if ($token) {
    // Lógica simulada de verificación de correo
    // Buscaríamos al usuario por el token y actualizaríamos email_verified a 1
    // Asumiremos que el token 'valid' funciona para demostración.
    if ($token === 'valid') {
        $success = "Tu dirección de correo electrónico ha sido verificada con éxito.";
    } else {
        $error = "El enlace de verificación es inválido o ha expirado.";
    }
} else {
    $error = "No se proporcionó token de verificación.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Correo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-100 p-8 text-center animate-fade-in">
        
        <?php if ($success): ?>
            <div class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center text-4xl mx-auto mb-6">
                <i class="fa-solid fa-envelope-circle-check"></i>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-800 mb-2">¡Correo Verificado!</h2>
            <p class="text-slate-500 text-sm mb-8"><?= htmlspecialchars($success) ?></p>
            <a href="/auth/login.php" class="inline-block w-full bg-indigo-600 text-white hover:bg-indigo-500 py-3 rounded-xl font-bold transition shadow-lg shadow-indigo-500/30">
                Ir a Iniciar Sesión
            </a>
        <?php else: ?>
            <div class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center text-4xl mx-auto mb-6">
                <i class="fa-solid fa-envelope-open-text"></i>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-800 mb-2">Error de Verificación</h2>
            <p class="text-slate-500 text-sm mb-8"><?= htmlspecialchars($error) ?></p>
            <a href="/auth/login.php" class="inline-block w-full bg-slate-100 text-slate-600 hover:bg-slate-200 py-3 rounded-xl font-bold transition">
                Volver al inicio
            </a>
        <?php endif; ?>
        
    </div>

</body>
</html>
