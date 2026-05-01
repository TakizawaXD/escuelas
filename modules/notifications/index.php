<?php
// /modules/notifications/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();

$db = Database::getInstance()->getConnection();
$u = Auth::user();

// Fetch communications targeting everyone or specific role
$query = "
    SELECT n.*, u.first_name, u.last_name, r.name as role_name
    FROM notifications n
    JOIN users u ON n.user_id = u.id
    LEFT JOIN roles r ON n.target_role_id = r.id
    WHERE n.target_role_id IS NULL OR n.target_role_id = ?
";

$params = [$u['role_id']];

if (Auth::hasRole(['ADMIN', 'DIRECTOR'])) {
    // Show everything
    $query = "
        SELECT n.*, u.first_name, u.last_name, r.name as role_name
        FROM notifications n
        JOIN users u ON n.user_id = u.id
        LEFT JOIN roles r ON n.target_role_id = r.id
    ";
    $params = [];
}

$query .= " ORDER BY n.id DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$notifications = $stmt->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<!-- Header Section -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Comunicaciones</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Anuncios institucionales y mensajes internos.</p>
    </div>
    <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR'])): ?>
        <a href="/modules/notifications/create.php" class="inline-flex items-center space-x-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 transition active:scale-[0.98]">
            <i class="fa-solid fa-bullhorn text-sm"></i>
            <span>Publicar Anuncio</span>
        </a>
    <?php endif; ?>
</div>

<!-- Communications stream / list -->
<div class="max-w-3xl space-y-5">
    <?php if (empty($notifications)): ?>
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 text-center text-slate-500">
            <i class="fa-solid fa-bell-slash text-3xl text-slate-300 mb-2"></i>
            <p>No se encontraron comunicados.</p>
        </div>
    <?php else: ?>
        <?php foreach ($notifications as $notif): ?>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 space-y-3 hover:shadow-md transition">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 flex-shrink-0 border border-indigo-100">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 text-base"><?= htmlspecialchars($notif['title']) ?></h3>
                            <p class="text-xs text-slate-400 font-medium">Publicado por: <?= htmlspecialchars($notif['first_name'] . ' ' . $notif['last_name']) ?> &bull; <?= date('d M, Y h:i A', strtotime($notif['created_at'])) ?></p>
                        </div>
                    </div>
                    <?php if ($notif['target_role_id']): ?>
                        <span class="inline-block px-2.5 py-1 text-xs font-bold rounded-xl bg-amber-50 text-amber-700 border border-amber-100">
                            Para: <?= htmlspecialchars($notif['role_name']) ?>
                        </span>
                    <?php else: ?>
                        <span class="inline-block px-2.5 py-1 text-xs font-bold rounded-xl bg-teal-50 text-teal-700 border border-teal-100">
                            Para: Todos
                        </span>
                    <?php endif; ?>
                </div>
                <div class="text-sm text-slate-600 leading-relaxed pt-1">
                    <?= nl2br(htmlspecialchars($notif['message'])) ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
