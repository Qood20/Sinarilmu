<?php
// api_check.php - File untuk mengecek status API dan sistem AI

require_once 'config/config.php';
require_once 'includes/ai_handler.php';

header('Content-Type: application/json');

try {
    $response = [
        'status' => 'success',
        'checks' => []
    ];
    
    // Cek apakah API key didefinisikan
    $hasApiKey = defined('OPENROUTER_API_KEY') && !empty(OPENROUTER_API_KEY) && strlen(OPENROUTER_API_KEY) >= 20;
    $response['checks']['api_key_defined'] = [
        'status' => $hasApiKey ? 'OK' : 'FAILED',
        'message' => $hasApiKey ? 'API Key didefinisikan dengan benar' : 'API Key tidak ditemukan atau tidak valid'
    ];
    
    if ($hasApiKey) {
        // Inisialisasi AI Handler
        $aiHandler = new AIHandler();
        
        // Cek fungsionalitas API
        $isFunctional = $aiHandler->isApiFunctional();
        $response['checks']['api_functional'] = [
            'status' => $isFunctional ? 'OK' : 'FAILED',
            'message' => $isFunctional ? 'API berfungsi dengan baik' : 'API tidak dapat diakses'
        ];
        
        if ($isFunctional) {
            // Coba kirim permintaan uji
            try {
                $testResponse = $aiHandler->sendMessage("Hanya balas dengan 'BERHASIL' jika kamu bisa diakses.", null, 50, 0.1);
                
                $response['checks']['api_test_request'] = [
                    'status' => 'OK',
                    'message' => 'Permintaan API berhasil',
                    'response_sample' => substr($testResponse, 0, 50)
                ];
            } catch (Exception $e) {
                $response['checks']['api_test_request'] = [
                    'status' => 'FAILED',
                    'message' => 'Permintaan API gagal: ' . $e->getMessage()
                ];
            }
        } else {
            $response['checks']['api_test_request'] = [
                'status' => 'SKIPPED',
                'message' => 'API tidak fungsional, tidak bisa mengirim permintaan'
            ];
        }
    } else {
        $response['checks']['api_functional'] = [
            'status' => 'SKIPPED',
            'message' => 'API Key tidak valid, tidak bisa mengecek fungsionalitas'
        ];
    }
    
    // Cek fallback system
    try {
        $fallbackResponse = $aiHandler->getFallbackResponse("Apa itu fungsi kuadrat?");
        $response['checks']['fallback_system'] = [
            'status' => !empty($fallbackResponse) ? 'OK' : 'FAILED',
            'message' => !empty($fallbackResponse) ? 'Sistem fallback berfungsi' : 'Sistem fallback tidak berfungsi'
        ];
    } catch (Exception $e) {
        $response['checks']['fallback_system'] = [
            'status' => 'FAILED',
            'message' => 'Sistem fallback error: ' . $e->getMessage()
        ];
    }
    
    // Cek extractFileContent function
    if (function_exists('extractFileContent')) {
        $response['checks']['extract_file_content_function'] = [
            'status' => 'OK',
            'message' => 'Fungsi extractFileContent tersedia'
        ];
    } else {
        $response['checks']['extract_file_content_function'] = [
            'status' => 'FAILED',
            'message' => 'Fungsi extractFileContent tidak ditemukan'
        ];
    }
    
    // Cek kesimpulan
    $allChecksPassed = true;
    foreach ($response['checks'] as $check) {
        if ($check['status'] === 'FAILED') {
            $allChecksPassed = false;
            break;
        }
    }
    
    $response['overall_status'] = $allChecksPassed ? 'SUCCESS' : 'ISSUES_FOUND';
    $response['message'] = $allChecksPassed ? 
        'Semua komponen AI berfungsi dengan baik!' : 
        'Beberapa komponen AI memiliki masalah. Harap periksa laporan di bawah.';
    
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
        'checks' => []
    ], JSON_PRETTY_PRINT);
}
?>