<?php
// /views/layout/sidebar.php
$u = Auth::user();
$role = $u['role_name'] ?? 'INVITADO';

require_once __DIR__ . '/../../config/database.php';
$dbSettings = Database::getInstance()->getConnection()->query("SELECT * FROM settings WHERE id = 1")->fetch();
if (!$dbSettings) {
    $dbSettings = [
        'app_name' => 'SISTEMA ESCOLAR',
        'logo_url' => '',
        'color_primary' => '#059669',
        'color_secondary' => '#10b981'
    ];
}
?>
<!-- Sidebar Container -->
<aside id="sidebar-menu" class="hidden md:flex w-full md:w-64 bg-slate-900 text-slate-400 flex-col justify-between border-r border-slate-800 shrink-0 select-none">
    <div class="p-5">
        <!-- Logo / Branding with Dynamic System Colors and Custom Logo URL Support -->
        <div class="flex items-center space-x-3 mb-8">
            <?php if (!empty($dbSettings['logo_url'])): ?>
                <div class="w-10 h-10 overflow-hidden rounded-xl bg-white border border-slate-700/50 flex items-center justify-center">
                    <img src="<?= htmlspecialchars($dbSettings['logo_url']) ?>" alt="Logo" class="w-full h-full object-cover">
                </div>
            <?php else: ?>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg" style="background-color: <?= $dbSettings['color_primary'] ?>;">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
            <?php endif; ?>
            <div>
                <h1 class="text-white font-bold tracking-wider leading-tight text-base uppercase"><?= htmlspecialchars($dbSettings['app_name']) ?></h1>
                <span class="text-[10px] font-semibold tracking-widest uppercase" style="color: <?= $dbSettings['color_secondary'] ?>;">ERP EDUCATIVO</span>
            </div>
        </div>

        <!-- Current User Profile Info -->
        <div class="bg-slate-800/50 rounded-2xl p-4 mb-6 border border-slate-800">
            <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Bienvenido</p>
            <p class="text-white font-medium text-base truncate"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></p>
            <span class="inline-block px-2 py-0.5 mt-1.5 text-xs font-semibold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                <?= htmlspecialchars($role) ?>
            </span>
        </div>

        <!-- Navigation Links -->
        <nav class="space-y-1.5">
            <!-- Universal Dashboard -->
            <a href="/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-800 hover:text-white transition-all text-slate-300 font-medium">
                <i class="fa-solid fa-chart-pie w-5"></i>
                <span>Dashboard</span>
            </a>

            <!-- Módulos - ADMIN, DIRECTOR, COORDINADOR -->
            <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])): ?>
                <div class="pt-4 pb-2">
                    <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold px-4">Administración</p>
                </div>
                
                <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR'])): ?>
                <a href="/modules/users/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-800 hover:text-white transition-all text-slate-300 font-medium">
                    <i class="fa-solid fa-users w-5"></i>
                    <span>Usuarios</span>
                </a>
                <?php endif; ?>

                <a href="/modules/students/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-800 hover:text-white transition-all text-slate-300 font-medium">
                    <i class="fa-solid fa-user-graduate w-5"></i>
                    <span>Estudiantes</span>
                </a>

                <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR'])): ?>
                <a href="/modules/teachers/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-800 hover:text-white transition-all text-slate-300 font-medium">
                    <i class="fa-solid fa-chalkboard-user w-5"></i>
                    <span>Docentes</span>
                </a>
                <?php endif; ?>

                <a href="/modules/subjects/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-800 hover:text-white transition-all text-slate-300 font-medium">
                    <i class="fa-solid fa-book w-5"></i>
                    <span>Materias</span>
                </a>

                <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR'])): ?>
                <a href="/modules/settings/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-800 hover:text-white transition-all text-slate-300 font-medium">
                    <i class="fa-solid fa-sliders w-5"></i>
                    <span>Ajustes / Personalizar</span>
                </a>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Académico & Operaciones - DOCENTE -->
            <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR', 'DOCENTE'])): ?>
                <div class="pt-4 pb-2">
                    <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold px-4">Académico</p>
                </div>
                <a href="/modules/grades/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-800 hover:text-white transition-all text-slate-300 font-medium">
                    <i class="fa-solid fa-file-pen w-5"></i>
                    <span>Notas</span>
                </a>
                <a href="/modules/attendance/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-800 hover:text-white transition-all text-slate-300 font-medium">
                    <i class="fa-solid fa-calendar-check w-5"></i>
                    <span>Asistencia</span>
                </a>
            <?php endif; ?>

            <!-- Finanzas & Padres / Estudiantes -->
            <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'ESTUDIANTE', 'PADRE'])): ?>
                <div class="pt-4 pb-2">
                    <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold px-4">Institucional</p>
                </div>
                <a href="/modules/payments/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-800 hover:text-white transition-all text-slate-300 font-medium">
                    <i class="fa-solid fa-wallet w-5"></i>
                    <span>Finanzas / Pagos</span>
                </a>
            <?php endif; ?>

            <!-- Panel Padres / Estudiantes específico -->
            <?php if (Auth::hasRole(['ESTUDIANTE', 'PADRE'])): ?>
                <a href="/modules/parent_portal/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-800 hover:text-white transition-all text-slate-300 font-medium">
                    <i class="fa-solid fa-heart w-5"></i>
                    <span>Portal Padres/Alumnos</span>
                </a>
            <?php endif; ?>

            <!-- Comunicados -->
            <div class="pt-4 pb-2">
                <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold px-4">Comunicados</p>
            </div>
            <a href="/modules/notifications/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-800 hover:text-white transition-all text-slate-300 font-medium">
                <i class="fa-solid fa-bell w-5"></i>
                <span>Comunicaciones</span>
            </a>
            <a href="/modules/news/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-800 hover:text-white transition-all text-slate-300 font-medium">
                <i class="fa-solid fa-newspaper w-5"></i>
                <span>Noticias / Blog</span>
            </a>
        </nav>
    </div>

    <!-- Sidebar Footer -->
    <div class="p-5 border-t border-slate-800/80">
        <a href="/auth/logout.php" class="flex items-center justify-between px-4 py-3 text-red-400 font-medium bg-red-500/5 hover:bg-red-500/10 hover:text-red-300 rounded-xl transition">
            <span class="flex items-center space-x-3">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Cerrar Sesión</span>
            </span>
            <i class="fa-solid fa-chevron-right text-xs"></i>
        </a>
    </div>
</aside>

<!-- Content Area -->
<main class="flex-1 overflow-y-auto bg-slate-50 min-h-screen p-6 md:p-10">
    <div class="max-w-7xl mx-auto space-y-6">
