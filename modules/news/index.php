<?php
// /modules/news/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
$u = Auth::user();
$db = Database::getInstance()->getConnection();

$error = '';
$success = '';

// Handle news posting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    $title = Auth::sanitize($_POST['title'] ?? '');
    $content = Auth::sanitize($_POST['content'] ?? '');
    $photo_url = Auth::sanitize($_POST['photo_url'] ?? '');

    if (!empty($title) && !empty($content)) {
        try {
            $stmt = $db->prepare("INSERT INTO news (title, content, photo_url) VALUES (?, ?, ?)");
            $stmt->execute([$title, $content, $photo_url ?: 'https://images.unsplash.com/photo-1546410531-bb4caa6b424d?auto=format&fit=crop&w=800&q=80']);
            $success = '¡Artículo publicado con éxito!';
        } catch (PDOException $e) {
            $error = 'Error de base de datos: ' . $e->getMessage();
        }
    } else {
        $error = 'Por favor completa título y contenido.';
    }
}

// Fetch all news
try {
    $newsList = $db->query("SELECT * FROM news ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {
    $newsList = [];
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-8 animate-fade-in">
    <!-- Header Banner -->
    <div class="relative overflow-hidden bg-gradient-to-r from-teal-600 via-emerald-600 to-teal-700 p-8 md:p-10 rounded-3xl text-white shadow-xl shadow-emerald-500/20">
        <div class="relative z-10">
            <span class="text-xs text-emerald-100 font-bold uppercase tracking-wider bg-emerald-500/20 backdrop-blur-md px-3 py-1 rounded-full border border-emerald-400/20">Portal de Información</span>
            <h2 class="text-3xl font-extrabold tracking-tight md:text-4xl mt-3">Rectoría / Noticias & Blog</h2>
            <p class="mt-2 text-emerald-50 font-medium text-base md:text-lg max-w-xl">
                Espacio oficial del Director/Rector para comunicados de alto impacto, novedades del campus y artículos de interés institucional.
            </p>
        </div>
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="p-4 rounded-xl bg-red-50 text-red-700 text-sm border border-red-200/60 flex items-start space-x-3">
            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
            <span class="font-medium"><?= $error ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="p-4 rounded-xl bg-emerald-50 text-emerald-700 text-sm border border-emerald-200/60 flex items-start space-x-3 animate-fade-in">
            <i class="fa-solid fa-circle-check mt-0.5"></i>
            <span class="font-medium"><?= $success ?></span>
        </div>
    <?php endif; ?>

    <!-- If Admin/Director, display posting panel -->
    <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR'])): ?>
        <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-slate-100">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 text-lg">
                    <i class="fa-solid fa-pen-nib"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-lg">Publicar Nuevo Artículo</h3>
                    <p class="text-xs text-slate-500 font-medium">Crea contenido para el blog institucional</p>
                </div>
            </div>

            <form method="POST" action="" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Título del Artículo</label>
                        <input type="text" name="title" required
                               class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm font-medium transition"
                               placeholder="Ej. Grandes logros en las Pruebas de Calidad">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">URL de la Imagen / Foto</label>
                        <input type="url" name="photo_url"
                               class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm font-medium transition"
                               placeholder="Ej. https://images.unsplash.com/photo...">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Contenido / Texto Largo</label>
                    <textarea name="content" required rows="6"
                              class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm font-medium transition"
                              placeholder="Escribe aquí el contenido extenso del comunicado..."></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold tracking-wide transition flex items-center space-x-2 shadow-lg shadow-emerald-500/20">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Publicar Artículo</span>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Blog Posts Grid / News Feed -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <?php if (empty($newsList)): ?>
            <div class="md:col-span-2 bg-white p-12 text-center rounded-3xl shadow-sm border border-slate-100">
                <div class="text-slate-300 text-5xl mb-3">
                    <i class="fa-solid fa-newspaper"></i>
                </div>
                <h4 class="text-slate-800 font-bold text-lg">Aún no hay publicaciones</h4>
                <p class="text-slate-500 text-sm max-w-sm mx-auto mt-1">Vuelve pronto para leer los comunicados y blogs oficiales.</p>
            </div>
        <?php else: ?>
            <?php foreach ($newsList as $news): ?>
                <div class="bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-md border border-slate-100 transition flex flex-col h-full">
                    <?php if (!empty($news['photo_url'])): ?>
                        <div class="h-56 w-full overflow-hidden relative">
                            <img src="<?= htmlspecialchars($news['photo_url']) ?>" alt="News Banner" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 via-transparent to-transparent pointer-events-none"></div>
                        </div>
                    <?php endif; ?>
                    <div class="p-6 md:p-8 flex flex-col flex-1 justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-xs font-semibold text-slate-400">
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 rounded-lg border border-emerald-100/60">Noticias</span>
                                <span><?= date('d M, Y', strtotime($news['created_at'])) ?></span>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 leading-snug"><?= htmlspecialchars($news['title']) ?></h3>
                            <p class="text-slate-600 text-sm leading-relaxed whitespace-pre-line"><?= htmlspecialchars($news['content']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
