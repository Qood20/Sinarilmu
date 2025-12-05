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

// File konfigurasi Anda (misalnya config.php)
// Ganti dengan API key OpenRouter yang valid Anda
// Dapatkan dari: https://openrouter.ai/keys
define('OPENROUTER_API_KEY',
    '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');
define('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1');
define('OPENROUTER_DEFAULT_MODEL', 'openai/gpt-3.5-turbo');
define('OPENROUTER_TIMEOUT', 120);
?>