<?php
// config/database.php - File konfigurasi database

// Konfigurasi koneksi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Ganti sesuai dengan username database Anda
define('DB_PASS', '');      // Ganti sesuai dengan password database Anda
define('DB_NAME', 'dbsinarilmu');

// Membuat koneksi ke database
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5, // Timeout 5 detik
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
    ]);
} catch(PDOException $e) {
    // Log error ke file
    error_log("Koneksi database gagal: " . $e->getMessage());
    // Jangan matikan aplikasi sepenuhnya, tetapi tandai bahwa koneksi gagal
    $pdo = null;
    if (strpos($e->getMessage(), 'Connection refused') !== false ||
        strpos($e->getMessage(), 'Access denied') !== false ||
        strpos($e->getMessage(), 'Unknown database') !== false) {
        // Hanya untuk error koneksi spesifik, kita bisa simpan info error
        $db_error = $e->getMessage();
    }
}