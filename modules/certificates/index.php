<?php
// /modules/certificates/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();

$db = Database::getInstance()->getConnection();
$role = Auth::user()['role_name'];
$user_id = Auth::user()['id'];
$search = Auth::sanitize($_GET['search'] ?? '');

// Fetch certificates
$query = "
    SELECT c.*, u.first_name, u.last_name, s.document, cr.name as course_name
    FROM certificates c
    JOIN students s ON c.student_id = s.id
    JOIN users u ON s.user_id = u.id
    LEFT JOIN courses cr ON s.course_id = cr.id
    WHERE 1=1
";
$params = [];

if ($role === 'ESTUDIANTE') {
    $query .= " AND s.user_id = ?";
    $params[] = $user_id;
} elseif ($role === 'PADRE') {
    // Ideally parent sees their kids
    // For now, no strict parent restriction implemented, assume limited access
}

if (!empty($search)) {
    $query .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR c.title LIKE ?)";
    array_push($params, "%$search%", "%$search%", "%$search%");
}

$query .= " ORDER BY c.issue_date DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$certificates = $stmt->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Certificados y Diplomas</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Historial de logros académicos y reconocimientos oficiales.</p>
    </div>
    <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR'])): ?>
    <a href="/modules/certificates/generate.php" class="inline-flex items-center space-x-2 bg-amber-500 hover:bg-amber-600 text-white px-5 py-3 rounded-xl font-bold shadow-lg shadow-amber-500/20 hover:shadow-amber-500/30 transition active:scale-[0.98]">
        <i class="fa-solid fa-award text-sm"></i>
        <span>Emitir Certificado</span>
    </a>
    <?php endif; ?>
</div>

<div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100/80 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
    <form method="GET" class="w-full max-w-md flex items-center space-x-2">
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </div>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Buscar por alumno o título del diploma..."
                   class="block w-full pl-10 pr-4 py-2.5 bg-slate-50/60 border border-slate-200 text-slate-800 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm font-medium transition">
        </div>
        <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition text-sm">Buscar</button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <?php if (empty($certificates)): ?>
        <div class="col-span-full bg-white rounded-3xl p-10 text-center text-slate-400 border border-slate-100">
            <i class="fa-solid fa-medal text-5xl mb-3 text-slate-200"></i>
            <p class="font-medium">No hay certificados emitidos que coincidan con la búsqueda.</p>
        </div>
    <?php else: ?>
        <?php foreach ($certificates as $cert): ?>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden flex flex-col transition hover:shadow-md hover:-translate-y-1 relative group">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-400/5 to-amber-600/5 pointer-events-none"></div>
                
                <div class="p-6 text-center border-b border-amber-100/50 relative">
                    <div class="w-16 h-16 mx-auto bg-gradient-to-br from-amber-400 to-amber-600 rounded-full flex items-center justify-center text-white text-3xl shadow-lg shadow-amber-500/30 mb-4 transform group-hover:scale-110 transition duration-300">
                        <i class="fa-solid fa-award"></i>
                    </div>
                    <h3 class="font-extrabold text-slate-800 text-lg leading-tight mb-1 text-balance"><?= htmlspecialchars($cert['title']) ?></h3>
                    <p class="text-[10px] uppercase font-bold text-amber-600 tracking-wider">Otorgado a</p>
                    <p class="text-sm font-bold text-slate-600 mt-1"><?= htmlspecialchars($cert['first_name'] . ' ' . $cert['last_name']) ?></p>
                </div>
                
                <div class="p-5 flex-1 flex flex-col relative bg-slate-50/50">
                    <div class="flex items-center justify-between text-xs text-slate-500 mb-4">
                        <div class="flex flex-col">
                            <span class="font-bold text-slate-400">Fecha de Emisión</span>
                            <span class="font-bold text-slate-700"><?= date('d M, Y', strtotime($cert['issue_date'])) ?></span>
                        </div>
                        <div class="flex flex-col text-right">
                            <span class="font-bold text-slate-400">ID Registro</span>
                            <span class="font-mono font-bold text-slate-700">#CERT-<?= str_pad($cert['id'], 5, '0', STR_PAD_LEFT) ?></span>
                        </div>
                    </div>
                    
                    <?php if ($cert['description']): ?>
                        <p class="text-xs text-slate-500 italic text-center mb-4 leading-relaxed bg-white p-3 rounded-xl border border-slate-100">
                            "<?= htmlspecialchars($cert['description']) ?>"
                        </p>
                    <?php endif; ?>
                    
                    <div class="mt-auto">
                        <button onclick="window.print()" class="w-full flex items-center justify-center space-x-2 bg-slate-800 hover:bg-slate-900 text-white py-2.5 rounded-xl text-sm font-bold transition shadow-md">
                            <i class="fa-solid fa-print"></i>
                            <span>Imprimir Copia</span>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Print Styles -->
<style>
@media print {
    body * { visibility: hidden; }
    .group * { visibility: visible; }
    .group { position: absolute; left: 0; top: 0; width: 100%; height: 100%; }
    button { display: none !important; }
}
</style>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
