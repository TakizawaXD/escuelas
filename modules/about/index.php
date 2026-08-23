<?php
// /modules/about/index.php
require_once __DIR__ . '/../../config/auth.php';
// Permitir acceso público o autenticado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isAuth = isset($_SESSION['user_id']);

include __DIR__ . '/../../views/layout/header.php';
if ($isAuth) {
    include __DIR__ . '/../../views/layout/sidebar.php';
}
?>

<div class="space-y-6 animate-fade-in max-w-4xl mx-auto <?= !$isAuth ? 'pt-10' : '' ?>">
    <div class="text-center space-y-4 mb-10">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-indigo-100 text-indigo-600 rounded-full mb-2">
            <i class="fa-solid fa-school text-4xl"></i>
        </div>
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Acerca de Nosotros</h1>
        <p class="text-slate-500 font-medium text-lg max-w-2xl mx-auto">
            Conoce más sobre nuestra institución, nuestra historia, misión y los valores que nos impulsan a brindar educación de calidad.
        </p>
    </div>

    <div class="bg-white p-8 md:p-12 rounded-3xl shadow-sm border border-slate-100 space-y-8">
        
        <section class="space-y-4">
            <h2 class="text-2xl font-bold text-slate-800 flex items-center space-x-3">
                <i class="fa-solid fa-bullseye text-indigo-500"></i>
                <span>Nuestra Misión</span>
            </h2>
            <p class="text-slate-600 leading-relaxed">
                Nuestra misión es formar estudiantes íntegros, críticos y creativos, proporcionándoles un ambiente educativo inclusivo y de excelencia, fundamentado en valores éticos y el uso de tecnologías innovadoras para enfrentar los retos del futuro.
            </p>
        </section>

        <section class="space-y-4">
            <h2 class="text-2xl font-bold text-slate-800 flex items-center space-x-3">
                <i class="fa-solid fa-eye text-indigo-500"></i>
                <span>Nuestra Visión</span>
            </h2>
            <p class="text-slate-600 leading-relaxed">
                Para el año 2030, seremos reconocidos como una institución educativa líder a nivel regional, destacando por nuestra innovación pedagógica, el alto desempeño académico de nuestros egresados y nuestro compromiso con el desarrollo sostenible de la comunidad.
            </p>
        </section>

        <section class="space-y-4">
            <h2 class="text-2xl font-bold text-slate-800 flex items-center space-x-3">
                <i class="fa-solid fa-heart text-indigo-500"></i>
                <span>Valores Institucionales</span>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div class="flex items-start space-x-3 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <i class="fa-solid fa-check text-emerald-500 mt-1"></i>
                    <div>
                        <h3 class="font-bold text-slate-800">Excelencia</h3>
                        <p class="text-sm text-slate-500">Buscamos siempre el más alto nivel en todo lo que hacemos.</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <i class="fa-solid fa-check text-emerald-500 mt-1"></i>
                    <div>
                        <h3 class="font-bold text-slate-800">Respeto</h3>
                        <p class="text-sm text-slate-500">Valoramos la diversidad y fomentamos la convivencia pacífica.</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <i class="fa-solid fa-check text-emerald-500 mt-1"></i>
                    <div>
                        <h3 class="font-bold text-slate-800">Innovación</h3>
                        <p class="text-sm text-slate-500">Adaptamos continuamente nuestras metodologías al cambio.</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <i class="fa-solid fa-check text-emerald-500 mt-1"></i>
                    <div>
                        <h3 class="font-bold text-slate-800">Responsabilidad</h3>
                        <p class="text-sm text-slate-500">Asumimos las consecuencias de nuestras acciones y decisiones.</p>
                    </div>
                </div>
            </div>
        </section>
        
    </div>
</div>

<?php 
if ($isAuth) {
    include __DIR__ . '/../../views/layout/footer.php'; 
} else {
    // Si no está autenticado, cerramos etiquetas de html
    echo '</body></html>';
}
?>
