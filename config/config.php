<?php
// config/config.php - File konfigurasi aplikasi

// Atur timezone
date_default_timezone_set('Asia/Jakarta');

// Mode debug (ubah ke false untuk produksi)
define('DEBUG', true);

// Atur error reporting
if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
}

// Konstanta aplikasi
define('APP_NAME', 'Sinar Ilmu');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']));