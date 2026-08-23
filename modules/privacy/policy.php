<?php
// /modules/privacy/policy.php
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
            <i class="fa-solid fa-shield-halved text-4xl"></i>
        </div>
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Política de Privacidad</h1>
        <p class="text-slate-500 font-medium text-lg max-w-2xl mx-auto">
            Última actualización: <?= date('d de M de Y') ?>
        </p>
    </div>

    <div class="bg-white p-8 md:p-12 rounded-3xl shadow-sm border border-slate-100 space-y-8 text-slate-600 leading-relaxed">
        
        <p>
            En nuestra Institución Educativa valoramos la privacidad de nuestros estudiantes, padres de familia y personal administrativo. Esta Política de Privacidad explica cómo recopilamos, usamos y protegemos su información personal al utilizar nuestra plataforma escolar.
        </p>

        <section class="space-y-3">
            <h2 class="text-xl font-bold text-slate-800">1. Información que Recopilamos</h2>
            <p>Recopilamos la siguiente información personal:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li><strong>Información de identificación personal:</strong> Nombres, apellidos, número de documento (DNI, pasaporte), fechas de nacimiento.</li>
                <li><strong>Información de contacto:</strong> Correo electrónico, dirección física, números de teléfono.</li>
                <li><strong>Información académica:</strong> Calificaciones, asistencias, anotaciones disciplinarias y progreso escolar.</li>
                <li><strong>Información financiera:</strong> Historial de pagos y estado de cuenta (no almacenamos datos de tarjetas de crédito).</li>
            </ul>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-bold text-slate-800">2. Uso de la Información</h2>
            <p>La información recopilada se utiliza exclusivamente para fines educativos y administrativos, tales como:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li>Gestionar el proceso de matrícula y el expediente académico del estudiante.</li>
                <li>Comunicar novedades, reportes de calificaciones y alertas a padres y tutores.</li>
                <li>Administrar la facturación y los pagos.</li>
                <li>Mejorar el rendimiento y la seguridad de nuestra plataforma.</li>
            </ul>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-bold text-slate-800">3. Protección de Datos</h2>
            <p>
                Implementamos medidas de seguridad físicas, electrónicas y administrativas para proteger su información contra accesos no autorizados, alteraciones o destrucción. Las contraseñas se almacenan mediante encriptación segura y el acceso a los datos está estrictamente limitado al personal autorizado según su rol.
            </p>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-bold text-slate-800">4. Compartir Información</h2>
            <p>
                No vendemos, alquilamos ni compartimos su información personal con terceros para fines comerciales. La información solo será compartida cuando sea requerido por la ley o por autoridades gubernamentales competentes.
            </p>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-bold text-slate-800">5. Derechos del Usuario</h2>
            <p>
                Usted tiene derecho a acceder, rectificar o solicitar la eliminación de sus datos personales. Para ejercer estos derechos, por favor contáctenos a través de nuestro formulario de soporte técnico o escribiendo a la secretaría académica.
            </p>
        </section>

    </div>
</div>

<?php 
if ($isAuth) {
    include __DIR__ . '/../../views/layout/footer.php'; 
} else {
    echo '</body></html>';
}
?>
