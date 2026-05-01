<?php
// /index.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$isGuest = !Auth::check();

if ($isGuest):
    // -------------------------------------------------------------
    // PREPA ANÁHUAC STYLE GUEST LANDING PAGE IN EMERALD GREEN
    // -------------------------------------------------------------
    $db = Database::getInstance()->getConnection();
    
    // Fetch recent news/blog posts for guests
    try {
        $newsList = $db->query("SELECT * FROM news ORDER BY id DESC LIMIT 4")->fetchAll();
    } catch (Exception $e) {
        $newsList = [];
    }

    // Fetch teachers for the showcase
    try {
        $teachers = $db->query("
            SELECT t.*, u.first_name, u.last_name, u.email 
            FROM teachers t
            JOIN users u ON t.user_id = u.id
            ORDER BY u.id ASC LIMIT 12
        ")->fetchAll();
    } catch (Exception $e) {
        $teachers = [];
    }
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BIENVENIDO A NUESTRO PORTAL ESCOLAR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .font-heading { font-family: 'Montserrat', sans-serif; }
        .carousel-item { display: none; }
        .carousel-item.active { display: block; animation: slideIn 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-white text-slate-800">
    <!-- Sophisticated Clean Header Exactly Like the User's Image Example -->
    <header class="sticky top-0 z-50 bg-white border-b border-slate-100 shadow-sm animate-fade-in">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <!-- Left Side Logo -->
            <div class="flex items-center space-x-3 select-none">
                <div class="w-12 h-12 bg-gradient-to-tr from-emerald-600 to-emerald-500 rounded-2xl flex items-center justify-center text-white font-extrabold text-2xl shadow-md shadow-emerald-500/20">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <div>
                    <h1 class="text-lg font-black tracking-tight text-slate-900 font-heading leading-none">PORTAL ESCOLAR</h1>
                    <span class="text-xs text-emerald-600 font-bold uppercase tracking-wider">Liderazgo & Excelencia</span>
                </div>
            </div>

            <!-- Navigation Links - Exactly as WP / Prepa Anáhuac Example -->
            <nav class="hidden lg:flex items-center space-x-8 text-xs font-extrabold uppercase tracking-widest text-slate-500">
                <a href="#nosotros" class="hover:text-emerald-600 transition">Nosotros</a>
                <a href="#experiencia" class="hover:text-emerald-600 transition">Experiencia</a>
                <a href="#facultad" class="hover:text-emerald-600 transition">Facultad</a>
                <a href="#noticias" class="hover:text-emerald-600 transition">Blog</a>
            </nav>

            <!-- Right Side CTA Hub+ button -->
            <div class="flex items-center space-x-4">
                <a href="/auth/login.php" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 hover:scale-[1.03] text-white font-black rounded-xl transition text-xs tracking-widest uppercase shadow-lg shadow-emerald-500/30">Hub+</a>
            </div>
        </div>
    </header>

    <!-- Content with premium Bento Grid / Photo Collage (Exactly like User Example) -->
    <main class="max-w-7xl mx-auto px-6 py-12 md:py-16 space-y-24">
        
        <!-- Hero Grid Section -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center min-h-[500px]">
            <!-- Left Information Details (WordPress style) -->
            <div class="lg:col-span-5 space-y-8 animate-fade-in">
                <div class="space-y-4">
                    <h2 class="text-4xl md:text-5xl lg:text-6xl font-black font-heading text-slate-900 tracking-tight leading-tight">
                        BIENVENIDO A <br><span class="text-emerald-600">NUESTRA ACADEMIA</span>
                    </h2>
                    <p class="text-base md:text-lg font-bold text-slate-700 leading-relaxed max-w-lg">
                        Formamos a líderes como tú. Líderes que impactarán positivamente a la sociedad y en el mundo por medio de la innovación, creatividad, valores y fe.
                    </p>
                </div>

                <div>
                    <a href="/auth/register.php" class="inline-block px-7 py-4 bg-emerald-600 hover:bg-emerald-500 hover:scale-[1.02] text-white rounded-xl font-extrabold tracking-wider uppercase text-sm shadow-xl shadow-emerald-500/25 transition">
                        Inicia tu proceso de admisión
                    </a>
                </div>

                <div class="select-none">
                    <span class="text-2xl md:text-3xl font-black font-heading tracking-widest text-emerald-600 italic uppercase">
                        #ForjandoLíderes
                    </span>
                </div>
            </div>

            <!-- Right Side Bento/Collage Photo Grid exactly matching user's reference -->
            <div class="lg:col-span-7 grid grid-cols-2 gap-4 h-full animate-fade-in">
                <!-- Large top image spanning right -->
                <div class="col-span-2 md:col-span-1 h-56 md:h-72 rounded-3xl overflow-hidden border border-slate-100 shadow-sm transition hover:scale-[1.02] duration-300">
                    <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=600" alt="Students in team" class="w-full h-full object-cover">
                </div>
                <!-- Top right image -->
                <div class="hidden md:block h-56 md:h-72 rounded-3xl overflow-hidden border border-slate-100 shadow-sm transition hover:scale-[1.02] duration-300">
                    <img src="https://images.unsplash.com/photo-1543269865-cbf427effbad?q=80&w=600" alt="Laughing and studying" class="w-full h-full object-cover">
                </div>
                <!-- Bottom left image -->
                <div class="h-44 md:h-56 rounded-3xl overflow-hidden border border-slate-100 shadow-sm transition hover:scale-[1.02] duration-300">
                    <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?q=80&w=600" alt="Work together in class" class="w-full h-full object-cover">
                </div>
                <!-- Bottom right image -->
                <div class="h-44 md:h-56 rounded-3xl overflow-hidden border border-slate-100 shadow-sm transition hover:scale-[1.02] duration-300">
                    <img src="https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?q=80&w=600" alt="Computer laboratory" class="w-full h-full object-cover">
                </div>
            </div>
        </div>

        <!-- Dynamic Animated Information Carousel Section (Emerald Theme) -->
        <div id="experiencia" class="bg-slate-50/70 backdrop-blur-md p-8 md:p-14 rounded-3xl border border-slate-100 text-center space-y-6 select-none scroll-mt-24 shadow-sm animate-fade-in">
            <h3 class="text-xs font-black text-emerald-600 uppercase tracking-widest font-heading">¿Por qué elegirnos?</h3>
            
            <div id="infoCarousel" class="relative max-w-3xl mx-auto h-44 flex items-center justify-center select-none">
                <!-- Slide 1 -->
                <div class="carousel-item active space-y-4">
                    <div class="w-16 h-16 bg-emerald-100 rounded-3xl flex items-center justify-center text-emerald-600 text-3xl mx-auto mb-2">
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                    <h4 class="text-2xl font-black text-slate-900 tracking-tight font-heading">Excelencia Académica</h4>
                    <p class="text-slate-600 text-base max-w-xl mx-auto leading-relaxed">Formamos a los mejores perfiles del país, logrando resultados sobresalientes en competencias mundiales.</p>
                </div>

                <!-- Slide 2 -->
                <div class="carousel-item space-y-4">
                    <div class="w-16 h-16 bg-teal-100 rounded-3xl flex items-center justify-center text-teal-600 text-3xl mx-auto mb-2">
                        <i class="fa-solid fa-microscope"></i>
                    </div>
                    <h4 class="text-2xl font-black text-slate-900 tracking-tight font-heading">Tecnología de Vanguardia</h4>
                    <p class="text-slate-600 text-base max-w-xl mx-auto leading-relaxed">Infraestructura tecnológica y laboratorios STEAM de última generación para innovar con fe.</p>
                </div>

                <!-- Slide 3 -->
                <div class="carousel-item space-y-4">
                    <div class="w-16 h-16 bg-amber-100 rounded-3xl flex items-center justify-center text-amber-600 text-3xl mx-auto mb-2">
                        <i class="fa-solid fa-users-rays"></i>
                    </div>
                    <h4 class="text-2xl font-black text-slate-900 tracking-tight font-heading">Crecimiento Humano</h4>
                    <p class="text-slate-600 text-base max-w-xl mx-auto leading-relaxed">Impulsamos la formación en valores, empatía y liderazgo ético para el futuro de la sociedad.</p>
                </div>
            </div>

            <!-- Dots Control -->
            <div class="flex items-center justify-center space-x-2.5 pt-4">
                <button onclick="changeSlide(0)" class="dot-btn h-2 w-7 bg-emerald-600 rounded-full transition-all"></button>
                <button onclick="changeSlide(1)" class="dot-btn h-2 w-3 bg-slate-300 rounded-full transition-all"></button>
                <button onclick="changeSlide(2)" class="dot-btn h-2 w-3 bg-slate-300 rounded-full transition-all"></button>
            </div>
        </div>

        <!-- Wordpress-styled News & Blog Section -->
        <div id="noticias" class="space-y-8 scroll-mt-24 animate-fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight font-heading">Blog y Comunicados</h2>
                    <p class="text-slate-500 font-medium text-sm mt-1">Sigue el día a día de la vida en nuestro campus.</p>
                </div>
                <a href="/modules/news/index.php" class="text-xs font-black uppercase tracking-wider text-emerald-600 hover:text-emerald-500 flex items-center space-x-1.5 transition">
                    <span>Ver Noticias</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <?php if (empty($newsList)): ?>
                    <div class="md:col-span-2 bg-white p-12 text-center rounded-3xl shadow-sm border border-slate-100">
                        <p class="text-slate-400 text-sm">No hay noticias publicadas en este momento.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($newsList as $news): ?>
                        <div class="bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-md border border-slate-100 transition flex flex-col h-full group">
                            <?php if (!empty($news['photo_url'])): ?>
                                <div class="h-60 w-full overflow-hidden relative">
                                    <img src="<?= htmlspecialchars($news['photo_url']) ?>" alt="News Banner" class="w-full h-full object-cover transition-all group-hover:scale-105 duration-500">
                                </div>
                            <?php endif; ?>
                            <div class="p-6 md:p-8 flex flex-col justify-between flex-1 space-y-4">
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between text-xs font-semibold text-slate-400">
                                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 rounded-lg">Comunicado</span>
                                        <span><?= date('d M, Y', strtotime($news['created_at'])) ?></span>
                                    </div>
                                    <h3 class="text-xl font-extrabold font-heading text-slate-900 leading-snug tracking-tight group-hover:text-emerald-600 transition"><?= htmlspecialchars($news['title']) ?></h3>
                                    <p class="text-slate-600 text-sm leading-relaxed whitespace-pre-line"><?= htmlspecialchars($news['content']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Faculty Showcase Section -->
        <div id="facultad" class="space-y-8 scroll-mt-24 animate-fade-in">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight font-heading">Nuestra Facultad</h2>
                <p class="text-slate-500 font-medium text-sm mt-1">Conoce a nuestro destacado equipo de maestros y docentes.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php if (empty($teachers)): ?>
                    <div class="lg:col-span-4 bg-white p-12 text-center rounded-3xl shadow-sm border border-slate-100">
                        <p class="text-slate-400 text-sm">Docentes aún no registrados.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($teachers as $t): ?>
                        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all flex flex-col items-center text-center space-y-4 group">
                            <div class="w-16 h-16 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 font-extrabold text-xl shadow-sm flex-shrink-0 transition-transform group-hover:scale-105 duration-300">
                                <?= strtoupper(substr($t['first_name'], 0, 1) . substr($t['last_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-slate-900 font-heading text-base leading-tight"><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></h4>
                                <span class="inline-block px-2.5 py-1 mt-1.5 text-xs font-black rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-700">
                                    <?= htmlspecialchars($t['specialty']) ?>
                                </span>
                            </div>
                            <p class="text-slate-400 font-medium text-xs truncate max-w-[200px]"><?= htmlspecialchars($t['email']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.carousel-item');
    const dots = document.querySelectorAll('.dot-btn');

    function changeSlide(index) {
        slides[currentSlide].classList.remove('active');
        dots[currentSlide].classList.remove('w-7', 'bg-emerald-600');
        dots[currentSlide].classList.add('w-3', 'bg-slate-300');

        currentSlide = index;

        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.remove('w-3', 'bg-slate-300');
        dots[currentSlide].classList.add('w-7', 'bg-emerald-600');
    }

    // Auto-rotate every 5 seconds
    setInterval(() => {
        let next = (currentSlide + 1) % slides.length;
        changeSlide(next);
    }, 5000);
    </script>
</body>
</html>
<?php
    exit;
endif; // End of Guest View

// -------------------------------------------------------------
// LOGGED IN VIEW (Dashboard Content)
// -------------------------------------------------------------
$u = Auth::user();
$db = Database::getInstance()->getConnection();

$totalStudents = 0;
$totalTeachers = 0;
$averageGrade = 0.0;
$totalDebts = 0.0;
$attendancePercentage = 0.0;

if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])) {
    $totalStudents = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $totalTeachers = $db->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
    
    $avg = $db->query("SELECT AVG(final_grade) FROM grades")->fetchColumn();
    $averageGrade = $avg ? round($avg, 2) : 0.0;

    $debt = $db->query("SELECT SUM(amount) FROM payments WHERE status = 'Pendiente'")->fetchColumn();
    $totalDebts = $debt ? round($debt, 2) : 0.0;

    $attCount = $db->query("SELECT COUNT(*) FROM attendance")->fetchColumn();
    if ($attCount > 0) {
        $present = $db->query("SELECT COUNT(*) FROM attendance WHERE status = 'Presente'")->fetchColumn();
        $attendancePercentage = round(($present / $attCount) * 100, 1);
    }
}

$recentNotifications = $db->prepare("
    SELECT n.*, u.first_name, u.last_name, r.name as role_name
    FROM notifications n
    JOIN users u ON n.user_id = u.id
    LEFT JOIN roles r ON n.target_role_id = r.id
    ORDER BY n.id DESC LIMIT 4
");
$recentNotifications->execute();
$notifications = $recentNotifications->fetchAll();

include __DIR__ . '/views/layout/header.php';
include __DIR__ . '/views/layout/sidebar.php';
?>

<div class="space-y-8 animate-fade-in">
    <!-- Beautiful Hero Section with Emerald Green Theme -->
    <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-emerald-950 to-slate-900 border border-slate-800/80 p-8 md:p-12 rounded-3xl text-white shadow-2xl shadow-emerald-500/10 mb-8 select-none">
        <div class="absolute -top-12 -right-12 w-64 h-64 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 w-64 h-64 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="flex-1 max-w-2xl space-y-4">
                <div class="inline-flex items-center space-x-2 bg-emerald-500/10 border border-emerald-400/20 backdrop-blur-md px-3 py-1 rounded-full">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-xs text-emerald-300 font-bold tracking-wider uppercase">Portal Educativo Oficial</span>
                </div>
                
                <h1 class="text-3xl font-extrabold tracking-tight md:text-5xl leading-tight">
                    ¡Bienvenido, <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-300 via-blue-200 to-teal-200"><?= htmlspecialchars($u['first_name']) ?></span>!
                </h1>
                
                <p class="text-slate-300 font-medium text-base md:text-lg max-w-xl leading-relaxed">
                    Gestiona calificaciones, asistencias, comunicados institucionales y procesos administrativos desde un panel moderno y centralizado.
                </p>

                <div class="flex flex-wrap gap-3 pt-2">
                    <a href="#available-modules" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-2xl font-bold tracking-wide transition flex items-center space-x-2 shadow-lg shadow-emerald-500/30">
                        <i class="fa-solid fa-layer-group"></i>
                        <span>Explorar Módulos</span>
                    </a>
                    <a href="/modules/notifications/index.php" class="px-5 py-3 bg-slate-800/80 hover:bg-slate-700/80 text-emerald-200 border border-slate-700 rounded-2xl font-bold tracking-wide transition flex items-center space-x-2 backdrop-blur-sm">
                        <i class="fa-solid fa-bullhorn"></i>
                        <span>Comunicaciones</span>
                    </a>
                </div>
            </div>

            <div class="flex-shrink-0 w-32 h-32 md:w-48 md:h-48 rounded-3xl bg-gradient-to-br from-emerald-500/10 to-teal-500/10 border border-emerald-400/20 backdrop-blur-sm flex items-center justify-center text-emerald-400 text-6xl md:text-7xl shadow-inner animate-pulse">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
        </div>
    </div>

    <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center justify-between hover:shadow-md transition cursor-pointer">
            <div class="space-y-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Estudiantes</span>
                <p class="text-4xl font-extrabold text-slate-900 tracking-tight"><?= $totalStudents ?></p>
                <div class="flex items-center text-xs text-emerald-600 font-semibold space-x-1">
                    <i class="fa-solid fa-arrow-up"></i>
                    <span>Registrados</span>
                </div>
            </div>
            <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 text-xl font-bold">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center justify-between hover:shadow-md transition cursor-pointer">
            <div class="space-y-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Docentes</span>
                <p class="text-4xl font-extrabold text-slate-900 tracking-tight"><?= $totalTeachers ?></p>
                <div class="flex items-center text-xs text-emerald-600 font-semibold space-x-1">
                    <i class="fa-solid fa-check"></i>
                    <span>Asignados</span>
                </div>
            </div>
            <div class="w-14 h-14 bg-teal-50 rounded-2xl flex items-center justify-center text-teal-600 text-xl font-bold">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center justify-between hover:shadow-md transition cursor-pointer">
            <div class="space-y-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Promedio Escolar</span>
                <p class="text-4xl font-extrabold text-slate-900 tracking-tight"><?= number_format($averageGrade, 2) ?></p>
                <div class="flex items-center text-xs text-amber-600 font-semibold space-x-1">
                    <i class="fa-solid fa-star"></i>
                    <span>Rendimiento Global</span>
                </div>
            </div>
            <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 text-xl font-bold">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center justify-between hover:shadow-md transition cursor-pointer">
            <div class="space-y-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Cartera de Pagos</span>
                <p class="text-4xl font-extrabold text-slate-900 tracking-tight">$<?= number_format($totalDebts, 0, ',', '.') ?></p>
                <div class="flex items-center text-xs text-red-600 font-semibold space-x-1">
                    <i class="fa-solid fa-clock"></i>
                    <span>Por recaudar</span>
                </div>
            </div>
            <div class="w-14 h-14 bg-red-50 rounded-2xl flex items-center justify-center text-red-600 text-xl font-bold">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- User Module Quick Links -->
    <div id="available-modules" class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 scroll-mt-6">
        <h3 class="font-bold text-slate-800 text-lg mb-6">Módulos disponibles del Sistema ERP</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
            <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR'])): ?>
                <a href="/modules/users/index.php" class="flex items-center space-x-4 p-4 rounded-2xl hover:bg-slate-50 border border-slate-100/80 transition-all">
                    <span class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-lg font-bold flex-shrink-0">
                        <i class="fa-solid fa-users"></i>
                    </span>
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm">Usuarios</h4>
                        <span class="text-xs text-slate-400 font-medium">Gestión de roles y cuentas</span>
                    </div>
                </a>
            <?php endif; ?>

            <a href="/modules/students/index.php" class="flex items-center space-x-4 p-4 rounded-2xl hover:bg-slate-50 border border-slate-100/80 transition-all">
                <span class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600 text-lg font-bold flex-shrink-0">
                    <i class="fa-solid fa-user-graduate"></i>
                </span>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">Estudiantes</h4>
                    <span class="text-xs text-slate-400 font-medium">Matrícula y expedientes</span>
                </div>
            </a>

            <a href="/modules/subjects/index.php" class="flex items-center space-x-4 p-4 rounded-2xl hover:bg-slate-50 border border-slate-100/80 transition-all">
                <span class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 text-lg font-bold flex-shrink-0">
                    <i class="fa-solid fa-book"></i>
                </span>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">Materias</h4>
                    <span class="text-xs text-slate-400 font-medium">Programas académicos</span>
                </div>
            </a>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/views/layout/footer.php';
?>
