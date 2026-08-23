<?php
// /modules/library/edit.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    header("Location: /index.php");
    exit;
}

$db = Database::getInstance()->getConnection();
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: /modules/library/index.php");
    exit;
}

$stmt = $db->prepare("SELECT * FROM library_books WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    header("Location: /modules/library/index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Delete Action First
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        try {
            // Delete PDF file if exists
            if ($book['pdf_path']) {
                $filePath = __DIR__ . '/../..' . $book['pdf_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            $stmt = $db->prepare("DELETE FROM library_books WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: /modules/library/index.php");
            exit;
        } catch (PDOException $e) {
            $error = 'No se puede eliminar el libro. Posiblemente tenga préstamos activos.';
        }
    } else {
        $title = Auth::sanitize($_POST['title'] ?? '');
        $author = Auth::sanitize($_POST['author'] ?? '');
        $isbn = Auth::sanitize($_POST['isbn'] ?? '');
        $total_copies = (int)($_POST['total_copies'] ?? 1);
        
        $pdf_path = $book['pdf_path'];
        
        // Handle PDF upload if provided
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['pdf_file']['tmp_name'];
            $fileName = $_FILES['pdf_file']['name'];
            $fileSize = $_FILES['pdf_file']['size'];
            
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
            
            if ($fileExtension !== 'pdf') {
                $error = 'Solo se permiten archivos PDF.';
            } elseif ($fileSize > 20000000) {
                $error = 'El archivo es demasiado grande (máximo 20 MB).';
            } else {
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $uploadFileDir = __DIR__ . '/../../uploads/library/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }
                
                $dest_path = $uploadFileDir . $newFileName;
                
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Remove old pdf
                    if ($book['pdf_path']) {
                        $oldPath = __DIR__ . '/../..' . $book['pdf_path'];
                        if (file_exists($oldPath)) unlink($oldPath);
                    }
                    $pdf_path = '/uploads/library/' . $newFileName;
                } else {
                    $error = 'Error al subir el nuevo archivo PDF.';
                }
            }
        }
    
        if (empty($title) || empty($author)) {
            $error = $error ?: 'El título y el autor son obligatorios.';
        } elseif ($total_copies < 0) {
            $error = $error ?: 'La cantidad de copias no puede ser negativa.';
        }
        
        if (!$error) {
            // Recalculate available copies based on total_copies difference
            // For simplicity in edit, if they change total_copies, just adjust available copies by the diff.
            // A more complex system would check active loans. Let's do diff.
            $diff = $total_copies - $book['total_copies'];
            $new_available = $book['available_copies'] + $diff;
            if ($new_available < 0) $new_available = 0;
            
            $stmt = $db->prepare("UPDATE library_books SET title = ?, author = ?, isbn = ?, total_copies = ?, available_copies = ?, pdf_path = ? WHERE id = ?");
            if ($stmt->execute([$title, $author, $isbn, $total_copies, $new_available, $pdf_path, $id])) {
                header("Location: /modules/library/index.php");
                exit;
            } else {
                $error = 'Error al actualizar el libro.';
            }
        }
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Editar Libro</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Actualizar inventario o sustituir versión digital.</p>
    </div>
    <div class="flex items-center space-x-2">
        <a href="/modules/library/index.php" class="inline-flex items-center space-x-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-3 rounded-xl font-bold transition active:scale-[0.98]">
            <i class="fa-solid fa-arrow-left text-sm"></i>
            <span>Volver a Catálogo</span>
        </a>
        <form method="POST" onsubmit="return confirm('¿Eliminar este libro de la biblioteca definitivamente?');">
            <input type="hidden" name="action" value="delete">
            <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-600 px-5 py-3 rounded-xl font-bold transition active:scale-[0.98]">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        </form>
    </div>
</div>

<div class="bg-white p-8 md:p-12 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 relative overflow-hidden group max-w-3xl">
    <?php if ($error): ?>
        <div class="bg-rose-50 text-rose-600 p-4 rounded-xl mb-6 font-medium text-sm border border-rose-100 flex items-center space-x-2">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="action" value="update">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Título del Libro <span class="text-rose-500">*</span></label>
                <input type="text" name="title" required value="<?= htmlspecialchars($book['title']) ?>"
                       class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            </div>
            
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Autor <span class="text-rose-500">*</span></label>
                <input type="text" name="author" required value="<?= htmlspecialchars($book['author']) ?>"
                       class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            </div>
            
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">ISBN</label>
                <input type="text" name="isbn" value="<?= htmlspecialchars($book['isbn'] ?? '') ?>"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-slate-800 transition font-mono text-sm">
            </div>
        </div>

        <div>
            <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Copias Físicas Totales</label>
            <input type="number" name="total_copies" required min="0" value="<?= htmlspecialchars($book['total_copies']) ?>"
                   class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
        </div>

        <div class="bg-slate-50 p-6 rounded-2xl border border-dashed border-slate-300 relative">
            <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fa-solid fa-cloud-arrow-up text-rose-500 mr-2"></i> Versión Digital (PDF)
                </div>
                <?php if ($book['pdf_path']): ?>
                    <span class="bg-emerald-100 text-emerald-700 text-[10px] px-2 py-1 rounded-full uppercase">PDF Subido</span>
                <?php endif; ?>
            </h3>
            
            <?php if ($book['pdf_path']): ?>
                <div class="mb-4 flex items-center space-x-2">
                    <a href="<?= htmlspecialchars($book['pdf_path']) ?>" target="_blank" class="text-indigo-600 text-sm font-bold hover:underline">
                        <i class="fa-solid fa-arrow-up-right-from-square text-xs mr-1"></i> Ver PDF Actual
                    </a>
                </div>
            <?php endif; ?>
            
            <p class="text-xs text-slate-500 mb-4">Selecciona un nuevo archivo PDF si deseas reemplazar el actual. (Máx 20MB)</p>
            
            <input type="file" name="pdf_file" accept=".pdf"
                   class="block w-full text-sm text-slate-500
                          file:mr-4 file:py-2.5 file:px-4
                          file:rounded-xl file:border-0
                          file:text-xs file:font-bold
                          file:bg-indigo-50 file:text-indigo-700
                          hover:file:bg-indigo-100 transition cursor-pointer">
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button type="submit" class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white px-8 py-3.5 rounded-xl font-bold shadow-lg shadow-indigo-500/30 hover:-translate-y-0.5 transition-all flex items-center justify-center">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
