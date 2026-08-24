<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEMA ESCOLAR - ACCESO AL PORTAL</title>
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
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Iniciar Sesión</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Acceso institucional ERP escolar</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-5 p-4 rounded-xl bg-red-50 text-red-700 text-sm border border-red-200/60 flex items-start space-x-3">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <span class="font-medium"><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="/auth/login.php" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
            <div>
                <label for="document" class="block text-sm font-semibold text-slate-700 mb-1.5">N° de Cédula / Documento</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <input type="text" name="document" id="document" required autocomplete="username"
                           class="block w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition"
                           placeholder="Ej. 12345678">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Contraseña</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <input type="password" name="password" id="password" required autocomplete="current-password"
                           class="block w-full pl-11 pr-4 py-3 bg-slate-50/50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition"
                           placeholder="••••••••">
                </div>
            </div>

            <div class="flex items-center justify-between text-sm py-1">
                <a href="/auth/recovery.php" class="font-semibold text-indigo-600 hover:text-indigo-500 transition">¿Olvidó su contraseña?</a>
            </div>

            <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 active:scale-[0.98] transition-all flex items-center justify-center space-x-2">
                <span>Ingresar</span>
                <i class="fa-solid fa-arrow-right-to-bracket text-sm"></i>
            </button>
        </form>

        <div class="mt-4 pt-3 text-center border-t border-slate-50">
            <span class="text-xs text-slate-500">¿No tienes una cuenta?</span>
            <a href="/auth/register.php" class="text-xs font-bold text-indigo-600 hover:text-indigo-500 ml-1 transition">Regístrate aquí</a>
        </div>

        <div class="mt-8 pt-6 border-t border-slate-100 text-xs text-slate-500 leading-relaxed">
            <p class="text-center font-medium text-slate-600 mb-2">Credenciales Administrador Demo:</p>
            <div class="flex justify-between px-4 py-2 bg-slate-50 rounded-xl border border-slate-200/60 font-mono">
                <span>Cédula: <span class="font-bold text-slate-700">12345678</span></span>
                <span>Clave: <span class="font-bold text-slate-700">admin</span></span>
            </div>
        </div>
    </div>
</body>
</html>
