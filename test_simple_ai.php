<?php
// test_simple_ai.php - File sederhana untuk menguji koneksi AI tanpa database

require_once 'config/api_config.php';
require_once 'includes/ai_handler.php';

// Aktifkan output error untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Menguji Koneksi Google AI API</h2>\n";

try {
    $aiHandler = new AIHandler();
    
    // Tes dengan prompt sederhana
    $prompt = "Jelaskan manfaat belajar secara singkat dalam bahasa Indonesia.";
    
    echo "<p><strong>Prompt:</strong> " . htmlspecialchars($prompt) . "</p>\n";
    echo "<p>Menghubungi Google AI API...</p>\n";
    
    $result = $aiHandler->generateContent($prompt);
    
    if (isset($result['error'])) {
        echo "<p style='color: red;'><strong>Error:</strong> " . htmlspecialchars($result['error']) . "</p>\n";
    } else {
        echo "<p style='color: green;'><strong>Respons berhasil diterima dari AI!</strong></p>\n";
        
        if (isset($result['candidates']) && count($result['candidates']) > 0) {
            $candidate = $result['candidates'][0];
            if (isset($candidate['content']['parts']) && count($candidate['content']['parts']) > 0) {
                $text = $candidate['content']['parts'][0]['text'];
                echo "<h3>Respons dari AI:</h3>\n";
                echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>" . nl2br(htmlspecialchars($text)) . "</div>\n";
            } else {
                echo "<p><strong>Struktur respons tidak terduga:</strong> " . htmlspecialchars(print_r($candidate, true)) . "</p>\n";
            }
        } else {
            echo "<p><strong>Respons tidak memiliki candidates:</strong> " . htmlspecialchars(print_r($result, true)) . "</p>\n";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Exception:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

echo "<h3>Catatan:</h3>";
echo "<p>Jika Anda melihat respons fallback, ini berarti koneksi ke Google AI API gagal dan sistem menggunakan respons cadangan.</p>";
echo "<p>Untuk menggunakan sistem sepenuhnya, pastikan MySQL dan Apache berjalan, dan API key Anda valid.</p>";
?>