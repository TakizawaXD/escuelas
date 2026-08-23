<?php
// /modules/inventory/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$error = '';
$success = '';

// Handle Create Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_category') {
    $cat_name = Auth::sanitize($_POST['name'] ?? '');
    if ($cat_name) {
        $stmt = $db->prepare("INSERT INTO inventory_categories (name) VALUES (?)");
        if ($stmt->execute([$cat_name])) {
            $success = 'Categoría agregada.';
        }
    }
}

// Handle Add Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_item') {
    $category_id = (int)$_POST['category_id'];
    $name = Auth::sanitize($_POST['name'] ?? '');
    $quantity = (int)$_POST['quantity'];
    
    if ($category_id && $name) {
        $stmt = $db->prepare("INSERT INTO inventory_items (category_id, name, quantity) VALUES (?, ?, ?)");
        if ($stmt->execute([$category_id, $name, $quantity])) {
            $success = 'Ítem de inventario registrado.';
        }
    }
}

$categories = $db->query("SELECT * FROM inventory_categories ORDER BY name ASC")->fetchAll();
$items = $db->query("
    SELECT i.*, c.name as category_name 
    FROM inventory_items i 
    JOIN inventory_categories c ON i.category_id = c.id 
    ORDER BY c.name ASC, i.name ASC
")->fetchAll();

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<!-- Premium UI Styles -->
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
</style>

<div class="space-y-8 animate-fade-in pb-10">
    <!-- Hero Header with Stats -->
    <div class="relative rounded-3xl bg-gradient-to-br from-slate-800 via-gray-900 to-zinc-950 p-8 shadow-2xl overflow-hidden text-white">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-72 h-72 rounded-full bg-slate-500 blur-[80px] opacity-30"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 rounded-full bg-zinc-500 blur-[80px] opacity-20"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="text-4xl font-extrabold tracking-tight mb-2">Inventario <span class="text-transparent bg-clip-text bg-gradient-to-r from-slate-300 to-zinc-300">Institucional</span></h2>
                <p class="text-slate-300/80 font-medium text-sm max-w-md leading-relaxed">Gestión centralizada de activos, recursos educativos y mobiliario de la escuela.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-4">
                <div class="glass-panel px-6 py-3 rounded-2xl flex items-center space-x-4 shadow-lg shadow-black/10">
                    <div class="w-12 h-12 rounded-full bg-slate-500/30 flex items-center justify-center text-slate-200">
                        <i class="fa-solid fa-boxes-stacked text-xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-slate-300/70 tracking-widest">Activos Total</p>
                        <p class="text-2xl font-extrabold text-white leading-none"><?= count($items) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="bg-emerald-50 text-emerald-600 p-4 rounded-2xl font-bold text-sm border border-emerald-200 shadow-sm flex items-center space-x-3 animate-fade-in">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Forms Column -->
        <div class="space-y-6">
            <!-- New Category (Glassmorphism style) -->
            <div class="glass-panel p-6 rounded-3xl shadow-sm border border-slate-100/80 relative overflow-hidden group">
                <div class="absolute -right-10 -top-10 w-32 h-32 bg-indigo-50 rounded-full blur-2xl opacity-50 transition-opacity group-hover:opacity-100"></div>
                <h3 class="font-extrabold text-slate-800 text-sm uppercase tracking-widest mb-4 flex items-center relative z-10">
                    <i class="fa-solid fa-folder-plus text-indigo-500 mr-2"></i> Nueva Categoría
                </h3>
                <form method="POST" class="flex space-x-2 relative z-10">
                    <input type="hidden" name="action" value="add_category">
                    <input type="text" name="name" required placeholder="Ej. Laboratorio, Biblioteca..."
                           class="flex-1 pl-4 pr-4 py-3 bg-white hover:bg-white/90 focus:bg-white border border-transparent focus:border-indigo-200 text-slate-800 rounded-xl outline-none text-sm font-medium transition shadow-inner">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl font-bold transition shadow-md shadow-indigo-500/20 hover:-translate-y-0.5 flex items-center justify-center">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </form>
            </div>

            <!-- New Item (Premium card) -->
            <div class="bg-white p-8 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-6 opacity-5 text-slate-900 group-hover:scale-110 group-hover:opacity-10 transition duration-500">
                    <i class="fa-solid fa-barcode text-6xl"></i>
                </div>
                <h3 class="font-extrabold text-slate-800 text-sm uppercase tracking-widest mb-6 flex items-center relative z-10">
                    <i class="fa-solid fa-box-open text-slate-500 mr-2"></i> Registrar Nuevo Ítem
                </h3>
                <form method="POST" class="space-y-5 relative z-10">
                    <input type="hidden" name="action" value="add_item">
                    
                    <div class="relative group/field">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Categoría de Destino</label>
                        <select name="category_id" required class="block w-full px-4 py-3 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition cursor-pointer">
                            <?php if (empty($categories)): ?>
                                <option value="" disabled selected>No hay categorías creadas</option>
                            <?php endif; ?>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="relative group/field">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Nombre del Activo</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within/field:text-slate-600 transition">
                                <i class="fa-solid fa-tag"></i>
                            </div>
                            <input type="text" name="name" required placeholder="Ej. Proyector Epson X10"
                                   class="block w-full pl-9 pr-4 py-3 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-medium transition">
                        </div>
                    </div>

                    <div class="relative group/field">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1.5">Cantidad Física Ingresada</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within/field:text-slate-600 transition">
                                <i class="fa-solid fa-cubes"></i>
                            </div>
                            <input type="number" name="quantity" required min="1" value="1"
                                   class="block w-full pl-9 pr-4 py-3 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition">
                        </div>
                    </div>

                    <button type="submit" class="w-full h-12 mt-2 bg-slate-900 hover:bg-black text-white rounded-xl font-bold transition shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 flex items-center justify-center space-x-2 border border-slate-800">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>Guardar Activo en Inventario</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Inventory Table (Premium) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden h-full flex flex-col relative z-10">
                <div class="overflow-x-auto flex-1">
                    <table class="min-w-full text-left text-sm whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-400 uppercase tracking-widest text-[10px] font-bold">
                                <th scope="col" class="px-6 py-5">Categoría Asignada</th>
                                <th scope="col" class="px-6 py-5">Activo / Ítem</th>
                                <th scope="col" class="px-6 py-5 text-center">Stock</th>
                                <th scope="col" class="px-6 py-5 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-24 text-center text-slate-400 font-medium">
                                        <div class="w-20 h-20 mx-auto bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-300 shadow-inner">
                                            <i class="fa-solid fa-boxes-stacked text-3xl"></i>
                                        </div>
                                        <p class="text-base text-slate-500">El inventario institucional se encuentra vacío.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <tr class="group hover:bg-slate-50/70 transition duration-300">
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center space-x-1.5 bg-indigo-50 border border-indigo-100 text-indigo-700 px-3 py-1 rounded-lg text-xs font-bold shadow-sm">
                                                <i class="fa-regular fa-folder text-[10px]"></i>
                                                <span><?= htmlspecialchars($item['category_name']) ?></span>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 border border-slate-200">
                                                    <i class="fa-solid fa-box-archive"></i>
                                                </div>
                                                <p class="font-extrabold text-slate-800 text-base leading-tight group-hover:text-slate-900 transition">
                                                    <?= htmlspecialchars($item['name']) ?>
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-900 text-white font-extrabold text-sm shadow-md">
                                                <?= $item['quantity'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] uppercase font-extrabold tracking-widest text-emerald-700 bg-emerald-50 border border-emerald-200 shadow-sm shadow-emerald-500/10">
                                                <i class="fa-solid fa-circle-check mr-1.5"></i>
                                                <?= htmlspecialchars($item['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
