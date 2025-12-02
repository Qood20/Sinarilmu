<?php
require_once 'config/database.php'; 

if($pdo) { 
    $stmt = $pdo->query('DESCRIBE materi_pelajaran'); 
    $columns = $stmt->fetchAll(); 
    echo "Struktur tabel materi_pelajaran:\n"; 
    foreach($columns as $col) { 
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")" . ($col['Null'] === 'YES' ? ", Null Diizinkan" : ", Tidak Boleh Null") . ($col['Key'] ? ", Key: " . $col['Key'] : "") . "\n"; 
    } 
} else { 
    echo 'Tidak bisa terhubung ke database'; 
}
?>