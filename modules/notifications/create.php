<?php
// /modules/notifications/create.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$roles = $db->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = Auth::sanitize($_POST['title'] ?? '');
    $message = Auth::sanitize($_POST['message'] ?? '');
    $target_role_id = !empty($_POST['target_role_id']) ? intval($_POST['target_role_id']) : null;
    $u = Auth::user();

    if (!empty($title) && !empty($message)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO notifications (user_id, target_role_id, title, message)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$u['id'], $target_role_id, $title, $message]);

            header("Location: /modules/notifications/index.php");
            exit;
        } catch (PDOException $e) {
            $error = 'Error publicando anuncio: ' . $e->getMessage();
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
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Publicar Anuncio</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Escriba el comunicado y elija a quién va dirigido.</p>
        </div>
        <a href="/modules/notifications/index.php" class="text-sm font-semibold text-slate-500 hover:text-slate-800 flex items-center space-x-1">
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
        <div>
            <label for="title" class="block text-sm font-semibold text-slate-700 mb-1.5">Título del Anuncio *</label>
            <input type="text" name="title" id="title" required placeholder="Ej. Suspensión de clases por mantenimiento"
                   class="block w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
        </div>

        <div>
            <label for="target_role_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Dirigido A</label>
            <select name="target_role_id" id="target_role_id"
                    class="block w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                <option value="">Todos los usuarios (Público)</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= $r['id'] ?>">Solo <?= htmlspecialchars($r['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="message" class="block text-sm font-semibold text-slate-700 mb-1.5">Mensaje del Anuncio *</label>
            <textarea name="message" id="message" required rows="5" placeholder="Escriba aquí los detalles del anuncio..."
                      class="block w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition"></textarea>
        </div>

        <div class="pt-4 flex items-center justify-end space-x-2 border-t border-slate-100">
            <a href="/modules/notifications/index.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition text-sm">Cancelar</a>
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition text-sm shadow-md">Publicar Ahora</button>
        </div>
    </form>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
