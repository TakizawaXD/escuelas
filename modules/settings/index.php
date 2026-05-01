<?php
// /modules/settings/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();

// Fetch settings
$settings = $db->query("SELECT * FROM settings WHERE id = 1")->fetch();

// If doesn't exist, create default
if (!$settings) {
    $db->exec("INSERT OR IGNORE INTO settings (id, app_name, logo_url, color_primary, color_secondary) VALUES (1, 'SISTEMA ESCOLAR', '', '#059669', '#10b981')");
    $settings = $db->query("SELECT * FROM settings WHERE id = 1")->fetch();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_name = Auth::sanitize($_POST['app_name'] ?? '');
    $logo_url = Auth::sanitize($_POST['logo_url'] ?? '');
    $color_primary = Auth::sanitize($_POST['color_primary'] ?? '#059669');
    $color_secondary = Auth::sanitize($_POST['color_secondary'] ?? '#10b981');

    // Handling direct file upload of a real logo image file!
    if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['logo_file']['tmp_name'];
        $fileName = $_FILES['logo_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadDir = __DIR__ . '/../../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $newFileName = 'logo_' . time() . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $logo_url = '/uploads/' . $newFileName;
            } else {
                $error = 'Error al mover el archivo subido.';
            }
        } else {
            $error = 'Extensión de archivo no permitida para el logotipo.';
        }
    }

    if (empty($error)) {
        if (!empty($app_name)) {
            try {
                $stmt = $db->prepare("
                    UPDATE settings 
                    SET app_name = ?, logo_url = ?, color_primary = ?, color_secondary = ? 
                    WHERE id = 1
                ");
                $stmt->execute([$app_name, $logo_url, $color_primary, $color_secondary]);

                $success = '¡Ajustes de personalización guardados exitosamente!';
                
                // Refresh local variable
                $settings['app_name'] = $app_name;
                $settings['logo_url'] = $logo_url;
                $settings['color_primary'] = $color_primary;
                $settings['color_secondary'] = $color_secondary;

            } catch (PDOException $e) {
                $error = 'Error guardando ajustes: ' . $e->getMessage();
            }
        } else {
            $error = 'El nombre de la aplicación es obligatorio.';
        }
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="max-w-4xl mx-auto bg-white rounded-3xl shadow-sm border border-slate-100 p-8 animate-fade-in">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight font-heading">Personalización y Ajustes</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Configura el nombre de la escuela, el logotipo (imagen real) y la paleta de colores de la aplicación.</p>
        </div>
        <a href="/index.php" class="text-sm font-semibold text-slate-500 hover:text-slate-800 flex items-center space-x-1">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver</span>
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="mb-5 p-4 rounded-xl bg-emerald-50 text-emerald-700 text-sm border border-emerald-200/60 flex items-start space-x-3">
            <i class="fa-solid fa-circle-check mt-0.5"></i>
            <span class="font-medium"><?= $success ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="mb-5 p-4 rounded-xl bg-red-50 text-red-700 text-sm border border-red-200/60 flex items-start space-x-3">
            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
            <span class="font-medium"><?= $error ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="app_name" class="block text-sm font-semibold text-slate-700 mb-1.5">Nombre de la Escuela / Aplicación *</label>
                <input type="text" name="app_name" id="app_name" required value="<?= htmlspecialchars($settings['app_name'] ?? 'SISTEMA ESCOLAR') ?>"
                       class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-emerald-500 outline-none text-sm font-medium transition">
            </div>

            <!-- Upload Real Image File Format for the School Logo -->
            <div class="md:col-span-2">
                <label for="logo_file" class="block text-sm font-semibold text-slate-700 mb-1.5">Subir Imagen de Logotipo (Formato de Imagen)</label>
                <div class="flex items-center space-x-4">
                    <div class="flex-1">
                        <input type="file" name="logo_file" id="logo_file" accept="image/*"
                               class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-emerald-500 outline-none text-sm font-medium transition">
                        <p class="text-xs text-slate-400 mt-1">Sube el logotipo oficial (PNG, JPG, SVG, WebP).</p>
                    </div>
                    <?php if (!empty($settings['logo_url'])): ?>
                        <div class="w-16 h-16 rounded-2xl overflow-hidden bg-slate-50 border border-slate-100 flex items-center justify-center p-1">
                            <img src="<?= htmlspecialchars($settings['logo_url']) ?>" alt="Logotipo actual" class="w-full h-full object-contain">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="md:col-span-2">
                <label for="logo_url" class="block text-sm font-semibold text-slate-700 mb-1.5">O bien, URL del Logotipo</label>
                <input type="url" name="logo_url" id="logo_url" placeholder="https://mi-escuela.com/logo.png" value="<?= htmlspecialchars($settings['logo_url'] ?? '') ?>"
                       class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-emerald-500 outline-none text-sm font-medium transition">
            </div>

            <div>
                <label for="color_primary" class="block text-sm font-semibold text-slate-700 mb-1.5">Color Primario (Tema Principal)</label>
                <div class="flex items-center space-x-3">
                    <input type="color" name="color_primary" id="color_primary" value="<?= htmlspecialchars($settings['color_primary'] ?? '#059669') ?>"
                           class="w-12 h-12 bg-transparent border-0 cursor-pointer outline-none">
                    <input type="text" value="<?= htmlspecialchars($settings['color_primary'] ?? '#059669') ?>" readonly
                           class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 text-slate-600 rounded-xl text-sm font-medium">
                </div>
            </div>

            <div>
                <label for="color_secondary" class="block text-sm font-semibold text-slate-700 mb-1.5">Color Secundario (Destacados)</label>
                <div class="flex items-center space-x-3">
                    <input type="color" name="color_secondary" id="color_secondary" value="<?= htmlspecialchars($settings['color_secondary'] ?? '#10b981') ?>"
                           class="w-12 h-12 bg-transparent border-0 cursor-pointer outline-none">
                    <input type="text" value="<?= htmlspecialchars($settings['color_secondary'] ?? '#10b981') ?>" readonly
                           class="w-full px-4 py-2.5 bg-slate-50/50 border border-slate-200 text-slate-600 rounded-xl text-sm font-medium">
                </div>
            </div>
        </div>

        <div class="pt-4 flex items-center justify-end space-x-2 border-t border-slate-100">
            <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold tracking-wider rounded-xl transition text-sm shadow-lg shadow-emerald-500/20 uppercase">
                Guardar Personalización
            </button>
        </div>
    </form>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
