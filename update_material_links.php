<?php
// update_material_links.php - Script untuk memperbarui semua link materi ke handler aman

$files_to_update = [
    'dashboard/pages/matematika_kelas11.php',
    'dashboard/pages/matematika_kelas12.php',
    'dashboard/pages/fisika_kelas10.php',
    'dashboard/pages/fisika_kelas11.php',
    'dashboard/pages/fisika_kelas12.php',
    'dashboard/pages/kimia_kelas10.php',
    'dashboard/pages/kimia_kelas11.php',
    'dashboard/pages/kimia_kelas12.php',
    'dashboard/pages/biologi_kelas10.php',
    'dashboard/pages/biologi_kelas11.php',
    'dashboard/pages/biologi_kelas12.php',
];

$old_link_pattern = 'href="<?php echo $material[\'file_path\']; ?>" target="_blank"';
$new_link_pattern = 'href="view_material.php?file=<?php echo urlencode($material[\'file_path\']); ?>&id=<?php echo $material[\'id\']; ?>" target="_blank"';

$old_link_pattern_alt = 'href="<?php echo $material[\'file_path\']; ?>"';
$new_link_pattern_alt = 'href="view_material.php?file=<?php echo urlencode($material[\'file_path\']); ?>&id=<?php echo $material[\'id\']; ?>"';

foreach ($files_to_update as $file) {
    $full_path = __DIR__ . '/' . $file;
    
    if (file_exists($full_path)) {
        $content = file_get_contents($full_path);
        
        // Ganti pattern pertama
        $content = str_replace($old_link_pattern, $new_link_pattern, $content);
        
        // Ganti pattern alternatif
        $content = str_replace($old_link_pattern_alt, $new_link_pattern_alt, $content);
        
        file_put_contents($full_path, $content);
        echo "Updated: $file\n";
    } else {
        echo "File not found: $file\n";
    }
}

echo "Update completed!\n";
?>