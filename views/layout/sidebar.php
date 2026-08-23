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
<!-- Top Navbar Container -->
<nav id="sidebar-menu" class="hidden md:flex w-full bg-[#1d2327] text-[#f0f0f1] shrink-0 select-none z-30 border-b border-[#2c3338] sticky top-8">
    <div class="max-w-7xl mx-auto w-full px-2 flex flex-col md:flex-row">
        <!-- Main Navbar List -->
        <ul class="flex flex-col md:flex-row text-[13px] font-medium w-full" id="adminmenu">
            
            <li>
                <a href="/index.php" class="flex items-center space-x-2 px-4 py-3 hover:bg-[#2c3338] hover:text-[#72aee6] transition <?= ($_SERVER['PHP_SELF'] == '/index.php') ? 'bg-[#2271b1] text-white hover:bg-[#2271b1] hover:text-white' : '' ?>">
                    <i class="fa-solid fa-gauge text-lg <?= ($_SERVER['PHP_SELF'] == '/index.php') ? 'text-white' : 'text-[#a7aaad]' ?>"></i>
                    <span>Panel Principal</span>
                </a>
            </li>

            <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR'])): ?>
            <li>
                <a href="/modules/users/index.php" class="flex items-center space-x-2 px-4 py-3 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                    <i class="fa-solid fa-users text-[#a7aaad] text-lg"></i>
                    <span>Usuarios</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])): ?>
                <!-- DROPDOWN: ACADÉMICO -->
                <li class="relative group">
                    <button class="flex items-center space-x-2 px-4 py-3 hover:bg-[#2c3338] hover:text-[#72aee6] transition w-full md:w-auto text-left">
                        <i class="fa-solid fa-graduation-cap text-[#a7aaad] text-lg"></i>
                        <span>Académico</span>
                        <i class="fa-solid fa-chevron-down text-[10px] ml-1 text-[#a7aaad]"></i>
                    </button>
                    <!-- Dropdown Content -->
                    <ul class="md:absolute left-0 top-full hidden group-hover:block bg-[#1d2327] border border-[#2c3338] shadow-lg min-w-[220px] z-50">
                        <li>
                            <a href="/modules/academic_years/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                                <i class="fa-solid fa-calendar-star w-4 text-center"></i> <span>Años Lectivos</span>
                            </a>
                        </li>
                        <li>
                            <a href="/modules/admissions/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-enfantOrange transition">
                                <i class="fa-solid fa-file-signature w-4 text-center"></i> <span>Admisiones</span>
                            </a>
                        </li>
                        <li>
                            <a href="/modules/students/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                                <i class="fa-solid fa-user-graduate w-4 text-center"></i> <span>Estudiantes</span>
                            </a>
                        </li>
                        <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR'])): ?>
                        <li>
                            <a href="/modules/teachers/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                                <i class="fa-solid fa-chalkboard-user w-4 text-center"></i> <span>Docentes</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <li>
                            <a href="/modules/subjects/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                                <i class="fa-solid fa-book w-4 text-center"></i> <span>Asignaturas</span>
                            </a>
                        </li>
                        <li>
                            <a href="/modules/classrooms/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                                <i class="fa-solid fa-school w-4 text-center"></i> <span>Aulas</span>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR', 'DOCENTE'])): ?>
                <!-- DROPDOWN: GESTIÓN DE CLASE -->
                <li class="relative group">
                    <button class="flex items-center space-x-2 px-4 py-3 hover:bg-[#2c3338] hover:text-[#72aee6] transition w-full md:w-auto text-left">
                        <i class="fa-solid fa-chalkboard text-[#a7aaad] text-lg"></i>
                        <span>Gestión de Clase</span>
                        <i class="fa-solid fa-chevron-down text-[10px] ml-1 text-[#a7aaad]"></i>
                    </button>
                    <!-- Dropdown Content -->
                    <ul class="md:absolute left-0 top-full hidden group-hover:block bg-[#1d2327] border border-[#2c3338] shadow-lg min-w-[200px] z-50">
                        <li>
                            <a href="/modules/grades/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                                <i class="fa-solid fa-file-pen w-4 text-center"></i> <span>Calificaciones</span>
                            </a>
                        </li>
                        <li>
                            <a href="/modules/attendance/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                                <i class="fa-solid fa-calendar-check w-4 text-center"></i> <span>Asistencia</span>
                            </a>
                        </li>
                        <li>
                            <a href="/modules/exams/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                                <i class="fa-solid fa-file-signature w-4 text-center"></i> <span>Exámenes</span>
                            </a>
                        </li>
                        <li>
                            <a href="/modules/certificates/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                                <i class="fa-solid fa-award w-4 text-center"></i> <span>Certificados</span>
                            </a>
                        </li>
                        <li>
                            <a href="/modules/library/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                                <i class="fa-solid fa-book-open-reader w-4 text-center"></i> <span>Biblioteca</span>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR', 'DOCENTE'])): ?>
                <!-- DROPDOWN: BIENESTAR -->
                <li class="relative group">
                    <button class="flex items-center space-x-2 px-4 py-3 hover:bg-[#2c3338] hover:text-[#72aee6] transition w-full md:w-auto text-left">
                        <i class="fa-solid fa-heart-pulse text-[#a7aaad] text-lg"></i>
                        <span>Bienestar</span>
                        <i class="fa-solid fa-chevron-down text-[10px] ml-1 text-[#a7aaad]"></i>
                    </button>
                    <!-- Dropdown Content -->
                    <ul class="md:absolute left-0 top-full hidden group-hover:block bg-[#1d2327] border border-[#2c3338] shadow-lg min-w-[200px] z-50">
                        <li>
                            <a href="/modules/medical_records/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                                <i class="fa-solid fa-notes-medical w-4 text-center"></i> <span>Fichas Médicas</span>
                            </a>
                        </li>
                        <li>
                            <a href="/modules/discipline/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                                <i class="fa-solid fa-clipboard-user w-4 text-center"></i> <span>Disciplina</span>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR'])): ?>
                <!-- DROPDOWN: ADMINISTRATIVO -->
                <li class="relative group">
                    <button class="flex items-center space-x-2 px-4 py-3 hover:bg-[#2c3338] hover:text-[#72aee6] transition w-full md:w-auto text-left">
                        <i class="fa-solid fa-building-columns text-[#a7aaad] text-lg"></i>
                        <span>Administrativo</span>
                        <i class="fa-solid fa-chevron-down text-[10px] ml-1 text-[#a7aaad]"></i>
                    </button>
                    <!-- Dropdown Content -->
                    <ul class="md:absolute left-0 top-full hidden group-hover:block bg-[#1d2327] border border-[#2c3338] shadow-lg min-w-[200px] z-50">
                        <li>
                            <a href="/modules/payments/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                                <i class="fa-solid fa-wallet w-4 text-center"></i> <span>Finanzas</span>
                            </a>
                        </li>
                        <li>
                            <a href="/modules/inventory/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                                <i class="fa-solid fa-boxes-stacked w-4 text-center"></i> <span>Inventario</span>
                            </a>
                        </li>
                        <li>
                            <a href="/modules/transport/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                                <i class="fa-solid fa-bus w-4 text-center"></i> <span>Transporte</span>
                            </a>
                        </li>
                        <li>
                            <a href="/modules/cafeteria/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                                <i class="fa-solid fa-utensils w-4 text-center"></i> <span>Comedor Escolar</span>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- DROPDOWN: COMUNICACIÓN -->
            <li class="relative group">
                <button class="flex items-center space-x-2 px-4 py-3 hover:bg-[#2c3338] hover:text-[#72aee6] transition w-full md:w-auto text-left">
                    <i class="fa-solid fa-comments text-[#a7aaad] text-lg"></i>
                    <span>Comunicación</span>
                    <i class="fa-solid fa-chevron-down text-[10px] ml-1 text-[#a7aaad]"></i>
                </button>
                <!-- Dropdown Content -->
                <ul class="md:absolute left-0 top-full hidden group-hover:block bg-[#1d2327] border border-[#2c3338] shadow-lg min-w-[200px] z-50">
                    <li>
                        <a href="/modules/notifications/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                            <i class="fa-solid fa-bell w-4 text-center"></i> <span>Notificaciones</span>
                        </a>
                    </li>
                    <li>
                        <a href="/modules/news/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                            <i class="fa-solid fa-thumbtack w-4 text-center"></i> <span>Publicaciones</span>
                        </a>
                    </li>
                    <li>
                        <a href="/modules/messages/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                            <i class="fa-regular fa-comments w-4 text-center"></i> <span>Mensajes</span>
                        </a>
                    </li>
                    <li>
                        <a href="/modules/calendar/index.php" class="flex items-center space-x-3 px-4 py-2 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                            <i class="fa-solid fa-calendar-days w-4 text-center"></i> <span>Eventos</span>
                        </a>
                    </li>
                </ul>
            </li>

            <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR'])): ?>
            <li class="md:ml-auto">
                <a href="/modules/settings/index.php" class="flex items-center space-x-2 px-4 py-3 hover:bg-[#2c3338] hover:text-[#72aee6] transition">
                    <i class="fa-solid fa-sliders text-[#a7aaad] text-lg"></i>
                    <span class="md:hidden lg:inline">Configuración</span>
                </a>
            </li>
            <?php endif; ?>

        </ul>
    </div>
</nav>

<!-- Content Area -->
<main class="flex-1 overflow-y-auto bg-[#f0f0f1] w-full p-4 md:p-6 text-[#3c434a] min-h-[calc(100vh-65px)]">
    <div class="max-w-7xl mx-auto space-y-6">
