<?php
// debug_api.php - File untuk debug koneksi API

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php';
require_once 'includes/ai_handler.php';

// Fungsi untuk mengecek API key
function check_api_key() {
    if (!defined('OPENROUTER_API_KEY')) {
        echo "ERROR: OPENROUTER_API_KEY tidak ditemukan di config<br>";
        return false;
    }

    $apiKey = OPENROUTER_API_KEY;
    if (empty($apiKey)) {
        echo "ERROR: OPENROUTER_API_KEY kosong<br>";
        return false;
    }

    if (strlen($apiKey) < 20) { // API key OpenRouter biasanya panjang
        echo "WARNING: OPENROUTER_API_KEY mungkin tidak valid (terlalu pendek)<br>";
        echo "Panjang API key: " . strlen($apiKey) . " karakter<br>";
    } else {
        // Tampilkan beberapa karakter pertama dan terakhir untuk identifikasi
        $displayKey = substr($apiKey, 0, 10) . '...' . substr($apiKey, -5);
        echo "API Key ditemukan, panjang: " . strlen($apiKey) . " karakter<br>";
        echo "API Key (sebagian): " . $displayKey . "<br>";
    }

    return true;
}

// Fungsi untuk test koneksi ke API
function test_api_connection() {
    if (!defined('OPENROUTER_API_KEY')) {
        echo "Tidak dapat melakukan test koneksi - API key tidak ditemukan<br>";
        return;
    }

    $apiKey = OPENROUTER_API_KEY;
    $baseUrl = OPENROUTER_BASE_URL ?? 'https://openrouter.ai/api/v1';

    // Coba fetch info pengguna
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => $baseUrl . '/user',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'SinarIlmu/1.0'
    ]);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
        echo "CURL Error: " . $error . "<br>";
        return;
    }

    echo "HTTP Code: " . $http_code . "<br>";
    echo "Response: " . $response . "<br>";

    if ($http_code === 200) {
        echo "Koneksi API berhasil! Akun ditemukan.<br>";
    } elseif ($http_code === 401) {
        echo "Koneksi API gagal - otentikasi tidak valid (401 Unauthorized)<br>";
    } else {
        echo "Koneksi API gagal dengan kode: " . $http_code . "<br>";
    }
}

echo "<h3>Debug API OpenRouter</h3>";

echo "<h4>1. Pengecekan API Key:</h4>";
check_api_key();

echo "<h4>2. Test Koneksi API:</h4>";
test_api_connection();

echo "<h4>3. Info Konfigurasi:</h4>";
echo "BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'Tidak ditemukan') . "<br>";
echo "OPENROUTER_BASE_URL: " . (defined('OPENROUTER_BASE_URL') ? OPENROUTER_BASE_URL : 'Tidak ditemukan') . "<br>";
echo "OPENROUTER_DEFAULT_MODEL: " . (defined('OPENROUTER_DEFAULT_MODEL') ? OPENROUTER_DEFAULT_MODEL : 'Tidak ditemukan') . "<br>";
?>