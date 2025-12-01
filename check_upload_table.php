<?php
require_once 'includes/functions.php';

global $pdo;

try {
    $stmt = $pdo->query('DESCRIBE upload_files');
    $columns = $stmt->fetchAll();
    
    echo "Struktur tabel upload_files:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>