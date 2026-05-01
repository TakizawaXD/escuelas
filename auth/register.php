<?php
// /auth/register.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

if (Auth::check()) {
    header("Location: /index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $document = Auth::sanitize($_POST['document'] ?? '');
    $first_name = Auth::sanitize($_POST['first_name'] ?? '');
    $last_name = Auth::sanitize($_POST['last_name'] ?? '');
    $email = Auth::sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_id = 5; // Default: Estudiante

    if (!empty($document) && !empty($first_name) && !empty($last_name) && !empty($email) && !empty($password)) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // 1. Check if document exists
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE document = ?");
            $stmt->execute([$document]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'El número de documento ya está registrado.';
            } else {
                // 2. Check if email exists
                $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetchColumn() > 0) {
                    $error = 'El correo electrónico ya está registrado.';
                } else {
                    // 3. Create user
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    $insertStmt = $db->prepare("
                        INSERT INTO users (role_id, document, first_name, last_name, email, password, status)
                        VALUES (?, ?, ?, ?, ?, ?, 1)
                    ");
                    $insertStmt->execute([$role_id, $document, $first_name, $last_name, $email, $hashedPassword]);
                    $newUserId = $db->lastInsertId();

                    // 4. Create student profile
                    $studentStmt = $db->prepare("
                        INSERT INTO students (user_id, course_id, birth_date, address)
                        VALUES (?, 1, '2010-01-01', 'Dirección de Prueba')
                    ");
                    $studentStmt->execute([$newUserId]);

                    $success = '¡Registro completado exitosamente! Ya puede iniciar sesión.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Error del sistema: ' . $e->getMessage();
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
    <title>SISTEMA ESCOLAR - REGISTRO</title>
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
        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-emerald-600 rounded-2xl mx-auto flex items-center justify-center text-white font-bold text-2xl shadow-lg shadow-emerald-500/30 mb-4">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Registro de Cuenta</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Crea tu cuenta de estudiante</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-5 p-4 rounded-xl bg-red-50 text-red-700 text-sm border border-red-200/60 flex items-start space-x-3 animate-fade-in">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <span class="font-medium"><?= $error ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="mb-5 p-4 rounded-xl bg-emerald-50 text-emerald-700 text-sm border border-emerald-200/60 flex items-start space-x-3 animate-fade-in">
                <i class="fa-solid fa-circle-check mt-0.5"></i>
                <span class="font-medium"><?= $success ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-4">
            <div>
                <label for="document" class="block text-sm font-semibold text-slate-700 mb-1">N° de Cédula / Documento</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <input type="text" name="document" id="document" required
                           class="block w-full pl-11 pr-4 py-2.5 bg-slate-50/50 border border-slate-200 text-slate-800 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm font-medium transition"
                           placeholder="Ej. 11223344" value="<?= isset($_POST['document']) ? htmlspecialchars($_POST['document']) : '' ?>">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-semibold text-slate-700 mb-1">Nombre</label>
                    <input type="text" name="first_name" id="first_name" required
                           class="block w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 text-slate-800 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm font-medium transition"
                           placeholder="Ej. María" value="<?= isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : '' ?>">
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-semibold text-slate-700 mb-1">Apellido</label>
                    <input type="text" name="last_name" id="last_name" required
                           class="block w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 text-slate-800 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm font-medium transition"
                           placeholder="Ej. Gómez" value="<?= isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : '' ?>">
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Correo Electrónico</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <input type="email" name="email" id="email" required
                           class="block w-full pl-11 pr-4 py-2.5 bg-slate-50/50 border border-slate-200 text-slate-800 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm font-medium transition"
                           placeholder="Ej. maria@escuela.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Contraseña</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <input type="password" name="password" id="password" required
                           class="block w-full pl-11 pr-4 py-2.5 bg-slate-50/50 border border-slate-200 text-slate-800 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm font-medium transition"
                           placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 active:scale-[0.98] transition-all flex items-center justify-center space-x-2">
                <span>Registrarse</span>
                <i class="fa-solid fa-user-check text-sm"></i>
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-100 text-center">
            <span class="text-xs text-slate-500">¿Ya tienes una cuenta?</span>
            <a href="/auth/login.php" class="text-xs font-bold text-indigo-600 hover:text-indigo-500 ml-1 transition">Inicia sesión aquí</a>
        </div>
    </div>
</body>
</html>
