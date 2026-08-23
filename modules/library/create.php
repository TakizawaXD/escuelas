<?php
// /modules/library/create.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

Auth::redirectIfNotAuth();
if (!Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    header("Location: /index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = Auth::sanitize($_POST['title'] ?? '');
    $author = Auth::sanitize($_POST['author'] ?? '');
    $isbn = Auth::sanitize($_POST['isbn'] ?? '');
    $total_copies = (int)($_POST['total_copies'] ?? 1);
    
    // File upload logic
    $pdf_path = null;
    
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['pdf_file']['tmp_name'];
        $fileName = $_FILES['pdf_file']['name'];
        $fileSize = $_FILES['pdf_file']['size'];
        $fileType = $_FILES['pdf_file']['type'];
        
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        if ($fileExtension !== 'pdf') {
            $error = 'Solo se permiten archivos PDF.';
        } elseif ($fileSize > 20000000) { // 20 MB limit
            $error = 'El archivo es demasiado grande (máximo 20 MB).';
        } else {
            // Generate unique name
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/../../uploads/library/';
            
            // Ensure dir exists
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;
            
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Public path to save in DB
                $pdf_path = '/uploads/library/' . $newFileName;
            } else {
                $error = 'Ocurrió un error al subir el archivo.';
            }
        }
    }

    if (empty($title) || empty($author)) {
        $error = $error ?: 'El título y el autor son obligatorios.';
    } elseif ($total_copies < 0) {
        $error = $error ?: 'La cantidad de copias no puede ser negativa.';
    } 
    
    if (!$error) {
        $db = Database::getInstance()->getConnection();
        
        // available_copies initially equals total_copies
        $stmt = $db->prepare("INSERT INTO library_books (title, author, isbn, total_copies, available_copies, pdf_path) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $author, $isbn, $total_copies, $total_copies, $pdf_path])) {
            header("Location: /modules/library/index.php");
            exit;
        } else {
            $error = 'Error al registrar el libro en la base de datos.';
        }
    }
}

include __DIR__ . '/../../views/layout/header.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 animate-fade-in">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Agregar Libro</h2>
        <p class="text-slate-500 font-medium text-sm mt-1">Registrar un ejemplar físico o subir un documento PDF.</p>
    </div>
    <a href="/modules/library/index.php" class="inline-flex items-center space-x-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-3 rounded-xl font-bold transition active:scale-[0.98]">
        <i class="fa-solid fa-arrow-left text-sm"></i>
        <span>Volver a Catálogo</span>
    </a>
</div>

<div class="bg-white p-8 md:p-12 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 relative overflow-hidden group max-w-3xl">
    <?php if ($error): ?>
        <div class="bg-rose-50 text-rose-600 p-4 rounded-xl mb-6 font-medium text-sm border border-rose-100 flex items-center space-x-2">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Título del Libro <span class="text-rose-500">*</span></label>
                <input type="text" name="title" required placeholder="Ej. El Principito, Álgebra de Baldor..."
                       class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            </div>
            
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Autor <span class="text-rose-500">*</span></label>
                <input type="text" name="author" required placeholder="Ej. Antoine de Saint-Exupéry"
                       class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            </div>
            
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">ISBN (Opcional)</label>
                <input type="text" name="isbn" placeholder="Ej. 978-3-16-148410-0"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 outline-none text-slate-800 transition font-mono text-sm">
            </div>
        </div>

        <div>
            <label class="block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-2">Copias Físicas Totales</label>
            <input type="number" name="total_copies" required min="0" value="1"
                   class="w-full px-5 py-3.5 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-800 rounded-xl outline-none text-sm font-bold transition-all shadow-inner focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            <p class="text-xs text-slate-400 mt-1">Coloque "0" si el libro será exclusivamente digital (PDF).</p>
        </div>

        <div class="bg-slate-50 p-6 rounded-2xl border border-dashed border-slate-300 relative">
            <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center">
                <i class="fa-solid fa-cloud-arrow-up text-rose-500 mr-2"></i> Subir Versión Digital (Opcional)
            </h3>
            <p class="text-xs text-slate-500 mb-4">Puedes adjuntar el PDF para que los estudiantes lo lean directamente en línea. Máx 20MB.</p>
            
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
                Guardar Libro
            </button>
        </div>
    </form>
</div>

<?php
include __DIR__ . '/../../views/layout/footer.php';
?>
