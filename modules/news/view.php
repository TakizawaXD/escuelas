<?php
// /modules/news/view.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /modules/news/index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("
    SELECT n.*, c.name as category_name, c.color as category_color 
    FROM news n 
    LEFT JOIN news_categories c ON n.category_id = c.id 
    WHERE n.id = ?
");
$stmt->execute([$id]);
$news = $stmt->fetch();

if (!$news) {
    header("Location: /modules/news/index.php");
    exit;
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="space-y-6 animate-fade-in max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <a href="/modules/news/index.php" class="p-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 hover:text-indigo-600 transition shadow-sm inline-flex items-center">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        
        <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COMUNICACIONES'])): ?>
            <div class="flex space-x-2">
                <a href="/modules/news/edit.php?id=<?= $news['id'] ?>" class="bg-indigo-50 text-indigo-600 hover:bg-indigo-100 px-4 py-2 rounded-xl text-sm font-bold transition">
                    <i class="fa-solid fa-pen mr-1"></i> Editar
                </a>
                <a href="/modules/news/delete.php?id=<?= $news['id'] ?>" onclick="return confirm('¿Eliminar noticia?');" class="bg-red-50 text-red-600 hover:bg-red-100 px-4 py-2 rounded-xl text-sm font-bold transition">
                    <i class="fa-solid fa-trash mr-1"></i> Eliminar
                </a>
            </div>
        <?php endif; ?>
    </div>

    <article class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <?php if (!empty($news['photo_url'])): ?>
            <div class="w-full h-80 bg-slate-100">
                <img src="<?= htmlspecialchars($news['photo_url']) ?>" alt="Portada" class="w-full h-full object-cover">
            </div>
        <?php endif; ?>
        
        <div class="p-8 md:p-12">
            <div class="flex items-center space-x-3 mb-4">
                <?php if ($news['category_name']): ?>
                    <span class="px-3 py-1 text-xs font-bold rounded-lg" style="background-color: <?= $news['category_color'] ?>20; color: <?= $news['category_color'] ?>; border: 1px solid <?= $news['category_color'] ?>30;">
                        <?= htmlspecialchars($news['category_name']) ?>
                    </span>
                <?php endif; ?>
                <span class="text-sm font-medium text-slate-400">
                    <i class="fa-regular fa-clock mr-1"></i> <?= date('d M Y, h:i A', strtotime($news['created_at'])) ?>
                </span>
            </div>

            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight mb-8 leading-tight">
                <?= htmlspecialchars($news['title']) ?>
            </h1>

            <div class="prose prose-slate prose-lg max-w-none text-slate-600 leading-relaxed">
                <?= nl2br(htmlspecialchars($news['content'])) ?>
            </div>
        </div>
    </article>
</div>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
