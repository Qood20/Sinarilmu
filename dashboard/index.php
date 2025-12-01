<?php
// dashboard/index.php - Dashboard utama untuk pengguna setelah login

ob_start(); // Start output buffering
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../?page=login');
    ob_end_clean(); // Clean the output buffer
    exit;
}

require_once '../includes/functions.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sinar Ilmu</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <div class="flex">
        <div class="w-64 bg-white shadow-md min-h-screen">
            <div class="p-4">
                <h1 class="text-xl font-bold text-blue-600">Sinar Ilmu</h1>
            </div>
            <nav class="mt-6">
                <a href="?page=home" class="block py-3 px-6 text-gray-700 hover:bg-blue-50 hover:text-blue-600 <?= $page === 'home' ? 'bg-blue-100 text-blue-600 border-r-2 border-blue-600' : '' ?>">
                    <i class="mr-2">🏠</i> Beranda
                </a>
                <a href="?page=upload" class="block py-3 px-6 text-gray-700 hover:bg-blue-50 hover:text-blue-600 <?= $page === 'upload' ? 'bg-blue-100 text-blue-600 border-r-2 border-blue-600' : '' ?>">
                    <i class="mr-2">📤</i> Unggah File
                </a>
                <a href="?page=exercises" class="block py-3 px-6 text-gray-700 hover:bg-blue-50 hover:text-blue-600 <?= $page === 'exercises' ? 'bg-blue-100 text-blue-600 border-r-2 border-blue-600' : '' ?>">
                    <i class="mr-2">✏️</i> Latihan Soal
                </a>
                <a href="?page=analisis_materi" class="block py-3 px-6 text-gray-700 hover:bg-blue-50 hover:text-blue-600 <?= $page === 'analisis_materi' ? 'bg-blue-100 text-blue-600 border-r-2 border-blue-600' : '' ?>">
                    <i class="mr-2">📚</i> Penjabaran Materi
                </a>
                <a href="?page=chat" class="block py-3 px-6 text-gray-700 hover:bg-blue-50 hover:text-blue-600 <?= $page === 'chat' ? 'bg-blue-100 text-blue-600 border-r-2 border-blue-600' : '' ?>">
                    <i class="mr-2">💬</i> Tanya Sinar
                </a>
                <a href="?page=profile" class="block py-3 px-6 text-gray-700 hover:bg-blue-50 hover:text-blue-600 <?= $page === 'profile' ? 'bg-blue-100 text-blue-600 border-r-2 border-blue-600' : '' ?>">
                    <i class="mr-2">👤</i> Profil Saya
                </a>
                <a href="../pages/logout.php" class="block py-3 px-6 text-gray-700 hover:bg-blue-50 hover:text-blue-600 mt-10">
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
                        case 'home': echo 'Beranda'; break;
                        case 'upload': echo 'Unggah File & Analisis AI'; break;
                        case 'exercises': echo 'Latihan Soal'; break;
                        case 'chat': echo 'Tanya Sinar'; break;
                        case 'profile': echo 'Profil Saya'; break;
                        default: echo 'Beranda';
                    }
                    ?>
                </h1>
                <div class="flex items-center">
                    <span class="mr-4">Halo, <?php echo escape($_SESSION['full_name']); ?>!</span>
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                        <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                    </div>
                </div>
            </div>
            
            <?php
            switch ($page) {
                case 'home':
                    include 'pages/home.php';
                    break;
                case 'upload':
                    include 'pages/upload.php';
                    break;
                case 'exercises':
                    include 'pages/exercises.php';
                    break;
                case 'analisis_materi':
                    include 'pages/analisis_materi.php';
                    break;
                case 'chat':
                    include 'pages/chat.php';
                    break;
                case 'profile':
                    include 'pages/profile.php';
                    break;
                default:
                    include 'pages/home.php';
                    break;
            }
            ?>
        </div>
    </div>
</body>
</html>