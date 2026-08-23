<?php
// /admissions.php
require_once __DIR__ . '/config/database.php';
$db = Database::getInstance()->getConnection();

// Fetch active courses to show as target grades
$courses = $db->query("SELECT * FROM courses ORDER BY name ASC")->fetchAll();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parent_fn = trim($_POST['parent_first_name'] ?? '');
    $parent_ln = trim($_POST['parent_last_name'] ?? '');
    $parent_email = trim($_POST['parent_email'] ?? '');
    $parent_phone = trim($_POST['parent_phone'] ?? '');
    $student_fn = trim($_POST['student_first_name'] ?? '');
    $student_ln = trim($_POST['student_last_name'] ?? '');
    $target_grade = trim($_POST['target_grade'] ?? '');
    $previous_school = trim($_POST['previous_school'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if ($parent_fn && $parent_email && $student_fn && $target_grade) {
        try {
            $stmt = $db->prepare("
                INSERT INTO admission_applications (parent_first_name, parent_last_name, parent_email, parent_phone, student_first_name, student_last_name, target_grade, previous_school, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$parent_fn, $parent_ln, $parent_email, $parent_phone, $student_fn, $student_ln, $target_grade, $previous_school, $notes]);
            $message = "¡Su solicitud de admisión ha sido enviada con éxito! Nuestro equipo se pondrá en contacto pronto.";
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Ocurrió un error al procesar su solicitud. Intente nuevamente.";
            $messageType = 'error';
        }
    } else {
        $message = "Por favor, complete todos los campos obligatorios (*).";
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admisiones | Enfant.</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        enfantBlue: '#0b2038',
                        enfantOrange: '#fc5c4c',
                    },
                    fontFamily: {
                        sans: ['Nunito', 'sans-serif'],
                        heading: ['Montserrat', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans text-gray-600 antialiased selection:bg-enfantOrange selection:text-white">

    <!-- Header (Simplified from main site) -->
    <header class="bg-white border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
            <a href="/" class="flex items-center space-x-2">
                <span class="font-bold text-2xl text-enfantBlue tracking-tighter">enfant<span class="text-enfantOrange">.</span></span>
            </a>
            <nav class="hidden md:flex space-x-8 text-[13px] font-bold text-enfantBlue">
                <a href="/" class="hover:text-enfantOrange transition">Inicio</a>
                <a href="/admissions.php" class="text-enfantOrange">Admisiones</a>
                <a href="/auth/login.php" class="hover:text-enfantOrange transition flex items-center"><i class="fa-solid fa-lock mr-2 text-gray-400"></i> Mi Portal</a>
            </nav>
        </div>
    </header>

    <!-- Page Title -->
    <div class="bg-enfantBlue py-16 text-center">
        <div class="max-w-3xl mx-auto px-6">
            <h1 class="text-4xl md:text-5xl font-heading font-bold text-white mb-4 uppercase tracking-widest">Admisiones</h1>
            <p class="text-gray-300 font-light text-lg">Inicia el proceso para ser parte de nuestra familia educativa.</p>
        </div>
    </div>

    <!-- Application Form Area -->
    <div class="max-w-4xl mx-auto px-6 py-16">
        
        <?php if ($message): ?>
            <div class="mb-8 p-6 rounded-2xl flex items-center space-x-4 border shadow-sm <?= $messageType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800' ?>">
                <i class="fa-solid <?= $messageType === 'success' ? 'fa-circle-check text-emerald-500' : 'fa-circle-exclamation text-red-500' ?> text-3xl"></i>
                <p class="font-bold text-lg"><?= htmlspecialchars($message) ?></p>
            </div>
            <?php if ($messageType === 'success'): ?>
                <div class="text-center pb-20">
                    <a href="/" class="inline-block bg-enfantOrange hover:bg-opacity-90 text-white font-bold px-8 py-3 rounded-full uppercase tracking-wider transition shadow-lg">Volver al Inicio</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!$message || $messageType === 'error'): ?>
        <form method="POST" class="bg-white p-8 md:p-12 rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100">
            <div class="mb-10 pb-6 border-b border-gray-100">
                <h2 class="text-2xl font-heading font-bold text-enfantBlue mb-2">1. Datos del Acudiente / Padre</h2>
                <p class="text-gray-400 text-sm">Información de la persona legalmente responsable del candidato.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Nombres *</label>
                    <input type="text" name="parent_first_name" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-5 py-3.5 focus:outline-none focus:border-enfantOrange focus:ring-1 focus:ring-enfantOrange transition text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Apellidos *</label>
                    <input type="text" name="parent_last_name" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-5 py-3.5 focus:outline-none focus:border-enfantOrange focus:ring-1 focus:ring-enfantOrange transition text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Correo Electrónico *</label>
                    <input type="email" name="parent_email" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-5 py-3.5 focus:outline-none focus:border-enfantOrange focus:ring-1 focus:ring-enfantOrange transition text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Teléfono Móvil *</label>
                    <input type="tel" name="parent_phone" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-5 py-3.5 focus:outline-none focus:border-enfantOrange focus:ring-1 focus:ring-enfantOrange transition text-sm">
                </div>
            </div>

            <div class="mb-10 pb-6 border-b border-gray-100">
                <h2 class="text-2xl font-heading font-bold text-enfantBlue mb-2">2. Datos del Aspirante</h2>
                <p class="text-gray-400 text-sm">Información del niño/joven que aspira a ingresar a nuestra institución.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Nombres del Aspirante *</label>
                    <input type="text" name="student_first_name" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-5 py-3.5 focus:outline-none focus:border-enfantOrange focus:ring-1 focus:ring-enfantOrange transition text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Apellidos del Aspirante *</label>
                    <input type="text" name="student_last_name" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-5 py-3.5 focus:outline-none focus:border-enfantOrange focus:ring-1 focus:ring-enfantOrange transition text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Grado al que aplica *</label>
                    <select name="target_grade" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-5 py-3.5 focus:outline-none focus:border-enfantOrange focus:ring-1 focus:ring-enfantOrange transition text-sm appearance-none">
                        <option value="">Seleccione un grado...</option>
                        <?php foreach($courses as $c): ?>
                            <option value="<?= htmlspecialchars($c['name']) ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Colegio de Procedencia</label>
                    <input type="text" name="previous_school" placeholder="Opcional" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-5 py-3.5 focus:outline-none focus:border-enfantOrange focus:ring-1 focus:ring-enfantOrange transition text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Comentarios / Necesidades Especiales</label>
                    <textarea name="notes" rows="3" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-5 py-3.5 focus:outline-none focus:border-enfantOrange focus:ring-1 focus:ring-enfantOrange transition text-sm"></textarea>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="w-full md:w-auto inline-flex justify-center items-center bg-enfantBlue hover:bg-opacity-90 text-white font-bold px-12 py-4 rounded-full uppercase tracking-wider transition shadow-lg shadow-enfantBlue/20 space-x-2">
                    <i class="fa-regular fa-paper-plane text-xl"></i>
                    <span>Enviar Solicitud de Admisión</span>
                </button>
                <p class="text-[11px] text-gray-400 mt-4">Al enviar, serás contactado por el departamento de coordinación académica.</p>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-enfantBlue text-gray-400 py-10 text-center border-t border-gray-800">
        <p class="text-xs">© <?= date('Y') ?> Escuela Primaria Enfant. Todos los derechos reservados.</p>
    </footer>

</body>
</html>
