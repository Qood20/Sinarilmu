<?php
// debug_login.php - File untuk debugging masalah login

session_start();
require_once 'includes/functions.php';

echo "<h3>Debug Informasi Login</h3>";
echo "<p>Session status: " . (session_status() === PHP_SESSION_ACTIVE ? "Active" : "Not Active") . "</p>";

// Coba query sederhana untuk memastikan koneksi database berfungsi
global $pdo;

try {
    $result = $pdo->query("SELECT id, full_name, email FROM users LIMIT 5");
    $users = $result->fetchAll();
    
    echo "<h3>Data Pengguna dalam Database:</h3>";
    foreach ($users as $user) {
        echo "<p>ID: " . $user['id'] . " - Nama: " . htmlspecialchars($user['full_name']) . " - Email: " . htmlspecialchars($user['email']) . "</p>";
    }
} catch (Exception $e) {
    echo "<p>Error mengakses database: " . $e->getMessage() . "</p>";
}

echo "<h3>Session Variables:</h3>";
foreach ($_SESSION as $key => $value) {
    echo "<p>\$_SESSION['" . $key . "'] = " . (is_string($value) ? htmlspecialchars($value) : print_r($value, true)) . "</p>";
}
?>
<br>
<a href="pages/logout.php">Logout</a> | <a href="index.php">Kembali ke Home</a>