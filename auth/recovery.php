<?php
// /auth/recovery.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $document = Auth::sanitize($_POST['document'] ?? '');

    if (!empty($document)) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE document = ? AND status = 1");
            $stmt->execute([$document]);
            $user = $stmt->fetch();

            if ($user) {
                // Generate secure temporary token
                $token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Save to security_tokens
                $stmt = $db->prepare("INSERT INTO security_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$user['id'], $token, $expires_at]);

                // Direct to the reset page directly with token for ease of use in demo
                $message = "Solicitud exitosa. Token generado correctamente. <a href='/auth/reset.php?token=$token' class='underline text-indigo-600 font-bold'>Haga clic aquí para restablecer su contraseña</a>";
            } else {
                $error = 'No se encontró ningún usuario activo con ese número de documento.';
            }
        } catch (PDOException $e) {
            $error = 'Error del sistema: ' . $e->getMessage();
        }
    } else {
        $error = 'Por favor complete el campo de cédula.';
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RECUPERAR CONTRASEÑA - SISTEMA ESCOLAR</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 bg-slate-50">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-8 border border-slate-100/80">
        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-indigo-600 rounded-2xl mx-auto flex items-center justify-center text-white font-bold text-2xl shadow-lg shadow-indigo-500/30 mb-4">
                <i class="fa-solid fa-key"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Recuperar Contraseña</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Ingrese su cédula para generar un enlace de restablecimiento</p>
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

        <form method="POST" action="" class="space-y-5">
            <div>
                <label for="document" class="block text-sm font-semibold text-slate-700 mb-1.5">N° de Cédula / Documento</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <input type="text" name="document" id="document" required
                           class="block w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition"
                           placeholder="Ej. 12345678">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 active:scale-[0.98] transition-all flex items-center justify-center space-x-2">
                <span>Generar Enlace</span>
                <i class="fa-solid fa-paper-plane text-sm"></i>
            </button>
            <div class="text-center mt-4">
                <a href="/auth/login.php" class="text-sm font-semibold text-slate-500 hover:text-indigo-600 transition">Regresar al inicio de sesión</a>
            </div>
        </form>
    </div>
</body>
</html>
