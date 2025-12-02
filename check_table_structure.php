<?php
// check_table_structure.php - Script untuk memeriksa struktur tabel

require_once 'config/database.php';

if ($pdo) {
    echo "Koneksi database berhasil.\n\n";
    
    try {
        $stmt = $pdo->query('SHOW COLUMNS FROM materi_pelajaran');
        $columns = $stmt->fetchAll();
        
        echo "Struktur tabel materi_pelajaran:\n";
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']})\n";
        }
        
        echo "\nJumlah kolom: " . count($columns) . "\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "Gagal terhubung ke database.\n";
}
?>