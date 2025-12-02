<?php
// File debug sederhana untuk menguji koneksi API

require_once 'config/config.php';
require_once 'includes/ai_handler.php';

echo "<pre>";
echo "Menguji koneksi API...\n";

try {
    $aiHandler = new AIHandler();
    echo "AIHandler berhasil dibuat\n";

    // Test dengan prompt sederhana
    $testPrompt = "Hanya jawab 'OK' jika kamu bisa terhubung.";

    echo "Mengirim permintaan ke API...\n";
    $result = $aiHandler->sendMessage($testPrompt);

    echo "SUCCESS: " . $result . "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";

    // Jika gagal, coba cek konfigurasi
    echo "\nMengecek konfigurasi API:\n";
    echo "OPENROUTER_API_KEY defined: " . (defined('OPENROUTER_API_KEY') ? 'YES' : 'NO') . "\n";
    if (defined('OPENROUTER_API_KEY')) {
        echo "API Key length: " . strlen(OPENROUTER_API_KEY) . "\n";
        echo "API Key starts with: " . substr(OPENROUTER_API_KEY, 0, 8) . "...\n";
    }
    echo "OPENROUTER_BASE_URL: " . (defined('OPENROUTER_BASE_URL') ? OPENROUTER_BASE_URL : 'NOT DEFINED') . "\n";
    echo "OPENROUTER_DEFAULT_MODEL: " . (defined('OPENROUTER_DEFAULT_MODEL') ? OPENROUTER_DEFAULT_MODEL : 'NOT DEFINED') . "\n";

    // Coba test koneksi dasar ke OpenRouter
    echo "\nMencoba koneksi ke OpenRouter...\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://openrouter.ai/api/v1/models");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . OPENROUTER_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    echo "HTTP Code: $http_code\n";
    if ($error) {
        echo "Curl Error: $error\n";
    } else {
        echo "Response: " . substr($response, 0, 200) . "...\n";
    }
    curl_close($ch);
}

echo "</pre>";
?>