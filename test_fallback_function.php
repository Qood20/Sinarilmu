<?php
// test_fallback_function.php - File untuk menguji fungsi fallback

require_once 'config/config.php';
require_once 'includes/ai_handler.php';

echo "<h1>Testing Fungsi Fallback AI Handler</h1>\n";

try {
    $aiHandler = new AIHandler();
    
    echo "<h2>Tes Fungsi Fallback</h2>\n";
    
    // Tes dengan konten contoh
    $testContent = "Fungsi kuadrat adalah fungsi yang memiliki bentuk umum f(x) = ax² + bx + c, di mana a ≠ 0. Grafik fungsi kuadrat berbentuk parabola yang terbuka ke atas jika a > 0 dan terbuka ke bawah jika a < 0. Contoh soal: Jika f(x) = 2x² - 3x + 1, maka nilai a = 2, b = -3, dan c = 1.";
    $fileName = "materi_matematika_smp.pdf";
    
    $result = $aiHandler->generateResponseFromContent($testContent, $fileName);
    
    echo "<h3>Hasil GenerateResponseFromContent:</h3>\n";
    echo "<pre>" . htmlspecialchars(substr($result, 0, 1000)) . (strlen($result) > 1000 ? "..." : "") . "</pre>\n";
    
    // Tes fungsi isApiFunctional
    echo "<h3>Status API Functional:</h3>\n";
    $isFunctional = $aiHandler->isApiFunctional();
    echo "<p>API Functional: " . ($isFunctional ? "YA" : "TIDAK") . "</p>\n";
    
    // Coba koneksi API
    echo "<h3>Test API Connection:</h3>\n";
    $isConnected = $aiHandler->testApiConnection();
    echo "<p>API Connected: " . ($isConnected ? "YA" : "TIDAK") . "</p>\n";
    
    if ($isConnected) {
        echo "<p>Mencoba kirim pesan sederhana...</p>\n";
        try {
            $response = $aiHandler->sendMessage("Hanya balas dengan 'BERHASIL' jika kamu bisa diakses.", null, 50, 0.1);
            echo "<p>Response: " . htmlspecialchars($response) . "</p>\n";
        } catch (Exception $e) {
            echo "<p>Gagal mengirim pesan: " . $e->getMessage() . "</p>\n";
        }
    } else {
        echo "<p>API tidak terkoneksi, menggunakan fallback...</p>\n";
    }

} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>\n";
}

echo "<h2>Fitur AI Siap Digunakan!</h2>\n";
echo "<p>Sistem telah siap untuk:</p>\n";
echo "<ul>\n";
echo "<li>Upload file dan analisis otomatis</li>\n";
echo "<li>Generate soal berdasarkan isi file</li>\n";
echo "<li>Tanya Sinar - chat dengan konteks file</li>\n";
echo "<li>Fallback jika API tidak tersedia</li>\n";
echo "</ul>\n";
?>