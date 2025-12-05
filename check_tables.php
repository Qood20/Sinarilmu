<?php
// check_tables.php - Script untuk memeriksa tabel-tabel yang ada

require_once 'config/database.php';

try {
    echo "Memeriksa tabel-tabel dalam database...\n\n";
    
    // Dapatkan semua tabel
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Daftar tabel dalam database:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
    echo "\nMemeriksa apakah tabel 'password_reset_tokens' ada...\n";
    
    if (in_array('password_reset_tokens', $tables)) {
        echo "✓ Tabel 'password_reset_tokens' ditemukan.\n";
        
        // Periksa struktur tabel
        $stmt = $pdo->query("DESCRIBE password_reset_tokens");
        $columns = $stmt->fetchAll();
        
        echo "\nStruktur tabel 'password_reset_tokens':\n";
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']}, {$column['Null']}, {$column['Key']})\n";
        }
    } else {
        echo "✗ Tabel 'password_reset_tokens' TIDAK DITEMUKAN.\n";
        echo "\nUntuk membuat tabel, jalankan perintah berikut di database Anda:\n";
        echo "CREATE TABLE password_reset_tokens (\n";
        echo "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        echo "    user_id INT NOT NULL,\n";
        echo "    token VARCHAR(255) NOT NULL UNIQUE,\n";
        echo "    expires_at TIMESTAMP NOT NULL,\n";
        echo "    used BOOLEAN DEFAULT FALSE,\n";
        echo "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
        echo "    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE\n";
        echo ");\n\n";
        
        echo "CREATE INDEX idx_password_reset_token ON password_reset_tokens(token);\n";
        echo "CREATE INDEX idx_password_reset_expires ON password_reset_tokens(expires_at);\n";
        echo "CREATE INDEX idx_password_reset_user_id ON password_reset_tokens(user_id);\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}