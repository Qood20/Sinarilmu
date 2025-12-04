<?php
// test_api_connection.php - File untuk menguji koneksi API

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

    echo json_encode([
        'status' => 'info',
        'message' => 'API key ditemukan di konfigurasi',
        'api_key_length' => strlen(OPENROUTER_API_KEY)
    ]);

    // Coba test koneksi ke API
    $aiHandler = new AIHandler();

    // Cek apakah API fungsional
    $reflection = new ReflectionClass('AIHandler');
    $method = $reflection->getMethod('testApiConnection');
    $method->setAccessible(true);
    $isFunctional = $method->invoke($aiHandler);

    if ($isFunctional) {
        echo json_encode([
            'status' => 'success',
            'message' => 'API berfungsi dengan baik',
            'functional' => true
        ]);
        
        // Lakukan uji coba kirim pesan sederhana
        try {
            $testPrompt = "Hanya respon dengan 'API berfungsi' jika kamu bisa diakses.";
            $response = $aiHandler->sendMessage($testPrompt, null, 100, 0.1);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Uji kirim pesan berhasil',
                'response_preview' => substr($response, 0, 100),
                'full_response' => $response
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Uji kirim pesan gagal: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'API tidak dapat diakses',
            'functional' => false
        ]);
    }

} catch (Exception $e) {
    $errorMessage = $e->getMessage();

    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan saat menginisialisasi AI Handler',
        'error' => $errorMessage
    ]);
}
?>