<?php
// repair_material_table.php - File untuk memperbaiki struktur tabel materi_pelajaran

require_once 'config/database.php';

try {
    $pdo->beginTransaction();
    
    // Cek apakah tabel materi_pelajaran ada
    $stmt = $pdo->query("SHOW TABLES LIKE 'materi_pelajaran'");
    if ($stmt->rowCount() == 0) {
        // Buat tabel jika tidak ada
        $createTableQuery = "
        CREATE TABLE materi_pelajaran (
            id INT AUTO_INCREMENT PRIMARY KEY,
            judul VARCHAR(255) NOT NULL,
            deskripsi TEXT,
            kelas ENUM('10', '11', '12') NOT NULL,
            mata_pelajaran ENUM('matematika', 'fisika', 'kimia', 'biologi', 'bahasa_indonesia', 'bahasa_inggris', 'sejarah', 'geografi', 'ekonomi', 'sosiologi', 'lainnya') NOT NULL,
            sub_topik VARCHAR(255),
            topik_spesifik VARCHAR(255),
            file_path VARCHAR(500) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_size INT,
            file_type VARCHAR(50),
            created_by INT NOT NULL,
            status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
        );
        
        CREATE INDEX idx_materi_pelajaran_kelas ON materi_pelajaran(kelas);
        CREATE INDEX idx_materi_pelajaran_mata_pelajaran ON materi_pelajaran(mata_pelajaran);
        CREATE INDEX idx_materi_pelajaran_created_by ON materi_pelajaran(created_by);
        ";
        
        $pdo->exec($createTableQuery);
        echo "✅ Tabel 'materi_pelajaran' berhasil dibuat\n";
    } else {
        // Periksa apakah kolom topik_spesifik sudah ada
        $stmt = $pdo->query("SHOW COLUMNS FROM materi_pelajaran LIKE 'topik_spesifik'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE materi_pelajaran ADD COLUMN topik_spesifik VARCHAR(255) AFTER sub_topik");
            echo "✅ Kolom 'topik_spesifik' berhasil ditambahkan ke tabel 'materi_pelajaran'\n";
        } else {
            echo "✅ Kolom 'topik_spesifik' sudah ada di tabel 'materi_pelajaran'\n";
        }
        echo "✅ Tabel 'materi_pelajaran' sudah ada\n";
    }
    
    // Cek juga tabel materi_pelajaran (lama) dan buat alias jika perlu
    $stmt = $pdo->query("SHOW TABLES LIKE 'materi_pelajaran'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabel 'materi_pelajaran' ditemukan\n";
    } else {
        // Jika tidak ada tabel dengan nama yang benar, coba cari dengan nama lain
        $checkStmt = $pdo->query("SHOW TABLES LIKE 'materi_pelajaran'");
        if ($checkStmt->rowCount() == 0) {
            $checkStmt = $pdo->query("SHOW TABLES LIKE 'materi_pelajaran'");
            if ($checkStmt->rowCount() > 0) {
                echo "ℹ️  Tabel dengan nama yang mirip ditemukan, mungkin perlu disesuaikan\n";
            }
        }
    }
    
    $pdo->commit();
    echo "\n🔧 Perbaikan struktur database selesai!\n";
    echo "\n📋 Langkah selanjutnya:\n";
    echo "   1. Restart Apache di XAMPP\n";
    echo "   2. Akses kembali aplikasi di http://localhost/Sinarilmu\n";
    echo "   3. Login ke admin panel dan coba upload materi baru\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "❌ Error: " . $e->getMessage();
}
?>