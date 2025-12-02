<?php
// Perbaikan sistem Sinar Ilmu

// 1. Cek apakah semua direktori penting ada
$required_dirs = [
    'config',
    'includes',
    'dashboard',
    'dashboard/pages',
    'admin',
    'admin/pages',
    'pages',
    'uploads',
    'uploads/materials'
];

foreach ($required_dirs as $dir) {
    $full_path = __DIR__ . '/' . $dir;
    if (!is_dir($full_path)) {
        if (mkdir($full_path, 0755, true)) {
            echo "✓ Direktori {$dir} berhasil dibuat<br>";
        } else {
            echo "✗ Gagal membuat direktori {$dir}<br>";
        }
    } else {
        echo "- Direktori {$dir} sudah ada<br>";
    }
}

// 2. Cek apakah file penting ada
$required_files = [
    'index.php',
    'config/database.php',
    'includes/functions.php',
    'dashboard/index.php',
    'admin/index.php'
];

foreach ($required_files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "- File {$file} ditemukan<br>";
    } else {
        echo "✗ File {$file} TIDAK ditemukan<br>";
    }
}

echo "<br><strong>Perbaikan selesai. Silakan restart XAMPP dan coba akses kembali aplikasi.</strong>";

// 3. Cek apakah tabel materi_pelajaran ada di database
require_once 'config/database.php';
if ($pdo !== null) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'materi_pelajaran'");
        if ($stmt->rowCount() == 0) {
            // Buat tabel jika belum ada
            $sql = "
            CREATE TABLE IF NOT EXISTS materi_pelajaran (
                id INT AUTO_INCREMENT PRIMARY KEY,
                judul VARCHAR(255) NOT NULL,
                deskripsi TEXT,
                kelas ENUM('10', '11', '12') NOT NULL,
                mata_pelajaran ENUM('matematika', 'fisika', 'kimia', 'biologi', 'bahasa_indonesia', 'bahasa_inggris', 'sejarah', 'geografi', 'ekonomi', 'sosiologi', 'lainnya') NOT NULL,
                sub_topik VARCHAR(255),
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
            
            CREATE INDEX IF NOT EXISTS idx_materi_pelajaran_kelas ON materi_pelajaran(kelas);
            CREATE INDEX IF NOT EXISTS idx_materi_pelajaran_mata_pelajaran ON materi_pelajaran(mata_pelajaran);
            CREATE INDEX IF NOT EXISTS idx_materi_pelajaran_created_by ON materi_pelajaran(created_by);
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            echo "<br>Tabel 'materi_pelajaran' berhasil dibuat.";
        } else {
            echo "<br>Tabel 'materi_pelajaran' sudah ada.";
        }
    } catch (Exception $e) {
        echo "<br>Error saat memeriksa/membuat tabel: " . $e->getMessage();
    }
}
?>