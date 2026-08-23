<?php
// /auth/2fa-setup.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

Auth::redirectIfNotAuth();

$db = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT tfa_enabled FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['enable'])) {
        // En un entorno real, aquí se generaría el secreto y el QR Code usando una librería como PHPGangsta_GoogleAuthenticator
        $secret = 'MOCK_SECRET_12345'; // Simulado
        $stmt = $db->prepare("UPDATE users SET tfa_enabled = 1, tfa_secret = ? WHERE id = ?");
        $stmt->execute([$secret, $user_id]);
        $success = "Autenticación en dos pasos activada exitosamente.";
        $user['tfa_enabled'] = 1;
    } elseif (isset($_POST['disable'])) {
        $stmt = $db->prepare("UPDATE users SET tfa_enabled = 0, tfa_secret = NULL WHERE id = ?");
        $stmt->execute([$user_id]);
        $success = "Autenticación en dos pasos desactivada.";
        $user['tfa_enabled'] = 0;
    }
}

include __DIR__ . '/../views/layout/header.php';
include __DIR__ . '/../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-3xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Seguridad 2FA</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Protege tu cuenta con verificación de dos pasos.</p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl flex items-center space-x-3">
            <i class="fa-solid fa-circle-check"></i>
            <span class="font-medium text-sm"><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 flex flex-col md:flex-row gap-8 items-center">
        
        <div class="w-48 h-48 bg-slate-50 rounded-2xl border-2 border-slate-200 flex flex-col items-center justify-center p-4">
            <?php if ($user['tfa_enabled']): ?>
                <i class="fa-solid fa-shield-check text-6xl text-emerald-500 mb-2"></i>
                <p class="text-xs font-bold text-emerald-700 bg-emerald-100 px-2 py-1 rounded">2FA ACTIVO</p>
            <?php else: ?>
                <i class="fa-solid fa-qrcode text-6xl text-slate-300 mb-2"></i>
                <p class="text-xs font-bold text-slate-500">Sin configurar</p>
            <?php endif; ?>
        </div>

        <div class="flex-1">
            <h3 class="text-xl font-bold text-slate-800 mb-2">Google Authenticator</h3>
            <p class="text-sm text-slate-600 mb-6 leading-relaxed">
                Añade una capa adicional de seguridad a tu cuenta. Una vez activado, deberás ingresar un código temporal generado por tu aplicación móvil al iniciar sesión.
            </p>
            
            <form method="POST">
                <?php if ($user['tfa_enabled']): ?>
                    <button type="submit" name="disable" onclick="return confirm('¿Seguro que deseas desactivar la autenticación de dos pasos?');" class="bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 px-6 py-2.5 rounded-xl font-bold transition">
                        <i class="fa-solid fa-shield-halved mr-1"></i> Desactivar 2FA
                    </button>
                <?php else: ?>
                    <button type="submit" name="enable" class="bg-indigo-600 text-white hover:bg-indigo-500 px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition active:scale-[0.98]">
                        <i class="fa-solid fa-shield mr-1"></i> Configurar Ahora
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../views/layout/footer.php'; ?>
