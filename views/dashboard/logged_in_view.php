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
            </div>
        </div>
    </div>

    <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])): ?>
        <!-- KPI Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Estudiantes -->
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center space-x-4">
                <div class="p-3 bg-emerald-50 text-emerald-500 rounded-2xl text-xl">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Estudiantes</span>
                    <span class="text-2xl font-bold text-slate-800"><?= $totalStudents ?></span>
                </div>
            </div>

            <!-- Docentes -->
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center space-x-4">
                <div class="p-3 bg-blue-50 text-blue-500 rounded-2xl text-xl">
                    <i class="fa-solid fa-chalkboard-teacher"></i>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Docentes</span>
                    <span class="text-2xl font-bold text-slate-800"><?= $totalTeachers ?></span>
                </div>
            </div>

            <!-- Promedio Académico -->
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center space-x-4">
                <div class="p-3 bg-amber-50 text-amber-500 rounded-2xl text-xl">
                    <i class="fa-solid fa-star"></i>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Promedio General</span>
                    <span class="text-2xl font-bold text-slate-800"><?= $averageGrade ?></span>
                </div>
            </div>

            <!-- Pagos Pendientes -->
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center space-x-4">
                <div class="p-3 bg-rose-50 text-rose-500 rounded-2xl text-xl">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Cartera Pendiente</span>
                    <span class="text-2xl font-bold text-slate-800">$<?= number_format($totalDebts, 2) ?></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Panel Inferior: Notificaciones Recientes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 h-full">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-slate-800 text-lg">Últimas Comunicaciones</h3>
                <a href="/modules/notifications/index.php" class="text-xs font-bold text-emerald-600 hover:text-emerald-500 uppercase tracking-wider">Ver Todas</a>
            </div>
            
            <div class="space-y-4">
                <?php if (empty($notifications)): ?>
                    <div class="text-center py-6 text-slate-400">
                        <p class="text-sm">No hay notificaciones recientes.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notif): ?>
                        <div class="flex space-x-4 p-4 rounded-2xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100">
                            <div class="w-10 h-10 rounded-full flex-shrink-0 bg-slate-100 text-slate-500 flex items-center justify-center font-bold">
                                <?= strtoupper(substr($notif['first_name'], 0, 1) . substr($notif['last_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800"><?= htmlspecialchars($notif['title']) ?></h4>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2"><?= htmlspecialchars($notif['message']) ?></p>
                                <span class="text-[10px] font-bold text-emerald-600 uppercase mt-2 block"><?= date('d M, h:i A', strtotime($notif['created_at'])) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])): ?>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 h-full flex flex-col">
                <h3 class="font-bold text-slate-800 text-lg mb-4 flex items-center">
                    <i class="fa-solid fa-chart-column text-indigo-500 mr-2"></i> Distribución de Estudiantes por Curso
                </h3>
                <div class="flex-1 w-full relative" style="min-height: 250px;">
                    <canvas id="studentsChart"></canvas>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php if (Auth::hasRole(['ADMIN', 'DIRECTOR', 'COORDINADOR'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('studentsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= $chartLabels ?? '[]' ?>,
                datasets: [{
                    label: 'Estudiantes',
                    data: <?= $chartData ?? '[]' ?>,
                    backgroundColor: '#10b981',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    });
</script>
<?php endif; ?>
