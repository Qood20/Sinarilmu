<?php
// admin/index.php - Dashboard admin untuk mengelola sistem

ob_start(); // Start output buffering
session_start();

// Cek apakah pengguna sudah login dan memiliki role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../?page=login');
    ob_end_clean(); // Clean the output buffer
    exit;
}

require_once '../includes/functions.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Debug: Tampilkan page yang diminta
if (defined('DEBUG') && DEBUG) {
    error_log("Admin requested page: " . $page);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sinar Ilmu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Animations CSS -->
    <style>
        @keyframes slide-in-left {
            0% {
                opacity: 0;
                transform: translateX(-20px);
            }
            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fade-in {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .animate-slide-in-left {
            animation: slide-in-left 0.5s ease-out;
        }

        .animate-fade-in {
            animation: fade-in 0.8s ease-out;
        }

        .animate-pulse {
            animation: pulse 2s ease-in-out infinite;
        }

        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }

        .transition-transform {
            transition: transform 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <div class="flex">
        <div class="w-64 bg-gradient-to-b from-gray-800 to-gray-900 shadow-xl min-h-screen animate-slide-in-left">
            <div class="p-5 border-b border-gray-700">
                <div class="flex items-center">
                    <div class="bg-red-600 p-2 rounded-lg animate-pulse">
                        <i class="fas fa-chalkboard-teacher text-white text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h1 class="text-xl font-bold text-white">Sinar Ilmu Admin</h1>
                        <p class="text-xs text-gray-400">Sistem Manajemen</p>
                    </div>
                </div>
            </div>
            <nav class="mt-6">
                <a href="?page=dashboard" class="flex items-center py-3 px-6 text-gray-300 hover:bg-red-600 hover:text-white transition duration-200 <?= $page === 'dashboard' ? 'bg-red-600 text-white border-l-4 border-white' : '' ?>">
                    <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                </a>
                <a href="?page=users" class="flex items-center py-3 px-6 text-gray-300 hover:bg-red-600 hover:text-white transition duration-200 <?= $page === 'users' ? 'bg-red-600 text-white border-l-4 border-white' : '' ?>">
                    <i class="fas fa-users mr-3"></i> Kelola Pengguna
                </a>
                <a href="?page=files" class="flex items-center py-3 px-6 text-gray-300 hover:bg-red-600 hover:text-white transition duration-200 <?= $page === 'files' ? 'bg-red-600 text-white border-l-4 border-white' : '' ?>">
                    <i class="fas fa-file-alt mr-3"></i> Kelola File
                </a>
                <a href="?page=exercises" class="flex items-center py-3 px-6 text-gray-300 hover:bg-red-600 hover:text-white transition duration-200 <?= $page === 'exercises' ? 'bg-red-600 text-white border-l-4 border-white' : '' ?>">
                    <i class="fas fa-tasks mr-3"></i> Kelola Soal
                </a>
                <a href="?page=material_content" class="flex items-center py-3 px-6 text-gray-300 hover:bg-red-600 hover:text-white transition duration-200 <?= $page === 'material_content' ? 'bg-red-600 text-white border-l-4 border-white' : '' ?>">
                    <i class="fas fa-book mr-3"></i> Kelola Materi
                </a>
                <a href="?page=content" class="flex items-center py-3 px-6 text-gray-300 hover:bg-red-600 hover:text-white transition duration-200 <?= $page === 'content' ? 'bg-red-600 text-white border-l-4 border-white' : '' ?>">
                    <i class="fas fa-edit mr-3"></i> Kelola Konten
                </a>
                <a href="?page=reports" class="flex items-center py-3 px-6 text-gray-300 hover:bg-red-600 hover:text-white transition duration-200 <?= $page === 'reports' ? 'bg-red-600 text-white border-l-4 border-white' : '' ?>">
                    <i class="fas fa-chart-bar mr-3"></i> Laporan Sistem
                </a>
                <a href="?page=settings" class="flex items-center py-3 px-6 text-gray-300 hover:bg-red-600 hover:text-white transition duration-200 <?= $page === 'settings' ? 'bg-red-600 text-white border-l-4 border-white' : '' ?>">
                    <i class="fas fa-cog mr-3"></i> Pengaturan Sistem
                </a>
                <a href="../pages/logout.php" class="flex items-center py-3 px-6 text-gray-300 hover:bg-red-600 hover:text-white transition duration-200 mt-10">
                    <i class="fas fa-sign-out-alt mr-3"></i> Keluar
                </a>
            </nav>
        </div>

        <!-- Konten Utama -->
        <div class="flex-1 p-8">
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8 flex justify-between items-center animate-fade-in delay-100">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        <?php
                        switch ($page) {
                            case 'dashboard': echo 'Dashboard Admin'; break;
                            case 'users': echo 'Kelola Pengguna'; break;
                            case 'files': echo 'Kelola File'; break;
                            case 'exercises': echo 'Kelola Soal'; break;
                            case 'material_content': echo 'Kelola Materi'; break;
                            case 'content': echo 'Kelola Konten'; break;
                            default: echo 'Dashboard Admin';
                        }
                        ?>
                    </h1>
                    <p class="text-gray-600 mt-1">Panel administrasi sistem Sinar Ilmu</p>
                </div>
                <div class="flex items-center animate-fade-in delay-200">
                    <div class="mr-4 text-right">
                        <p class="text-sm font-medium text-gray-900">Halo, <span class="font-bold text-red-600"><?php echo escape($_SESSION['full_name']); ?></span></p>
                        <p class="text-xs text-gray-600">Admin</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-red-600 rounded-full flex items-center justify-center text-white font-bold text-lg transform hover:scale-110 transition-transform">
                        <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                    </div>
                </div>
            </div>

            <?php
            switch ($page) {
                case 'dashboard':
                    include 'pages/dashboard.php';
                    break;
                case 'users':
                    include 'pages/users.php';
                    break;
                case 'files':
                    include 'pages/files.php';
                    break;
                case 'exercises':
                    include 'pages/exercises.php';
                    break;
                case 'content':
                    include 'pages/settings_content.php';
                    break;
                case 'material_content':
                    include 'pages/material_content.php';
                    break;
                case 'reports':
                    include 'pages/reports.php';
                    break;
                case 'settings':
                    include 'pages/settings.php';
                    break;
                case 'edit_user':
                    include 'pages/edit_user.php';
                    break;
                case 'add_user':
                    include 'pages/add_user.php';
                    break;
                case 'delete_user':
                    include 'process_delete_user.php';
                    break;
                case 'delete_file':
                    include 'process_delete_file.php';
                    break;
                case 'file_detail':
                    include 'pages/file_detail.php';
                    break;
                case 'file_analysis':
                    include 'pages/file_analysis.php';
                    break;
                case 'exercise_detail':
                    include 'pages/exercise_detail.php';
                    break;
                case 'delete_exercise_result':
                    include 'process_delete_exercise_result.php';
                    break;
                default:
                    include 'pages/dashboard.php';
                    break;
            }
            ?>
        </div>
    </div>
</body>
</html>