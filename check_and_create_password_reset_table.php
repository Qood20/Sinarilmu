<?php
// check_and_create_password_reset_table.php - Script untuk memeriksa dan membuat tabel password_reset_tokens jika belum ada

require_once 'config/database.php';

try {
    echo "Memeriksa koneksi database...\n";
    
    // Periksa apakah tabel sudah ada
    $stmt = $pdo->query("SHOW TABLES LIKE 'password_reset_tokens'");
    $tableExists = $stmt->fetch();

    if ($tableExists) {
        echo "✓ Tabel password_reset_tokens sudah ada di database.\n";
    } else {
        echo "Membuat tabel password_reset_tokens...\n";
        
        // Buat tabel password_reset_tokens
        $sql = "CREATE TABLE password_reset_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(255) NOT NULL UNIQUE,
            expires_at TIMESTAMP NOT NULL,
            used BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );";
        
        $pdo->exec($sql);
        echo "✓ Tabel password_reset_tokens berhasil dibuat.\n";
        
        // Buat indeks
        $pdo->exec("CREATE INDEX idx_password_reset_token ON password_reset_tokens(token);");
        $pdo->exec("CREATE INDEX idx_password_reset_expires ON password_reset_tokens(expires_at);");
        $pdo->exec("CREATE INDEX idx_password_reset_user_id ON password_reset_tokens(user_id);");
        echo "✓ Indeks berhasil dibuat.\n";
    }
    
    // Test the password reset functionality
    echo "\nMenguji fungsi request_password_reset...\n";
    
    // Include functions after database connection is established
    require_once 'includes/functions.php';
    
    // Test with a valid email
    $result = request_password_reset('budi@example.com');
    if ($result) {
        echo "✓ Fungsi request_password_reset bekerja dengan baik.\n";
    } else {
        echo "✗ Terjadi masalah dengan fungsi request_password_reset.\n";
    }
    
    echo "Proses selesai.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}