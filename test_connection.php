<?php
echo "PHP is working correctly!<br>";
echo "Current directory: " . __DIR__ . "<br>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// Test database connection
require_once 'config/database.php';

if ($pdo !== null) {
    echo "Database connection successful!<br>";
    
    // Test if required tables exist
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() > 0) {
            echo "Users table exists<br>";
        } else {
            echo "Users table does NOT exist<br>";
        }
        
        $stmt = $pdo->query("SHOW TABLES LIKE 'materi_pelajaran'");
        if ($stmt->rowCount() > 0) {
            echo "Materi_pelajaran table exists<br>";
        } else {
            echo "Materi_pelajaran table does NOT exist<br>";
        }
    } catch (Exception $e) {
        echo "Error checking tables: " . $e->getMessage() . "<br>";
    }
} else {
    echo "Database connection failed<br>";
}
?>