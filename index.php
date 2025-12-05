<?php
// Halaman utama sebelum login
ob_start(); // Start output buffering
session_start();

// Jika pengguna sudah login, arahkan ke dashboard yang sesuai dengan role
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header('Location: admin/');
    } else {
        header('Location: dashboard/');
    }
    ob_end_clean(); // Clean the output buffer
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinar Ilmu - Aplikasi Belajar Berbasis AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-blue-600">Sinar Ilmu</h1>
                </div>
                <nav class="flex items-center space-x-4">
                    <a href="?page=home" class="px-3 py-2 rounded-md text-sm font-medium <?= $page === 'home' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100' ?>">Home</a>
                    <a href="?page=about" class="px-3 py-2 rounded-md text-sm font-medium <?= $page === 'about' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100' ?>">Tentang</a>
                    <a href="?page=contact" class="px-3 py-2 rounded-md text-sm font-medium <?= $page === 'contact' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100' ?>">Kontak</a>
                    <a href="?page=login" class="px-3 py-2 rounded-md text-sm font-medium <?= $page === 'login' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100' ?>">Login</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Konten Utama -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php
        switch ($page) {
            case 'home':
                include 'pages/home.php';
                break;
            case 'about':
                include 'pages/about.php';
                break;
            case 'contact':
                include 'pages/contact.php';
                break;
            case 'login':
                include 'pages/login.php';
                break;
            case 'register':
                include 'pages/register.php';
                break;
            case 'forgot_password':
                include 'pages/forgot_password.php';
                break;
            case 'reset_password':
                include 'pages/reset_password.php';
                break;
            default:
                include 'pages/home.php';
                break;
        }
        ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="md:flex md:justify-between">
                <div class="mb-6 md:mb-0">
                    <h2 class="text-lg font-semibold text-gray-900">Sinar Ilmu</h2>
                    <p class="mt-2 text-sm text-gray-600">Aplikasi belajar berbasis kecerdasan buatan</p>
                </div>
                <div class="grid grid-cols-2 gap-8 sm:grid-cols-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Halaman</h3>
                        <ul class="mt-4 space-y-2">
                            <li><a href="?page=home" class="text-sm text-gray-600 hover:text-gray-900">Home</a></li>
                            <li><a href="?page=about" class="text-sm text-gray-600 hover:text-gray-900">Tentang</a></li>
                            <li><a href="?page=contact" class="text-sm text-gray-600 hover:text-gray-900">Kontak</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Layanan</h3>
                        <ul class="mt-4 space-y-2">
                            <li><a href="#" class="text-sm text-gray-600 hover:text-gray-900">Analisis AI</a></li>
                            <li><a href="#" class="text-sm text-gray-600 hover:text-gray-900">Latihan Soal</a></li>
                            <li><a href="#" class="text-sm text-gray-600 hover:text-gray-900">Chat AI</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="mt-8 border-t border-gray-200 pt-8 md:flex md:items-center md:justify-between">
                <div class="flex space-x-6 md:order-2">
                    <!-- Social media icons can go here -->
                </div>
                <p class="mt-8 text-sm text-gray-600 md:mt-0 md:order-1">
                    &copy; 2025 Sinar Ilmu. Hak Cipta Dilindungi.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>