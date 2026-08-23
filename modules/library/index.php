<?php
// /modules/library/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();

$db = Database::getInstance()->getConnection();
$search = Auth::sanitize($_GET['search'] ?? '');
$success = '';
$error = '';
$userId = Auth::user()['id'];

// Handle loan request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'loan' && Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
        $book_id = intval($_POST['book_id']);
        $loan_user_id = intval($_POST['loan_user_id']);
        $due_date = Auth::sanitize($_POST['due_date'] ?? '');

        // Check available copies
        $bStmt = $db->prepare("SELECT available_copies FROM library_books WHERE id = ?");
        $bStmt->execute([$book_id]);
        $bk = $bStmt->fetch();

        if ($bk && $bk['available_copies'] > 0 && $loan_user_id && $due_date) {
            $lStmt = $db->prepare("INSERT INTO library_loans (book_id, user_id, loan_date, due_date, status) VALUES (?, ?, date('now'), ?, 'Activo')");
            if ($lStmt->execute([$book_id, $loan_user_id, $due_date])) {
                $db->prepare("UPDATE library_books SET available_copies = available_copies - 1 WHERE id = ?")->execute([$book_id]);
                $success = 'Préstamo registrado exitosamente.';
            }
        } else {
            $error = 'No hay copias disponibles o datos incompletos.';
        }
    }

    if ($_POST['action'] === 'return' && Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
        $loan_id = intval($_POST['loan_id']);
        $rStmt = $db->prepare("SELECT book_id FROM library_loans WHERE id = ? AND status = 'Activo'");
        $rStmt->execute([$loan_id]);
        $loan = $rStmt->fetch();
        if ($loan) {
            $db->prepare("UPDATE library_loans SET status = 'Devuelto', return_date = date('now') WHERE id = ?")->execute([$loan_id]);
            $db->prepare("UPDATE library_books SET available_copies = available_copies + 1 WHERE id = ?")->execute([$loan['book_id']]);
            $success = 'Libro devuelto correctamente.';
        }
    }
}

$query = "SELECT * FROM library_books WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR author LIKE ? OR isbn LIKE ?)";
    array_push($params, "%$search%", "%$search%", "%$search%");
}

$query .= " ORDER BY title ASC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$books = $stmt->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<!-- Header Section -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Biblioteca Digital y Física</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Explora nuestro catálogo, previsualiza PDFs y gestiona préstamos.</p>
    </div>
    <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])): ?>
    <a href="/modules/library/create.php" class="inline-flex items-center space-x-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 transition active:scale-[0.98]">
        <i class="fa-solid fa-plus text-sm"></i>
        <span>Agregar Libro</span>
    </a>
    <?php endif; ?>
</div>

<?php if ($success): ?>
    <div class="bg-emerald-50 text-emerald-600 p-4 rounded-2xl mb-5 font-bold text-sm border border-emerald-200 flex items-center space-x-3">
        <i class="fa-solid fa-circle-check text-lg"></i><span><?= htmlspecialchars($success) ?></span>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-rose-50 text-rose-600 p-4 rounded-2xl mb-5 font-bold text-sm border border-rose-200 flex items-center space-x-3">
        <i class="fa-solid fa-circle-exclamation text-lg"></i><span><?= htmlspecialchars($error) ?></span>
    </div>
<?php endif; ?>

