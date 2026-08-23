<?php
// /modules/teachers/create.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();

// Available users with role DOCENTE that are not yet in teachers table
$teacherUsers = $db->query("
    SELECT u.* FROM users u 
    LEFT JOIN teachers t ON u.id = t.user_id 
    WHERE u.role_id = 4 AND t.id IS NULL
    ORDER BY u.first_name ASC
")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $create_type = $_POST['create_type'] ?? 'existing';
    $specialty = Auth::sanitize($_POST['specialty'] ?? '');

    $user_id = null;

    if ($create_type === 'new') {
        $document = Auth::sanitize($_POST['document'] ?? '');
        $first_name = Auth::sanitize($_POST['first_name'] ?? '');
        $last_name = Auth::sanitize($_POST['last_name'] ?? '');
        $email = Auth::sanitize($_POST['email'] ?? '');
        $phone = Auth::sanitize($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!empty($document) && !empty($first_name) && !empty($last_name) && !empty($email) && !empty($password)) {
            try {
                // Check uniqueness
                $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE document = ? OR email = ?");
                $stmt->execute([$document, $email]);
                if ($stmt->fetchColumn() > 0) {
                    $error = 'Ya existe un usuario con ese número de documento o correo electrónico.';
                } else {
                    $pwdHash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("
                        INSERT INTO users (role_id, document, first_name, last_name, email, phone, password, status) 
                        VALUES (4, ?, ?, ?, ?, ?, ?, 1)
                    ");
                    $stmt->execute([$document, $first_name, $last_name, $email, $phone, $pwdHash]);
                    $user_id = $db->lastInsertId();
                }
            } catch (PDOException $e) {
                $error = 'Error creando perfil de usuario: ' . $e->getMessage();
            }
        } else {
            $error = 'Por favor complete todos los campos obligatorios del nuevo usuario.';
        }
    } else {
        $user_id = intval($_POST['user_id'] ?? 0);
        if (empty($user_id)) {
            $error = 'Debe seleccionar un usuario docente válido.';
        }
    }

    if (empty($error) && !empty($user_id) && !empty($specialty)) {
        try {
            $stmt = $db->prepare("INSERT INTO teachers (user_id, specialty) VALUES (?, ?)");
            $stmt->execute([$user_id, $specialty]);

            header("Location: /modules/teachers/index.php");
            exit;
        } catch (PDOException $e) {
            $error = 'Error vinculando docente: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<!-- Form -->
<div class="max-w-3xl mx-auto bg-white rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 p-8 md:p-12 relative overflow-hidden group">
    <!-- Premium ambient background -->
    <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-50 rounded-full blur-3xl opacity-50 pointer-events-none transition-opacity group-hover:opacity-100"></div>
    <div class="relative z-10">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-1">Agregar Docente</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Cree una nueva cuenta o asocie un perfil docente.</p>
        </div>
        <a href="/modules/teachers/index.php" class="text-sm font-semibold text-slate-500 hover:text-slate-800 flex items-center space-x-1">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver</span>
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="mb-5 p-4 rounded-xl bg-red-50 text-red-700 text-sm border border-red-200/60 flex items-start space-x-3">
            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
            <span class="font-medium"><?= $error ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <input type="hidden" name="create_type" value="new">

        <!-- new user panel -->
        <div id="panel-new" class="space-y-4 p-5 rounded-2xl bg-indigo-50/40 border border-indigo-100/60">
            <p class="text-xs font-extrabold text-indigo-600 uppercase tracking-widest mb-2 flex items-center space-x-1.5"><i class="fa-solid fa-user-plus"></i><span>Datos del Nuevo Docente</span></p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Documento / Cédula *</label>
                    <input type="text" name="document" placeholder="Ej. 778899"
                           class="block w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Correo Electrónico *</label>
                    <input type="email" name="email" placeholder="profesor@ejemplo.com"
                           class="block w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Nombres *</label>
                    <input type="text" name="first_name" placeholder="Ej. Mario"
                           class="block w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Apellidos *</label>
                    <input type="text" name="last_name" placeholder="Ej. Vargas"
                           class="block w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Teléfono</label>
                    <input type="text" name="phone" placeholder="Ej. 3012223344"
                           class="block w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Contraseña Inicial *</label>
                    <input type="password" name="password" placeholder="Mínimo 4 caracteres"
                           class="block w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>
            </div>
        </div>

        <!-- Informacion de Especialidad -->
        <div class="border-t border-slate-100 pt-5">
            <label for="specialty" class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Especialidad del Docente *</label>
            <input type="text" name="specialty" id="specialty" required placeholder="Ej. Matemáticas y Física, Ciencias Sociales"
                   class="block w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
        </div>

        <div class="pt-4 flex items-center justify-end space-x-2 border-t border-slate-100">
            <a href="/modules/teachers/index.php" class="px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-all text-sm">Cancelar</a>
            <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 text-sm flex items-center justify-center space-x-2">Guardar Docente</button>
        </div>
    </form>
</div>

<script>
function toggleCreationType(type) {
    const existingPanel = document.getElementById('panel-existing');
    const newPanel = document.getElementById('panel-new');

    if (type === 'new') {
        existingPanel.classList.add('hidden');
        newPanel.classList.remove('hidden');
    } else {
        existingPanel.classList.remove('hidden');
        newPanel.classList.add('hidden');
    }
}
</script>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
