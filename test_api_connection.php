<?php
// test_api_connection.php - File uji koneksi ke API Google AI

require_once 'config/api_config.php';

echo "Menguji koneksi ke Google AI API...\n\n";

// Cek apakah cURL tersedia
if (!function_exists('curl_init')) {
    echo "cURL tidak tersedia di sistem Anda.\n";
} else {
    echo "cURL tersedia.\n";
}

// Cek apakah file_get_contents dengan context tersedia
if (in_array('http', stream_get_wrappers())) {
    echo "stream_get_wrappers (http) tersedia.\n";
} else {
    echo "stream_get_wrappers (http) tidak tersedia.\n";
}

// Coba koneksi ke endpoint Google AI
$testUrl = GOOGLE_AI_BASE_URL . '/gemini-pro:generateContent?key=' . GOOGLE_AI_API_KEY;

$testData = [
    'contents' => [
        [
            'parts' => [
                [
                    'text' => 'Halo, ini hanya tes koneksi.'
                ]
            ]
        ]
    ]
];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

if ($result === false) {
    echo "Kesalahan cURL: " . $error . "\n";
} else {
    echo "Kode HTTP: " . $httpCode . "\n";
    $decoded = json_decode($result, true);
    
    if ($httpCode == 200) {
        echo "Koneksi berhasil! API merespons dengan benar.\n";
    } elseif ($httpCode == 400) {
        echo "Koneksi berhasil, tetapi permintaan tidak valid (mungkin prompt terlalu pendek).\n";
    } elseif ($httpCode == 403) {
        echo "API key tidak valid atau tidak diotorisasi.\n";
        if (isset($decoded['error'])) {
            echo "Detail error: " . $decoded['error']['message'] . "\n";
        }
    } else {
        echo "Kesalahan: " . $result . "\n";
    }
}

echo "\nSelesai.\n";
?>