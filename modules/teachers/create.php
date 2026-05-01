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
<div class="max-w-2xl mx-auto bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Agregar Docente</h2>
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
        <!-- Selector -->
        <div class="bg-slate-50/60 p-4 rounded-2xl border border-slate-100 flex items-center justify-around mb-4">
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="radio" name="create_type" value="existing" checked onclick="toggleCreationType('existing')" class="text-indigo-600 focus:ring-indigo-500 border-slate-300">
                <span class="text-sm font-bold text-slate-700">Usuario Existente</span>
            </label>
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="radio" name="create_type" value="new" onclick="toggleCreationType('new')" class="text-indigo-600 focus:ring-indigo-500 border-slate-300">
                <span class="text-sm font-bold text-slate-700">Crear Nuevo Usuario</span>
            </label>
        </div>

        <!-- existing -->
        <div id="panel-existing" class="space-y-4">
            <div>
                <label for="user_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Usuario del Docente *</label>
                <select name="user_id" id="user_id"
                        class="block w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                    <option value="">Seleccione un docente...</option>
                    <?php foreach ($teacherUsers as $usr): ?>
                        <option value="<?= $usr['id'] ?>"><?= htmlspecialchars($usr['first_name'] . ' ' . $usr['last_name'] . ' (' . $usr['document'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-slate-400 mt-1 font-medium">Docentes con rol DOCENTE creados previamente que no están vinculados.</p>
            </div>
        </div>

        <!-- new -->
        <div id="panel-new" class="hidden space-y-4 border p-4 rounded-2xl bg-slate-50/40 border-slate-200/60">
            <p class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-2">Datos de nueva cuenta de Docente</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Documento / Cédula *</label>
                    <input type="text" name="document" placeholder="Ej. 778899"
                           class="block w-full px-4 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Correo Electrónico *</label>
                    <input type="email" name="email" placeholder="profesor@ejemplo.com"
                           class="block w-full px-4 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nombres *</label>
                    <input type="text" name="first_name" placeholder="Ej. Mario"
                           class="block w-full px-4 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Apellidos *</label>
                    <input type="text" name="last_name" placeholder="Ej. Vargas"
                           class="block w-full px-4 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Teléfono</label>
                    <input type="text" name="phone" placeholder="Ej. 3012223344"
                           class="block w-full px-4 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Contraseña Inicial *</label>
                    <input type="password" name="password" placeholder="Mínimo 4 caracteres"
                           class="block w-full px-4 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                </div>
            </div>
        </div>

        <!-- Informacion de Especialidad -->
        <div class="border-t border-slate-100 pt-5">
            <label for="specialty" class="block text-sm font-semibold text-slate-700 mb-1.5">Especialidad del Docente *</label>
            <input type="text" name="specialty" id="specialty" required placeholder="Ej. Matemáticas y Física, Ciencias Sociales"
                   class="block w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
        </div>

        <div class="pt-4 flex items-center justify-end space-x-2 border-t border-slate-100">
            <a href="/modules/teachers/index.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition text-sm">Cancelar</a>
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition text-sm shadow-md">Guardar Docente</button>
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
