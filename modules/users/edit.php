<?php
// /modules/users/edit.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    header("Location: /index.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: /modules/users/index.php");
    exit;
}

$roles = $db->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role_id = intval($_POST['role_id'] ?? 0);
    $document = Auth::sanitize($_POST['document'] ?? '');
    $first_name = Auth::sanitize($_POST['first_name'] ?? '');
    $last_name = Auth::sanitize($_POST['last_name'] ?? '');
    $email = Auth::sanitize($_POST['email'] ?? '');
    $phone = Auth::sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($role_id) && !empty($document) && !empty($first_name) && !empty($last_name) && !empty($email)) {
        try {
            // Uniqueness check for document and email on other users
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE (document = ? OR email = ?) AND id <> ?");
            $stmt->execute([$document, $email, $id]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Ya existe un usuario con ese número de documento o correo electrónico.';
            } else {
                if (!empty($password)) {
                    $pwdHash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("
                        UPDATE users 
                        SET role_id = ?, document = ?, first_name = ?, last_name = ?, email = ?, phone = ?, password = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([$role_id, $document, $first_name, $last_name, $email, $phone, $pwdHash, $id]);
                } else {
                    $stmt = $db->prepare("
                        UPDATE users 
                        SET role_id = ?, document = ?, first_name = ?, last_name = ?, email = ?, phone = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([$role_id, $document, $first_name, $last_name, $email, $phone, $id]);
                }
                
                header("Location: /modules/users/index.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Error al actualizar: ' . $e->getMessage();
        }
    } else {
        $error = 'Por favor complete todos los campos obligatorios.';
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<!-- Form -->
<div class="max-w-2xl mx-auto bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Editar Usuario</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Realice los cambios necesarios del usuario.</p>
        </div>
        <a href="/modules/users/index.php" class="text-sm font-semibold text-slate-500 hover:text-slate-800 flex items-center space-x-1">
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="document" class="block text-sm font-semibold text-slate-700 mb-1.5">N° de Cédula / Documento *</label>
                <input type="text" name="document" id="document" required value="<?= htmlspecialchars($user['document']) ?>"
                       class="block w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
            </div>

            <div>
                <label for="role_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Rol *</label>
                <select name="role_id" id="role_id" required
                        class="block w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                    <option value="">Seleccione un rol...</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= $r['id'] == $user['role_id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="first_name" class="block text-sm font-semibold text-slate-700 mb-1.5">Nombres *</label>
                <input type="text" name="first_name" id="first_name" required value="<?= htmlspecialchars($user['first_name']) ?>"
                       class="block w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
            </div>

            <div>
                <label for="last_name" class="block text-sm font-semibold text-slate-700 mb-1.5">Apellidos *</label>
                <input type="text" name="last_name" id="last_name" required value="<?= htmlspecialchars($user['last_name']) ?>"
                       class="block w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Correo Electrónico *</label>
                <input type="email" name="email" id="email" required value="<?= htmlspecialchars($user['email']) ?>"
                       class="block w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
            </div>

            <div>
                <label for="phone" class="block text-sm font-semibold text-slate-700 mb-1.5">Teléfono</label>
                <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($user['phone']) ?>"
                       class="block w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
            </div>

            <div class="md:col-span-2">
                <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Contraseña (Dejar en blanco para no modificar)</label>
                <input type="password" name="password" id="password" minlength="4"
                       class="block w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
            </div>
        </div>

        <div class="pt-4 flex items-center justify-end space-x-2 border-t border-slate-100">
            <a href="/modules/users/index.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition text-sm">Cancelar</a>
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition text-sm shadow-md">Actualizar Usuario</button>
        </div>
    </form>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
