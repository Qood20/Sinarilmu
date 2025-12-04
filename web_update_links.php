<?php
// web_update_links.php - Script untuk memperbarui link di semua halaman materi

// Set content type untuk output
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Memperbarui Link Materi ke Handler Aman</h1>\n";

$files_to_update = [
    'dashboard/pages/matematika_kelas11.php',
    'dashboard/pages/matematika_kelas12.php',
    'dashboard/pages/fisika_kelas11.php',
    'dashboard/pages/fisika_kelas12.php',
    'dashboard/pages/kimia_kelas10.php',
    'dashboard/pages/kimia_kelas11.php',
    'dashboard/pages/kimia_kelas12.php',
    'dashboard/pages/biologi_kelas10.php',
    'dashboard/pages/biologi_kelas11.php',
    'dashboard/pages/biologi_kelas12.php',
];

// Counter untuk melacak perubahan
$updated_count = 0;

foreach ($files_to_update as $file) {
    $full_path = __DIR__ . '/' . $file;
    
    if (file_exists($full_path)) {
        $content = file_get_contents($full_path);
        $original_content = $content;
        
        // Pattern untuk link download yang lama
        $pattern = '/href="(?:\s*)\<\?php\s+echo\s+\$material\[\'file_path\'\];\s+\?\>"(?:\s*)target="_blank"/';
        
        // Ganti dengan link baru
        $replacement = 'href="view_material.php?file=<?php echo urlencode($material[\'file_path\']); ?>&id=<?php echo $material[\'id\']; ?>" target="_blank"';
        
        $updated_content = preg_replace($pattern, $replacement, $content);
        
        // Cek apakah ada perubahan
        if ($updated_content !== $original_content) {
            file_put_contents($full_path, $updated_content);
            echo "<p style='color: green;'>✓ Updated: $file</p>\n";
            $updated_count++;
        } else {
            echo "<p style='color: blue;'>→ No changes needed: $file</p>\n";
        }
    } else {
        echo "<p style='color: red;'>✗ File not found: $file</p>\n";
    }
}

echo "<h2>Proses Selesai!</h2>\n";
echo "<p>Total file yang diperbarui: $updated_count dari " . count($files_to_update) . " file</p>\n";
echo "<p><a href='javascript:window.location.reload();'>Refresh halaman</a> untuk menjalankan lagi.</p>\n";

// Hapus file ini setelah selesai untuk alasan keamanan
if (isset($_GET['cleanup']) && $_GET['cleanup'] == '1') {
    unlink(__FILE__);
    echo "<p>File ini telah dihapus untuk alasan keamanan.</p>\n";
}
?>