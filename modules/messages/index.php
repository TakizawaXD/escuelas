<?php
// /modules/messages/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();

$db = Database::getInstance()->getConnection();
$user_id = Auth::user()['id'];
$error = '';
$success = '';

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    $receiver_id = (int)$_POST['receiver_id'];
    $subject = Auth::sanitize($_POST['subject'] ?? '');
    $body = Auth::sanitize($_POST['body'] ?? '');
    
    if ($receiver_id && $subject && $body) {
        $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, subject, body) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $receiver_id, $subject, $body])) {
            $success = 'Mensaje enviado correctamente.';
        } else {
            $error = 'Error al enviar el mensaje.';
        }
    }
}

// Mark as read
if (isset($_GET['read_id'])) {
    $read_id = (int)$_GET['read_id'];
    $stmt = $db->prepare("UPDATE messages SET is_read = 1 WHERE id = ? AND receiver_id = ?");
    $stmt->execute([$read_id, $user_id]);
    header("Location: /modules/messages/index.php");
    exit;
}

// Get all users for dropdown
$users = $db->query("SELECT id, first_name, last_name, role_id FROM users WHERE id != $user_id ORDER BY first_name ASC")->fetchAll();

// Get Inbox
$inboxStmt = $db->prepare("
    SELECT m.*, u.first_name, u.last_name 
    FROM messages m 
    JOIN users u ON m.sender_id = u.id 
    WHERE m.receiver_id = ? 
    ORDER BY m.created_at DESC
");
$inboxStmt->execute([$user_id]);
$inbox = $inboxStmt->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Bandeja de Mensajes</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Comunicación interna directa entre usuarios del colegio.</p>
    </div>
</div>

<?php if ($success): ?>
    <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 font-medium text-sm border border-emerald-100 flex items-center space-x-2">
        <i class="fa-solid fa-circle-check"></i>
        <span><?= htmlspecialchars($success) ?></span>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-rose-50 text-rose-600 p-4 rounded-xl mb-6 font-medium text-sm border border-rose-100 flex items-center space-x-2">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Compose Column -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 h-fit">
        <h3 class="font-bold text-slate-800 text-sm uppercase tracking-widest mb-4"><i class="fa-solid fa-paper-plane text-indigo-500 mr-2"></i> Redactar Mensaje</h3>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="send">
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Destinatario</label>
                <select name="receiver_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
                    <option value="">Seleccionar usuario...</option>
                    <?php foreach($users as $usr): ?>
                        <option value="<?= $usr['id'] ?>"><?= htmlspecialchars($usr['first_name'] . ' ' . $usr['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Asunto</label>
                <input type="text" name="subject" required placeholder="Motivo del mensaje"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Mensaje</label>
                <textarea name="body" required rows="4" placeholder="Escribe aquí tu mensaje..."
                       class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition resize-none"></textarea>
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl font-bold transition shadow-md flex justify-center items-center space-x-2">
                <span>Enviar Mensaje</span>
            </button>
        </form>
    </div>

    <!-- Inbox Column -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden h-full">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-sm uppercase tracking-widest"><i class="fa-solid fa-inbox text-slate-500 mr-2"></i> Recibidos</h3>
            </div>
            <div class="divide-y divide-slate-100">
                <?php if (empty($inbox)): ?>
                    <div class="p-10 text-center text-slate-400">
                        <i class="fa-regular fa-envelope-open text-4xl mb-3 text-slate-200"></i>
                        <p>No tienes mensajes en tu bandeja.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($inbox as $msg): ?>
                        <div class="p-6 transition hover:bg-slate-50 <?= $msg['is_read'] ? 'opacity-70' : 'bg-indigo-50/30' ?>">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm">
                                        <?= strtoupper(substr($msg['first_name'], 0, 1) . substr($msg['last_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 <?= !$msg['is_read'] ? 'text-indigo-900' : '' ?>"><?= htmlspecialchars($msg['subject']) ?></h4>
                                        <span class="text-xs font-medium text-slate-500">De: <?= htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']) ?></span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end space-y-2">
                                    <span class="text-xs font-bold text-slate-400"><?= date('d M, H:i', strtotime($msg['created_at'])) ?></span>
                                    <?php if (!$msg['is_read']): ?>
                                        <a href="?read_id=<?= $msg['id'] ?>" class="text-[10px] uppercase font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-2 py-1 rounded transition">
                                            Marcar Leído
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p class="text-sm text-slate-600 mt-3 pl-13 leading-relaxed">
                                <?= nl2br(htmlspecialchars($msg['body'])) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
