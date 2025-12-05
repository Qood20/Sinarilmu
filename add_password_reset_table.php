<?php
// add_password_reset_table.php - Script untuk menambahkan tabel password_reset_tokens ke database

require_once 'config/database.php';

try {
    // Periksa apakah tabel sudah ada
    $stmt = $pdo->query("SHOW TABLES LIKE 'password_reset_tokens'");
    $tableExists = $stmt->fetch();

    if ($tableExists) {
        echo "Tabel password_reset_tokens sudah ada di database.\n";
    } else {
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
        echo "Tabel password_reset_tokens berhasil dibuat.\n";

        // Buat indeks
        $pdo->exec("CREATE INDEX idx_password_reset_token ON password_reset_tokens(token);");
        $pdo->exec("CREATE INDEX idx_password_reset_expires ON password_reset_tokens(expires_at);");
        $pdo->exec("CREATE INDEX idx_password_reset_user_id ON password_reset_tokens(user_id);");
        echo "Indeks berhasil dibuat.\n";
    }

    echo "Proses selesai.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}