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
<body class="h-full text-[#3c434a] bg-[#f0f0f1]">
    <!-- WP Admin Bar -->
    <div id="wpadminbar" class="w-full h-8 bg-[#1d2327] flex items-center justify-between px-3 text-[#f0f0f1] text-[13px] sticky top-0 z-50">
        <div class="flex items-center space-x-4">
            <a href="/" class="hover:text-[#72aee6] transition flex items-center space-x-1">
                <i class="fa-brands fa-wordpress"></i>
            </a>
            <a href="/" class="hover:text-[#72aee6] transition flex items-center space-x-1">
                <i class="fa-solid fa-house text-[10px]"></i>
                <span class="font-medium"><?= htmlspecialchars($dbSettings['app_name']) ?></span>
            </a>
            <a href="/modules/news/index.php" class="hover:text-[#72aee6] transition flex items-center space-x-1">
                <i class="fa-solid fa-plus text-[10px]"></i>
                <span class="font-medium">New</span>
            </a>
        </div>
        <div class="flex items-center space-x-4">
            <div class="group relative flex items-center h-8 cursor-pointer">
                <span class="hover:text-[#72aee6] transition flex items-center space-x-2">
                    <span>Howdy, <?= htmlspecialchars($currentUser['first_name']) ?></span>
                    <div class="w-5 h-5 bg-[#646970] rounded-sm flex items-center justify-center text-[10px]">
                        <?= strtoupper(substr($currentUser['first_name'], 0, 1)) ?>
                    </div>
                </span>
                <!-- Dropdown -->
                <div class="absolute right-0 top-8 bg-[#1d2327] border border-[#2c3338] shadow-lg hidden group-hover:block min-w-[200px]">
                    <a href="/auth/logout.php" class="block px-4 py-2 text-[#a7aaad] hover:text-[#72aee6] hover:bg-[#2c3338] transition">Log Out</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Top Header for Mobile only -->
    <!-- Mobile Header (Hidden in WP desktop) -->
    <div class="md:hidden bg-[#1d2327] p-3 flex items-center justify-between sticky top-8 z-40 border-b border-[#2c3338]">
        <div class="flex items-center space-x-3 text-white">
            <h1 class="font-medium text-sm"><?= htmlspecialchars($dbSettings['app_name']) ?></h1>
        </div>
        <button id="mobile-menu-toggle" class="text-[#a7aaad] hover:text-white focus:outline-none p-1 transition">
            <i class="fa-solid fa-bars text-xl"></i>
        </button>
    </div>

    <div class="min-h-full flex flex-col">
