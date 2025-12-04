<?php
// verify_api_integration.php - File untuk memverifikasi integrasi OpenRouter API

require_once 'config/config.php';
require_once 'includes/ai_handler.php';

echo "<h2>Verifikasi Integrasi OpenRouter.ai</h2>\n";

// Tampilkan informasi konfigurasi
echo "<h3>Informasi Konfigurasi:</h3>\n";
echo "OPENROUTER_API_KEY: " . (defined('OPENROUTER_API_KEY') ? (strlen(OPENROUTER_API_KEY) > 10 ? 'Terdefinisi (' . strlen(OPENROUTER_API_KEY) . ' karakter)' : 'Terlalu pendek atau tidak valid') : 'Tidak ditemukan') . "<br>\n";
echo "OPENROUTER_BASE_URL: " . (defined('OPENROUTER_BASE_URL') ? OPENROUTER_BASE_URL : 'Tidak ditemukan') . "<br>\n";
echo "OPENROUTER_DEFAULT_MODEL: " . (defined('OPENROUTER_DEFAULT_MODEL') ? OPENROUTER_DEFAULT_MODEL : 'Tidak ditemukan') . "<br>\n";
echo "OPENROUTER_TIMEOUT: " . (defined('OPENROUTER_TIMEOUT') ? OPENROUTER_TIMEOUT : 'Tidak ditemukan') . "<br>\n";

echo "<h3>Menguji Fungsi AI Handler:</h3>\n";

try {
    $aiHandler = new AIHandler();
    echo "✅ AI Handler berhasil diinisialisasi<br>\n";
    
    // Coba test koneksi
    if ($aiHandler->isApiFunctional()) {
        echo "✅ Koneksi API berfungsi<br>\n";
        
        // Coba kirim permintaan sederhana
        try {
            $testPrompt = "Hanya jawab dengan 'API berfungsi dengan baik' jika kamu menerima pesan ini.";
            $response = $aiHandler->sendMessage($testPrompt, null, 100, 0.1);
            echo "✅ Permintaan API berhasil dikirim dan diterima<br>\n";
            echo "<strong>Respons AI:</strong> " . htmlspecialchars(substr($response, 0, 100)) . (strlen($response) > 100 ? '...' : '') . "<br>\n";
        } catch (Exception $e) {
            echo "❌ Gagal mengirim permintaan API: " . $e->getMessage() . "<br>\n";
        }
    } else {
        echo "❌ Koneksi API tidak berfungsi<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Gagal menginisialisasi AI Handler: " . $e->getMessage() . "<br>\n";
}

echo "<h3>Ringkasan:</h3>\n";
echo "Jika semua langkah di atas sukses (✅), maka integrasi OpenRouter.ai berfungsi dengan baik.<br>\n";
echo "Jika ada langkah yang gagal (❌), maka perlu diperiksa kembali konfigurasi API.<br>\n";
?>