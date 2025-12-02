<?php
// File untuk menampilkan semua model yang tersedia di OpenRouter
require_once 'config/config.php';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://openrouter.ai/api/v1/models");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . OPENROUTER_API_KEY
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

if ($error) {
    echo "cURL Error: $error\n";
} elseif ($http_code === 200) {
    $data = json_decode($response, true);
    echo "<h2>Model-model yang tersedia di OpenRouter:</h2>\n";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr><th>ID Model</th><th>Nama</th><th>Slug</th><th>Tipe</th></tr>\n";
    
    foreach ($data['data'] as $model) {
        $id = htmlspecialchars($model['id']);
        $name = htmlspecialchars($model['name'] ?? 'N/A');
        $slug = htmlspecialchars($model['canonical_slug'] ?? 'N/A');
        $type = isset($model['pricing']) ? ($model['pricing']['prompt'] < 0.01 ? 'Gratis/Sangat Murah' : 'Berbayar') : 'Tidak Diketahui';
        
        echo "<tr>";
        echo "<td>$id</td>";
        echo "<td>$name</td>";
        echo "<td>$id</td>";  // Use the ID as the slug
        echo "<td>$type</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
} else {
    echo "HTTP Error: $http_code\n";
    echo "Response: " . $response . "\n";
}
curl_close($ch);
?>