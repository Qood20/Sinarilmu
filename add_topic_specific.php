<?php
// File untuk menambah kolom topik_spesifik ke tabel materi_pelajaran

require_once 'config/database.php';

try {
    // Periksa apakah kolom topik_spesifik sudah ada
    $stmt = $pdo->query("SHOW COLUMNS FROM materi_pelajaran LIKE 'topik_spesifik'");
    $column = $stmt->fetch();
    
    if (!$column) {
        // Tambahkan kolom topik_spesifik
        $pdo->exec("ALTER TABLE materi_pelajaran ADD COLUMN topik_spesifik VARCHAR(255) AFTER sub_topik");
        echo "Kolom 'topik_spesifik' berhasil ditambahkan ke tabel 'materi_pelajaran'";
    } else {
        echo "Kolom 'topik_spesifik' sudah ada";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>