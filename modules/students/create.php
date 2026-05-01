<?php
// /modules/students/create.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();

$courses = $db->query("SELECT * FROM courses ORDER BY name ASC")->fetchAll();
$studentUsers = $db->query("
    SELECT u.* FROM users u 
    LEFT JOIN students s ON u.id = s.user_id 
    WHERE u.role_id = 5 AND s.id IS NULL
    ORDER BY u.first_name ASC
")->fetchAll();
$parentUsers = $db->query("SELECT * FROM users WHERE role_id = 6 ORDER BY first_name ASC")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $create_type = $_POST['create_type'] ?? 'existing';
    $course_id = intval($_POST['course_id'] ?? 0);
    $parent_user_id = !empty($_POST['parent_user_id']) ? intval($_POST['parent_user_id']) : null;
    $birth_date = Auth::sanitize($_POST['birth_date'] ?? '');
    $address = Auth::sanitize($_POST['address'] ?? '');
    $photo_url = Auth::sanitize($_POST['photo_url'] ?? '');
    $grade = Auth::sanitize($_POST['grade'] ?? '');
    $gpa = floatval($_POST['gpa'] ?? 0.00);
    $scalability = Auth::sanitize($_POST['scalability'] ?? '');

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
                $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE document = ? OR email = ?");
                $stmt->execute([$document, $email]);
                if ($stmt->fetchColumn() > 0) {
                    $error = 'Ya existe un usuario con ese número de documento o correo electrónico.';
                } else {
                    $pwdHash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("
                        INSERT INTO users (role_id, document, first_name, last_name, email, phone, password, status) 
                        VALUES (5, ?, ?, ?, ?, ?, ?, 1)
                    ");
                    $stmt->execute([$document, $first_name, $last_name, $email, $phone, $pwdHash]);
                    $user_id = $db->lastInsertId();
                }
            } catch (PDOException $e) {
                $error = 'Error creando el perfil de usuario: ' . $e->getMessage();
            }
        } else {
            $error = 'Por favor llene todos los campos del nuevo usuario.';
        }
    } else {
        $user_id = intval($_POST['user_id'] ?? 0);
        if (empty($user_id)) {
            $error = 'Debe seleccionar un usuario válido.';
        }
    }

    if (empty($error) && !empty($user_id) && !empty($course_id) && !empty($birth_date)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO students (user_id, course_id, parent_user_id, birth_date, address, photo_url, grade, gpa, scalability)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $course_id, $parent_user_id, $birth_date, $address, $photo_url, $grade, $gpa, $scalability]);

            header("Location: /modules/students/index.php");
            exit;
        } catch (PDOException $e) {
            $error = 'Error matriculando al estudiante: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="max-w-4xl mx-auto bg-white rounded-3xl shadow-sm border border-slate-100 p-8 animate-fade-in">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Matricular Estudiante</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Completa el registro y los datos de rendimiento del estudiante.</p>
        </div>
        <a href="/modules/students/index.php" class="text-sm font-semibold text-slate-500 hover:text-slate-800 flex items-center space-x-1">
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
        <!-- Panel de Creación -->
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

        <!-- Panel Existente -->
        <div id="panel-existing" class="space-y-4">
            <div>
                <label for="user_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Usuario del Estudiante *</label>
                <select name="user_id" id="user_id"
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 outline-none text-sm font-medium transition">
                    <option value="">Seleccione un usuario...</option>
                    <?php foreach ($studentUsers as $usr): ?>
                        <option value="<?= $usr['id'] ?>"><?= htmlspecialchars($usr['first_name'] . ' ' . $usr['last_name'] . ' (' . $usr['document'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Panel Nuevo -->
        <div id="panel-new" class="hidden space-y-4 border p-4 rounded-2xl bg-slate-50/40 border-slate-200/60">
            <p class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-2">Nueva Cuenta de Estudiante</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Documento / Cédula *</label>
                    <input type="text" name="document" placeholder="Ej. 11223344"
                           class="block w-full px-4 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Correo Electrónico *</label>
                    <input type="email" name="email" placeholder="estudiante@ejemplo.com"
                           class="block w-full px-4 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nombres *</label>
                    <input type="text" name="first_name" placeholder="Ej. Carlos"
                           class="block w-full px-4 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Apellidos *</label>
                    <input type="text" name="last_name" placeholder="Ej. Ruiz"
                           class="block w-full px-4 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Teléfono</label>
                    <input type="text" name="phone" placeholder="Ej. 3001234567"
                           class="block w-full px-4 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Contraseña Inicial *</label>
                    <input type="password" name="password" placeholder="Mínimo 4 caracteres"
                           class="block w-full px-4 py-2.5 bg-white border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                </div>
            </div>
        </div>

        <!-- Matrícula Académica Ampliada -->
        <div class="border-t border-slate-100 pt-5 space-y-4">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Perfil Completo del Estudiante</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="course_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Curso asignado *</label>
                    <select name="course_id" id="course_id" required
                            class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                        <option value="">Seleccione un curso...</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="parent_user_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Acudiente / Padre</label>
                    <select name="parent_user_id" id="parent_user_id"
                            class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                        <option value="">No asignar...</option>
                        <?php foreach ($parentUsers as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name'] . ' (' . $p['document'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="birth_date" class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha de Nacimiento *</label>
                    <input type="date" name="birth_date" id="birth_date" required
                           class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                </div>

                <div>
                    <label for="photo_url" class="block text-sm font-semibold text-slate-700 mb-1.5">URL de Foto de Perfil</label>
                    <input type="url" name="photo_url" id="photo_url" placeholder="https://images.unsplash.com/..."
                           class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                </div>

                <div>
                    <label for="grade" class="block text-sm font-semibold text-slate-700 mb-1.5">Grado / Nivel</label>
                    <input type="text" name="grade" id="grade" placeholder="Ej. 10° Grado"
                           class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                </div>

                <div>
                    <label for="gpa" class="block text-sm font-semibold text-slate-700 mb-1.5">Promedio de Notas (GPA)</label>
                    <input type="number" step="0.01" name="gpa" id="gpa" placeholder="Ej. 4.50"
                           class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                </div>

                <div class="md:col-span-2">
                    <label for="scalability" class="block text-sm font-semibold text-slate-700 mb-1.5">Escalabilidad / Reporte de Progreso Académico</label>
                    <textarea name="scalability" id="scalability" placeholder="Anotaciones sobre metas, fortalezas y rendimiento a futuro del estudiante..." rows="3"
                              class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition"></textarea>
                </div>
            </div>
        </div>

        <div class="pt-4 flex items-center justify-end space-x-2 border-t border-slate-100">
            <a href="/modules/students/index.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition text-sm">Cancelar</a>
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition text-sm shadow-md">Guardar Matrícula</button>
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
