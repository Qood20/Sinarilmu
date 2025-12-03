<?php
// test_api_connection.php - File untuk mengetes koneksi API

require_once 'config/config.php';
require_once 'includes/ai_handler.php';

header('Content-Type: application/json');

try {
    if (!defined('OPENROUTER_API_KEY') || empty(OPENROUTER_API_KEY)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'API key tidak ditemukan atau kosong di konfigurasi'
        ]);
        exit;
    }
    
    // Coba test koneksi ke API
    $aiHandler = new AIHandler();
    
    // Test sederhana untuk melihat apakah API bisa diakses
    $testPrompt = "Hanya respon dengan 'API berfungsi' jika kamu bisa diakses.";
    
    $response = $aiHandler->sendMessage($testPrompt, null, 100, 0.1);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'API berfungsi dengan baik',
        'response' => $response
    ]);
    
} catch (Exception $e) {
    $errorMessage = $e->getMessage();
    
    // Cek apakah ini error otentikasi
    if (strpos($errorMessage, '401') !== false || strpos($errorMessage, 'authentication') !== false || strpos($errorMessage, 'invalid API') !== false) {
        echo json_encode([
            'status' => 'error',
            'message' => 'API key tidak valid (otentikasi gagal)',
            'error' => $errorMessage
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan saat mengakses API',
            'error' => $errorMessage
        ]);
    }
}
?>