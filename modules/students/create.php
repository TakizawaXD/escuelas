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
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db->beginTransaction();
    try {
        // --- 1. HANDLE GUARDIAN (ACUDIENTE) ---
        $parent_type = $_POST['parent_type'] ?? 'none';
        $parent_user_id = null;

        if ($parent_type === 'existing') {
            $parent_user_id = !empty($_POST['parent_user_id']) ? intval($_POST['parent_user_id']) : null;
        } elseif ($parent_type === 'new') {
            $p_doc = Auth::sanitize($_POST['p_document'] ?? '');
            $p_fn = Auth::sanitize($_POST['p_first_name'] ?? '');
            $p_ln = Auth::sanitize($_POST['p_last_name'] ?? '');
            $p_email = Auth::sanitize($_POST['p_email'] ?? '');
            $p_phone = Auth::sanitize($_POST['p_phone'] ?? '');

            if (!$p_doc || !$p_fn || !$p_email) throw new Exception("Faltan datos obligatorios del nuevo acudiente.");
            
            // Check if parent email/doc exists
            $stmt = $db->prepare("SELECT id FROM users WHERE document = ? OR email = ?");
            $stmt->execute([$p_doc, $p_email]);
            if ($stmt->fetch()) {
                throw new Exception("El documento o correo del acudiente ya está registrado.");
            }

            // Create Parent User (Role 6)
            $pwdHash = password_hash($p_doc, PASSWORD_BCRYPT); // Default password is their document
            $stmt = $db->prepare("INSERT INTO users (role_id, document, first_name, last_name, email, phone, password, status) VALUES (6, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$p_doc, $p_fn, $p_ln, $p_email, $p_phone, $pwdHash]);
            $parent_user_id = $db->lastInsertId();
        }

        // --- 2. HANDLE STUDENT USER ---
        $create_type = $_POST['create_type'] ?? 'existing';
        $user_id = null;

        if ($create_type === 'new') {
            $document = Auth::sanitize($_POST['document'] ?? '');
            $first_name = Auth::sanitize($_POST['first_name'] ?? '');
            $last_name = Auth::sanitize($_POST['last_name'] ?? '');
            $email = Auth::sanitize($_POST['email'] ?? '');
            $phone = Auth::sanitize($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';

            if (!$document || !$first_name || !$email || !$password) throw new Exception("Faltan datos obligatorios del nuevo estudiante.");
            
            $stmt = $db->prepare("SELECT id FROM users WHERE document = ? OR email = ?");
            $stmt->execute([$document, $email]);
            if ($stmt->fetch()) throw new Exception("El documento o correo del estudiante ya existe.");

            $pwdHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (role_id, document, first_name, last_name, email, phone, password, status) VALUES (5, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$document, $first_name, $last_name, $email, $phone, $pwdHash]);
            $user_id = $db->lastInsertId();
        } else {
            $user_id = intval($_POST['user_id'] ?? 0);
            if (empty($user_id)) throw new Exception("Debe seleccionar un usuario estudiante existente.");
        }

        // --- 3. CREATE STUDENT RECORD ---
        $course_id = intval($_POST['course_id'] ?? 0);
        $birth_date = Auth::sanitize($_POST['birth_date'] ?? '');
        $address = Auth::sanitize($_POST['address'] ?? '');
        $photo_url = Auth::sanitize($_POST['photo_url'] ?? '');
        $grade = Auth::sanitize($_POST['grade'] ?? '');
        $gpa = floatval($_POST['gpa'] ?? 0.00);
        $scalability = Auth::sanitize($_POST['scalability'] ?? '');

        if (empty($course_id) || empty($birth_date)) throw new Exception("El curso y la fecha de nacimiento son obligatorios.");

        $stmt = $db->prepare("
            INSERT INTO students (user_id, course_id, parent_user_id, birth_date, address, photo_url, grade, gpa, scalability)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $course_id, $parent_user_id, $birth_date, $address, $photo_url, $grade, $gpa, $scalability]);

        $db->commit();
        header("Location: /modules/students/index.php");
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="max-w-5xl mx-auto space-y-6 animate-fade-in pb-10">
    <div class="flex items-center justify-between mb-2">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Matricular Estudiante</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Sigue los 3 pasos para completar el registro en el ERP.</p>
        </div>
        <a href="/modules/students/index.php" class="text-sm font-bold text-slate-500 hover:text-indigo-600 bg-white px-4 py-2 rounded-xl shadow-sm border border-slate-200 transition flex items-center space-x-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver</span>
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="p-4 rounded-xl bg-red-50 text-red-700 text-sm border border-red-200/60 flex items-start space-x-3 shadow-sm">
            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            <span class="font-bold"><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        
        <!-- SECTION 1: STUDENT INFO -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="bg-indigo-600 px-6 py-4 flex items-center space-x-3">
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white font-bold">1</div>
                <h3 class="text-white font-bold tracking-wide uppercase text-sm">Cuenta del Estudiante</h3>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="bg-slate-50 p-2 rounded-xl flex items-center justify-around border border-slate-100 max-w-md mx-auto">
                    <label class="flex flex-1 items-center justify-center space-x-2 cursor-pointer py-2 px-4 rounded-lg hover:bg-white transition" onclick="toggleStudentPanel('existing')">
                        <input type="radio" name="create_type" value="existing" checked class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-bold text-slate-700">Usuario Existente</span>
                    </label>
                    <label class="flex flex-1 items-center justify-center space-x-2 cursor-pointer py-2 px-4 rounded-lg hover:bg-white transition" onclick="toggleStudentPanel('new')">
                        <input type="radio" name="create_type" value="new" class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-bold text-slate-700">Nuevo Usuario</span>
                    </label>
                </div>

                <div id="student-existing" class="max-w-md mx-auto">
                    <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Buscar Usuario (Rol: Estudiante sin matrícula) *</label>
                    <select name="user_id" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 outline-none text-sm font-medium transition">
                        <option value="">Seleccione...</option>
                        <?php foreach ($studentUsers as $usr): ?>
                            <option value="<?= $usr['id'] ?>"><?= htmlspecialchars($usr['first_name'] . ' ' . $usr['last_name'] . ' (' . $usr['document'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="student-new" class="hidden grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Documento / Cédula *</label>
                        <input type="text" name="document" placeholder="Ej. 11223344" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Correo Electrónico *</label>
                        <input type="email" name="email" placeholder="estudiante@ejemplo.com" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nombres *</label>
                        <input type="text" name="first_name" placeholder="Ej. Carlos" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Apellidos *</label>
                        <input type="text" name="last_name" placeholder="Ej. Ruiz" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Teléfono</label>
                        <input type="text" name="phone" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Contraseña Inicial *</label>
                        <input type="password" name="password" placeholder="Min. 4 caracteres" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 text-sm font-medium transition">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: ACADEMIC INFO -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="bg-emerald-500 px-6 py-4 flex items-center space-x-3">
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white font-bold">2</div>
                <h3 class="text-white font-bold tracking-wide uppercase text-sm">Asignación Académica</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Curso / Año Lectivo *</label>
                    <select name="course_id" required class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-emerald-500 outline-none text-sm font-medium transition">
                        <option value="">Seleccione el curso base...</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha de Nacimiento *</label>
                    <input type="date" name="birth_date" required class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-emerald-500 text-sm font-medium transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">URL Foto de Perfil (Opcional)</label>
                    <input type="url" name="photo_url" placeholder="https://..." class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-emerald-500 text-sm font-medium transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Grado / Nomenclatura Interna</label>
                    <input type="text" name="grade" placeholder="Ej. A, B, Avanzado..." class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-emerald-500 text-sm font-medium transition">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Escalabilidad (Notas de traslado / Admisión)</label>
                    <textarea name="scalability" rows="2" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-emerald-500 text-sm font-medium transition"></textarea>
                </div>
            </div>
        </div>

        <!-- SECTION 3: GUARDIAN INFO -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="bg-rose-500 px-6 py-4 flex items-center space-x-3">
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white font-bold">3</div>
                <h3 class="text-white font-bold tracking-wide uppercase text-sm">Información del Acudiente / Familia</h3>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="bg-slate-50 p-2 rounded-xl flex items-center justify-around border border-slate-100 w-full md:w-3/4 mx-auto">
                    <label class="flex flex-1 items-center justify-center space-x-2 cursor-pointer py-2 px-2 rounded-lg hover:bg-white transition" onclick="toggleParentPanel('none')">
                        <input type="radio" name="parent_type" value="none" checked class="text-rose-500 focus:ring-rose-500">
                        <span class="text-xs md:text-sm font-bold text-slate-700">Sin Acudiente</span>
                    </label>
                    <label class="flex flex-1 items-center justify-center space-x-2 cursor-pointer py-2 px-2 rounded-lg hover:bg-white transition" onclick="toggleParentPanel('existing')">
                        <input type="radio" name="parent_type" value="existing" class="text-rose-500 focus:ring-rose-500">
                        <span class="text-xs md:text-sm font-bold text-slate-700">Buscar Existente</span>
                    </label>
                    <label class="flex flex-1 items-center justify-center space-x-2 cursor-pointer py-2 px-2 rounded-lg hover:bg-white transition" onclick="toggleParentPanel('new')">
                        <input type="radio" name="parent_type" value="new" class="text-rose-500 focus:ring-rose-500">
                        <span class="text-xs md:text-sm font-bold text-slate-700">Registrar Nuevo</span>
                    </label>
                </div>

                <div id="parent-existing" class="hidden max-w-md mx-auto">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Buscar Acudiente Registrado</label>
                    <select name="parent_user_id" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-rose-500 outline-none text-sm font-medium transition">
                        <option value="">Seleccione...</option>
                        <?php foreach ($parentUsers as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name'] . ' (' . $p['document'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="parent-new" class="hidden grid grid-cols-1 md:grid-cols-2 gap-5 border border-rose-100 bg-rose-50/20 p-5 rounded-2xl relative">
                    <div class="absolute -top-3 left-4 bg-rose-500 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full shadow-sm">Creación Express</div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Documento / ID *</label>
                        <input type="text" name="p_document" placeholder="Documento de identidad" class="block w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-xl focus:border-rose-500 focus:ring-rose-500 text-sm font-medium transition">
                        <p class="text-[10px] text-slate-400 mt-1">Este será también su contraseña por defecto.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Correo Electrónico *</label>
                        <input type="email" name="p_email" placeholder="correo@ejemplo.com" class="block w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-xl focus:border-rose-500 focus:ring-rose-500 text-sm font-medium transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nombres del Padre/Madre *</label>
                        <input type="text" name="p_first_name" class="block w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-xl focus:border-rose-500 focus:ring-rose-500 text-sm font-medium transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Apellidos</label>
                        <input type="text" name="p_last_name" class="block w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-xl focus:border-rose-500 focus:ring-rose-500 text-sm font-medium transition">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Teléfono Móvil</label>
                        <input type="text" name="p_phone" class="block w-full px-4 py-3 bg-white border border-slate-200 text-slate-800 rounded-xl focus:border-rose-500 focus:ring-rose-500 text-sm font-medium transition">
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-6 flex justify-end">
            <button type="submit" class="w-full md:w-auto px-10 py-4 bg-slate-900 hover:bg-black text-white font-extrabold rounded-2xl transition text-base shadow-xl shadow-slate-900/20 hover:-translate-y-1 active:translate-y-0 flex items-center justify-center space-x-3">
                <i class="fa-solid fa-user-check"></i>
                <span>Finalizar Matrícula</span>
            </button>
        </div>
    </form>
</div>

<script>
function toggleStudentPanel(type) {
    if (type === 'new') {
        document.getElementById('student-existing').classList.add('hidden');
        document.getElementById('student-new').classList.remove('hidden');
    } else {
        document.getElementById('student-existing').classList.remove('hidden');
        document.getElementById('student-new').classList.add('hidden');
    }
}

function toggleParentPanel(type) {
    const existing = document.getElementById('parent-existing');
    const createNew = document.getElementById('parent-new');
    
    if (type === 'existing') {
        existing.classList.remove('hidden');
        createNew.classList.add('hidden');
    } else if (type === 'new') {
        existing.classList.add('hidden');
        createNew.classList.remove('hidden');
    } else {
        existing.classList.add('hidden');
        createNew.classList.add('hidden');
    }
}
</script>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
