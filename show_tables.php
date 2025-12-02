<?php
require_once 'config/database.php'; 

if($pdo) { 
    $stmt = $pdo->query('SHOW TABLES'); 
    $tables = $stmt->fetchAll(); 
    foreach($tables as $table) { 
        echo reset($table) . "\n"; 
    } 
} else { 
    echo 'Tidak bisa terhubung ke database'; 
}
?>