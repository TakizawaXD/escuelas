<?php
// /modules/news/category.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'COMUNICACIONES'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $name = $_POST['name'] ?? '';
        $color = $_POST['color'] ?? '#4f46e5';
        
        if (!empty($name)) {
            $stmt = $db->prepare("INSERT INTO news_categories (name, color) VALUES (?, ?)");
            try {
                $stmt->execute([$name, $color]);
                $success = "Categoría añadida.";
            } catch (PDOException $e) {
                $error = "La categoría ya existe o ocurrió un error.";
            }
        }
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['category_id'] ?? null;
        if ($id) {
            $db->prepare("DELETE FROM news_categories WHERE id = ?")->execute([$id]);
            $success = "Categoría eliminada.";
        }
    }
}

$stmt = $db->query("SELECT * FROM news_categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-5xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/news/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Categorías de Noticias</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Organiza el contenido del portal de comunicaciones.</p>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-50 text-red-600 px-4 py-3 rounded-xl flex items-center space-x-3 border border-red-200">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span class="font-medium text-sm"><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-emerald-50 text-emerald-600 px-4 py-3 rounded-xl flex items-center space-x-3 border border-emerald-200">
            <i class="fa-solid fa-circle-check"></i>
            <span class="font-medium text-sm"><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Add Category Form -->
        <div class="lg:col-span-1 bg-white p-6 rounded-3xl shadow-sm border border-slate-100 h-fit">
            <h3 class="text-lg font-bold text-slate-800 mb-6">Nueva Categoría</h3>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre</label>
                    <input type="text" name="name" required placeholder="Ej: Eventos" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Color Identificador</label>
                    <input type="color" name="color" value="#4f46e5" class="w-full h-12 px-1 border border-slate-200 rounded-xl cursor-pointer">
                </div>
                <button type="submit" name="add" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white py-2.5 rounded-xl font-bold transition">
                    Crear Categoría
                </button>
            </form>
        </div>

        <!-- List Categories -->
        <div class="lg:col-span-2 bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <h3 class="text-lg font-bold text-slate-800 mb-6">Categorías Actuales</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach($categories as $cat): ?>
                    <div class="flex items-center justify-between p-4 border border-slate-200 rounded-2xl hover:bg-slate-50 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-4 h-4 rounded-full" style="background-color: <?= htmlspecialchars($cat['color']) ?>"></div>
                            <span class="font-bold text-slate-700"><?= htmlspecialchars($cat['name']) ?></span>
                        </div>
                        <form method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta categoría? Las noticias no se borrarán.');">
                            <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                            <button type="submit" name="delete" class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 hover:bg-red-50 hover:text-red-600 flex items-center justify-center transition">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($categories)): ?>
                    <div class="col-span-1 sm:col-span-2 text-center py-6 text-slate-400">
                        <p class="text-sm">No hay categorías creadas aún.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
