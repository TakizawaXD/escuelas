<?php
// /modules/terms/conditions.php
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
            <i class="fa-solid fa-file-contract text-4xl"></i>
        </div>
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Términos y Condiciones</h1>
        <p class="text-slate-500 font-medium text-lg max-w-2xl mx-auto">
            Última actualización: <?= date('d de M de Y') ?>
        </p>
    </div>

    <div class="bg-white p-8 md:p-12 rounded-3xl shadow-sm border border-slate-100 space-y-8 text-slate-600 leading-relaxed">
        
        <p>
            Bienvenido a la plataforma del Sistema de Gestión Escolar. Al acceder y utilizar esta aplicación, usted acepta cumplir y estar sujeto a los siguientes términos y condiciones de uso.
        </p>

        <section class="space-y-3">
            <h2 class="text-xl font-bold text-slate-800">1. Aceptación de los Términos</h2>
            <p>
                Al acceder a esta plataforma, ya sea como estudiante, profesor, acudiente o personal administrativo, usted acepta estos Términos y Condiciones, así como nuestra Política de Privacidad. Si no está de acuerdo con alguna parte, no debe utilizar el sistema.
            </p>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-bold text-slate-800">2. Uso de la Cuenta</h2>
            <ul class="list-disc pl-6 space-y-2">
                <li>Usted es responsable de mantener la confidencialidad de su cuenta y contraseña.</li>
                <li>Acuerda notificar inmediatamente cualquier uso no autorizado de su cuenta.</li>
                <li>La institución no será responsable de ninguna pérdida o daño que surja del incumplimiento de esta obligación de seguridad.</li>
            </ul>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-bold text-slate-800">3. Conducta del Usuario</h2>
            <p>Al usar la plataforma, usted se compromete a no:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li>Utilizar el sistema para propósitos ilegales o no autorizados.</li>
                <li>Modificar, adaptar o hackear el sistema, o intentar obtener acceso no autorizado a los servidores.</li>
                <li>Transmitir virus, gusanos o cualquier código de naturaleza destructiva.</li>
                <li>Compartir información confidencial de otros usuarios sin su consentimiento.</li>
            </ul>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-bold text-slate-800">4. Disponibilidad del Sistema</h2>
            <p>
                Nos esforzamos por mantener la plataforma disponible las 24 horas del día. Sin embargo, no garantizamos que el servicio será ininterrumpido o libre de errores. Es posible que realicemos mantenimiento programado que afecte temporalmente la disponibilidad.
            </p>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-bold text-slate-800">5. Modificaciones a los Términos</h2>
            <p>
                Nos reservamos el derecho de modificar estos términos en cualquier momento. Las modificaciones entrarán en vigor inmediatamente después de su publicación en la plataforma. Es su responsabilidad revisar periódicamente estos términos.
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
