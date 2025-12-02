<?php
require_once 'config/database.php';

try {
    // Check if sub_topik column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM materi_pelajaran LIKE 'sub_topik'");
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        // Add the sub_topik column
        $pdo->exec("ALTER TABLE materi_pelajaran ADD COLUMN sub_topik VARCHAR(255) AFTER mata_pelajaran");
        echo "Column 'sub_topik' added successfully!";
    } else {
        echo "Column 'sub_topik' already exists.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}