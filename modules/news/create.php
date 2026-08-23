<?php
// /modules/news/create.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COMUNICACIONES'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT * FROM news_categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $category_id = $_POST['category_id'] ?? null;
    $photo_url = $_POST['photo_url'] ?? '';

    if (empty($title) || empty($content)) {
        $error = "El título y el contenido son obligatorios.";
    } else {
        $stmt = $db->prepare("INSERT INTO news (title, content, photo_url, category_id) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$title, $content, $photo_url, $category_id])) {
            header("Location: /modules/news/index.php?msg=created");
            exit;
        } else {
            $error = "Error al publicar la noticia.";
        }
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-4xl mx-auto">
    <div class="flex items-center space-x-4">
        <a href="/modules/news/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Publicar Noticia</h2>
            <p class="text-slate-500 font-medium text-sm mt-1">Crea un nuevo comunicado o artículo para la comunidad.</p>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-50 text-red-600 px-4 py-3 rounded-xl flex items-center space-x-3 border border-red-200">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span class="font-medium text-sm"><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white p-8 md:p-12 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 relative overflow-hidden group">
    <!-- ambient background -->
    <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-50 rounded-full blur-3xl opacity-50 pointer-events-none transition-opacity group-hover:opacity-100"></div>
    <div class="relative z-10">
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Título de la Noticia</label>
                <input type="text" name="title" required placeholder="Ej: Inicio de matrículas 2026" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50 text-lg font-medium">
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Categoría</label>
                    <select name="category_id" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50">
                        <option value="">General</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">URL de Imagen (Opcional)</label>
                    <input type="url" name="photo_url" placeholder="https://ejemplo.com/imagen.jpg" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Contenido</label>
                <textarea name="content" rows="8" required class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-indigo-500 outline-none bg-slate-50"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white px-8 py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 transition-all flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-paper-plane mr-2"></i> Publicar Ahora
                </button>
            </div>
        </div>
</form>
    </div>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