<?php
// Show active loans panel for admins
if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])):
    $activeLoans = $db->query("
        SELECT ll.*, lb.title, u.first_name, u.last_name
        FROM library_loans ll
        JOIN library_books lb ON ll.book_id = lb.id
        JOIN users u ON ll.user_id = u.id
        WHERE ll.status = 'Activo'
        ORDER BY ll.due_date ASC
    ")->fetchAll();
    $allUsers = $db->query("SELECT id, first_name, last_name FROM users ORDER BY first_name ASC")->fetchAll();
?>
<?php if (!empty($activeLoans)): ?>
<div class="bg-white rounded-3xl shadow-sm border border-amber-100 overflow-hidden mb-6">
    <div class="flex items-center justify-between px-6 py-4 border-b border-amber-100 bg-amber-50/50">
        <h3 class="font-extrabold text-amber-800 text-sm flex items-center space-x-2">
            <i class="fa-solid fa-book-bookmark text-amber-500"></i>
            <span>Préstamos Activos (<?= count($activeLoans) ?>)</span>
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-xs font-extrabold uppercase tracking-widest text-slate-400 bg-slate-50/50">
                <tr>
                    <th class="px-5 py-3 text-left">Libro</th>
                    <th class="px-5 py-3 text-left">Usuario</th>
                    <th class="px-5 py-3 text-left">Fecha Límite</th>
                    <th class="px-5 py-3 text-center">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php foreach ($activeLoans as $loan): ?>
                    <?php $overdue = strtotime($loan['due_date']) < time(); ?>
                    <tr class="hover:bg-slate-50/40 transition">
                        <td class="px-5 py-3 font-bold text-slate-800"><?= htmlspecialchars($loan['title']) ?></td>
                        <td class="px-5 py-3 text-slate-600"><?= htmlspecialchars($loan['first_name'] . ' ' . $loan['last_name']) ?></td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full <?= $overdue ? 'bg-red-100 text-red-700' : 'bg-emerald-50 text-emerald-700' ?>">
                                <?= htmlspecialchars($loan['due_date']) ?> <?= $overdue ? '⚠️ Vencido' : '' ?>
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <form method="POST">
                                <input type="hidden" name="action" value="return">
                                <input type="hidden" name="loan_id" value="<?= $loan['id'] ?>">
                                <button type="submit" class="inline-flex items-center space-x-1.5 bg-teal-50 hover:bg-teal-100 text-teal-700 px-4 py-2 rounded-xl text-xs font-bold transition hover:-translate-y-0.5">
                                    <i class="fa-solid fa-rotate-left"></i>
                                    <span>Devolver</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>


<!-- Search Filtering -->
<div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100/80 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
    <form method="GET" class="w-full max-w-md flex items-center space-x-2">
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </div>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar por título o autor..."
                   class="block w-full pl-10 pr-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
        </div>
        <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition text-sm">Buscar</button>
    </form>
    <div class="text-slate-400 text-sm font-medium">
        Títulos: <span class="font-extrabold text-slate-700"><?= count($books) ?></span>
    </div>
</div>

<!-- Table list / Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <?php if (empty($books)): ?>
        <div class="col-span-full bg-white rounded-3xl p-10 text-center text-slate-400 border border-slate-100">
            <i class="fa-solid fa-book-open text-4xl mb-3 text-slate-200"></i>
            <p>No se encontraron libros en la biblioteca.</p>
        </div>
    <?php else: ?>
        <?php foreach ($books as $book): ?>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden flex flex-col transition hover:shadow-md hover:-translate-y-1">
                <div class="h-32 bg-slate-800 relative flex items-center justify-center p-4">
                    <div class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                    <i class="fa-solid fa-book text-5xl text-white/90 drop-shadow-md z-10"></i>
                    
                    <?php if ($book['pdf_path']): ?>
                        <span class="absolute top-3 right-3 bg-rose-500 text-white text-[10px] font-extrabold px-2 py-1 rounded-lg uppercase tracking-wider shadow-sm z-10">
                            PDF
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="p-5 flex-1 flex flex-col">
                    <h3 class="font-bold text-slate-800 text-lg leading-tight mb-1"><?= htmlspecialchars($book['title']) ?></h3>
                    <p class="text-slate-500 text-sm mb-4"><?= htmlspecialchars($book['author']) ?></p>
                    
                    <div class="mt-auto">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-500 mb-4 bg-slate-50 p-2 rounded-lg border border-slate-100">
                            <span class="<?= $book['available_copies'] > 0 ? 'text-emerald-600' : 'text-rose-500' ?>">
                                <?= $book['available_copies'] ?> / <?= $book['total_copies'] ?> Disp.
                            </span>
                            <span class="text-[10px] uppercase font-mono bg-slate-200 px-1.5 py-0.5 rounded"><?= htmlspecialchars($book['isbn'] ?: 'S/N') ?></span>
                        </div>
                        
                        <div class="flex space-x-2">
                            <?php if ($book['pdf_path']): ?>
                                <a href="<?= htmlspecialchars($book['pdf_path']) ?>" target="_blank" class="flex-1 flex items-center justify-center space-x-1 bg-rose-50 hover:bg-rose-100 text-rose-600 py-2 rounded-xl text-xs font-bold transition">
                                    <i class="fa-solid fa-file-pdf"></i>
                                    <span>Leer PDF</span>
                                </a>
                            <?php else: ?>
                                <button disabled class="flex-1 flex items-center justify-center space-x-1 bg-slate-50 text-slate-400 py-2 rounded-xl text-xs font-bold cursor-not-allowed">
                                    <i class="fa-solid fa-ban text-[10px]"></i>
                                    <span>Físico Solo</span>
                                </button>
                            <?php endif; ?>
                            
                            <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])): ?>
                                <a href="/modules/library/edit.php?id=<?= $book['id'] ?>" class="flex items-center justify-center bg-indigo-50 hover:bg-indigo-100 text-indigo-600 px-3 py-2 rounded-xl transition" title="Editar Libro">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
