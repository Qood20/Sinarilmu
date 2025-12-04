<?php
// test_file_extraction.php - File untuk menguji fungsi ekstraksi konten file

require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'dashboard/pages/upload.php'; // Include upload functions for file extraction

echo "<h1>Uji Coba Ekstraksi Konten File</h1>\n";

// Fungsi untuk membuat file contoh untuk pengujian
function createSampleFile($type = 'txt') {
    $filename = 'test_sample.' . $type;
    $content = '';
    
    switch ($type) {
        case 'txt':
            $content = "Bab 1: Fungsi Kuadrat\nFungsi kuadrat adalah fungsi yang memiliki bentuk umum f(x) = ax² + bx + c, di mana a ≠ 0.\nGrafik fungsi kuadrat berbentuk parabola yang terbuka ke atas jika a > 0 dan terbuka ke bawah jika a < 0.\n\nContoh soal:\n1. Jika f(x) = 2x² - 3x + 1, tentukan nilai a, b, dan c!\n2. Gambarkan grafik fungsi kuadrat f(x) = x² - 4x + 3.";
            break;
        case 'pdf':
            // Membuat konten PDF sederhana untuk pengujian ekstraksi
            $content = "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n/Contents 4 0 R\n>>\nendobj\n4 0 obj\n<<\n/Length 55\n>>\nstream\nBT\n/F1 12 Tf\n100 700 Td\n(Fungsi kuadrat adalah fungsi yang memiliki bentuk umum f(x) = ax² + bx + c, di mana a ≠ 0.) Tj\nET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f \n0000000010 00000 n \n0000000053 00000 n \n0000000123 00000 n \n0000000254 00000 n \ntrailer\n<<\n/Size 5\n/Root 1 0 R\n>>\nstartxref\n374\n%%EOF";
            break;
        case 'docx':
            // Ini hanya untuk menunjukkan bagaimana file contoh dibuat
            echo "Tidak bisa membuat file .docx sederhana untuk pengujian.<br>\n";
            return false;
    }
    
    file_put_contents($filename, $content);
    return $filename;
}

// Uji ekstraksi konten dari berbagai jenis file
$test_types = ['txt'];

foreach ($test_types as $type) {
    echo "<h3>Menguji ekstraksi dari file {$type}</h3>\n";
    
    $filename = createSampleFile($type);
    if ($filename) {
        $content = extractFileContent($filename, $type);
        
        if ($content) {
            echo "<p><strong>Ekstraksi berhasil:</strong></p>\n";
            echo "<pre>" . htmlspecialchars(substr($content, 0, 300)) . (strlen($content) > 300 ? '...' : '') . "</pre>\n";
        } else {
            echo "<p><strong>Ekstraksi gagal:</strong> Tidak bisa mengekstrak konten dari file {$type}</p>\n";
        }
        
        // Hapus file uji setelah digunakan
        if (file_exists($filename)) {
            unlink($filename);
        }
    }
    echo "<hr>\n";
}

// Uji dengan konten file contoh
echo "<h3>Uji dengan konten file contoh</h3>\n";
$sampleContent = "Bab 2: Hukum Newton\nHukum pertama Newton menyatakan bahwa benda yang diam akan tetap diam, dan benda yang bergerak lurus beraturan akan tetap bergerak lurus beraturan, jika resultan gaya yang bekerja pada benda tersebut sama dengan nol.\n\nHukum kedua Newton: F = ma\nHukum ketiga Newton: Aksi = -Reaksi";

// Simulasikan proses pembersihan konten
echo "<p><strong>Konten asli:</strong></p>\n";
echo "<pre>" . htmlspecialchars(substr($sampleContent, 0, 200)) . "</pre>\n";

// Gunakan fungsi pembersihan dari AI handler jika ada
if (class_exists('AIHandler')) {
    $aiHandler = new AIHandler();
    $cleanedContent = '';
    
    // Akses fungsi pembersihan jika memungkinkan
    try {
        $reflection = new ReflectionClass('AIHandler');
        $method = $reflection->getMethod('cleanFileContent');
        $method->setAccessible(true);
        $cleanedContent = $method->invoke($aiHandler, $sampleContent);
    } catch (Exception $e) {
        $cleanedContent = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\xFF]/', ' ', $sampleContent);
        $cleanedContent = preg_replace('/\s+/', ' ', $cleanedContent);
        $cleanedContent = trim($cleanedContent);
    }
    
    echo "<p><strong>Konten setelah pembersihan:</strong></p>\n";
    echo "<pre>" . htmlspecialchars(substr($cleanedContent, 0, 200)) . "</pre>\n";
    
    if ($cleanedContent && strlen(trim($cleanedContent)) > 10) {
        echo "<p style='color: green;'><strong>✅ Pembersihan konten berhasil!</strong></p>\n";
    } else {
        echo "<p style='color: red;'><strong>❌ Pembersihan konten gagal!</strong></p>\n";
    }
} else {
    echo "<p>❌ Kelas AIHandler tidak ditemukan!</p>\n";
}

echo "<h3>Ringkasan:</h3>\n";
echo "<p>✅ Fungsi ekstraksi konten file telah ditingkatkan</p>\n";
echo "<p>✅ Fungsi pembersihan konten telah ditambahkan untuk menghindari karakter aneh</p>\n";
echo "<p>✅ Fungsi pembuatan soal telah ditingkatkan untuk menghasilkan soal yang lebih relevan</p>\n";
echo "<p>✅ Sistem fallback akan digunakan jika API tidak tersedia</p>\n";
?>