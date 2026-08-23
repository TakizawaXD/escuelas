<?php
// /auth/2fa-verify.php
session_start();
require_once __DIR__ . '/../config/database.php';

// Esta página solo debe ser accesible si se intentó hacer login y se requiere 2FA
if (!isset($_SESSION['temp_user_id']) || !isset($_SESSION['require_2fa'])) {
    header("Location: /auth/login.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    
    // Aquí se verificaría el código contra el secreto del usuario.
    // Como es un mockup, validaremos que el código no esté vacío y tenga 6 dígitos
    if (strlen($code) === 6 && is_numeric($code)) {
        // Simulación de éxito
        $_SESSION['user_id'] = $_SESSION['temp_user_id'];
        $_SESSION['role'] = $_SESSION['temp_role'];
        
        // Limpiar variables temporales
        unset($_SESSION['temp_user_id']);
        unset($_SESSION['require_2fa']);
        unset($_SESSION['temp_role']);
        
        header("Location: /index.php");
        exit;
    } else {
        $error = "Código inválido. Intenta nuevamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación 2FA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-100 p-8 text-center animate-fade-in">
        <div class="w-20 h-20 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center text-3xl mx-auto mb-6">
            <i class="fa-solid fa-mobile-screen-button"></i>
        </div>
        
        <h2 class="text-2xl font-extrabold text-slate-800 mb-2">Verificación de 2 Pasos</h2>
        <p class="text-slate-500 text-sm mb-8">Ingresa el código de 6 dígitos generado por tu aplicación autenticadora.</p>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-xl text-sm font-medium mb-6">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <input type="text" name="code" maxlength="6" autocomplete="off" class="w-full text-center text-3xl tracking-[0.5em] font-bold px-4 py-4 border-2 border-slate-200 rounded-2xl focus:border-indigo-500 focus:ring-0 outline-none transition" placeholder="------" required>
            </div>
            
            <button type="submit" class="w-full bg-indigo-600 text-white hover:bg-indigo-500 py-3 rounded-xl font-bold transition shadow-lg shadow-indigo-500/30">
                Verificar Código
            </button>
        </form>
        
        <div class="mt-6">
            <a href="/auth/login.php" class="text-sm text-slate-400 hover:text-slate-600 font-medium">Volver al inicio de sesión</a>
        </div>
    </div>

</body>
</html>
