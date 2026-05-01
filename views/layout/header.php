<?php
// /views/layout/header.php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

Auth::redirectIfNotAuth();
$currentUser = Auth::user();

// Fetch settings for dynamic branding
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
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($dbSettings['app_name']) ?> - ERP GESTIÓN TOTAL</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        @media (max-width: 767px) {
            #sidebar-menu.show {
                display: flex !important;
                position: fixed;
                top: 65px;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 40;
                width: 100%;
                height: calc(100vh - 65px);
                overflow-y: auto;
                background-color: rgb(15 23 42);
            }
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brandPrimary: '<?= $dbSettings["color_primary"] ?>',
                        brandSecondary: '<?= $dbSettings["color_secondary"] ?>'
                    }
                }
            }
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('mobile-menu-toggle');
            const sidebar = document.getElementById('sidebar-menu');
            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('hidden');
                    sidebar.classList.toggle('show');
                    const icon = toggleBtn.querySelector('i');
                    if (sidebar.classList.contains('show')) {
                        icon.className = 'fa-solid fa-xmark text-xl';
                    } else {
                        icon.className = 'fa-solid fa-bars text-xl';
                    }
                });
            }
        });
    </script>
</head>
<body class="h-full text-slate-800">
    <!-- Top Header for Mobile only -->
    <div class="md:hidden bg-slate-900 border-b border-slate-800 p-4 flex items-center justify-between sticky top-0 z-50">
        <div class="flex items-center space-x-3">
            <?php if (!empty($dbSettings['logo_url'])): ?>
                <div class="w-9 h-9 overflow-hidden rounded-xl flex items-center justify-center bg-white border border-slate-700/50">
                    <img src="<?= htmlspecialchars($dbSettings['logo_url']) ?>" alt="Logo" class="w-full h-full object-cover">
                </div>
            <?php else: ?>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg" style="background-color: <?= $dbSettings['color_primary'] ?>;">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
            <?php endif; ?>
            <div>
                <h1 class="text-white font-bold tracking-wider leading-tight text-sm"><?= htmlspecialchars($dbSettings['app_name']) ?></h1>
                <span class="text-[10px] font-semibold tracking-widest uppercase" style="color: <?= $dbSettings['color_secondary'] ?>;">ERP EDUCATIVO</span>
            </div>
        </div>
        <button id="mobile-menu-toggle" class="text-slate-300 hover:text-white focus:outline-none p-2 rounded-xl bg-slate-800 border border-slate-700/50 transition">
            <i class="fa-solid fa-bars text-xl"></i>
        </button>
    </div>

    <div class="min-h-full flex flex-col md:flex-row">
