<?php
// test_routing.php - File untuk menguji routing sistem

// Mulai sesi jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../?page=login');
    exit;
}

require_once '../includes/functions.php';

// Debug: cetak halaman yang diminta
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
echo "<h2>Debug Information</h2>";
echo "<p>Page requested: " . htmlspecialchars($page) . "</p>";
echo "<p>Session status: " . session_status() . "</p>";
echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
echo "<p>Role: " . ($_SESSION['role'] ?? 'Not set') . "</p>";

// Coba include file yang diminta
$file_path = 'pages/' . $page . '.php';

if (file_exists($file_path)) {
    echo "<p>File exists: $file_path</p>";
    echo "<p>Include result: Attempting to include...</p>";
    include $file_path;
} else {
    echo "<p>File does not exist: $file_path</p>";
    
    // List semua file di direktori pages
    echo "<h3>Files in pages directory:</h3>";
    $files = glob('pages/*.php');
    foreach ($files as $file) {
        echo "<p>" . basename($file) . "</p>";
    }
}
?>