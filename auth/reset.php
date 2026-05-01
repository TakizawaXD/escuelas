<?php
// /auth/reset.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

$message = '';
$error = '';
$token = Auth::sanitize($_GET['token'] ?? '');
$user_id = null;

if (!empty($token)) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT * FROM security_tokens 
            WHERE token = ? AND expires_at > NOW()
        ");
        $stmt->execute([$token]);
        $tokenRow = $stmt->fetch();

        if ($tokenRow) {
            $user_id = $tokenRow['user_id'];
        } else {
            $error = 'El token es inválido o ha expirado.';
        }
    } catch (PDOException $e) {
        $error = 'Error del sistema: ' . $e->getMessage();
    }
} else {
    $error = 'Falta el token de restablecimiento de contraseña.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_id) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (!empty($password) && !empty($confirm)) {
        if ($password === $confirm) {
            try {
                $db = Database::getInstance()->getConnection();
                
                // Update password
                $newHash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$newHash, $user_id]);

                // Delete used tokens for this user
                $stmt = $db->prepare("DELETE FROM security_tokens WHERE user_id = ?");
                $stmt->execute([$user_id]);

                $message = "Su contraseña ha sido cambiada exitosamente. <a href='/auth/login.php' class='underline text-indigo-600 font-bold'>Inicie sesión aquí</a>";
                $user_id = null; // Hide form after success
            } catch (PDOException $e) {
                $error = 'Error del sistema: ' . $e->getMessage();
            }
        } else {
            $error = 'Las contraseñas no coinciden.';
        }
    } else {
        $error = 'Por favor complete todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REABLECER CONTRASEÑA - SISTEMA ESCOLAR</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Outfit', sans-serif; } </style>
</head>
<body class="h-full flex items-center justify-center p-4 bg-slate-50">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-8 border border-slate-100/80">
        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-indigo-600 rounded-2xl mx-auto flex items-center justify-center text-white font-bold text-2xl shadow-lg shadow-indigo-500/30 mb-4">
                <i class="fa-solid fa-lock-open"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Restablecer Contraseña</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Ingrese su nueva contraseña de acceso</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-5 p-4 rounded-xl bg-red-50 text-red-700 text-sm border border-red-200/60 flex items-start space-x-3">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <span class="font-medium"><?= $error ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <div class="mb-5 p-4 rounded-xl bg-green-50 text-green-700 text-sm border border-green-200/60 flex items-start space-x-3">
                <i class="fa-solid fa-circle-check mt-0.5 text-green-500"></i>
                <span class="font-medium"><?= $message ?></span>
            </div>
        <?php endif; ?>

        <?php if ($user_id): ?>
        <form method="POST" action="" class="space-y-5">
            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Nueva Contraseña</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <input type="password" name="password" id="password" required minlength="4"
                           class="block w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition"
                           placeholder="••••••••">
                </div>
            </div>

            <div>
                <label for="confirm" class="block text-sm font-semibold text-slate-700 mb-1.5">Confirmar Contraseña</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-check-double"></i>
                    </div>
                    <input type="password" name="confirm" id="confirm" required minlength="4"
                           class="block w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition"
                           placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 active:scale-[0.98] transition-all flex items-center justify-center space-x-2">
                <span>Actualizar Contraseña</span>
                <i class="fa-solid fa-arrows-rotate text-sm"></i>
            </button>
        </form>
        <?php endif; ?>

        <div class="text-center mt-6">
            <a href="/auth/login.php" class="text-sm font-semibold text-slate-500 hover:text-indigo-600 transition">Regresar al inicio de sesión</a>
        </div>
    </div>
</body>
</html>
