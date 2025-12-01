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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sinar Ilmu</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <div class="flex">
        <div class="w-64 bg-white shadow-md min-h-screen">
            <div class="p-4">
                <h1 class="text-xl font-bold text-red-600">Dashboard Admin</h1>
                <p class="text-sm text-gray-600 mt-1">Sinar Ilmu - Sistem Manajemen</p>
            </div>
            <nav class="mt-6">
                <a href="?page=dashboard" class="block py-3 px-6 text-gray-700 hover:bg-red-50 hover:text-red-600 <?= $page === 'dashboard' ? 'bg-red-100 text-red-600 border-r-2 border-red-600' : '' ?>">
                    <i class="mr-2">📊</i> Dashboard
                </a>
                <a href="?page=users" class="block py-3 px-6 text-gray-700 hover:bg-red-50 hover:text-red-600 <?= $page === 'users' ? 'bg-red-100 text-red-600 border-r-2 border-red-600' : '' ?>">
                    <i class="mr-2">👥</i> Kelola Pengguna
                </a>
                <a href="?page=files" class="block py-3 px-6 text-gray-700 hover:bg-red-50 hover:text-red-600 <?= $page === 'files' ? 'bg-red-100 text-red-600 border-r-2 border-red-600' : '' ?>">
                    <i class="mr-2">📁</i> Kelola File
                </a>
                <a href="?page=exercises" class="block py-3 px-6 text-gray-700 hover:bg-red-50 hover:text-red-600 <?= $page === 'exercises' ? 'bg-red-100 text-red-600 border-r-2 border-red-600' : '' ?>">
                    <i class="mr-2">✏️</i> Kelola Soal
                </a>
                <a href="?page=content" class="block py-3 px-6 text-gray-700 hover:bg-red-50 hover:text-red-600 <?= $page === 'content' ? 'bg-red-100 text-red-600 border-r-2 border-red-600' : '' ?>">
                    <i class="mr-2">📝</i> Kelola Konten
                </a>
                <a href="?page=reports" class="block py-3 px-6 text-gray-700 hover:bg-red-50 hover:text-red-600 <?= $page === 'reports' ? 'bg-red-100 text-red-600 border-r-2 border-red-600' : '' ?>">
                    <i class="mr-2">📈</i> Laporan Sistem
                </a>
                <a href="?page=settings" class="block py-3 px-6 text-gray-700 hover:bg-red-50 hover:text-red-600 <?= $page === 'settings' ? 'bg-red-100 text-red-600 border-r-2 border-red-600' : '' ?>">
                    <i class="mr-2">⚙️</i> Pengaturan Sistem
                </a>
                <a href="../pages/logout.php" class="block py-3 px-6 text-gray-700 hover:bg-red-50 hover:text-red-600 mt-10">
                    <i class="mr-2">🚪</i> Keluar
                </a>
            </nav>
        </div>
        
        <!-- Konten Utama -->
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800">
                    <?php
                    switch ($page) {
                        case 'dashboard': echo 'Dashboard Admin'; break;
                        case 'users': echo 'Kelola Pengguna'; break;
                        case 'files': echo 'Kelola File'; break;
                        case 'exercises': echo 'Kelola Soal'; break;
                        case 'content': echo 'Kelola Konten'; break;
                        default: echo 'Dashboard Admin';
                    }
                    ?>
                </h1>
                <div class="flex items-center">
                    <span class="mr-4">Halo, <span class="font-bold text-red-600"><?php echo escape($_SESSION['full_name']); ?></span>! (Admin)</span>
                    <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center text-white font-bold">
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